# Libernet
Libernet is open source web app for tunneling internet using SSH, V2Ray, Trojan, Shadowsocks on OpenWRT with ease.

### Working Features:
- SSH with proxy
- SSH-SSL
- V2Ray VMess
- V2Ray VLESS
- V2Ray Trojan
- Trojan
- Shadowsocks

<details><summary>Requirements packages (click to expand)</summary>
<p>
 
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

</p>
</details>

### Installation
- If you don't have git on OpenWRT, please install first: ```opkg update && opkg install git```
- Prepare installation directory: ```mkdir -p ~/Downloads && cd ~/Downloads```
- Clone this repository: ```git clone git://github.com/lutfailham96/libernet.git```
- Run installation script: ```cd libernet && bash install.sh```
- Open Libernet on http://openwrt.lan/libernet
- Fill your tunnel server, save configuration & run Libernet

### Updating
- Just run updater script:
```sh
cd ~/Downloads/libernet && bash update.sh
```
- Updater script will updating Libernet to latest version

### Default Username & Password
- Username: admin
- Password: libernet

### Additional Information
In home menu, check 'Use tun2socks legacy' to use badvpn-tun2socks or uncheck to use go-tun2socks instead.
