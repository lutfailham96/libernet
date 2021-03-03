#!/bin/bash

# Tun2socks Wrapper
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
TUN2SOCKS_MODE="$(jq -r '.tun2socks.legacy' < ${SYSTEM_CONFIG})"
TUN_DEV="$(jq -r '.tun2socks.dev' < ${SYSTEM_CONFIG})"
TUN_ADDRESS="$(jq -r '.tun2socks.address' < ${SYSTEM_CONFIG})"
TUN_NETMASK="$(jq -r '.tun2socks.netmask' < ${SYSTEM_CONFIG})"
TUN_GATEWAY="$(jq -r '.tun2socks.gateway' < ${SYSTEM_CONFIG})"
TUN_MTU="$(jq -r '.tun2socks.mtu' < ${SYSTEM_CONFIG})"
SOCKS_SERVER="$(jq -r '.tun2socks.socks.ip' < ${SYSTEM_CONFIG}):$(jq -r '.tun2socks.socks.port' < ${SYSTEM_CONFIG})"
UDPGW="$(jq -r '.tun2socks.udpgw.ip' < ${SYSTEM_CONFIG}):$(jq -r '.tun2socks.udpgw.port' < ${SYSTEM_CONFIG})"
GATEWAY="$(ip route | grep -v tun | awk '/default/ { print $3 }')"
SERVER_IP="$(jq -r '.server' < ${SYSTEM_CONFIG})"
readarray -t PROXY_IPS < <(jq -r '.proxy_servers[]' < ${SYSTEM_CONFIG})
readarray -t DNS_IPS < <(jq -r '.dns_servers[]' < ${SYSTEM_CONFIG})

function init_tun_dev {
  ip tuntap add dev ${TUN_DEV} mode tun
  ifconfig ${TUN_DEV} mtu ${TUN_MTU}
}

function destroy_tun_dev {
  ifconfig ${TUN_DEV} down
  ip tuntap del dev ${TUN_DEV} mode tun
}

function start_tun2socks {
  ifconfig ${TUN_DEV} ${TUN_GATEWAY} netmask ${TUN_NETMASK} up
  if [[ $TUN2SOCKS_MODE == "false" ]]; then
    screen -AmdS go-tun2socks go-tun2socks -loglevel error -proxyServer "${SOCKS_SERVER}" -proxyType socks -tunName "${TUN_DEV}" -tunAddr "${TUN_ADDRESS}" -tunGw "${TUN_GATEWAY}" -tunMask "${TUN_NETMASK}"
  else
    screen -AmdS badvpn-tun2socks badvpn-tun2socks --tundev ${TUN_DEV} --netif-ipaddr ${TUN_ADDRESS} --netif-netmask ${TUN_NETMASK} --socks-server-addr ${SOCKS_SERVER} --udpgw-remote-server-addr "${UDPGW}"
  fi
  route add default gw ${TUN_ADDRESS} metric 6
}

function stop_tun2socks {
  if [[ $TUN2SOCKS_MODE == "false" ]]; then
    kill $(screen -list | grep go-tun2socks | awk -F '[.]' {'print $1'})
  else
    kill $(screen -list | grep badvpn-tun2socks | awk -F '[.]' {'print $1'})
  fi
  route del default gw ${TUN_ADDRESS} metric 6
}

function route_add_ip {
  route add ${SERVER_IP} gw ${GATEWAY} metric 4
  for IP in "${PROXY_IPS[@]}"; do
    route add ${IP} gw ${GATEWAY} metric 4
  done
  for IP in "${DNS_IPS[@]}"; do
    route add ${IP} gw ${GATEWAY} metric 4
  done
}

function route_del_ip {
  for IP in "${DNS_IPS[@]}"; do
    route del ${IP} gw ${GATEWAY} metric 4
  done
  for IP in "${PROXY_IPS[@]}"; do
    route del ${IP} gw ${GATEWAY} metric 4
  done
  route del ${SERVER_IP} gw ${GATEWAY} metric 4
}

while getopts ":idrsyz" opt; do
  case ${opt} in
  i)
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Tun2socks: initializing tun device"
    init_tun_dev > /dev/null 2>&1
    echo -e "Tun device initialized!"
    ;;
  d)
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Tun2socks: removing tun device"
    destroy_tun_dev > /dev/null 2>&1
    echo -e "Tun device removed!"
    ;;
  r)
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Starting tun2socks service"
    start_tun2socks > /dev/null 2>&1
    echo -e "Tun2socks started!"
    ;;
  s)
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Stopping tun2socks service"
    stop_tun2socks > /dev/null 2>&1
    echo -e "Tun2socks stopped!"
    ;;
  y)
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Tun2socks: routing server, proxy and DNS IPs"
    route_add_ip > /dev/null 2>&1
    echo -e "Routes initialized!"
    ;;
  z)
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Tun2socks: removing routes"
    route_del_ip > /dev/null 2>&1
    echo -e "Routes removed!"
    ;;
  *)
    echo -e "Usage:"
    echo -e "\t-i\tInitialize tun device"
    echo -e "\t-d\tDestroy tun device"
    echo -e "\t-y\tRoute add server & dns"
    echo -e "\t-z\tRoute del server & dns"
    echo -e "\t-r\tRun tun2socks"
    echo -e "\t-s\tStop tun2socks"
    ;;
  esac
done