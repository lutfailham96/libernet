#!/bin/bash

# Trojan Wrapper
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
TROJAN_PROFILE="$(jq -r '.tunnel.profile.trojan' < ${SYSTEM_CONFIG})"
TROJAN_CONFIG="${LIBERNET_DIR}/bin/config/trojan/${TROJAN_PROFILE}.json"

function start_trojan() {
  screen -AmdS trojan-client trojan-go -config "${TROJAN_CONFIG}"
}

function stop_trojan() {
  kill $(screen -list | grep trojan-client | awk -F '[.]' {'print $1'})
  killall trojan-go
}

while getopts ":rs" opt; do
  case ${opt} in
  r)
    start_trojan > /dev/null 2>&1
    echo -e "Trojan started!"
    ;;
  s)
    stop_trojan > /dev/null 2>&1
    echo -e "Trojan stopped!"
    ;;
  *)
    echo -e "Usage:"
    echo -e "\t-r\tRun Trojan"
    echo -e "\t-s\tStop Trojan"
    ;;
  esac
done