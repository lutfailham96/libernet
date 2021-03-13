#!/bin/bash

# HTTP Proxy Wrapper
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
SSH_PROFILE="$(grep 'ssh":' ${SYSTEM_CONFIG}  | awk '{print $2}' | sed 's/,//g; s/"//g')"
SSH_CONFIG="${LIBERNET_DIR}/bin/config/ssh/${SSH_PROFILE}.json"
LISTEN_PORT="$(grep 'port":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '3p')"

function start_http() {
  screen -AmdS http-proxy python3 -u "${LIBERNET_DIR}/bin/http.py" "${SSH_CONFIG}" -l ${LISTEN_PORT}
}

function stop_http() {
  kill $(screen -list | grep http-proxy | awk -F '[.]' {'print $1'})
}

while getopts ":rs" opt; do
  case ${opt} in
  r)
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Starting HTTP Proxy service"
    start_http > /dev/null 2>&1
    echo -e "HTTP Proxy started!"
    ;;
  s)
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Stopping HTTP Proxy service"
    stop_http > /dev/null 2>&1
    echo -e "HTTP Proxy stopped!"
    ;;
  *)
    echo -e "Usage:"
    echo -e "\t-r\tRun HTTP Proxy"
    echo -e "\t-s\tStop HTTP Proxy"
    ;;
  esac
done