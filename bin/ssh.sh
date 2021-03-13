#!/bin/bash

# SSH Connector
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
SSH_PROFILE="$(grep 'ssh":' ${SYSTEM_CONFIG}  | awk '{print $2}' | sed 's/,//g; s/"//g')"
SSH_CONFIG="${LIBERNET_DIR}/bin/config/ssh/${SSH_PROFILE}.json"
SSH_HOST="$(grep 'host":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
SSH_PORT="$(grep 'port":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')"
SSH_USER="$(grep 'username":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
SSH_PASS="$(grep 'password":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
PROXY_IP="$(grep 'ip":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '4p')"
PROXY_PORT="$(grep 'port":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '4p')"
DYNAMIC_PORT="$(grep 'port":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')"
ENABLE_HTTP="$(grep 'enable_http":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"

function start_ssh() {
  if [[ $ENABLE_HTTP == 'true' ]]; then
    screen -AmdS ssh-connector "${LIBERNET_DIR}/bin/ssh-loop.sh" -e "${SSH_USER}" "${SSH_PASS}" "${SSH_HOST}" "${SSH_PORT}" "${DYNAMIC_PORT}" "${PROXY_IP}" "${PROXY_PORT}"
  else
    screen -AmdS ssh-connector "${LIBERNET_DIR}/bin/ssh-loop.sh" -d "${SSH_USER}" "${SSH_PASS}" "${SSH_HOST}" "${SSH_PORT}" "${DYNAMIC_PORT}"
  fi
}

function stop_ssh() {
  kill $(screen -list | grep ssh-connector | awk -F '[.]' {'print $1'})
}

while getopts ":rs" opt; do
  case ${opt} in
  r)
    start_ssh > /dev/null 2>&1
    echo -e "SSH started!"
    ;;
  s)
    stop_ssh > /dev/null 2>&1
    echo -e "SSH stopped!"
    ;;
  *)
    echo -e "Usage:"
    echo -e "\t-r\tRun SSH"
    echo -e "\t-s\tStop SSH"
    ;;
  esac
done