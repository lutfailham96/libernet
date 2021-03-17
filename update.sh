#!/bin/bash

REPOSITORY_URL="git://github.com/lutfailham96/libernet.git"

function update_libernet() {
  if git branch > /dev/null 2>&1; then
    update_libernet_cli
  else
    echo -e "This is not Libernet installer directory, please use installer directory to update Libernet!"
  fi
}

function update_libernet_cli() {
  echo -e "Updating Libernet ..." \
    && git fetch origin master \
    && git reset --hard FETCH_HEAD \
    && bash install.sh \
    && echo -e "\nLibernet successfully updated!"
}

function update_libernet_web() {
  DOWNLOADS_DIR="${HOME}/Downloads"
  LIBERNET_TMP="${DOWNLOADS_DIR}/libernet"
  # create & change working directory
  mkdir -p "${DOWNLOADS_DIR}" \
    && cd "${DOWNLOADS_DIR}"
  # update Libernet
  "${LIBERNET_DIR}/bin/log.sh" -u 1
  if [[ -d "${LIBERNET_TMP}" ]]; then
    cd "${LIBERNET_TMP}" \
      && update_libernet_cli
  else
    git clone "${REPOSITORY_URL}" \
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