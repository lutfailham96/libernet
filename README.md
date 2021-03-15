# Libernet
Libernet is open source web app for tunneling internet using SSH, V2Ray, Trojan, Shadowsocks on OpenWRT with ease.

## Requirements
- bash
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
- dnsmasq-full
- https-dns-proxy
- php7
- php7-cgi
- php7-mod-session
- php7-mod-json

## Working Features:
- SSH with proxy
- SSH-SSL
- V2Ray VMess
- V2Ray VLESS
- V2Ray Trojan
- Trojan
- Shadowsocks

## Installation
- Prepare installation directory: 
```sh
mkdir -p ~/Downloads && cd ~/Downloads
```
- Clone this repository: 
```sh
git clone git://github.com/lutfailham96/libernet.git
```
- Run installation script:
```sh
cd libernet && bash install.sh
```
- Open Libernet on http://router-ip/libernet
- Fill your tunnel server, save configuration & run Libernet

## Updating
- Just run updater script: 
```sh
cd ~/Downloads/libernet && bash update.sh
```
- Updater script will updating Libernet to latest version

## Default Username & Password
- Username: admin
- Password: libernet

## Additional Information
In home menu, check 'Use tun2socks legacy' to use badvpn-tun2socks or uncheck to use go-tun2socks instead.
