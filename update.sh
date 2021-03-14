#!/bin/bash

function update_libernet() {
  echo -e "Updating Libernet ..." \
  && git fetch origin master \
  && git reset --hard FETCH_HEAD \
  && bash ./install.sh \
  && echo -e "\nLibernet successfully updated!"
}

function update_libernet_web() {
  libernet_tmp="/tmp/libernet"
  echo 1 > "${LIBERNET_DIR}/log/update.log" \
    && cd /tmp \
    && rm -rf "${libernet_tmp}" \
    && git clone git://github.com/lutfailham96/libernet.git \
    && cd "${libernet_tmp}" \
    && bash install.sh \
    && cd /tmp \
    && rm -rf "${libernet_tmp}" \
    && echo 2 > "${LIBERNET_DIR}/log/update.log"
  killall git
  killall update.sh
}

case $1 in
  -web)
    update_libernet_web || echo 3 > "${LIBERNET_DIR}/log/update.log"
    ;;
  *)
    update_libernet
    ;;
esac