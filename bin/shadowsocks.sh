#!/bin/bash

# Shadowsocks Wrapper
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
SHADOWSOCKS_PROFILE="$(grep 'shadowsocks":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
SHADOWSOCKS_CONFIG="${LIBERNET_DIR}/bin/config/shadowsocks/${SHADOWSOCKS_PROFILE}.json"

function start_shadowsocks() {
  screen -AmdS ss-client ss-local -c "${SHADOWSOCKS_CONFIG}"
}

function stop_shadowsocks() {
  kill $(screen -list | grep ss-client | awk -F '[.]' {'print $1'})
  # kill plugins
  killall obfs-local
  killall ck-client
}

while getopts ":rs" opt; do
  case ${opt} in
  r)
    start_shadowsocks > /dev/null 2>&1
    echo -e "Shadowsocks started!"
    ;;
  s)
    stop_shadowsocks > /dev/null 2>&1
    echo -e "Shadowsocks stopped!"
    ;;
  *)
    echo -e "Usage:"
    echo -e "\t-r\tRun Shadowsocks"
    echo -e "\t-s\tStop Shadowsocks"
    ;;
  esac
done