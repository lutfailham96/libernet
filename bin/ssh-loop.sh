#!/bin/bash

# SSH Loop
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
SSH_PROFILE="$(jq -r '.tunnel.profile.ssh' < ${SYSTEM_CONFIG})"
SSH_CONFIG="${LIBERNET_DIR}/bin/config/ssh/${SSH_PROFILE}.json"
SSH_HOST="$(jq -r '.host' < ${SSH_CONFIG})"
SSH_PORT="$(jq -r '.port' < ${SSH_CONFIG})"
SSH_USER="$(jq -r '.username' < ${SSH_CONFIG})"
SSH_PASS="$(jq -r '.password' < ${SSH_CONFIG})"
PROXY_IP="$(jq -r '.http.ip' < ${SSH_CONFIG})"
PROXY_PORT="$(jq -r '.http.port' < ${SSH_CONFIG})"
DYNAMIC_PORT="$(jq -r '.tun2socks.socks.port' < ${SYSTEM_CONFIG})"
ENABLE_HTTP="$(jq -r '.enable_http' < ${SSH_CONFIG})"

function connect_ssh() {
  if [[ $ENABLE_HTTP == 'true' ]]; then
    sshpass -p "${SSH_PASS}" ssh \
      -4CND "${DYNAMIC_PORT}" \
      -p "${SSH_PORT}" \
      -o ProxyCommand="/usr/bin/corkscrew ${PROXY_IP} ${PROXY_PORT} %h %p" \
      -o StrictHostKeyChecking=no \
      -o UserKnownHostsFile=/dev/null \
      "${SSH_USER}@${SSH_HOST}"
  else
    sshpass -p "${SSH_PASS}" ssh \
      -4CND "${DYNAMIC_PORT}" \
      -p "${SSH_PORT}" \
      -o StrictHostKeyChecking=no \
      -o UserKnownHostsFile=/dev/null \
      "${SSH_USER}@${SSH_HOST}"
  fi
}

while true; do
  connect_ssh
  sleep 3
done