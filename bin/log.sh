#!/bin/bash

# Service Log Wrapper
# by Lutfa Ilham
# v1.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

LOG_FILE="${LIBERNET_DIR}/log/service.log"
STATUS_FILE="${LIBERNET_DIR}/log/status.log"
UPDATE_FILE="${LIBERNET_DIR}/log/update.log"
CONNECTED_FILE="${LIBERNET_DIR}/log/connected.log"

function write_log() {
  echo -e "[$(date '+%H:%M:%S')] ${1}" >> ${LOG_FILE}
}

function write_status() {
  echo -e "${1}" > "${STATUS_FILE}"
}

function write_update() {
  echo -e "${1}" > "${UPDATE_FILE}"
}

function write_connected() {
  echo -e "${1}" > "${CONNECTED_FILE}"
}

function reset_log() {
  rm "${LOG_FILE}" \
    && touch "${LOG_FILE}"
}

function reset_all_log() {
  reset_log \
    && rm "${STATUS_FILE}" \
    && touch "${STATUS_FILE}" \
    && rm "${UPDATE_FILE}" \
    && touch "${UPDATE_FILE}" \
    && rm "${CONNECTED_FILE}" \
    && touch "${CONNECTED_FILE}"
}

case "${1}" in
  -w)
    write_log "${2}"
    ;;
  -s)
    write_status "${2}"
    ;;
  -u)
    write_update "${2}"
    ;;
  -c)
    write_connected "${2}"
    ;;
  -r)
    reset_log
    ;;
  -ra)
    reset_all_log
    ;;
esac
