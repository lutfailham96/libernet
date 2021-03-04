#!/bin/bash

# SSH-SSL Loop
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

function connect_ssh_ssl() {
  sshpass -p "${2}" ssh \
    -4CND "${3}" \
    -p 10443 \
    -o StrictHostKeyChecking=no \
    -o UserKnownHostsFile=/dev/null \
    "${1}@127.0.0.1"
}

while true; do
  # command username password dynamic_port
  connect_ssh_ssl $1 $2 $3
  sleep 3
done