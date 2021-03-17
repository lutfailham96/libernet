#!/bin/bash

# Libernet Service Wrapper
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
TUNNEL_MODE="$(grep 'mode":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
declare -x SSH_PROFILE
declare -x SSH_CONFIG
declare -x ENABLE_HTTP
CONNECTED=false
DYNAMIC_PORT="$(grep 'port":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | head -1)"
DNS_RESOLVER="$(grep 'dns_resolver":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
MEMORY_CLEANER="$(grep 'memory_cleaner":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"

if [[ $TUNNEL_MODE == "0" ]]; then
  SSH_PROFILE="$(grep 'ssh":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | head -1)"
  SSH_CONFIG="${LIBERNET_DIR}/bin/config/ssh/${SSH_PROFILE}.json"
  ENABLE_HTTP="$(grep 'enable_http":' ${SSH_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
fi

# Restore failing service first
usbmode -s > /dev/null 2>&1

function service_dns_resolver() {
  if [[ $DNS_RESOLVER == 'true' ]]; then
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Starting DNS resolver service"
    ${LIBERNET_DIR}/bin/dns.sh -r > /dev/null 2>&1
    echo -e "DNS resolver service started!"
  fi
}

function service_memory_cleaner() {
  if [[ $MEMORY_CLEANER == 'true' ]]; then
    # write to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Starting memory cleaner service"
    ${LIBERNET_DIR}/bin/memory-cleaner.sh -r > /dev/null 2>&1
    echo -e "Memory cleaner service started!"
  fi
}

function service_v2ray() {
  ${LIBERNET_DIR}/bin/v2ray.sh -r > /dev/null 2>&1
  echo -e "V2Ray service started!"
  check_connection
  service_tun2socks > /dev/null 2>&1
  service_dns_resolver > /dev/null 2>&1
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
  service_dns_resolver > /dev/null 2>&1
  service_memory_cleaner > /dev/null 2>&1
  echo -e "Tun2socks service started!"
}

function service_ssh_ssl() {
  ${LIBERNET_DIR}/bin/ssh-ssl.sh -r /dev/null 2>&1
  echo -e "SSH-SSL service started!"
  check_connection
  service_tun2socks > /dev/null 2>&1
  service_dns_resolver > /dev/null 2>&1
  service_memory_cleaner > /dev/null 2>&1
  echo -e "Tun2socks service started!"
}

function service_trojan() {
  ${LIBERNET_DIR}/bin/trojan.sh -r /dev/null 2>&1
  echo -e "Trojan service started!"
  check_connection
  service_tun2socks > /dev/null 2>&1
  service_dns_resolver > /dev/null 2>&1
  service_memory_cleaner > /dev/null 2>&1
  echo -e "Tun2socks service started!"
}

function service_shadowsocks() {
  ${LIBERNET_DIR}/bin/shadowsocks.sh -r /dev/null 2>&1
  echo -e "Shadowsocks service started!"
  check_connection
  service_tun2socks > /dev/null 2>&1
  service_dns_resolver > /dev/null 2>&1
  service_memory_cleaner > /dev/null 2>&1
  echo -e "Tun2socks service started!"
}

function stop_services() {
  case $TUNNEL_MODE in
    "0")
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
      ;;
    "1")
      # kill v2ray
      # write to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Stopping V2Ray service"
      echo -e "Stopping V2Ray service ..."
      ${LIBERNET_DIR}/bin/v2ray.sh -s
      ;;
    "2")
      # kill ssh-ssl
      # write to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Stopping SSH-SSL service"
      echo -e "Stopping SSH-SSL service ..."
      ${LIBERNET_DIR}/bin/ssh-ssl.sh -s
      ;;
    "3")
      # kill trojan
      # write to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Stopping Trojan service"
      echo -e "Stopping Trojan service ..."
      ${LIBERNET_DIR}/bin/trojan.sh -s
      ;;
    "4")
      # kill shadowsocks
      # write to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Stopping Shadowsocks service"
      echo -e "Stopping Shadowsocks service ..."
      ${LIBERNET_DIR}/bin/shadowsocks.sh -s
      ;;
  esac
  # kill tun2socks
  ${LIBERNET_DIR}/bin/tun2socks.sh -w
  # kill dns resolver
  if [[ $DNS_RESOLVER == 'true' ]]; then
    "${LIBERNET_DIR}/bin/log.sh" -w "Stopping DNS resolver service"
    ${LIBERNET_DIR}/bin/dns.sh -s
  fi
  # kill memory cleaner service
  if [[ $MEMORY_CLEANER == 'true' ]]; then
    "${LIBERNET_DIR}/bin/log.sh" -w "Stopping memory cleaner service"
    ${LIBERNET_DIR}/bin/memory-cleaner.sh -s
  fi
}

function start_services() {
  # write service status: running
  "${LIBERNET_DIR}/bin/log.sh" -s 1
  case $TUNNEL_MODE in
    "0")
      # write v2ray to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Starting SSH service"
      service_ssh
      ;;
    "1")
      # write v2ray to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Starting V2Ray service"
      service_v2ray
      ;;
    "2")
      # write ssh-ssl to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Starting SSH-SSL service"
      service_ssh_ssl
      ;;
    "3")
      # write trojan to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Starting Trojan service"
      service_trojan
      ;;
    "4")
      # write shadowsocks to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Starting Shadowsocks service"
      service_shadowsocks
      ;;
  esac
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
  case $TUNNEL_MODE in
    "0")
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
      ;;
    "1")
      # kill v2ray
      # write to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Stopping V2Ray service"
      echo -e "Stopping V2Ray service ..."
      ${LIBERNET_DIR}/bin/v2ray.sh -s
      ;;
    "2")
      # kill ssh-ssl
      # write to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Stopping SSH-SSL service"
      echo -e "Stopping SSH-SSL service ..."
      ${LIBERNET_DIR}/bin/ssh-ssl.sh -s
      ;;
    "3")
      # kill trojan
      # write to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Stopping Trojan service"
      echo -e "Stopping Trojan service ..."
      ${LIBERNET_DIR}/bin/trojan.sh -s
      ;;
    "4")
      # kill shadowsocks
      # write to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "Stopping Shadowsocks service"
      echo -e "Stopping Shadowsocks service ..."
      ${LIBERNET_DIR}/bin/shadowsocks.sh -s
      ;;
  esac
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
    if curl -so /dev/null -x "socks5://127.0.0.1:${DYNAMIC_PORT}"  "http://bing.com"; then
      CONNECTED=true
      # write connection success to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "<span style=\"color: green\">Socks connection available</span>"
      echo -e "Socks connection available"
      break
    fi
    counter=$[counter + 1]
  done
  if ! $CONNECTED; then
    # write not connectivity to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "<span style=\"color: red\">Socks connection unavailable</span>"
    echo -e "Socks connection unavailable"
    # cancel Libernet service
    cancel_services
    exit 1
  fi
}

function auto_start() {
  while true; do
    if ip route show | grep -q default; then
      # reset all service log
      "${LIBERNET_DIR}/bin/log.sh" -ra
      # start Libernet service
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
  -tr)
    service_trojan
    ;;
  -ss)
    service_shadowsocks
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