<?php
    include('config.inc.php');
    //include('auth.php');
    //check_session();

    function json_response($data) {
        $resp = array(
            'status' => 'OK',
            'data' => $data);
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($resp);
    }
    if (isset($_POST)) {
        $json = json_decode(file_get_contents('php://input'), true);
        if ($json['action'] === 'get_system_config') {
            $data = file_get_contents($libernet_dir.'/system/config.json');
            $data = json_decode($data);
            json_response($data);
        }
        if ($json['action'] === 'get_ssh_config') {
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
        }
        if ($json['action'] === 'get_sshl_config') {
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
        }
        if ($json['action'] === 'get_v2ray_config') {
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
        }
        if ($json['action'] === 'get_v2ray_configs') {
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
        }
        if ($json['action'] === 'get_ssh_configs') {
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
        }
        if ($json['action'] === 'get_sshl_configs') {
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
        }
        if ($json['action'] == 'apply_config' && isset($json['data'])) {
            $system_config = file_get_contents($libernet_dir.'/system/config.json');
            $system_config = json_decode($system_config);
            $data = $json['data'];
            $profile = $data['profile'];
            $mode = $data['mode'];
            $tun2socks_legacy = $data['tun2socks_legacy'];

            if ($mode == 0) {
                $ssh_config = file_get_contents($libernet_dir.'/bin/config/ssh/'.$profile.'.json');
                $ssh_config = json_decode($ssh_config);
                $system_config->tunnel->profile->ssh = $profile;
                $system_config->server = $ssh_config->ip;
                $system_config->tun2socks->udpgw->ip = $ssh_config->udpgw->ip;
                $system_config->tun2socks->udpgw->port = $ssh_config->udpgw->port;
            } elseif ($mode == 1) {
                $v2ray_config = file_get_contents($libernet_dir.'/bin/config/v2ray/'.$profile.'.json');
                $v2ray_config = json_decode($v2ray_config);
                $protocol = $v2ray_config->outbounds[0]->protocol;
                $system_config->server = $v2ray_config->etc->ip;
//                if ($protocol == "vmess") {
//                    $system_config->server = $v2ray_config->outbounds[0]->settings->vnext[0]->ip;
//                } elseif ($protocol == "trojan") {
//                    $system_config->server = $v2ray_config->outbounds[0]->settings->servers[0]->ip;
//                }
                $system_config->tun2socks->udpgw->ip = $v2ray_config->etc->udpgw->ip;
                $system_config->tun2socks->udpgw->port = $v2ray_config->etc->udpgw->port;
                $system_config->tunnel->profile->v2ray = $profile;
            } elseif ($mode == 2) {
                $sshl_config = file_get_contents($libernet_dir.'/bin/config/ssh-ssl/'.$profile.'.json');
                $sshl_config = json_decode($sshl_config);
                $system_config->tunnel->profile->ssh_ssl = $profile;
                $system_config->server = $sshl_config->ip;
                $system_config->tun2socks->udpgw->ip = $sshl_config->udpgw->ip;
                $system_config->tun2socks->udpgw->port = $sshl_config->udpgw->port;
            }
            $system_config->tunnel->mode = $mode;
            $system_config->tun2socks->legacy = $tun2socks_legacy;
            $system_config = json_encode($system_config, JSON_PRETTY_PRINT);
            file_put_contents($libernet_dir.'/system/config.json', $system_config);
            json_response('Configuration applied');
        }
        if ($json['action'] == 'delete_config' && isset($json['data'])) {
            $data = $json['data'];
            $mode = $data['mode'];
            $profile = $data['profile'];
            if ($mode == 0) {
                unlink($libernet_dir.'/bin/config/ssh/'.$profile.'.json');
                json_response('SSH config removed');
            } elseif ($mode == 1) {
                unlink($libernet_dir.'/bin/config/v2ray/'.$profile.'.json');
                json_response('V2Ray config removed');
            } elseif ($mode == 2) {
                unlink($libernet_dir.'/bin/config/ssh-ssl/'.$profile.'.json');
                json_response('SSH-SSL config removed');
            }
        }
        if ($json['action'] == 'save_config' && isset($json['data'])) {
            $system_config = file_get_contents($libernet_dir.'/system/config.json');
            $system_config = json_decode($system_config);
            $data = $json['data'];
            $mode = $data['mode'];
            $profile = $data['profile'];
            if ($mode == 0) {
//                $system_config->server = $config['ip'];
//                $system_config = json_encode($system_config, JSON_PRETTY_PRINT);
//                file_put_contents($libernet_dir.'/system/config.json', $system_config);
//                json_response('Configuration applied');
                $config = $data['config'];
                $config = json_encode($config, JSON_UNESCAPED_SLASHES);
                file_put_contents($libernet_dir.'/bin/config/ssh/'.$profile.'.json', $config);
                json_response('SSH config saved');
            } elseif ($mode == 1) {
                $config = $data['config'];
                $host = $config['host'];
                $id = $config['id'];
                $ip = $config['ip'];
                $level = $config['level'];
                $password = $config['password'];
                $port = $config['port'];
                $protocol = $config['protocol'];
                $security = $config['security'];
                $sni = $config['sni'];
                $username = $config['username'];
                $udpgw_ip = $config['udpgw']['ip'];
                $udpgw_port = $config['udpgw']['port'];
                if ($protocol == "vmess") {
                    $vmess_config = file_get_contents($libernet_dir.'/bin/config/v2ray/templates/vmess.json');
                    $vmess_config = json_decode($vmess_config);
                    $vmess_config->etc->ip = $ip;
//                    $vmess_config->outbounds[0]->settings->vnext[0]->ip = $ip;
                    $vmess_config->outbounds[0]->settings->vnext[0]->address = $host;
                    $vmess_config->outbounds[0]->settings->vnext[0]->port = $port;
                    $vmess_config->outbounds[0]->settings->vnext[0]->users[0]->id = $id;
                    $vmess_config->outbounds[0]->settings->vnext[0]->users[0]->security = $security;
                    $vmess_config->outbounds[0]->settings->vnext[0]->users[0]->level = $level;
                    $vmess_config->outbounds[0]->streamSettings->tlsSettings->serverName = $sni;
                    $vmess_config->etc->udpgw->ip = $udpgw_ip;
                    $vmess_config->etc->udpgw->port = $udpgw_port;
                    file_put_contents($libernet_dir.'/bin/config/v2ray/'.$profile.'.json', json_encode($vmess_config, JSON_PRETTY_PRINT));
                    json_response('V2Ray vmess config saved');
                } elseif ($protocol == "trojan") {
                    $trojan_config = file_get_contents($libernet_dir.'/bin/config/v2ray/templates/trojan.json');
                    $trojan_config = json_decode($trojan_config);
                    $trojan_config->etc->ip = $ip;
//                    $trojan_config->outbounds[0]->settings->servers[0]->ip = $ip;
                    $trojan_config->outbounds[0]->settings->servers[0]->address = $host;
                    $trojan_config->outbounds[0]->settings->servers[0]->port = $port;
                    $trojan_config->outbounds[0]->settings->servers[0]->password = $password;
                    $trojan_config->outbounds[0]->settings->servers[0]->level = $level;
                    $trojan_config->outbounds[0]->streamSettings->tlsSettings->serverName = $sni;
                    $trojan_config->etc->udpgw->ip = $udpgw_ip;
                    $trojan_config->etc->udpgw->port = $udpgw_port;
                    file_put_contents($libernet_dir.'/bin/config/v2ray/'.$profile.'.json', json_encode($trojan_config, JSON_PRETTY_PRINT));
                    json_response('V2Ray trojan config saved');
                }
            } elseif ($mode == 2) {
                $config = $data['config'];
                $config = json_encode($config, JSON_PRETTY_PRINT);
                file_put_contents($libernet_dir . '/bin/config/ssh-ssl/' . $profile . '.json', $config);
                json_response('SSH-SSL config saved');
            }
        }
        if ($json['action'] === 'start_libernet') {
            $system_config = file_get_contents($libernet_dir.'/system/config.json');
            $system_config = json_decode($system_config);
            // clear service log
            exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/log.sh -r');
            exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/log.sh -w "Startng Libernet service"');
            if ($system_config->tunnel->mode === 0) {
                $ssh_config = file_get_contents($libernet_dir.'/bin/config/ssh/'.$system_config->tunnel->profile->ssh.'.json');
                $ssh_config = json_decode($ssh_config);
                exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/log.sh -w "Config: '.$system_config->tunnel->profile->ssh.', Mode: SSH"');
            } elseif ($system_config->tunnel->mode === 1) {
                $v2ray_config = file_get_contents($libernet_dir.'/bin/config/v2ray/'.$system_config->tunnel->profile->v2ray.'.json');
                $v2ray_config = json_decode($v2ray_config);
                if ($v2ray_config->outbounds[0]->protocol === "trojan") {
                    exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/log.sh -w "Config: '.$system_config->tunnel->profile->v2ray.', Mode: V2Ray, Protocol: trojan"');
                } elseif ($v2ray_config->outbounds[0]->protocol === 'vmess') {
                    exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/log.sh -w "Config: '.$system_config->tunnel->profile->v2ray.', Mode: V2Ray, Protocol: vmess"');
                }
            } elseif ($system_config->tunnel->mode === 2) {
                $sshl_config = file_get_contents($libernet_dir.'/bin/config/ssh-ssl/'.$system_config->tunnel->profile->ssh_ssl.'.json');
                $sshl_config = json_decode($ssh_config);
                exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/log.sh -w "Config: '.$system_config->tunnel->profile->ssh_ssl.', Mode: SSH-SSL"');
            }
            exec('export LIBERNET_DIR='.$libernet_dir.' && '.$libernet_dir.'/bin/service.sh -sl');
            json_response('Libernet service started');
        }
        if ($json['action'] === 'stop_libernet') {
            exec('export LIBERNET_DIR="'.$libernet_dir.'" && '.$libernet_dir.'/bin/service.sh -ds');
            json_response('Libernet service stopped');
        }
        if ($json['action'] === 'get_service_status') {
            $status = file_get_contents($libernet_dir.'/log/status.log');
            json_response(array('status' => intval($status)));
        }
        if ($json['action'] === 'get_service_log') {
            $log = file_get_contents($libernet_dir.'/log/service.log');
            json_response(array('log' => $log));
        }
    }
?>