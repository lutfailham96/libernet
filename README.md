<p align="center">
  <img src="https://i.ibb.co/ccZHLCR/Screenshot-from-2022-02-22-13-50-31.png" alt="dashboard" />
</p>

# Libernet
Libernet is open source web app for tunneling internet using SSH, V2Ray, Trojan, Shadowsocks, OpenVPN on OpenWRT with ease.

## Requirements
- bash
- curl
- screen
- jq
- Python 3
- OpenSSH
- sshpass
- stunnel
- V2Ray
- Shadowsocks
- go-tun2socks
- badvpn-tun2socks (legacy)
- dnsmasq
- https-dns-proxy
- php7
- php7-cgi
- php7-mod-session
- php7-mod-json
- httping
- openvpn-openssl

## Working Features:
- SSH with proxy
- SSH-SSL
- SSH-WS-SSL (CDN)
- V2Ray VMess
- V2Ray VLESS
- V2Ray Trojan
- Trojan
- Shadowsocks
- OpenVPN

## Installation
- If you don't have bash & curl on OpenWRT, please install first:
```sh
opkg update && opkg install bash curl
```
- Run installation script:
```sh
bash -c "$(curl -sko - 'https://raw.githubusercontent.com/lutfailham96/libernet/main/install.sh')"
```
- Reboot router, if necessary
- Open Libernet on your browser: http://router-ip/libernet
- Fill your tunnel server, save configuration & run Libernet

## Updating
- Just run updater script:
```sh
bash ~/Downloads/libernet/update.sh
```
- Updater script will updating Libernet to latest version

## Fresh Install / Fresh Update
- Remove Libernet installer directory
```sh
rm -rf ~/Downloads/libernet
```
- Run Libernet online installer
```sh
bash -c "$(curl -sko - 'https://raw.githubusercontent.com/lutfailham96/libernet/main/install.sh')"
```
- Latest version Libernet will be installed on your system

## Installation Note
Don't forget to always clear browser cache after installing or upgrading Libernet to prevent unwanted error.

## Default Username & Password
- Username: admin
- Password: libernet

## Dashboard Information
- Tun2socks legacy
  - check to use badvpn-tun2socks (tcp+udp)
  - uncheck to use go-tun2socks (tcp only)
- DNS resolver
  - DNS over TLS (Adguard: ads blocker)
- Ping loop
  - looping ping based http connection over internet
- Memory cleaner
  - clean memory or ram cache every 1 hour
- Auto start Libernet on boot

