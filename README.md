<p align="center">
  <img src="https://i.ibb.co/TwD8Vyy/Screenshot-from-2021-03-18-21-24-17.png" alt="dashboard" />
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
- V2Ray VMess
- V2Ray VLESS
- V2Ray Trojan
- Trojan
- Shadowsocks

## Installation
- If you don't have bash & curl on OpenWRT, please install first:
```sh
opkg update && opkg install bash curl
```
- Run installation script:
```sh
bash -c "$(curl -sko - 'https://raw.githubusercontent.com/lutfailham96/libernet/master/install.sh')"
```
- Open Libernet on your browser: http://router-ip/libernet
- Fill your tunnel server, save configuration & run Libernet

## Updating
- Just run updater script:
```sh
bash ~/Downloads/libernet/update.sh
```
- Updater script will updating Libernet to latest version

## Default Username & Password
- Username: admin
- Password: libernet

## Additional Information
In home menu, check 'Use tun2socks legacy' to use badvpn-tun2socks or uncheck to use go-tun2socks instead.
