#!/bin/bash

# SSH Connector
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

function start_ssh() {
  screen -AmdS ssh-connector "${LIBERNET_DIR}/bin/ssh-loop.sh"
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