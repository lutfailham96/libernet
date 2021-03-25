#!/bin/bash

# DNS Wrapper
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SERVICE_NAME="DNS Resolver"

function run() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Starting ${SERVICE_NAME} service"
  echo -e "Starting ${SERVICE_NAME} service ..."
  # initialize iptables
  iptables -t nat -A OUTPUT -p udp --dport 53 -j DNAT --to-destination 127.0.0.1:5453
  iptables -t nat -A OUTPUT -p tcp --dport 53 -j DNAT --to-destination 127.0.0.1:5453
  screen -AmdS stubby stubby -C "${LIBERNET_DIR}/bin/config/dns/stubby.yml" -v 0 \
    && echo -e "${SERVICE_NAME} service started!"
}

function stop() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Stopping ${SERVICE_NAME} service"
  echo -e "Stopping ${SERVICE_NAME} service ..."
  # remove iptables
  iptables -t nat -D OUTPUT -p udp --dport 53 -j DNAT --to-destination 127.0.0.1:5453
  iptables -t nat -D OUTPUT -p tcp --dport 53 -j DNAT --to-destination 127.0.0.1:5453
  kill $(screen -list | grep stubby | awk -F '[.]' {'print $1'})
  killall stubby
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
