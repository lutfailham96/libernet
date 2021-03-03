#!/bin/bash

# Libernet Installer
# by Lutfa Ilham
# v1.1

if [ "$(id -u)" != "0" ]; then
  echo "This script must be run as root" 1>&2
  exit 1
fi

LIBERNET_DIR="/root/libernet"
LIBERNET_DIR_ESCAPED="\/root\/libernet"
LIBERNET_WWW="/www/libernet"

function install_packages() {
  while IFS= read -r line; do
    opkg install "${line}"
  done < requirements.txt
}

function install_requirements() {
  echo -e "Installing packages" \
    && opkg update \
    && install_packages \
    && echo -e "Copying proprietary binary" \
    && cp -avpf proprietary/* /usr/bin/
}

function enable_uhttp_php() {
  echo -e "Enabling uhttp php execution" \
    && sed -i '/^#.*php-cgi/s/^#//' '/etc/config/uhttpd' \
    && uci commit uhttpd \
    && echo -e "Restarting uhttp service" \
    && /etc/init.d/uhttpd restart
}

function add_libernet_environment() {
  echo -e "Adding Libernet environment" \
    && echo -e "# Libernet\nexport LIBERNET_DIR=${LIBERNET_DIR}" | tee -a '/etc/profile'
}

function install_libernet() {
  echo -e "Installing Libernet" \
    && mkdir -p "${LIBERNET_DIR}" \
    && echo -e "Copying binary" \
    && mkdir -p "${LIBERNET_DIR}/bin" \
    && cp -avpf bin/* "${LIBERNET_DIR}/bin/" \
    && echo -e "Copying system" \
    && mkdir -p "${LIBERNET_DIR}/system" \
    && cp -avpf system/* "${LIBERNET_DIR}/system/" \
    && echo -e "Copying log" \
    && mkdir -p "${LIBERNET_DIR}/log" \
    && cp -avpf log/* "${LIBERNET_DIR}/log/" \
    && echo -e "Copying web files" \
    && mkdir -p "${LIBERNET_WWW}" \
    && cp -avpf web/* "${LIBERNET_WWW}/" \
    && echo -e "Configuring Libernet" \
    && sed -i "s/LIBERNET_DIR/${LIBERNET_DIR_ESCAPED}/g" "${LIBERNET_WWW}/config.inc.php"
}

function finish_install() {
    echo -e "Libernet successfully installed!\nURL: http://router-ip/libernet"
}

install_requirements \
  && install_libernet \
  && add_libernet_environment \
  && enable_uhttp_php \
  && finish_install