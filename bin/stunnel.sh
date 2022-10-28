#!/usr/bin/env bash

# Stunnel Wrapper
# by Lutfa Ilham
# v1.0.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SERVICE_NAME="Stunnel"
STUNNEL_DIR="${LIBERNET_DIR}/config/stunnel"

configure() {
  STUNNEL_MODE="${1}"
  STUNNEL_PROFILE="${2}"
  STUNNEL_HOST="${3}"
  STUNNEL_PORT="${4}"
  STUNNEL_SNI="${5}"
  STUNNEL_CONFIG="${STUNNEL_DIR}/${STUNNEL_MODE}/${STUNNEL_PROFILE}.conf"
  # create stunnel mode directory if not exist
  if [[ ! -d "${STUNNEL_DIR}/${STUNNEL_MODE}" ]]; then
    mkdir "${STUNNEL_DIR}/${STUNNEL_MODE}"
  fi
  # copying from template
  cp -af "${LIBERNET_DIR}/config/stunnel/templates/stunnel.conf" "${STUNNEL_CONFIG}"
  # updating host & port value
  sed -i "s/^connect = .*/connect = ${STUNNEL_HOST}:${STUNNEL_PORT}/g" "${STUNNEL_CONFIG}"
  # updating sni value
  sed -i "s/^sni = .*/sni = ${STUNNEL_SNI}/g" "${STUNNEL_CONFIG}"
  # updating cert value
  sed -i "s/^cert = .*/cert = $(echo "${LIBERNET_DIR}/config/stunnel/stunnel.pem" | sed 's/\//\\\//g')/g" "${STUNNEL_CONFIG}"
}

run() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Starting ${SERVICE_NAME} service"
  echo -e "Starting ${SERVICE_NAME} service ..."
  configure "${1}" "${2}" "${3}" "${4}" "${5}" \
    && screen -AmdS stunnel bash -c "while true; do stunnel \"${STUNNEL_CONFIG}\"; sleep 3; done" \
    && echo -e "${SERVICE_NAME} service started!"
}

stop() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Stopping ${SERVICE_NAME} service"
  echo -e "Stopping ${SERVICE_NAME} service ..."
  kill "$(screen -list | grep stunnel | awk -F '[.]' '{print $1}')"
  killall stunnel
  echo -e "${SERVICE_NAME} service stopped!"
}

usage() {
  cat <<EOF
Usage:
  -r  Run ${SERVICE_NAME} service
  -s  Stop ${SERVICE_NAME} service
EOF
}

case "${1}" in
  -r)
    # command mode profile host port sni
    run "${2}" "${3}" "${4}" "${5}" "${6}"
  ;;
  -s)
    stop
  ;;
  *)
    usage
  ;;
esac
