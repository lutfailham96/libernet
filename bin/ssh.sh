#!/bin/bash

# SSH Connector
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
SSH_PROFILE="$(jq -r '.tunnel.profile.ssh' < ${SYSTEM_CONFIG})"
SSH_CONFIG="${LIBERNET_DIR}/bin/config/ssh/${SSH_PROFILE}.json"
SSH_HOST="$(jq -r '.host' < ${SSH_CONFIG})"
SSH_PORT="$(jq -r '.port' < ${SSH_CONFIG})"
SSH_USER="$(jq -r '.username' < ${SSH_CONFIG})"
SSH_PASS="$(jq -r '.password' < ${SSH_CONFIG})"
PROXY_IP="$(jq -r '.http.ip' < ${SSH_CONFIG})"
PROXY_PORT="$(jq -r '.http.port' < ${SSH_CONFIG})"
DYNAMIC_PORT="$(jq -r '.tun2socks.socks.port' < ${SYSTEM_CONFIG})"
ENABLE_HTTP="$(jq -r '.enable_http' < ${SSH_CONFIG})"

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