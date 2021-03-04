#!/bin/bash

echo -e "Updating Libernet ..." \
  && git pull \
  && bash ./install.sh \
  && echo -e "\nLibernet successfully updated!"