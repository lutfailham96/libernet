#!/bin/bash

# DNS Wrapper
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

function setup_dns() {
  # reset dns settings
  while uci -q delete https-dns-proxy.@https-dns-proxy[0]; do :; done
  # setup dns
  uci set https-dns-proxy.dns="https-dns-proxy"
  uci set https-dns-proxy.dns.bootstrap_dns="94.140.14.14,94.140.15.15"
  uci set https-dns-proxy.dns.resolver_url="https://dns.adguard.com/dns-query"
  uci set https-dns-proxy.dns.listen_addr="127.0.0.1"
  uci set https-dns-proxy.dns.listen_port="5053"
  uci commit https-dns-proxy
}

function start_dns() {
  setup_dns \
    && /etc/init.d/https-dns-proxy start
}

function stop_dns() {
  /etc/init.d/https-dns-proxy stop
}

while getopts ":rs" opt; do
  case ${opt} in
  r)
    start_dns > /dev/null 2>&1
    echo -e "DNS resolver started!"
    ;;
  s)
    stop_dns > /dev/null 2>&1
    echo -e "DNS resolver stopped!"
    ;;
  *)
    echo -e "Usage:"
    echo -e "\t-r\tRun DNS resolver"
    echo -e "\t-s\tStop DNS resolver"
    ;;
  esac
done