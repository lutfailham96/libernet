#!/bin/bash

# Libernet Updater
# by Lutfa Ilham
# v1.0

HOME="/root"
DOWNLOADS_DIR="${HOME}/Downloads"
LIBERNET_TMP="${DOWNLOADS_DIR}/libernet"
REPOSITORY_URL="git://github.com/lutfailham96/libernet.git"

function update_libernet() {
  if [[ ! -d "${LIBERNET_TMP}" ]]; then
    echo -e "There's no Libernet installer on ~/Downloads directory, please clone it first!"
    exit 1
  fi
  # change working dir to Libernet installer
  cd "${LIBERNET_TMP}"
  # verify Libernet installer
  if git branch > /dev/null 2>&1; then
    update_libernet_cli
  else
    echo -e "This is not Libernet installer directory, please use installer directory to update Libernet!"
    exit 1
  fi
}

function update_libernet_cli() {
  echo -e "Updating Libernet ..." \
    && git fetch origin main \
    && git reset --hard FETCH_HEAD \
    && bash install.sh \
    && echo -e "\nLibernet successfully updated!"
}

function update_libernet_web() {
  # create downloads directory if not exist
  if [[ ! -d "${DOWNLOADS_DIR}" ]]; then
    mkdir -p "${DOWNLOADS_DIR}"
  fi
  # update Libernet
  "${LIBERNET_DIR}/bin/log.sh" -u 1
  if [[ -d "${LIBERNET_TMP}" ]]; then
    update_libernet
  else
    git clone --depth 1 "${REPOSITORY_URL}" "${LIBERNET_TMP}" \
      && cd "${LIBERNET_TMP}" \
      && bash install.sh \
      && echo -e "\nLibernet successfully updated!"
  fi
  "${LIBERNET_DIR}/bin/log.sh" -u 2
}

case $1 in
  -web)
    update_libernet_web || "${LIBERNET_DIR}/bin/log.sh" -u 3
    ;;
  *)
    update_libernet
    ;;
esac
