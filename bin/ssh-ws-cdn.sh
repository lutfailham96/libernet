#!/usr/bin/env bash

# SSH-WS-CDN Connector Wrapper
# by Lutfa Ilham
# v1.0.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SERVICE_NAME="SSH-WS-CDN"
SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
SSH_WS_CDN_PROFILE=$(grep 'ssh_ws_cdn":' "${SYSTEM_CONFIG}"  | awk '{print $2}' | sed 's/,//g; s/"//g')
SSH_WS_CDN_CONFIG="${LIBERNET_DIR}/bin/config/ssh_ws_cdn/${SSH_WS_CDN_PROFILE}.json"
SSH_WS_CDN_HOST=$(grep 'host":' "${SSH_WS_CDN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g')
SSH_WS_CDN_PORT=$(grep 'port":' "${SSH_WS_CDN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')
SSH_WS_CDN_USER=$(grep 'username":' "${SSH_WS_CDN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g')
SSH_WS_CDN_PASS=$(grep 'password":' "${SSH_WS_CDN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g')
PROXY_IP=$(grep 'ip":' "${SSH_WS_CDN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '3p')
PROXY_PORT=$(grep 'port":' "${SSH_WS_CDN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '3p')
CDN_SNI=$(grep 'sni":' "${SSH_WS_CDN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g')
CDN_IP=$(grep 'ip":' "${SSH_WS_CDN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '5p')
CDN_PORT=$(grep 'port":' "${SSH_WS_CDN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '5p')
DYNAMIC_PORT=$(grep 'port":' "${SYSTEM_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')
ENABLE_HTTP=$(grep 'enable_http":' "${SSH_WS_CDN_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g')

run() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Config: ${SSH_WS_CDN_PROFILE}, Mode: ${SERVICE_NAME}"
  if [[ "${ENABLE_HTTP}" == 'true' ]]; then
    export ENABLE_WS_CDN=true \
      && "${LIBERNET_DIR}/bin/http.sh" -r \
      && "${LIBERNET_DIR}/bin/stunnel.sh" -r "ssh_ws_cdn" "${SSH_WS_CDN_PROFILE}" "${CDN_IP}" "${CDN_PORT}" "${CDN_SNI}" \
      && "${LIBERNET_DIR}/bin/log.sh" -w "Starting ${SERVICE_NAME} service" \
      && echo -e "Starting ${SERVICE_NAME} service ..." \
      && screen -AmdS ssh-connector "${LIBERNET_DIR}/bin/ssh-loop.sh" -e "${SSH_WS_CDN_USER}" "${SSH_WS_CDN_PASS}" "${SSH_WS_CDN_HOST}" "${SSH_WS_CDN_PORT}" "${DYNAMIC_PORT}" "${PROXY_IP}" "${PROXY_PORT}" \
      && echo -e "${SERVICE_NAME} service started!"
    # prepare using go-tcp-proxy-tunnel & don't need to use Stunnel
    #export ENABLE_WS_CDN=true \
    #  && "${LIBERNET_DIR}/bin/http.sh" -r \
    #  && "${LIBERNET_DIR}/bin/log.sh" -w "Starting ${SERVICE_NAME} service" \
    #  && echo -e "Starting ${SERVICE_NAME} service ..." \
    #  && screen -AmdS ssh-connector "${LIBERNET_DIR}/bin/ssh-loop.sh" -e "${SSH_WS_CDN_USER}" "${SSH_WS_CDN_PASS}" "${SSH_WS_CDN_HOST}" "${SSH_WS_CDN_PORT}" "${DYNAMIC_PORT}" "${PROXY_IP}" "${PROXY_PORT}" \
    #  && echo -e "${SERVICE_NAME} service started!"
  fi
}

stop() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Stopping ${SERVICE_NAME} service"
  echo -e "Stopping ${SERVICE_NAME} service ..."
  kill "$(screen -list | grep ssh-connector | awk -F '[.]' '{print $1}')"
  echo -e "${SERVICE_NAME} service stopped!"
  "${LIBERNET_DIR}/bin/stunnel.sh" -s
  export ENABLE_WS_CDN=true \
    && "${LIBERNET_DIR}/bin/http.sh" -s
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
    run
    ;;
  -s)
    stop
    ;;
  *)
    usage
    ;;
esac
