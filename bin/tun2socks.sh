#!/bin/bash

# Tun2socks Wrapper
# by Lutfa Ilham
# v1.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
TUN2SOCKS_MODE="$(grep 'legacy":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
TUN_DEV="$(grep 'dev":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
TUN_ADDRESS="$(grep 'address":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
TUN_NETMASK="$(grep 'netmask":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
TUN_GATEWAY="$(grep 'gateway":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
TUN_MTU="$(grep 'mtu":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
SOCKS_IP="$(grep 'ip":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')"
SOCKS_PORT="$(grep 'port":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')"
SOCKS_SERVER="${SOCKS_IP}:${SOCKS_PORT}"
UDPGW_IP="$(grep 'ip":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '2p')"
UDPGW_PORT="$(grep 'port":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '2p')"
UDPGW="${UDPGW_IP}:${UDPGW_PORT}"
GATEWAY="$(ip route | grep -v tun | awk '/default/ { print $3 }')"
SERVER_IP="$(grep 'server":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')"
readarray -t PROXY_IPS < <(jq -r '.proxy_servers[]' < ${SYSTEM_CONFIG})
readarray -t DNS_IPS < <(jq -r '.dns_servers[]' < ${SYSTEM_CONFIG})
ROUTE_LOG="${LIBERNET_DIR}/log/route.log"
DEFAULT_ROUTE="$(ip route show | grep default)"

function init_tun_dev {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Tun2socks: initializing tun device"
  # remove tun dev if already exist
  if ifconfig "${TUN_DEV}" > /dev/null 2>&1; then
    ifconfig ${TUN_DEV} down
    ip tuntap del dev ${TUN_DEV} mode tun
  fi
  # finally init tun dev
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
    screen -AmdS go-tun2socks go-tun2socks -loglevel none -proxyServer "${SOCKS_SERVER}" -proxyType socks -tunName "${TUN_DEV}" -tunAddr "${TUN_ADDRESS}" -tunGw "${TUN_GATEWAY}" -tunMask "${TUN_NETMASK}"
  else
    screen -AmdS badvpn-tun2socks badvpn-tun2socks --loglevel 0 --tundev ${TUN_DEV} --netif-ipaddr ${TUN_ADDRESS} --netif-netmask ${TUN_NETMASK} --socks-server-addr ${SOCKS_SERVER} --udpgw-remote-server-addr "${UDPGW}"
  fi
  # removing default route
  echo ${DEFAULT_ROUTE} > ${ROUTE_LOG} \
    && ip route del ${DEFAULT_ROUTE}
  # add default route to tun2socks
  ip route add default via ${TUN_ADDRESS} metric 6
  echo -e "Tun2socks started!"
  # write connected time
  "${LIBERNET_DIR}/bin/log.sh" -c "$(date +"%s")"
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
  ip route add $(cat "${ROUTE_LOG}") \
    && rm -rf "${ROUTE_LOG}"
  # remove default route to tun2socks
  ip route del default via ${TUN_ADDRESS} metric 6
  echo -e "Tun2socks stopped!"
}

function route_add_ip {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Tun2socks: routing server, proxy and DNS IPs"
  ip route add ${SERVER_IP} via ${GATEWAY} metric 4 &
  for IP in "${PROXY_IPS[@]}"; do
    ip route add ${IP} via ${GATEWAY} metric 4 &
  done
  for IP in "${DNS_IPS[@]}"; do
    ip route add ${IP} via ${GATEWAY} metric 4 &
  done
  echo -e "Routes initialized!"
}

function route_del_ip {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Tun2socks: removing routes"
  for IP in "${DNS_IPS[@]}"; do
    ip route del ${IP} &
  done
  for IP in "${PROXY_IPS[@]}"; do
    ip route del ${IP} &
  done
  ip route del ${SERVER_IP} &
  echo -e "Routes removed!"
}

function usage() {
  cat <<EOF
Usage:
  -i  Initialize tun device
  -d  Destroy tun device
  -y  Route server, proxy & dns
  -z  Remove route server, proxy & dns
  -r  Run tun2socks
  -s  Stop tun2socks
EOF
}

case "${1}" in
  -v)
    # start tun2socks service
    init_tun_dev
    route_add_ip
    start_tun2socks
    ;;
  -w)
    # stop tun2socks service
    echo -e "Stopping Tun2socks service ..."
    stop_tun2socks
    echo -e "Removing routes ..."
    route_del_ip
    echo -e "Removing tun device ..."
    destroy_tun_dev
    ;;
  -i)
    init_tun_dev
    ;;
  -d)
    destroy_tun_dev
    ;;
  -r)
    start_tun2socks
    ;;
  -s)
    stop_tun2socks
    ;;
  -y)
    route_add_ip
    ;;
  -z)
    route_del_ip
    ;;
  *)
    usage
    ;;
esac
