# Libernet
Libernet is open source web app for tunneling internet using SSH, V2Ray on OpenWRT with ease.

## Requirements
- bash
- screen
- jq
- Python 3
- OpenSSH
- sshpass
- stunnel
- V2Ray
- go-tun2socks
- badvpn-tun2socks (legacy)
- php7
- php7-cgi
- php7-mod-session
- php7-mod-json

## Working Features:
- SSH with proxy
- SSH-SSL
- V2Ray trojan
- V2Ray vmess

## Installation
- If you don't have git on OpenWRT, please install first via terminal: ```opkg update && opkg install git```
- Clone this repository on OpenWRT via terminal: ```mkdir -p ~/Downloads && cd ~/Downloads && git clone git://github.com/lutfailham96/libernet.git```
- Run install script: ```cd libernet && bash install.sh```
- Open Libernet on http://router-ip/libernet
- Fill your tunnel server & run Libernet
- Don't forget to update firewall configuration on 'tun1' device to WAN via Luci

## Default Username & Password
- Username: admin
- Password: libernet

## Additional Information
In home menu, check 'Use tun2socks legacy' to use badvpn-tun2socks or uncheck to use go-tun2socks instead.
