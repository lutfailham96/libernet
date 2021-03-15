#!/bin/bash

# Libernet Installer
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

ARCH="$(grep 'DISTRIB_ARCH' /etc/openwrt_release | awk -F '=' '{print $2}' | sed "s/'//g")"
LIBERNET_DIR="/root/libernet"
LIBERNET_WWW="/www/libernet"

function install_packages() {
  while IFS= read -r line; do
    opkg install "${line}"
  done < requirements.txt
}

function install_proprietary_binaries() {
  echo -e "Copying proprietary binaries" \
    && cp -arvf proprietary/${ARCH}/binaries/* /usr/bin/
}

function install_proprietary_packages() {
  echo -e "Installing proprietary packages" \
    && opkg install proprietary/${ARCH}/packages/*.ipk
}

function install_prerequisites() {
  # update packages index
  opkg update
}

function install_requirements() {
  echo -e "Installing packages" \
    && install_prerequisites \
    && install_packages \
    && install_proprietary_binaries \
    && install_proprietary_packages
}

function enable_uhttp_php() {
  echo -e "Enabling uhttp php execution" \
    && sed -i '/^#.*php-cgi/s/^#//' '/etc/config/uhttpd' \
    && uci commit uhttpd \
    && echo -e "Restarting uhttp service" \
    && /etc/init.d/uhttpd restart
}

function add_libernet_environment() {
  if ! grep -q LIBERNET_DIR /etc/profile; then
    echo -e "Adding Libernet environment" \
      && echo -e "# Libernet\nexport LIBERNET_DIR=${LIBERNET_DIR}" | tee -a '/etc/profile'
  fi
}

function install_libernet() {
  echo -e "Installing Libernet" \
    && mkdir -p "${LIBERNET_DIR}" \
    && echo -e "Copying updater script" \
    && cp -avf update.sh "${LIBERNET_DIR}/" \
    && echo -e "Copying binary" \
    && cp -arvf bin "${LIBERNET_DIR}/" \
    && echo -e "Copying system" \
    && cp -arvf system "${LIBERNET_DIR}/" \
    && echo -e "Copying log" \
    && cp -arvf log "${LIBERNET_DIR}/" \
    && echo -e "Copying web files" \
    && mkdir -p "${LIBERNET_WWW}" \
    && cp -arvf web/* "${LIBERNET_WWW}/" \
    && echo -e "Configuring Libernet" \
    && sed -i "s/LIBERNET_DIR/$(echo ${LIBERNET_DIR} | sed 's/\//\\\//g')/g" "${LIBERNET_WWW}/config.inc.php"
}

function configure_libernet_firewall() {
  if ! uci get network.libernet > /dev/null 2>&1; then
    echo "Configuring Libernet firewall" \
      && uci set network.libernet=interface \
      && uci set network.libernet.proto='none' \
      && uci set network.libernet.ifname='tun1' \
      && uci commit \
      && uci add firewall zone \
      && uci set firewall.@zone[-1].network='libernet' \
      && uci set firewall.@zone[-1].name='libernet' \
      && uci set firewall.@zone[-1].masq='1' \
      && uci set firewall.@zone[-1].mtu_fix='1' \
      && uci set firewall.@zone[-1].input='REJECT' \
      && uci set firewall.@zone[-1].forward='REJECT' \
      && uci set firewall.@zone[-1].output='ACCEPT' \
      && uci commit \
      && uci add firewall forwarding \
      && uci set firewall.@forwarding[-1].src='lan' \
      && uci set firewall.@forwarding[-1].dest='libernet' \
      && uci commit \
      && /etc/init.d/network restart
  fi
}

function configure_libernet_service() {
  echo -e "Configuring Libernet service"
  # disable dns resolver startup
  /etc/init.d/https-dns-proxy disable
}

function finish_install() {
  echo -e "Libernet successfully installed!\nLibernet URL: http://router-ip/libernet"
}

install_requirements \
  && install_libernet \
  && add_libernet_environment \
  && enable_uhttp_php \
  && configure_libernet_firewall \
  && configure_libernet_service \
  && finish_install
