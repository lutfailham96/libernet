#!/bin/bash

# SSH-SSL Connector Wrapper
# by Lutfa Ilham
# v1.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SERVICE_NAME="SSH-SSL"
SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
SSH_SSL_PROFILE="$(grep 'ssh_ssl":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
SSH_SSL_CONFIG="${LIBERNET_DIR}/bin/config/ssh_ssl/${SSH_SSL_PROFILE}.json"
SSH_SSL_HOST="$(grep 'host":' ${SSH_SSL_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
SSH_SSL_PORT="$(grep 'port":' ${SSH_SSL_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')"
SSH_SSL_USER="$(grep 'username":' ${SSH_SSL_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
SSH_SSL_PASS="$(grep 'password":' ${SSH_SSL_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
SSH_SSL_SNI="$(grep 'sni":' ${SSH_SSL_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
DYNAMIC_PORT="$(grep 'port":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')"

function run() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Config: ${SSH_SSL_PROFILE}, Mode: ${SERVICE_NAME}"
  "${LIBERNET_DIR}/bin/stunnel.sh" -r "ssh" "${SSH_SSL_PROFILE}" "${SSH_SSL_HOST}" "${SSH_SSL_PORT}" "${SSH_SSL_SNI}" \
    && "${LIBERNET_DIR}/bin/log.sh" -w "Starting ${SERVICE_NAME} service" \
    && echo -e "Starting ${SERVICE_NAME} service ..." \
    && screen -AmdS ssh-ssl-connector "${LIBERNET_DIR}/bin/ssh-ssl-loop.sh" "${SSH_SSL_USER}" "${SSH_SSL_PASS}" "${DYNAMIC_PORT}" \
    && echo -e "${SERVICE_NAME} service started!"
}

function stop() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Stopping ${SERVICE_NAME} service"
  echo -e "Stopping ${SERVICE_NAME} service ..."
  # kill ssh-ssl background service
  kill $(screen -list | grep ssh-ssl-connector | awk -F '[.]' {'print $1'})
  "${LIBERNET_DIR}/bin/stunnel.sh" -s
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
  *)
    usage
  ;;
esac
