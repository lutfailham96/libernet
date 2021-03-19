#!/bin/bash

# SSH Loop Wrapper
# by Lutfa Ilham
# v1.0

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

function connect() {
  sshpass -p "${2}" ssh \
    -4CND "${5}" \
    -p "${4}" \
    -o StrictHostKeyChecking=no \
    -o UserKnownHostsFile=/dev/null \
    "${1}@${3}"
}

function connect_with_proxy() {
  sshpass -p "${2}" ssh \
    -4CND "${5}" \
    -p "${4}" \
    -o ProxyCommand="/usr/bin/corkscrew ${6} ${7} %h %p" \
    -o StrictHostKeyChecking=no \
    -o UserKnownHostsFile=/dev/null \
    "${1}@${3}"
}

case "${1}" in
  -d)
    while true; do
      # command username password host port dynamic_port
      connect "${2}" "${3}" "${4}" "${5}" "${6}"
      sleep 3
    done
    ;;
  -e)
    while true; do
      # command username password host port dynamic_port proxy_ip proxy_port
      connect_with_proxy "${2}" "${3}" "${4}" "${5}" "${6}" "${7}" "${8}"
      sleep 3
    done
    ;;
esac
