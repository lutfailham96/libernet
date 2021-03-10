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
ROUTE_LOG="${LIBERNET_DIR}/log/route.log"
DEFAULT_ROUTE="$(ip route show | grep default)"

function init_tun_dev {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Tun2socks: initializing tun device"
  ip tuntap add dev ${TUN_DEV} mode tun
  ifconfig ${TUN_DEV} mtu ${TUN_MTU}
  echo -e "Tun device initialized!"
}

function destroy_tun_dev {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Tun2socks: removing tun device"
  ifconfig ${TUN_DEV} down
  ip tuntap del dev ${TUN_DEV} mode tun
  echo -e "Tun device removed!"
}

function start_tun2socks {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Starting tun2socks service"
  ifconfig ${TUN_DEV} ${TUN_GATEWAY} netmask ${TUN_NETMASK} up
  if [[ $TUN2SOCKS_MODE == "false" ]]; then
    screen -AmdS go-tun2socks go-tun2socks -loglevel error -proxyServer "${SOCKS_SERVER}" -proxyType socks -tunName "${TUN_DEV}" -tunAddr "${TUN_ADDRESS}" -tunGw "${TUN_GATEWAY}" -tunMask "${TUN_NETMASK}"
  else
    screen -AmdS badvpn-tun2socks badvpn-tun2socks --tundev ${TUN_DEV} --netif-ipaddr ${TUN_ADDRESS} --netif-netmask ${TUN_NETMASK} --socks-server-addr ${SOCKS_SERVER} --udpgw-remote-server-addr "${UDPGW}"
  fi
  # change default route metric to 8
  echo ${DEFAULT_ROUTE} > ${ROUTE_LOG} \
    && ip route del ${DEFAULT_ROUTE} \
    && ip route add ${DEFAULT_ROUTE} metric 8
  # add default route to tun2socks
  route add default gw ${TUN_ADDRESS} metric 6
  echo -e "Tun2socks started!"
}

function stop_tun2socks {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Stopping tun2socks service"
  if [[ $TUN2SOCKS_MODE == "false" ]]; then
    kill $(screen -list | grep go-tun2socks | awk -F '[.]' {'print $1'})
  else
    kill $(screen -list | grep badvpn-tun2socks | awk -F '[.]' {'print $1'})
  fi
  # recover default route
  ip route del $(cat "${ROUTE_LOG}") metric 8 \
    && ip route add $(cat "${ROUTE_LOG}") \
    && rm -rf "${ROUTE_LOG}"
  # remove default route to tun2socks
  route del default gw ${TUN_ADDRESS} metric 6
  echo -e "Tun2socks stopped!"
}

function route_add_ip {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Tun2socks: routing server, proxy and DNS IPs"
  route add ${SERVER_IP} gw ${GATEWAY} metric 4 &
  for IP in "${PROXY_IPS[@]}"; do
    route add ${IP} gw ${GATEWAY} metric 4 &
  done
  for IP in "${DNS_IPS[@]}"; do
    route add ${IP} gw ${GATEWAY} metric 4 &
  done
  echo -e "Routes initialized!"
}

function route_del_ip {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Tun2socks: removing routes"
  for IP in "${DNS_IPS[@]}"; do
    route del ${IP} gw ${GATEWAY} metric 4 &
  done
  for IP in "${PROXY_IPS[@]}"; do
    route del ${IP} gw ${GATEWAY} metric 4 &
  done
  route del ${SERVER_IP} gw ${GATEWAY} metric 4 &
  echo -e "Routes removed!"
}

while getopts ":idrsyzvw" opt; do
  case ${opt} in
  v)
    # start tun2socks service
    init_tun_dev \
      && route_add_ip \
      && start_tun2socks
    ;;
  w)
    # stop tun2socks service
    echo -e "Stopping Tun2socks service ..."
    stop_tun2socks
    echo -e "Removing routes ..."
    route_del_ip
    echo -e "Removing tun device ..."
    destroy_tun_dev
    ;;
  i)
    init_tun_dev
    ;;
  d)
    destroy_tun_dev
    ;;
  r)
    start_tun2socks
    ;;
  s)
    stop_tun2socks
    ;;
  y)
    route_add_ip
    ;;
  z)
    route_del_ip
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