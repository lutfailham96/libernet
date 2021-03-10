#!/bin/bash

# V2Ray Wrapper
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
declare -x V2RAY_PROFILE

function start_v2ray() {
  if [[ -z ${1} ]];then
    V2RAY_PROFILE="$(jq -r '.tunnel.profile.v2ray' < ${SYSTEM_CONFIG})"
    screen -AmdS v2ray-client v2ray -c "${LIBERNET_DIR}/bin/config/v2ray/${V2RAY_PROFILE}.json"
  else
    screen -AmdS v2ray-client v2ray -c "${LIBERNET_DIR}/bin/config/v2ray/${1}.json"
  fi
}

function stop_v2ray() {
  kill $(screen -list | grep v2ray-client | awk -F '[.]' {'print $1'})
}

while getopts ":rs" opt; do
  case ${opt} in
  r)
    start_v2ray $2 > /dev/null 2>&1
    echo "V2Ray started!"
    ;;
  s)
    stop_v2ray > /dev/null 2>&1
    echo "V2Ray stopped!"
    ;;
  *)
    echo -e "Usage:"
    echo -e "\t-r\tRun V2Ray"
    echo -e "\t-s\tStop V2Ray"
    ;;
  esac
done