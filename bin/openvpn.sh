#!/bin/bash

# OpenVPN Wrapper
# by Lutfa Ilham
# v1.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SERVICE_NAME="OpenVPN"
SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
OPENVPN_PROFILE="$(grep 'openvpn":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
OPENVPN_CONFIG="${LIBERNET_DIR}/bin/config/openvpn/${OPENVPN_PROFILE}.json"
OPENVPN_OVPN="$(grep 'ovpn":' ${OPENVPN_CONFIG} | awk -F ': "' '{ print $2 }' | sed 's/",//g')"
OPENVPN_CFG="${LIBERNET_DIR}/bin/config/openvpn/${OPENVPN_PROFILE}.ovpn"
OPENVPN_CRED="${LIBERNET_DIR}/bin/config/openvpn/${OPENVPN_PROFILE}.txt"
OPENVPN_HOST="$(echo -e ${OPENVPN_OVPN} | grep 'remote ' | awk '{print $2}')"
OPENVPN_PORT="$(echo -e ${OPENVPN_OVPN} | grep 'remote ' | awk '{print $3}')"
OPENVPN_USER="$(grep 'username":' ${OPENVPN_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
OPENVPN_PASS="$(grep 'password":' ${OPENVPN_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
OPENVPN_SNI="$(grep 'sni":' ${OPENVPN_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
ENABLE_SSL="$(grep 'ssl":' ${OPENVPN_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
TUN_DEV="$(grep 'dev":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
GATEWAY="$(ip route | grep -v tun | awk '/default/ { print $3 }')"
SERVER_IP="$(ping  -4Ac 1 -W 1 ${OPENVPN_HOST} | awk '{print $3}' | sed -n '1p' | sed 's/(//g; s/)//g; s/://g')"
readarray -t PROXY_IPS < <(jq -r '.proxy_servers[]' < ${SYSTEM_CONFIG})
readarray -t DNS_IPS < <(jq -r '.dns_servers[]' < ${SYSTEM_CONFIG})
ROUTE_LOG="${LIBERNET_DIR}/log/route.log"
DEFAULT_ROUTE="$(ip route show | grep default | grep -v ${TUN_DEV})"

function configure() {
  # generate ovpn
  echo -e "${OPENVPN_OVPN}" > "${OPENVPN_CFG}"
  # change tun dev
  sed -i "s/dev .*/dev ${TUN_DEV}/g" "${OPENVPN_CFG}"
  # change host port
  sed -i "s/remote .*/remote ${OPENVPN_HOST} ${OPENVPN_PORT}/g" "${OPENVPN_CFG}"
  # auth
  echo -e "${OPENVPN_USER}\n${OPENVPN_PASS}" > "${OPENVPN_CRED}"
  sed -i "s/auth-user-pass.*/auth-user-pass \"$(echo ${OPENVPN_CRED} | sed 's/\//\\\//g')\"/g" "${OPENVPN_CFG}"
  # remove up & down resolve
  sed -i "/up .*/d" "${OPENVPN_CFG}"
  sed -i "/down .*/d" "${OPENVPN_CFG}"
}

function route() {
  case "${1}" in
    -a)
      # route server
      ip route add "${SERVER_IP}" via "${GATEWAY}" metric 4
      # route proxy & dns
      for IP in "${PROXY_IPS[@]}"; do
        ip route add ${IP} via ${GATEWAY} metric 4 &
      done
      for IP in "${DNS_IPS[@]}"; do
        ip route add ${IP} via ${GATEWAY} metric 4 &
      done
      ;;
    -d)
      # recover default route, from service
      ip route add $(cat "${ROUTE_LOG}") \
        && rm -rf "${ROUTE_LOG}"
      # remove proxy & dns route
      for IP in "${DNS_IPS[@]}"; do
       ip route del ${IP}
      done
      for IP in "${PROXY_IPS[@]}"; do
        ip route del ${IP}
      done
      # remove server route
      ip route del "${SERVER_IP}"
      ;;
  esac
}

function run() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Config: ${OPENVPN_PROFILE}, Mode: ${SERVICE_NAME}"
  route -a
  configure
  if [[ "${ENABLE_SSL}" == 'true' ]]; then
    # change host port to stunnel
     sed -i "s/remote .*/remote 127.0.0.1 10443/g" "${OPENVPN_CFG}"
    "${LIBERNET_DIR}/bin/stunnel.sh" -r "openvpn" "${OPENVPN_PROFILE}" "${OPENVPN_HOST}" "${OPENVPN_PORT}" "${OPENVPN_SNI}"
  fi
  "${LIBERNET_DIR}/bin/log.sh" -w "Starting ${SERVICE_NAME} service"
  echo -e "Starting ${SERVICE_NAME} service ..."
  screen -AmdS openvpn bash -c "while true; do openvpn \"${OPENVPN_CFG}\" > \"${LIBERNET_DIR}/log/openvpn.log\"; sleep 3; done" \
    && echo -e "${SERVICE_NAME} service started!"
}

function stop() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Stopping ${SERVICE_NAME} service"
  echo -e "Stopping ${SERVICE_NAME} service ..."
  kill $(screen -list | grep openvpn | awk -F '[.]' {'print $1'})
  killall openvpn
  if [[ "${ENABLE_SSL}" == 'true' ]]; then
    "${LIBERNET_DIR}/bin/stunnel.sh" -s
  fi
  # remove openvpn log file
  rm "${LIBERNET_DIR}/log/openvpn.log"
  route -d
  echo -e "${SERVICE_NAME} service stopped!"
}

function usage() {
  cat <<EOF
Usage:
  -r  Run ${SERVICE_NAME} service
  -s  Stop ${SERVICE_NAME} service
EOF
}

case "${1}" in
  -r)
    run
  ;;
  -s)
    stop
  ;;
  *)
    usage
  ;;
esac
