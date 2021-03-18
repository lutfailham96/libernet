#!/bin/bash

# PING Loop Wrapper
# by Lutfa Ilham
# v1.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
INTERVAL="3"
HOST="bing.com"

function http_ping() {
  httping -qi "${INTERVAL}" "${HOST}"
}

function loop() {
  while true; do
    http_ping
    sleep $INTERVAL
  done
}

function run() {
  echo -e "Starting PING loop service..."
  screen -AmdS ping-loop "${LIBERNET_DIR}/bin/ping-loop.sh" -l
}

function stop() {
  echo -e "Stopping PING loop service ..."
  kill $(screen -list | grep ping-loop | awk -F '[.]' {'print $1'}) > /dev/null 2>&1
}

case $1 in
  -r)
    run
    ;;
  -s)
    stop
    ;;
  -l)
    loop
    ;;
esac