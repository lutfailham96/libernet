#!/bin/bash

# SSH-SSL Connector
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

SYSTEM_CONFIG="${LIBERNET_DIR}/system/config.json"
SSH_SSL_PROFILE="$(grep 'ssh_ssl":' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
SSH_SSL_CONFIG="${LIBERNET_DIR}/bin/config/ssh_ssl/${SSH_SSL_PROFILE}.json"
SSH_SSL_HOST="$(grep 'host:"' ${SSH_SSL_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
SSH_SSL_PORT="$(grep 'port:"' ${SSH_SSL_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')"
SSH_SSL_USER="$(grep 'username:"' ${SSH_SSL_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
SSH_SSL_PASS="$(grep 'password:"' ${SSH_SSL_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
SSH_SSL_SNI="$(grep 'sni:"' ${SSH_SSL_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g')"
STUNNEL_CONFIG="${LIBERNET_DIR}/bin/config/ssh_ssl/${SSH_SSL_PROFILE}.conf"
DYNAMIC_PORT="$(grep 'port:"' ${SYSTEM_CONFIG} | awk '{print $2}' | sed 's/,//g; s/"//g' | sed -n '1p')"

function start_ssh_ssl() {
  configure_ssh_ssl \
    && screen -AmdS ssh-ssl-stunnel stunnel "${STUNNEL_CONFIG}" \
    && screen -AmdS ssh-ssl-connector "${LIBERNET_DIR}/bin/ssh-ssl-loop.sh" "${SSH_SSL_USER}" "${SSH_SSL_PASS}" "${DYNAMIC_PORT}"
}

function stop_ssh_ssl() {
  kill $(screen -list | grep ssh-ssl-connector | awk -F '[.]' {'print $1'})
  kill $(screen -list | grep ssh-ssl-stunnel | awk -F '[.]' {'print $1'})
  killall stunnel
}

function configure_ssh_ssl() {
  # copying from template
  cp -af "${LIBERNET_DIR}/bin/config/ssh_ssl/templates/ssh-ssl.conf" "${STUNNEL_CONFIG}"
  # updating host & port value
  sed -i "s/^connect = .*/connect = ${SSH_SSL_HOST}:${SSH_SSL_PORT}/g" "${STUNNEL_CONFIG}"
  # updating sni value
  sed -i "s/^sni = .*/sni = ${SSH_SSL_SNI}/g" "${STUNNEL_CONFIG}"
  # updating cert value
  sed -i "s/^cert = .*/cert = $(echo ${LIBERNET_DIR}/bin/config/ssh_ssl/ssh-ssl.pem | sed 's/\//\\\//g')/g" "${STUNNEL_CONFIG}"
}

while getopts ":rs" opt; do
  case ${opt} in
  r)
    start_ssh_ssl > /dev/null 2>&1
    echo -e "SSH-SSL started!"
    ;;
  s)
    stop_ssh_ssl > /dev/null 2>&1
    echo -e "SSH-SSL stopped!"
    ;;
  *)
    echo -e "Usage:"
    echo -e "\t-r\tRun SSH-SSL"
    echo -e "\t-s\tStop SSH-SSL"
    ;;
  esac
done