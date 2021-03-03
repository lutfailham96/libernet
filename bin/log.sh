#!/bin/bash

# Log Wrapper
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

LOG_FILE="${LIBERNET_DIR}/log/service.log"
STATUS_FILE="${LIBERNET_DIR}/log/status.log"

function write_log() {
  echo -e "[$(date '+%H:%M:%S')] ${1}" >> ${LOG_FILE}
}

function write_status() {
    echo -e "${1}" > "${STATUS_FILE}"
}

function reset_log() {
  rm "${LOG_FILE}" && touch "${LOG_FILE}"
}

case $1 in
  -w)
    write_log "${2}"
    ;;
  -s)
    write_status "${2}"
    ;;
  -r)
    reset_log
    ;;
esac