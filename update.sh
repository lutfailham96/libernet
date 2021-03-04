#!/bin/bash

echo -e "Updating Libernet ..." \
  && git fetch origin master \
  && git reset --hard FETCH_HEAD \
  && bash ./install.sh \
  && echo -e "\nLibernet successfully updated!"