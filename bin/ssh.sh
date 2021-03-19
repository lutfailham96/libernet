#!/bin/bash

# SSH Connector Wrapper
# by Lutfa Ilham
# v1.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SERVICE_NAME="SSH"
SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
SSH_PROFILE="$(grep 'ssh":' ${SYSTEM_CONFIG}  | awk '{print $2}' | sed 's/,//g; s/"//g')"
SSH_CONFIG="${LIBERNET_DIR}/bin/config/ssh/${SSH_PROFILE}.json"
SSH_HOST="$(grep 'host":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
SSH_PORT="$(grep 'port":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')"
SSH_USER="$(grep 'username":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
SSH_PASS="$(grep 'password":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
PROXY_IP="$(grep 'ip":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '3p')"
PROXY_PORT="$(grep 'port":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '3p')"
DYNAMIC_PORT="$(grep 'port":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')"
ENABLE_HTTP="$(grep 'enable_http":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"

function run() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Config: ${SSH_PROFILE}, Mode: ${SERVICE_NAME}"
  if [[ "${ENABLE_HTTP}" == 'true' ]]; then
    "${LIBERNET_DIR}/bin/http.sh" -r \
      && "${LIBERNET_DIR}/bin/log.sh" -w "Starting ${SERVICE_NAME} service" \
      && echo -e "Starting ${SERVICE_NAME} service ..." \
      && screen -AmdS ssh-connector "${LIBERNET_DIR}/bin/ssh-loop.sh" -e "${SSH_USER}" "${SSH_PASS}" "${SSH_HOST}" "${SSH_PORT}" "${DYNAMIC_PORT}" "${PROXY_IP}" "${PROXY_PORT}" \
      && echo -e "${SERVICE_NAME} service started!"
  else
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Starting ${SERVICE_NAME} service"
    echo -e "Starting ${SERVICE_NAME} service ..."
    screen -AmdS ssh-connector "${LIBERNET_DIR}/bin/ssh-loop.sh" -d "${SSH_USER}" "${SSH_PASS}" "${SSH_HOST}" "${SSH_PORT}" "${DYNAMIC_PORT}"
  fi
}

function stop() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Stopping ${SERVICE_NAME} service"
  echo -e "Stopping ${SERVICE_NAME} service ..."
  kill $(screen -list | grep ssh-connector | awk -F '[.]' {'print $1'})
  echo -e "${SERVICE_NAME} service stopped!"
  if [[ "${ENABLE_HTTP}" == 'true' ]]; then
    "${LIBERNET_DIR}/bin/http.sh" -s
  fi
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
