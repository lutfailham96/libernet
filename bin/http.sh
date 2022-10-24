#!/usr/bin/env bash

# HTTP Proxy Wrapper
# by Lutfa Ilham
# v1.0.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SERVICE_NAME="HTTP Proxy"
SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
SSH_PROFILE=$(grep 'ssh":' "${SYSTEM_CONFIG}"  | awk '{print $2}' | sed 's/,//g; s/"//g')
SSH_CONFIG="${LIBERNET_DIR}/bin/config/ssh/${SSH_PROFILE}.json"
if [ "${ENABLE_WS_CDN}" ]; then
  SSH_PROFILE=$(grep 'ssh_ws_cdn":' "${SYSTEM_CONFIG}"  | awk '{print $2}' | sed 's/,//g; s/"//g')
  SSH_CONFIG="${LIBERNET_DIR}/bin/config/ssh_ws_cdn/${SSH_PROFILE}.json"
fi
LISTEN_PORT=$(grep 'port":' "${SSH_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '3p')

run() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Starting ${SERVICE_NAME} service"
  echo -e "Starting ${SERVICE_NAME} service ..."
  screen -AmdS http-proxy bash -c "while true; do python3 -u \"${LIBERNET_DIR}/bin/http.py\" \"${SSH_CONFIG}\" -l ${LISTEN_PORT}; sleep 3; done" \
    && echo -e "${SERVICE_NAME} service started!"
  # prepare using go-tcp-proxy-tunnel
  #if [ "${ENABLE_WS_CDN}" ]; then
  #  payload=$(grep 'payload":' "${SSH_CONFIG}" | awk -F \" '{print $4}' | sed -n '1p')
  #  remote_port=$(grep 'port":' "${SSH_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')
  #  remote_host=$(grep 'host":' "${SSH_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')
  #  cdn_ip=$(grep 'ip":' "${SSH_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '5p')
  #  cdn_sni=$(grep 'sni":' "${SSH_CONFIG}" | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')
  #  screen -AmdS http-proxy bash -c "while true; do \"${LIBERNET_DIR}/bin/go-http\" -l 127.0.0.1:${LISTEN_PORT} -r ${cdn_ip}:443 -s ${remote_host}:${remote_port} -dsr -tls -sni ${cdn_sni} -op \"${payload}\"; sleep 3; done" \
  #    && echo -e "${SERVICE_NAME} service started!"
  #else
  #  screen -AmdS http-proxy bash -c "while true; do python3 -u \"${LIBERNET_DIR}/bin/http.py\" \"${SSH_CONFIG}\" -l ${LISTEN_PORT}; sleep 3; done" \
  #    && echo -e "${SERVICE_NAME} service started!"
  #fi
}

stop() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Stopping ${SERVICE_NAME} service"
  echo -e "Stopping ${SERVICE_NAME} service ..."
  kill "$(screen -list | grep http-proxy | awk -F '[.]' '{print $1}')"
  killall python3
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
    run
    ;;
  -s)
    stop
    ;;
  *)
    usage
    ;;
esac
