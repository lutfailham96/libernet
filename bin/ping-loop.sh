#!/usr/bin/env bash

# PING Loop Wrapper
# by Lutfa Ilham
# v1.0.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SERVICE_NAME="PING loop"
#SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
INTERVAL="3"
HOST="bing.com"

http_ping() {
  httping -qi "${INTERVAL}" -t "${INTERVAL}" "${HOST}"
}

loop() {
  while true; do
    http_ping
    sleep $INTERVAL
  done
}

run() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Starting ${SERVICE_NAME} service"
  echo -e "Starting ${SERVICE_NAME} service ..."
  screen -AmdS ping-loop "${LIBERNET_DIR}/bin/ping-loop.sh" -l \
    && echo -e "${SERVICE_NAME} service started!"
}

stop() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Stopping ${SERVICE_NAME} service"
  echo -e "Stopping ${SERVICE_NAME} service ..."
  kill "$(screen -list | grep ping-loop | awk -F '[.]' '{print $1}')" > /dev/null 2>&1
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
  -l)
    loop
    ;;
  *)
    usage
    ;;
esac
