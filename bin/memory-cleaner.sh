#!/bin/bash

# Memory Cleaner Wrapper
# by Lutfa Ilham
# v1.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

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
    sleep $INTERVAL
  done
}

function run() {
  echo -e "Running memory cleaner service..."
  screen -AmdS memory-cleaner "${LIBERNET_DIR}/bin/memory-cleaner.sh" -l
}

function stop() {
  echo -e "Stopping memory cleaner service ..."
  kill $(screen -list | grep memory-cleaner | awk -F '[.]' {'print $1'}) > /dev/null 2>&1
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