#!/bin/bash

# Memory Cleaner Wrapper
# by Lutfa Ilham
# v1.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SERVICE_NAME="Memory Cleaner"
SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
INTERVAL="1h"

function clear_memory() {
  clear \
    && sync \
    && echo -e "Memory usage before:" \
    && free \
    && echo -e "" \
    && echo -e "Performing clear memory ..." \
    && echo 3 > /proc/sys/vm/drop_caches \
    && echo -e "Done!\n" \
    && echo -e "Memory usage after:" \
    && free
}

function loop() {
  while true; do
    clear_memory
    sleep "${INTERVAL}"
  done
}

function run() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Starting ${SERVICE_NAME} service"
  echo -e "Starting ${SERVICE_NAME} service ..."
  screen -AmdS memory-cleaner "${LIBERNET_DIR}/bin/memory-cleaner.sh" -l \
    && echo -e "${SERVICE_NAME} service started!"
}

function stop() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Stopping ${SERVICE_NAME} service"
  echo -e "Stopping ${SERVICE_NAME} service ..."
  kill $(screen -list | grep memory-cleaner | awk -F '[.]' {'print $1'})
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
  -l)
    loop
    ;;
  *)
    usage
    ;;
esac
