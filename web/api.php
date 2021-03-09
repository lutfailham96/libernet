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
                $data = null;
                $ssh_config = null;
                if (isset($json['profile'])) {
                    $ssh_config = file_get_contents($libernet_dir.'/bin/config/ssh/'.$json['profile'].'.json');
                } else {
                    $system_config = file_get_contents($libernet_dir.'/system/config.json');
                    $system_config = json_decode($system_config);
                    $ssh_config = file_get_contents($libernet_dir.'/bin/config/ssh/'.$system_config->tunnel->profile->ssh.'.json');
                }
                $data = json_decode($ssh_config);
                json_response($data);
                break;
            case 'get_sshl_config':
                $data = null;
                $sshl_config = null;
                if (isset($json['profile'])) {
                    $sshl_config = file_get_contents($libernet_dir.'/bin/config/ssh-ssl/'.$json['profile'].'.json');
                } else {
                    $system_config = file_get_contents($libernet_dir.'/system/config.json');
                    $system_config = json_decode($system_config);
                    $sshl_config = file_get_contents($libernet_dir.'/bin/config/ssh-ssl/'.$system_config->tunnel->profile->ssh_ssl.'.json');
                }
                $data = json_decode($sshl_config);
                json_response($data);
                break;
            case 'get_v2ray_config':
                $data = null;
                $v2ray_config = null;
                if (isset($json['profile'])) {
                    $v2ray_config = file_get_contents($libernet_dir.'/bin/config/v2ray/'.$json['profile'].'.json');
                } else {
                    $system_config = file_get_contents($libernet_dir.'/system/config.json');
                    $system_config = json_decode($system_config);
                    $v2ray_config = file_get_contents($libernet_dir.'/bin/config/v2ray/'.$system_config->tunnel->profile->v2ray.'.json');
                }
                $data = json_decode($v2ray_config);
                json_response($data);
                break;
            case 'get_v2ray_configs':
                $profiles = array();
                if ($handle = opendir($libernet_dir.'/bin/config/v2ray/')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'json') {
                            array_push($profiles, preg_replace('/\\.[^.\\s]{3,4}$/', '', $file));
                        }
                    }
                    closedir($handle);
                }
                json_response($profiles);
                break;
            case 'get_ssh_configs':
                $profiles = array();
                if ($handle = opendir($libernet_dir.'/bin/config/ssh/')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'json') {
                            array_push($profiles, preg_replace('/\\.[^.\\s]{3,4}$/', '', $file));
                        }
                    }
                    closedir($handle);
                }
                json_response($profiles);
                break;
            case 'get_sshl_configs':
                $profiles = array();
                if ($handle = opendir($libernet_dir.'/bin/config/ssh-ssl/')) {
                    while (false !== ($file = readdir($handle))) {
                        if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'json') {
                            array_push($profiles, preg_replace('/\\.[^.\\s]{3,4}$/', '', $file));
                        }
                    }
                    closedir($handle);
                }
                json_response($profiles);
                break;
            case 'start_libernet':
                $system_config = file_get_contents($libernet_dir.'/system/config.json');
                $system_config = json_decode($system_config);
                // clear service log
                exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/log.sh -r');
                // write starting service log
                exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/log.sh -w "Startng Libernet service"');
                switch ($system_config->tunnel->mode) {
                    // ssh
                    case 0:
                        $ssh_config = file_get_contents($libernet_dir.'/bin/config/ssh/'.$system_config->tunnel->profile->ssh.'.json');
                        $ssh_config = json_decode($ssh_config);
                        exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/log.sh -w "Config: '.$system_config->tunnel->profile->ssh.', Mode: SSH"');
                        break;
                    // v2ray
                    case 1:
                        $v2ray_config = file_get_contents($libernet_dir.'/bin/config/v2ray/'.$system_config->tunnel->profile->v2ray.'.json');
                        $v2ray_config = json_decode($v2ray_config);
                        if ($v2ray_config->outbounds[0]->protocol === "trojan") {
                            exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/log.sh -w "Config: '.$system_config->tunnel->profile->v2ray.', Mode: V2Ray, Protocol: trojan"');
                        } elseif ($v2ray_config->outbounds[0]->protocol === 'vmess') {
                            exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/log.sh -w "Config: '.$system_config->tunnel->profile->v2ray.', Mode: V2Ray, Protocol: vmess"');
                        }
                        break;
                    // ssh-ssl
                    case 2:
                        $sshl_config = file_get_contents($libernet_dir.'/bin/config/ssh-ssl/'.$system_config->tunnel->profile->ssh_ssl.'.json');
                        $sshl_config = json_decode($sshl_config);
                        exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/log.sh -w "Config: '.$system_config->tunnel->profile->ssh_ssl.', Mode: SSH-SSL"');
                        break;
                }
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
            case 'get_service_status':
                $status = file_get_contents($libernet_dir.'/log/status.log');
                json_response(array('status' => intval($status)));
                break;
            case 'get_service_log':
                $log = file_get_contents($libernet_dir.'/log/service.log');
                json_response(array('log' => $log));
                break;
            case 'save_config':
                if (isset($json['data'])) {
                    $system_config = file_get_contents($libernet_dir.'/system/config.json');
                    $system_config = json_decode($system_config);
                    $data = $json['data'];
                    $mode = $data['mode'];
                    $profile = $data['profile'];
                    switch ($mode) {
                        // ssh
                        case 0:
                            $config = $data['config'];
                            $config = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                            file_put_contents($libernet_dir.'/bin/config/ssh/'.$profile.'.json', $config);
                            json_response('SSH config saved');
                            break;
                        // v2ray
                        case 1:
                            $config = $data['config'];
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
                            $config = $data['config'];
                            $config = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                            file_put_contents($libernet_dir . '/bin/config/ssh-ssl/' . $profile . '.json', $config);
                            json_response('SSH-SSL config saved');
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
                            $protocol = $v2ray_config->outbounds[0]->protocol;
                            $system_config->server = $v2ray_config->etc->ip;
                            $system_config->tun2socks->udpgw->ip = $v2ray_config->etc->udpgw->ip;
                            $system_config->tun2socks->udpgw->port = $v2ray_config->etc->udpgw->port;
                            $system_config->tunnel->profile->v2ray = $profile;
                            break;
                        // ssh-ssl
                        case 2:
                            $sshl_config = file_get_contents($libernet_dir.'/bin/config/ssh-ssl/'.$profile.'.json');
                            $sshl_config = json_decode($sshl_config);
                            $system_config->tunnel->profile->ssh_ssl = $profile;
                            $system_config->server = $sshl_config->ip;
                            $system_config->tun2socks->udpgw->ip = $sshl_config->udpgw->ip;
                            $system_config->tun2socks->udpgw->port = $sshl_config->udpgw->port;
                            break;
                    }
                    $system_config->tunnel->mode = $mode;
                    $system_config->tun2socks->legacy = $tun2socks_legacy;
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
                            unlink($libernet_dir.'/bin/config/ssh-ssl/'.$profile.'.json');
                            json_response('SSH-SSL config removed');
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
                $output = null;
                $retval = null;
                exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/update.sh -web', $output, $retval);
                if (!$retval) {
                    json_response('Libernet updated!');
                }
                break;
        }
    }
?>