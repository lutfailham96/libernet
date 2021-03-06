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
CONNECTED=false
DYNAMIC_PORT="$(jq -r '.tun2socks.socks.port' < ${SYSTEM_CONFIG})"

if [[ $TUNNEL_MODE == "0" ]]; then
  SSH_PROFILE="$(jq -r '.tunnel.profile.ssh' < ${SYSTEM_CONFIG})"
  SSH_CONFIG="${LIBERNET_DIR}/bin/config/ssh/${SSH_PROFILE}.json"
  ENABLE_HTTP="$(jq -r '.enable_http' < ${SSH_CONFIG})"
fi

# Restore failing service first
usbmode -s > /dev/null 2>&1

function service_v2ray() {
  ${LIBERNET_DIR}/bin/v2ray.sh -r > /dev/null 2>&1
  echo -e "V2Ray service started!"
  check_connection
  service_tun2socks > /dev/null 2>&1
  echo -e "Tun2socks service started!"
}

function service_tun2socks() {
  ${LIBERNET_DIR}/bin/tun2socks.sh -v
}

function service_http_proxy() {
  ${LIBERNET_DIR}/bin/http.sh -r
}

function service_ssh() {
  if [[ $ENABLE_HTTP == 'true' ]]; then
    service_http_proxy > /dev/null 2>&1
    echo -e "HTTP Proxy service started!"
  fi
  ${LIBERNET_DIR}/bin/ssh.sh -r > /dev/null 2>&1
  echo -e "SSH service started!"
  check_connection
  service_tun2socks > /dev/null 2>&1
  echo -e "Tun2socks service started!"
}

function service_ssh_ssl() {
  ${LIBERNET_DIR}/bin/ssh-ssl.sh -r /dev/null 2>&1
  echo -e "SSH-SSL service started!"
  check_connection
  service_tun2socks > /dev/null 2>&1
  echo -e "Tun2socks service started!"
}

function stop_services() {
  if [[ $TUNNEL_MODE == "0" ]]; then
    # kill http proxy
    if [[ $ENABLE_HTTP == 'true' ]]; then
      # write to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Stopping HTTP Proxy service"
      echo -e "Stopping HTTP Proxy service ..."
      ${LIBERNET_DIR}/bin/http.sh -s
    fi
    # kill ssh
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Stopping SSH service"
    echo -e "Stopping SSH service ..."
    ${LIBERNET_DIR}/bin/ssh.sh -s
  elif [[ $TUNNEL_MODE == "1" ]]; then
    # kill v2ray
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Stopping V2Ray service"
    echo -e "Stopping V2Ray service ..."
    ${LIBERNET_DIR}/bin/v2ray.sh -s
  elif [[ $TUNNEL_MODE == "2" ]]; then
    # kill ssh-ssl
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Stopping SSH-SSL service"
    echo -e "Stopping SSH-SSL service ..."
    ${LIBERNET_DIR}/bin/ssh-ssl.sh -s
  fi
  # kill tun2socks
  ${LIBERNET_DIR}/bin/tun2socks.sh -w
}

function start_services() {
  # write service status: running
  "${LIBERNET_DIR}/bin/log.sh" -s 1
  if [[ $TUNNEL_MODE == "0" ]]; then
    # write v2ray to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Starting SSH service"
    service_ssh
  elif [[ $TUNNEL_MODE == "1" ]]; then
    # write v2ray to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Starting V2Ray service"
    service_v2ray
  elif [[ $TUNNEL_MODE == "2" ]]; then
    # write ssh-ssl to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Starting SSH-SSL service"
    service_ssh_ssl
  fi
  # write service status: connected
  "${LIBERNET_DIR}/bin/log.sh" -s 2
  # write libernet to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "<span style=\"color: blue\">Libernet ready to used</span>"
  echo -e "Libernet service started!"
}

function cancel_services() {
  # write service status: stopping
  "${LIBERNET_DIR}/bin/log.sh" -s 3
  # write stopping to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Stopping Libernet service"
    if [[ $TUNNEL_MODE == "0" ]]; then
    # kill http proxy
    if [[ $ENABLE_HTTP == 'true' ]]; then
      # write to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Stopping HTTP Proxy service"
      echo -e "Stopping HTTP Proxy service ..."
      ${LIBERNET_DIR}/bin/http.sh -s
    fi
    # kill ssh
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Stopping SSH service"
    echo -e "Stopping SSH service ..."
    ${LIBERNET_DIR}/bin/ssh.sh -s
  elif [[ $TUNNEL_MODE == "1" ]]; then
    # kill v2ray
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Stopping V2Ray service"
    echo -e "Stopping V2Ray service ..."
    ${LIBERNET_DIR}/bin/v2ray.sh -s
  elif [[ $TUNNEL_MODE == "2" ]]; then
    # kill ssh-ssl
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Stopping SSH-SSL service"
    echo -e "Stopping SSH-SSL service ..."
    ${LIBERNET_DIR}/bin/ssh-ssl.sh -s
  fi
  # write service status: stop
  "${LIBERNET_DIR}/bin/log.sh" -s 0
  # write libernet to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Libernet service stopped"
  echo -e "Libernet services stopped!"
}

function check_connection() {
  counter=0
  while [[ $counter -lt 3 ]]; do
    sleep 5
    # write connection checking to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Checking connection, attempt: $[$counter + 1]"
    echo -e "Checking connection, attempt: $[$counter + 1]"
    if curl -so /dev/null -x "socks://127.0.0.1:${DYNAMIC_PORT}"  "http://google.com"; then
      CONNECTED=true
      # write connection success to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "<span style=\"color: green\">Connection available</span>"
      echo -e "Connection available"
      break
    fi
    counter=$[counter + 1]
  done
  if ! $CONNECTED; then
    # write not connectivity to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "<span style=\"color: red\">Connection unavailable</span>"
    echo -e "Connection unavailable"
    # cancel Libernet service
    cancel_services
    exit 1
  fi
}

function auto_start() {
  while true; do
    if ip route show | grep -q default; then
      start_services
      break
    fi
    echo -e "Waiting available connection, try again"
    sleep 3
  done
}

function enable_auto_start() {
  if ! grep -q "service.sh -as" /etc/rc.local; then
    echo -e "Enable Libernet auto start"
    sed -i "s/exit 0/$(echo "export LIBERNET_DIR=\"${LIBERNET_DIR}\" \&\& ${LIBERNET_DIR}/bin/service.sh -as > /dev/null 2>\&1 \&" | sed 's/\//\\\//g')\nexit 0/g" /etc/rc.local
  fi
}

function disable_auto_start() {
  echo -e "Disable Libernet auto start"
  sed -i "/service.sh -as/d" /etc/rc.local
}

case $1 in
  -as)
    auto_start
    ;;
  -ea)
    enable_auto_start
    ;;
  -da)
    disable_auto_start
    ;;
  -cl)
    cancel_services
    ;;
  -sl)
    start_services
    ;;
  -sh)
    service_ssh
    ;;
  -sshl)
    service_ssh_ssl
    ;;
  -sv)
    service_v2ray
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