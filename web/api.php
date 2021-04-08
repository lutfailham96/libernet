<?php
    include('config.inc.php');
    //include('auth.php');
    //check_session();

    function json_response($data) {
        $resp = array(
            'status' => 'OK',
            'data' => $data
        );
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($resp, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    function get_profiles($mode) {
        global $libernet_dir;
        $profiles = array();
        if ($handle = opendir($libernet_dir.'/bin/config/'.$mode.'/')) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'json') {
                    array_push($profiles, preg_replace('/\\.[^.\\s]{3,4}$/', '', $file));
                }
            }
            closedir($handle);
        }
        json_response($profiles);
    }

    function get_config($mode, $profile) {
        global $libernet_dir;
        $data = null;
        $config = null;
        if ($profile) {
            $config = file_get_contents($libernet_dir.'/bin/config/'.$mode.'/'.$profile.'.json');
        } else {
            $system_config = file_get_contents($libernet_dir.'/system/config.json');
            $system_config = json_decode($system_config);
            $config = file_get_contents($libernet_dir.'/bin/config/'.$mode.'/'.$system_config->tunnel->profile->$mode.'.json');
        }
        $data = json_decode($config);
        json_response($data);
    }

    function set_v2ray_config($config, $protocol, $network, $security, $sni, $path, $ip, $udpgw_ip, $udpgw_port) {
        $config->outbounds[0]->protocol = $protocol;
        $config->outbounds[0]->streamSettings->network = $network;
        $config->outbounds[0]->streamSettings->security = $security;
        // forcing security to none if network http
        if ($network === 'http') {
            $config->outbounds[0]->streamSettings->security = 'none';
        }
        // tls
        $config->outbounds[0]->streamSettings->tlsSettings->serverName = $sni;
        // ws
        $config->outbounds[0]->streamSettings->wsSettings->path = $path;
        $config->outbounds[0]->streamSettings->wsSettings->headers->Host = $sni;
        // http
        $config->outbounds[0]->streamSettings->httpSettings->host[0] = $sni;
        $config->outbounds[0]->streamSettings->httpSettings->path = $path;
        // misc
        $config->etc->ip = $ip;
        $config->etc->udpgw->ip = $udpgw_ip;
        $config->etc->udpgw->port = $udpgw_port;
    }

    function set_auto_start($status) {
        global $libernet_dir;
        $system_config = file_get_contents($libernet_dir.'/system/config.json');
        $system_config = json_decode($system_config);
        if ($status) {
            // enable auto start
            exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/service.sh -ea');
            $system_config->tunnel->autostart = true;
        } else {
            // disable auto start
            exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/service.sh -da');
            $system_config->tunnel->autostart = false;
        }
        $system_config = json_encode($system_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($libernet_dir.'/system/config.json', $system_config);
    }

    if (isset($_POST)) {
        $json = json_decode(file_get_contents('php://input'), true);
        switch ($json['action']) {
            case 'get_system_config':
                $system_config = file_get_contents($libernet_dir.'/system/config.json');
                $data = json_decode($system_config);
                json_response($data);
                break;
            case 'get_ssh_config':
                $profile = $json['profile'];
                get_config('ssh', $profile);
                break;
            case 'get_sshl_config':
                $profile = $json['profile'];
                get_config('ssh_ssl', $profile);
                break;
            case 'get_v2ray_config':
                $profile = $json['profile'];
                get_config('v2ray', $profile);
                break;
            case 'get_trojan_config':
                $profile = $json['profile'];
                get_config('trojan', $profile);
                break;
            case 'get_shadowsocks_config':
                $profile = $json['profile'];
                get_config('shadowsocks', $profile);
                break;
            case 'get_openvpn_config':
                $profile = $json['profile'];
                get_config('openvpn', $profile);
                break;
            case 'get_v2ray_configs':
                get_profiles('v2ray');
                break;
            case 'get_ssh_configs':
                get_profiles('ssh');
                break;
            case 'get_sshl_configs':
                get_profiles('ssh_ssl');
                break;
            case 'get_trojan_configs':
                get_profiles('trojan');
                break;
            case 'get_shadowsocks_configs':
                get_profiles('shadowsocks');
                break;
            case 'get_openvpn_configs':
                get_profiles('openvpn');
                break;
            case 'start_libernet':
                $system_config = file_get_contents($libernet_dir.'/system/config.json');
                $system_config = json_decode($system_config);
                exec('export LIBERNET_DIR='.$libernet_dir.' && '.$libernet_dir.'/bin/service.sh -sl');
                json_response('Libernet service started');
                break;
            case 'cancel_libernet':
                exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/service.sh -cl');
                json_response('Libernet service canceled');
                break;
            case 'stop_libernet':
                exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/service.sh -ds');
                json_response('Libernet service stopped');
                break;
            case 'get_dashboard_info':
                $status = file_get_contents($libernet_dir.'/log/status.log');
                $log = file_get_contents($libernet_dir.'/log/service.log');
                $connected = file_get_contents($libernet_dir.'/log/connected.log');
                // use hard coded tun device
                exec("ifconfig tun1 | grep 'bytes:' | awk '{print $3, $4}' | sed 's/(//g; s/)//g'", $rx);
                exec("ifconfig tun1 | grep 'bytes:' | awk '{print $7, $8}' | sed 's/(//g; s/)//g'", $tx);
                json_response(array(
                    'status' => intval($status),
                    'log' => $log,
                    'connected' => $connected,
                    'total_data' => [
                        'tx' => implode($tx),
                        'rx' => implode($rx),
                    ]
                ));
                break;
            case 'save_config':
                if (isset($json['data'])) {
                    $system_config = file_get_contents($libernet_dir.'/system/config.json');
                    $system_config = json_decode($system_config);
                    $data = $json['data'];
                    $mode = $data['mode'];
                    $profile = $data['profile'];
                    $config = $data['config'];
                    switch ($mode) {
                        // ssh
                        case 0:
                            file_put_contents($libernet_dir.'/bin/config/ssh/'.$profile.'.json', json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                            json_response('SSH config saved');
                            break;
                        // v2ray
                        case 1:
                            $protocol = $config['protocol'];
                            $network = $config['network'];
                            $security = $config['security'];
                            // remote server
                            $host = $config['server']['host'];
                            $port = $config['server']['port'];
                            // user settings
                            $user_level = $config['server']['user']['level'];
                            $vmess_id = $config['server']['user']['vmess']['id'];
                            $vless_id = $config['server']['user']['vless']['id'];
                            $vmess_security = $config['server']['user']['vmess']['security'];
                            $trojan_password = $config['server']['user']['trojan']['password'];
                            // stream settings
                            $sni = $config['stream']['sni'];
                            $path = $config['stream']['path'];
                            // misc
                            $ip = $config['etc']['ip'];
                            $udpgw_ip = $config['etc']['udpgw']['ip'];
                            $udpgw_port = $config['etc']['udpgw']['port'];
                            switch ($protocol) {
                                // vmess
                                case "vmess":
                                    $vmess_config = file_get_contents($libernet_dir.'/bin/config/v2ray/templates/vmess.json');
                                    $vmess_config = json_decode($vmess_config);
                                    $vmess_config->outbounds[0]->settings->vnext[0]->address = $host;
                                    $vmess_config->outbounds[0]->settings->vnext[0]->port = $port;
                                    $vmess_config->outbounds[0]->settings->vnext[0]->users[0]->level = $user_level;
                                    $vmess_config->outbounds[0]->settings->vnext[0]->users[0]->alterId = $user_level;
                                    $vmess_config->outbounds[0]->settings->vnext[0]->users[0]->id = $vmess_id;
                                    $vmess_config->outbounds[0]->settings->vnext[0]->users[0]->security = $vmess_security;
                                    set_v2ray_config($vmess_config, $protocol, $network, $security, $sni, $path, $ip, $udpgw_ip, $udpgw_port);
                                    file_put_contents($libernet_dir.'/bin/config/v2ray/'.$profile.'.json', json_encode($vmess_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                    json_response('V2Ray vmess config saved');
                                    break;
                                // vless
                                case "vless":
                                    $vless_config = file_get_contents($libernet_dir.'/bin/config/v2ray/templates/vless.json');
                                    $vless_config = json_decode($vless_config);
                                    $vless_config->outbounds[0]->settings->vnext[0]->address = $host;
                                    $vless_config->outbounds[0]->settings->vnext[0]->port = $port;
                                    $vless_config->outbounds[0]->settings->vnext[0]->users[0]->level = $user_level;
                                    $vless_config->outbounds[0]->settings->vnext[0]->users[0]->id = $vless_id;
                                    set_v2ray_config($vless_config, $protocol, $network, $security, $sni, $path, $ip, $udpgw_ip, $udpgw_port);
                                    file_put_contents($libernet_dir.'/bin/config/v2ray/'.$profile.'.json', json_encode($vless_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                    json_response('V2Ray vless config saved');
                                    break;
                                // trojan
                                case "trojan":
                                    $trojan_config = file_get_contents($libernet_dir.'/bin/config/v2ray/templates/trojan.json');
                                    $trojan_config = json_decode($trojan_config);
                                    $trojan_config->outbounds[0]->settings->servers[0]->address = $host;
                                    $trojan_config->outbounds[0]->settings->servers[0]->port = $port;
                                    $trojan_config->outbounds[0]->settings->servers[0]->level = $user_level;
                                    $trojan_config->outbounds[0]->settings->servers[0]->password = $trojan_password;
                                    set_v2ray_config($trojan_config, $protocol, $network, $security, $sni, $path, $ip, $udpgw_ip, $udpgw_port);
                                    file_put_contents($libernet_dir.'/bin/config/v2ray/'.$profile.'.json', json_encode($trojan_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                                    json_response('V2Ray trojan config saved');
                                    break;
                            }
                            break;
                        // ssh-ssl
                        case 2:
                            file_put_contents($libernet_dir.'/bin/config/ssh_ssl/'.$profile.'.json', json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                            json_response('SSH-SSL config saved');
                            break;
                        // trojan
                        case 3:
                            $trojan_config = file_get_contents($libernet_dir.'/bin/config/trojan/templates/trojan.json');
                            $trojan_config = json_decode($trojan_config);
                            $trojan_config->remote_addr = $config['host'];
                            $trojan_config->remote_port = $config['port'];
                            $trojan_config->password[0] = $config['password'];
                            $trojan_config->ssl->sni = $config['sni'];
                            $trojan_config->etc->ip = $config['ip'];
                            $trojan_config->etc->udpgw->ip = $config['udpgw']['ip'];
                            $trojan_config->etc->udpgw->port = $config['udpgw']['port'];
                            file_put_contents($libernet_dir.'/bin/config/trojan/'.$profile.'.json', json_encode($trojan_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                            json_response('Trojan config saved');
                            break;
                        // shadowsocks
                        case 4:
                            $plugin = $config['plugin'];
                            $shadowsocks_config = null;
                            switch ($plugin) {
                                case 'obfs-local':
                                    $shadowsocks_config = file_get_contents($libernet_dir.'/bin/config/shadowsocks/templates/simple-obfs.json');
                                    $shadowsocks_config = json_decode($shadowsocks_config);
                                    // set obfs security
                                    $shadowsocks_config->plugin_opts = str_replace('obfs_security', $config['simple_obfs'], $shadowsocks_config->plugin_opts);
                                    // set obfs host
                                    $shadowsocks_config->plugin_opts = str_replace('obfs_host', $config['sni'], $shadowsocks_config->plugin_opts);
                                    break;
                                case 'ck-client':
                                    $shadowsocks_config = file_get_contents($libernet_dir.'/bin/config/shadowsocks/templates/cloak.json');
                                    $shadowsocks_config = json_decode($shadowsocks_config);
                                    // set cloak uid
                                    $shadowsocks_config->plugin_opts = str_replace('cloak_uid', $config['cloak']['uid'], $shadowsocks_config->plugin_opts);
                                    // set cloak public key
                                    $shadowsocks_config->plugin_opts = str_replace('cloak_pub', $config['cloak']['public_key'], $shadowsocks_config->plugin_opts);
                                    // set cloak host
                                    $shadowsocks_config->plugin_opts = str_replace('cloak_host', $config['sni'], $shadowsocks_config->plugin_opts);
                                    break;
                                default:
                                    $shadowsocks_config = file_get_contents($libernet_dir.'/bin/config/shadowsocks/templates/normal.json');
                                    $shadowsocks_config = json_decode($shadowsocks_config);
                                    break;
                            }
                            $shadowsocks_config->server = $config['host'];
                            $shadowsocks_config->server_port = $config['port'];
                            $shadowsocks_config->password = $config['password'];
                            $shadowsocks_config->method = $config['method'];
                            $shadowsocks_config->etc->ip = $config['ip'];
                            $shadowsocks_config->etc->udpgw->ip = $config['udpgw']['ip'];
                            $shadowsocks_config->etc->udpgw->port = $config['udpgw']['port'];
                            file_put_contents($libernet_dir.'/bin/config/shadowsocks/'.$profile.'.json', json_encode($shadowsocks_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                            json_response('Shadowsocks config saved');
                            break;
                        // openvpn
                        case 5:
                            file_put_contents($libernet_dir.'/bin/config/openvpn/'.$profile.'.json', json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                            json_response('OpenVPN config saved');
                            break;
                    }
                }
                break;
            case 'apply_config':
                if (isset($json['data'])) {
                    $system_config = file_get_contents($libernet_dir.'/system/config.json');
                    $system_config = json_decode($system_config);
                    $data = $json['data'];
                    $profile = $data['profile'];
                    $mode = $data['mode'];
                    $tun2socks_legacy = $data['tun2socks_legacy'];
                    $dns_resolver = $data['dns_resolver'];
                    $memory_cleaner = $data['memory_cleaner'];
                    $ping_loop = $data['ping_loop'];
                    switch ($mode) {
                        // ssh
                        case 0:
                            $ssh_config = file_get_contents($libernet_dir.'/bin/config/ssh/'.$profile.'.json');
                            $ssh_config = json_decode($ssh_config);
                            $system_config->tunnel->profile->ssh = $profile;
                            $system_config->server = $ssh_config->ip;
                            $system_config->tun2socks->udpgw->ip = $ssh_config->udpgw->ip;
                            $system_config->tun2socks->udpgw->port = $ssh_config->udpgw->port;
                            break;
                        // v2ray
                        case 1:
                            $v2ray_config = file_get_contents($libernet_dir.'/bin/config/v2ray/'.$profile.'.json');
                            $v2ray_config = json_decode($v2ray_config);
                            $system_config->tunnel->profile->v2ray = $profile;
                            $system_config->server = $v2ray_config->etc->ip;
                            $system_config->tun2socks->udpgw->ip = $v2ray_config->etc->udpgw->ip;
                            $system_config->tun2socks->udpgw->port = $v2ray_config->etc->udpgw->port;
                            break;
                        // ssh-ssl
                        case 2:
                            $sshl_config = file_get_contents($libernet_dir.'/bin/config/ssh_ssl/'.$profile.'.json');
                            $sshl_config = json_decode($sshl_config);
                            $system_config->tunnel->profile->ssh_ssl = $profile;
                            $system_config->server = $sshl_config->ip;
                            $system_config->tun2socks->udpgw->ip = $sshl_config->udpgw->ip;
                            $system_config->tun2socks->udpgw->port = $sshl_config->udpgw->port;
                            break;
                        // trojan
                        case 3:
                            $trojan_config = file_get_contents($libernet_dir.'/bin/config/trojan/'.$profile.'.json');
                            $trojan_config = json_decode($trojan_config);
                            $system_config->tunnel->profile->trojan = $profile;
                            $system_config->server = $trojan_config->etc->ip;
                            $system_config->tun2socks->udpgw->ip = $trojan_config->etc->udpgw->ip;
                            $system_config->tun2socks->udpgw->port = $trojan_config->etc->udpgw->port;
                            break;
                        // shadowsocks
                        case 4:
                            $shadowsocks_config = file_get_contents($libernet_dir.'/bin/config/shadowsocks/'.$profile.'.json');
                            $shadowsocks_config = json_decode($shadowsocks_config);
                            $system_config->tunnel->profile->shadowsocks = $profile;
                            $system_config->server = $shadowsocks_config->etc->ip;
                            $system_config->tun2socks->udpgw->ip = $shadowsocks_config->etc->udpgw->ip;
                            $system_config->tun2socks->udpgw->port = $shadowsocks_config->etc->udpgw->port;
                            break;
                        // openvpn
                        case 5:
                            $openvpn_config = file_get_contents($libernet_dir.'/bin/config/openvpn/'.$profile.'.json');
                            $openvpn_config = json_decode($openvpn_config);
                            $system_config->tunnel->profile->openvpn = $profile;
                            $system_config->tun2socks->udpgw->ip = $openvpn_config->udpgw->ip;
                            $system_config->tun2socks->udpgw->port = $openvpn_config->udpgw->port;
                            break;
                    }
                    $system_config->tunnel->mode = $mode;
                    $system_config->tun2socks->legacy = $tun2socks_legacy;
                    $system_config->tunnel->dns_resolver = $dns_resolver;
                    $system_config->system->memory_cleaner = $memory_cleaner;
                    $system_config->tunnel->ping_loop = $ping_loop;
                    $system_config = json_encode($system_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    file_put_contents($libernet_dir.'/system/config.json', $system_config);
                    json_response('Configuration applied');
                }
                break;
            case 'delete_config':
                if (isset($json['data'])) {
                    $data = $json['data'];
                    $mode = $data['mode'];
                    $profile = $data['profile'];
                    switch ($mode) {
                        case 0:
                            unlink($libernet_dir.'/bin/config/ssh/'.$profile.'.json');
                            json_response('SSH config removed');
                            break;
                        case 1:
                            unlink($libernet_dir.'/bin/config/v2ray/'.$profile.'.json');
                            json_response('V2Ray config removed');
                            break;
                        case 2:
                            unlink($libernet_dir.'/bin/config/ssh_ssl/'.$profile.'.json');
                            json_response('SSH-SSL config removed');
                            break;
                        case 3:
                            unlink($libernet_dir.'/bin/config/trojan/'.$profile.'.json');
                            json_response('Trojan config removed');
                            break;
                        case 4:
                            unlink($libernet_dir.'/bin/config/shadowsocks/'.$profile.'.json');
                            json_response('Shadowsocks config removed');
                            break;
                        case 5:
                            unlink($libernet_dir.'/bin/config/openvpn/'.$profile.'.json');
                            json_response('OpenVPN config removed');
                            break;
                    }
                }
                break;
            case 'set_auto_start':
                $status = $json['status'];
                set_auto_start($status);
                if ($status) {
                    json_response("Libernet service auto start enabled");
                } else {
                    json_response("Libernet service auto start disabled");
                }
                break;
            case 'check_update':
                $update_status = file_get_contents($libernet_dir.'/log/update.log');
                json_response($update_status);
                break;
            case 'update_libernet':
                $output = null;
                $retval = null;
                exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/update.sh -web > /dev/null 2>&1 &', $output, $retval);
                if (!$retval) {
                    json_response('Libernet updated!');
                }
                break;
            case 'resolve_host':
                $output = null;
                $retval = null;
                $host = $json['host'];
                exec("ping -4Ac 1 -W 1 ".$host." | grep PING | awk '{print $3}' | sed 's/(//g; s/)//g; s/://g' | sed -n '1p'", $output, $retval);
                if (!$retval) {
                    json_response($output);
                }
                break;
            case 'change_password':
                $password = $json['password'];
                $system_config = file_get_contents($libernet_dir.'/system/config.json');
                $system_config = json_decode($system_config);
                $system_config->system->password = $password;
                $system_config = json_encode($system_config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                file_put_contents($libernet_dir.'/system/config.json', $system_config);
                json_response("Password changed");
                break;
        }
    }
?>