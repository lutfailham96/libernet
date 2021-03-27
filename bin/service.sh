#!/bin/bash

# Libernet Service Wrapper
# by Lutfa Ilham
# v1.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
TUNNEL_MODE="$(grep 'mode":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
CONNECTED=false
DYNAMIC_PORT="$(grep 'port":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | head -1)"
DNS_RESOLVER="$(grep 'dns_resolver":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
MEMORY_CLEANER="$(grep 'memory_cleaner":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
PING_LOOP="$(grep 'ping_loop":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"

function check_connection() {
  counter=0
  max_retries=3
  while [[ "${counter}" -lt "${max_retries}" ]]; do
    sleep 5
    # write connection checking to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Checking connection, attempt: $[${counter} + 1]"
    echo -e "Checking connection, attempt: $[${counter} + 1]"
    if curl -so /dev/null -x "socks5://127.0.0.1:${DYNAMIC_PORT}" "http://bing.com"; then
      # write connection success to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "<span style=\"color: green\">Socks connection available</span>"
      echo -e "Socks connection available!"
      CONNECTED=true
      break
    fi
    counter=$[${counter} + 1]
    # max retries reach
    if [[ "${counter}" -eq "${max_retries}" ]]; then
      # write not connectivity to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "<span style=\"color: red\">Socks connection unavailable</span>"
      echo -e "Socks connection unavailable!"
      # cancel Libernet service
      cancel_services
      exit 1
    fi
  done
}

function run_other_services() {
  if ${CONNECTED}; then
    service_tun2socks
    dns_resolver_service
    memory_cleaner_service
    ping_loop_service
  fi
}

function dns_resolver_service() {
  if [[ "${DNS_RESOLVER}" == 'true' ]]; then
    "${LIBERNET_DIR}/bin/dns.sh" -r
  fi
}

function memory_cleaner_service() {
  if [[ "${MEMORY_CLEANER}" == 'true' ]]; then
    "${LIBERNET_DIR}/bin/memory-cleaner.sh" -r
  fi
}

function ping_loop_service() {
  if [[ "${PING_LOOP}" == 'true' ]]; then
    "${LIBERNET_DIR}/bin/ping-loop.sh" -r
  fi
}

function service_tun2socks() {
  "${LIBERNET_DIR}/bin/tun2socks.sh" -v
}

function ssh_service() {
  "${LIBERNET_DIR}/bin/ssh.sh" -r
  check_connection
  run_other_services
}

function v2ray_service() {
  "${LIBERNET_DIR}/bin/v2ray.sh" -r
  check_connection
  run_other_services
}

function ssh_ssl_service() {
  "${LIBERNET_DIR}/bin/ssh-ssl.sh" -r
  check_connection
  run_other_services
}

function trojan_service() {
  "${LIBERNET_DIR}/bin/trojan.sh" -r
  check_connection
  run_other_services
}

function shadowsocks_service() {
  "${LIBERNET_DIR}/bin/shadowsocks.sh" -r
  check_connection
  run_other_services
}

function openvpn_service() {
  "${LIBERNET_DIR}/bin/openvpn.sh" -r
  # check connection
  tun_dev="$(grep 'dev":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
  route_log="${LIBERNET_DIR}/log/route.log"
  default_route="$(ip route show | grep default | grep -v ${tun_dev})"
  counter=0
  max_retries=3
  while [[ "${counter}" -lt "${max_retries}" ]]; do
    sleep 5
    # write connection checking to service log
    "${LIBERNET_DIR}/bin/log.sh" -w "Checking connection, attempt: $[${counter} + 1]"
    echo -e "Checking connection, attempt: $[${counter} + 1]"
    if grep -q 'Initialization Sequence Completed' "${LIBERNET_DIR}/log/openvpn.log"; then
      # write connection success to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "<span style=\"color: green\">OpenVPN connection available</span>"
      echo -e "OpenVPN connection available!"
      # write connected time
      "${LIBERNET_DIR}/bin/log.sh" -c "$(date +"%s")"
      # save default route & change default route to tunnel
      echo -e "${default_route}" > "${route_log}"
      ip route del ${default_route}
      # run other services
      dns_resolver_service
      memory_cleaner_service
      ping_loop_service
      break
    fi
    counter=$[${counter} + 1]
    # max retries reach
    if [[ "${counter}" -eq "${max_retries}" ]]; then
      # write not connectivity to service log
      "${LIBERNET_DIR}/bin/log.sh" -w "<span style=\"color: red\">OpenVPN connection unavailable</span>"
      echo -e "OpenVPN connection unavailable!"
      # cancel Libernet service
      cancel_services
      exit 1
    fi
  done
}

function start_services() {
  # clear service log
  "${LIBERNET_DIR}/bin/log.sh" -r
  # write service status: running
  "${LIBERNET_DIR}/bin/log.sh" -s 1
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Starting Libernet service"
  case "${TUNNEL_MODE}" in
    "0")
      ssh_service
      ;;
    "1")
      v2ray_service
      ;;
    "2")
      ssh_ssl_service
      ;;
    "3")
      trojan_service
      ;;
    "4")
      shadowsocks_service
      ;;
    "5")
      openvpn_service
      ;;
  esac
  # write service status: connected
  "${LIBERNET_DIR}/bin/log.sh" -s 2
  # write libernet to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "<span style=\"color: blue\">Libernet ready to used</span>"
  echo -e "Libernet service started!"
}

function stop_services() {
  # write service status: stopping
  "${LIBERNET_DIR}/bin/log.sh" -s 3
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Stopping Libernet service"
  case "${TUNNEL_MODE}" in
    "0")
      "${LIBERNET_DIR}/bin/ssh.sh" -s
      ;;
    "1")
      "${LIBERNET_DIR}/bin/v2ray.sh" -s
      ;;
    "2")
      "${LIBERNET_DIR}/bin/ssh-ssl.sh" -s
      ;;
    "3")
      "${LIBERNET_DIR}/bin/trojan.sh" -s
      ;;
    "4")
      "${LIBERNET_DIR}/bin/shadowsocks.sh" -s
      ;;
    "5")
      "${LIBERNET_DIR}/bin/openvpn.sh" -s
      ;;
  esac
  if [[ "${1}" != '-c' ]]; then
    # kill tun2socks if not openvpn
    if [[ "${TUNNEL_MODE}" != '5' ]]; then
      "${LIBERNET_DIR}/bin/tun2socks.sh" -w
    fi
    # kill memory cleaner service
    if [[ "${MEMORY_CLEANER}" == 'true' ]]; then
      "${LIBERNET_DIR}/bin/memory-cleaner.sh" -s
    fi
    # kill ping loop service
    if [[ "${PING_LOOP}" == 'true' ]]; then
      "${LIBERNET_DIR}/bin/ping-loop.sh" -s
    fi
    # kill dns resolver
    if [[ "${DNS_RESOLVER}" == 'true' ]]; then
      "${LIBERNET_DIR}/bin/dns.sh" -s
    fi
  fi
  # write service status: stop
  "${LIBERNET_DIR}/bin/log.sh" -s 0
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "<span style=\"color: gray\">Libernet service stopped</span>"
  echo -e "Libernet services stopped!"
}

function cancel_services() {
  stop_services -c
}

function auto_start() {
  while true; do
    # switch usb mode until active
    usbmode -s > /dev/null 2>&1 &
    # reset all service log
    "${LIBERNET_DIR}/bin/log.sh" -ra
    if ip route show | grep -q default; then
      # start Libernet service
      start_services
      break
    fi
    echo -e "Waiting available connection, try again"
    sleep 3
  done
}

function enable_auto_start() {
  # force re-enable
  echo -e "Enable Libernet auto start ..."
  sed -i "/service.sh -as/d" /etc/rc.local
  sed -i "s/exit 0/$(echo "export LIBERNET_DIR=\"${LIBERNET_DIR}\" \&\& screen -AmdS libernet ${LIBERNET_DIR}/bin/service.sh -as" | sed 's/\//\\\//g')\nexit 0/g" /etc/rc.local \
    && echo -e "Libernet auto start enabled!"
}

function disable_auto_start() {
  echo -e "Disable Libernet auto start ..."
  sed -i "/service.sh -as/d" /etc/rc.local \
    && echo -e "Libernet auto start disabled!"
}

case "${1}" in
  -sh)
    ssh_service
    ;;
  -sshl)
    ssh_ssl_service
    ;;
  -sv)
    v2ray_service
    ;;
  -tr)
    trojan_service
    ;;
  -ss)
    shadowsocks_service
    ;;
  -so)
    openvpn_service
    ;;
  -sl)
    start_services
    ;;
  -ds)
    stop_services
    ;;
  -cl)
    cancel_services
    ;;
  -ea)
    enable_auto_start
    ;;
  -da)
    disable_auto_start
    ;;
  -as)
    auto_start
    ;;
esac
