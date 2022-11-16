#!/usr/bin/env bash

# Trojan Wrapper
# by Lutfa Ilham
# v1.0.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SERVICE_NAME="Trojan"
SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
TROJAN_PROFILE=$(grep 'trojan":' "${SYSTEM_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g')
TROJAN_CONFIG="${LIBERNET_DIR}/config/trojan/${TROJAN_PROFILE}.json"
TROJAN_CONFIG="/opt/libernet/config/trojan/tetew.json"
PROXY_ENABLED=$(grep 'proxy_enabled":' "${TROJAN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g')
PROXY_TYPE=$(grep 'proxy_type":' "${TROJAN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g')
PROXY_SNI=$(grep 'proxy_sni":' "${TROJAN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g')
LISTEN_IP=$(grep 'remote_addr":' "${TROJAN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')
LISTEN_PORT=$(grep 'remote_port":' "${TROJAN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')
REMOTE_IP=$(grep 'proxy_remote_ip":' "${TROJAN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')
REMOTE_PORT=$(grep 'proxy_remote_port":' "${TROJAN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')

run() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Config: ${TROJAN_PROFILE}, Mode: ${SERVICE_NAME}"
  "${LIBERNET_DIR}/bin/log.sh" -w "Starting ${SERVICE_NAME} service"
  echo -e "Starting ${SERVICE_NAME} service ..."
  if [ "${PROXY_ENABLED}" = 'true' ]; then
    screen -AmdS trojan-go-proxy bash -c "while true; do \"${LIBERNET_DIR}/bin/go-http\" -tls -sni \"${PROXY_SNI}\" -l \"${LISTEN_IP}:${LISTEN_PORT}\" -r \"${REMOTE_IP}:${REMOTE_PORT}\" -k \"${PROXY_TYPE}\"; sleep 3; done" \
      && screen -AmdS trojan-go-client bash -c "while true; do trojan-go -config \"${TROJAN_CONFIG}\"; sleep 3; done" \
      && echo -e "${SERVICE_NAME} service started!"
  else
    screen -AmdS trojan-client bash -c "while true; do trojan-go -config \"${TROJAN_CONFIG}\"; sleep 3; done" \
      && echo -e "${SERVICE_NAME} service started!"
  fi
}

stop() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Stopping ${SERVICE_NAME} service"
  echo -e "Stopping ${SERVICE_NAME} service ..."
  kill "$(screen -list | grep trojan-client | awk -F '[.]' '{print $1}')" 2> /dev/null
  kill "$(screen -list | grep trojan-go-proxy | awk -F '[.]' '{print $1}')" 2> /dev/null
  kill "$(screen -list | grep trojan-go-client | awk -F '[.]' '{print $1}')" 2> /dev/null
  killall trojan-go 2> /dev/null
  echo -e "${SERVICE_NAME} service stopped!"
}

usage() {
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
