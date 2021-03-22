#!/bin/bash

# Shadowsocks Wrapper
# by Lutfa Ilham
# v1.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SERVICE_NAME="Shadowsocks"
SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
SHADOWSOCKS_PROFILE="$(grep 'shadowsocks":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
SHADOWSOCKS_CONFIG="${LIBERNET_DIR}/bin/config/shadowsocks/${SHADOWSOCKS_PROFILE}.json"

function run() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Config: ${SHADOWSOCKS_PROFILE}, Mode: ${SERVICE_NAME}"
  "${LIBERNET_DIR}/bin/log.sh" -w "Starting ${SERVICE_NAME} service"
  echo -e "Starting ${SERVICE_NAME} service ..."
  screen -AmdS ss-client bash -c "while true; do ss-local -c \"${SHADOWSOCKS_CONFIG}\"; sleep 3; done" \
    && echo -e "${SERVICE_NAME} service started!"
}

function stop() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Stopping ${SERVICE_NAME} service"
  echo -e "Stopping ${SERVICE_NAME} service ..."
  kill $(screen -list | grep ss-client | awk -F '[.]' {'print $1'})
  killall ss-local
  # kill plugins
  killall obfs-local
  killall ck-client
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
