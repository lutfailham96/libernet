#!/usr/bin/env bash

# DNS Wrapper
# by Lutfa Ilham
# v1.0.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SERVICE_NAME="DNS Resolver"
SCREEN_NAME="stubby"

check() {
  if screen -list | grep -q "${SCREEN_NAME}"; then
    echo -e "${SERVICE_NAME} service already running, exiting ..."
    exit 1
  fi
}

run() {
  check
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Starting ${SERVICE_NAME} service"
  echo -e "Starting ${SERVICE_NAME} service ..."
  # initialize iptables
  iptables -w -t nat -A OUTPUT ! -d 127.0.0.1 -p udp --dport 53 -j REDIRECT --to-ports 5453 2> /dev/null
  iptables -w -t nat -A OUTPUT ! -d 127.0.0.1 -p tcp --dport 53 -j REDIRECT --to-ports 5453 2> /dev/null
  screen -AmdS "${SCREEN_NAME}" stubby -C "${LIBERNET_DIR}/config/dns/stubby.yml" -v 0 \
    && echo -e "${SERVICE_NAME} service started!"
}

stop() {
  # write to service log
  "${LIBERNET_DIR}/bin/log.sh" -w "Stopping ${SERVICE_NAME} service"
  echo -e "Stopping ${SERVICE_NAME} service ..."
  # remove iptables
  iptables -w -t nat -D OUTPUT ! -d 127.0.0.1 -p udp --dport 53 -j REDIRECT --to-ports 5453 2> /dev/null
  iptables -w -t nat -D OUTPUT ! -d 127.0.0.1 -p tcp --dport 53 -j REDIRECT --to-ports 5453 2> /dev/null
  kill "$(screen -list | grep "${SCREEN_NAME}" | awk -F '[.]' '{print $1}')" 2> /dev/null
  killall stubby 2> /dev/null
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
