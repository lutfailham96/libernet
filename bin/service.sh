#!/bin/bash

# Libernet Service Wrapper
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
TUNNEL_MODE="$(jq -r '.tunnel.mode' < ${SYSTEM_CONFIG})"
declare -x SSH_PROFILE
declare -x SSH_CONFIG
declare -x ENABLE_HTTP

if [[ $TUNNEL_MODE == "0" ]]; then
  SSH_PROFILE="$(jq -r '.tunnel.profile.ssh' < ${SYSTEM_CONFIG})"
  SSH_CONFIG="${LIBERNET_DIR}/bin/config/ssh/${SSH_PROFILE}.json"
  ENABLE_HTTP="$(jq -r '.enable_http' < ${SSH_CONFIG})"
fi

# Restore failing service first
usbmode -s > /dev/null 2>&1

function service_v2ray() {
  ${LIBERNET_DIR}/bin/v2ray.sh -r
}

function service_tun2socks() {
  ${LIBERNET_DIR}/bin/tun2socks.sh -i \
    && ${LIBERNET_DIR}/bin/tun2socks.sh -y \
    && ${LIBERNET_DIR}/bin/tun2socks.sh -r
}

function service_http_proxy() {
  ${LIBERNET_DIR}/bin/http.sh -r
}

function service_ssh() {
  ${LIBERNET_DIR}/bin/ssh.sh -r
}

function stop_services() {
  if [[ $TUNNEL_MODE == "0" ]]; then
    # kill http proxy
    if [[ $ENABLE_HTTP == 'true' ]]; then
      echo -e "Stopping HTTP Proxy service ..."
      ${LIBERNET_DIR}/bin/http.sh -s
    fi
    # kill ssh
    echo -e "Stopping SSH service ..."
    ${LIBERNET_DIR}/bin/ssh.sh -s
  elif [[ $TUNNEL_MODE == "1" ]]; then
    # kill v2ray
    echo -e "Stopping V2Ray service ..."
    ${LIBERNET_DIR}/bin/v2ray.sh -s
  fi
  # kill tun2socks
  echo -e "Stopping Tun2socks service ..."
  ${LIBERNET_DIR}/bin/tun2socks.sh -s
  echo -e "Removing routes ..."
  ${LIBERNET_DIR}/bin/tun2socks.sh -z
  echo -e "Removing tun device ..."
  ${LIBERNET_DIR}/bin/tun2socks.sh -d
}

case $1 in
  -sl)
    # write service status: running
    "${LIBERNET_DIR}/bin/log.sh" -s 1
    if [[ $TUNNEL_MODE == "0" ]]; then
      # write v2ray to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Starting SSH service"
      ${0} -sh
    elif [[ $TUNNEL_MODE == "1" ]]; then
      # write v2ray to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Starting V2Ray service"
      ${0} -sv
    fi
    # write service status: connected
    "${LIBERNET_DIR}/bin/log.sh" -s 2
    # write libernet to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "<span style=\"color: blue\">Libernet ready to used</span>"
    echo -e "Libernet service started!"
    ;;
  -sh)
    if [[ $ENABLE_HTTP == 'true' ]]; then
      service_http_proxy > /dev/null 2>&1
      echo -e "HTTP Proxy service started!"
    fi
    service_ssh > /dev/null 2>&1
    echo -e "SSH service started!"
    service_tun2socks > /dev/null 2>&1
    echo -e "Tun2socks service started!"
    ;;
  -sv)
    service_v2ray > /dev/null 2>&1
    echo -e "V2Ray service started!"
    service_tun2socks > /dev/null 2>&1
    echo -e "Tun2socks service started!"
    ;;
  -ds)
    # write service status: stopping
    "${LIBERNET_DIR}/bin/log.sh" -s 3
    # write stopping to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Stopping Libernet service"
    stop_services
    # write service status: stop
    "${LIBERNET_DIR}/bin/log.sh" -s 0
    # write libernet to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Libernet service stopped"
    echo -e "Libernet services stopped!"
    ;;
esac