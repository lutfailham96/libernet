<?php
    include('auth.php');
    check_session();
?>
<!doctype html>
<html lang="en">
<head>
    <?php
        $title = "Configuration";
        include("head.php");
    ?>
</head>
<body>
<div id="app">
    <?php include('navbar.php'); ?>
    <div class="container">
        <div class="row py-2">
            <div class="col-lg-8 col-md-12 mx-auto mt-3">
                <div class="card">
                    <div class="card-header">
                        <div class="text-center">
                            <h3><i class="fa fa-gears"></i> Configuration</h3>
                        </div>
                        <hr>
                        <form @submit.prevent="getConfig">
                            <div class="form-group form-row my-auto">
                                <div class="col-lg-4 col-md-4 form-row py-1">
                                    <div class="col-lg-4 col-md-3 my-auto">
                                        <label class="my-auto">Mode</label>
                                    </div>
                                    <div class="col">
                                        <select class="form-control" v-model.number="config.mode" required>
                                            <option v-for="mode in config.temp.modes" :value="mode.value">{{ mode.name }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-4 form-row py-1">
                                    <div class="col-lg-4 col-md-3 my-auto">
                                        <label class="my-auto">Config</label>
                                    </div>
                                    <div class="col">
                                        <select class="form-control" v-model="config.profile" required>
                                            <option v-for="profile in config.profiles" :value="profile">{{ profile }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-3 form-row py-1">
                                    <div class="col d-flex">
                                        <button type="submit" class="btn btn-secondary mr-1">Load</button>
                                        <button type="button" class="btn btn-danger ml-1" @click="deleteConfig">Delete</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="saveConfig">
                            <div class="form-row pb-lg-2">
                                <div class="col-md-6">
                                    <label>Mode</label>
                                    <select v-model.number="config.temp.mode" class="form-control" required>
                                        <option v-for="mode in config.temp.modes" :value="mode.value">{{ mode.name }}</option>
                                    </select>
                                </div>
                                <div v-if="config.temp.mode === 0" class="col-md-6 pt-md-4 pl-lg-3 my-auto">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" v-model="config.temp.modes[0].profile.enable_http" checked id="enable-http">
                                        <label class="form-check-label" for="enable-http">
                                            Enable HTTP Proxy
                                        </label>
                                    </div>
                                </div>
                                <div v-if="config.temp.mode === 1" class="col-md-6">
                                    <label>Protocol</label>
                                    <select class="form-control" v-model="config.temp.modes[1].profile.protocol" required>
                                        <option v-for="protocol in config.temp.modes[1].protocols" :value="protocol.value">{{ protocol.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div v-if="config.temp.mode === 0" class="ssh pb-lg-2">
                                <div v-if="config.temp.modes[0].profile.enable_http" class="proxy">
                                    <div class="form-row pb-lg-2">
                                        <div class="col-md-6">
                                            <label>Proxy IP</label>
                                            <input type="text" class="form-control" placeholder="192.168.1.1" v-model="config.temp.modes[0].profile.http.proxy.ip" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Proxy Port</label>
                                            <input type="number" class="form-control" placeholder="8080" v-model.number="config.temp.modes[0].profile.http.proxy.port" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Payload</label>
                                        <textarea class="form-control" v-model="config.temp.modes[0].profile.http.payload" rows="5" placeholder="GET http://libernet.tld/ HTTP/1.1[crlf][crlf]CONNECT [host_port] HTTP/1.1[crlf]Connection: keep-allive[crlf][crlf]" required></textarea>
                                    </div>
                                </div>
                                <div class="form-row pb-lg-2">
                                    <div class="col-md-6">
                                        <label>Server Host</label>
                                        <input type="text" class="form-control" placeholder="node1.libernet.tld" v-model="config.temp.modes[0].profile.host" @input="resolveServerHost" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Server IP</label>
                                        <input type="text" class="form-control" placeholder="192.168.1.1" v-model="config.temp.modes[0].profile.ip" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Server Port</label>
                                        <input type="number" class="form-control" placeholder="443" v-model.number="config.temp.modes[0].profile.port" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-4">
                                        <label>Username</label>
                                        <input type="text" class="form-control" placeholder="libernet" v-model="config.temp.modes[0].profile.username" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Password</label>
                                        <input type="text" class="form-control" placeholder="StrongPassword" v-model="config.temp.modes[0].profile.password" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>UDPGW Port</label>
                                        <input type="number" class="form-control" placeholder="7300" v-model.number="config.temp.modes[0].profile.udpgw.port" required>
                                    </div>
                                </div>
                            </div>

                            <div v-if="config.temp.mode === 1" class="v2ray">
                                <div v-if="config.temp.modes[1].profile.protocol === 'vmess'" class="form-row pt-lg-2 pb-lg-2 v2ray-vmess">
                                    <div class="col">
                                        <label>Import VMess from URL</label>
                                        <div class="d-flex">
                                            <input type="text" class="form-control mr-1" placeholder="vmess://xxxxxxxxxxxx" v-model="config.temp.modes[1].import_url">
                                            <button type="button" class="btn btn-primary ml-1" @click="importV2rayConfig">Import</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row pb-lg-2">
                                    <div class="col-md-5">
                                        <label>Server Host</label>
                                        <input type="text" class="form-control" placeholder="node1.libernet.tld" v-model="config.temp.modes[1].profile.server.host" @input="resolveServerHost" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Server IP</label>
                                        <input type="text" class="form-control" placeholder="192.168.1.1" v-model="config.temp.modes[1].profile.etc.ip" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Server Port</label>
                                        <input type="number" class="form-control" placeholder="443" v-model.number="config.temp.modes[1].profile.server.port" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label>UDPGW Port</label>
                                        <input type="number" class="form-control" placeholder="7300" v-model.number="config.temp.modes[1].profile.etc.udpgw.port" required>
                                    </div>
                                </div>
                                <div v-if="config.temp.modes[1].profile.protocol === 'trojan'" class="form-row pb-lg-2 v2ray-trojan">
                                    <div class="col-md-6">
                                        <label>Trojan Password</label>
                                        <input type="text" class="form-control" placeholder="StrongPassword" v-model="config.temp.modes[1].profile.server.user.trojan.password" required>
                                    </div>
                                </div>
                                <div v-if="config.temp.modes[1].profile.protocol === 'vmess'" class="form-row pb-lg-2 v2ray-vmess">
                                    <div class="col-md-8">
                                        <label>VMess ID</label>
                                        <input type="text" class="form-control" placeholder="900c42c7-a23d-46dd-a1a0-72c37edf8a03" v-model="config.temp.modes[1].profile.server.user.vmess.id" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>VMess Security</label>
                                        <select class="custom-select" v-model="config.temp.modes[1].profile.server.user.vmess.security" required>
                                            <option v-for="security in config.temp.modes[1].protocols[0].securities" :value="security">{{ security }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div v-if="config.temp.modes[1].profile.protocol === 'vless'" class="form-row pb-lg-2 v2ray-vless">
                                    <div class="col-md-8">
                                        <label>VLESS ID</label>
                                        <input type="text" class="form-control" placeholder="900c42c7-a23d-46dd-a1a0-72c37edf8a03" v-model="config.temp.modes[1].profile.server.user.vless.id" required>
                                    </div>
                                </div>
                                <div class="form-row pb-lg-2">
                                    <div class="col-md-2">
                                        <label>Network</label>
                                        <select class="custom-select" v-model="config.temp.modes[1].profile.network" required>
                                            <option v-for="network in config.temp.modes[1].networks" :value="network.value">{{ network.name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Security</label>
                                        <select v-if="config.temp.modes[1].profile.network === 'tcp'" class="custom-select" v-model="config.temp.modes[1].profile.security" required>
                                            <option :value="config.temp.modes[1].securities[1].value">{{ config.temp.modes[1].securities[1].name }}</option>
                                        </select>
                                        <select v-else-if="config.temp.modes[1].profile.network === 'http'" class="custom-select" v-model="config.temp.modes[1].profile.security" required>
                                            <option :value="config.temp.modes[1].securities[0].value">{{ config.temp.modes[1].securities[0].name }}</option>
                                        </select>
                                        <select v-else class="custom-select" v-model="config.temp.modes[1].profile.security" required>
                                            <option v-for="security in config.temp.modes[1].securities" :value="security.value">{{ security.name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>SNI</label>
                                        <input type="text" class="form-control" placeholder="unblocked-web.tld" v-model="config.temp.modes[1].profile.stream.sni" required>
                                    </div>
                                    <div v-if="config.temp.modes[1].profile.network !== 'tcp'" class="col-md-4">
                                        <label>Path</label>
                                        <input type="text" class="form-control" placeholder="/" v-model="config.temp.modes[1].profile.stream.path" @input="decodePath" required>
                                    </div>
                                </div>
                            </div>

                            <div v-if="config.temp.mode === 2" class="ssh-ssl pb-lg-2">
                                <div class="form-row pb-lg-2">
                                    <div class="col-md-6">
                                        <label>Server Host</label>
                                        <input type="text" class="form-control" placeholder="node1.libernet.tld" v-model="config.temp.modes[2].profile.host" @input="resolveServerHost" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Server IP</label>
                                        <input type="text" class="form-control" placeholder="192.168.1.1" v-model="config.temp.modes[2].profile.ip" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Server Port</label>
                                        <input type="number" class="form-control" placeholder="443" v-model.number="config.temp.modes[2].profile.port" required>
                                    </div>
                                </div>
                                <div class="form-row pb-lg-2">
                                    <div class="col-md-6">
                                        <label>Username</label>
                                        <input type="text" class="form-control" placeholder="libernet" v-model="config.temp.modes[2].profile.username" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Password</label>
                                        <input type="text" class="form-control" placeholder="StrongPassword" v-model="config.temp.modes[2].profile.password" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label>SNI</label>
                                        <input type="text" class="form-control" placeholder="unblocked-web.tld" v-model.number="config.temp.modes[2].profile.sni" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>UDPGW Port</label>
                                        <input type="number" class="form-control" placeholder="7300" v-model.number="config.temp.modes[2].profile.udpgw.port" required>
                                    </div>
                                </div>
                            </div>

                            <div v-if="config.temp.mode === 3" class="trojan pb-lg-2">
                                <div class="form-row pt-lg-2 pb-lg-2">
                                    <div class="col">
                                        <label>Import Trojan from URL</label>
                                        <div class="d-flex">
                                            <input type="text" class="form-control mr-1" placeholder="trojan://xxxxxxxxxxxx" v-model="config.temp.modes[3].import_url">
                                            <button type="button" class="btn btn-primary ml-1" @click="importTrojanConfig">Import</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row pb-lg-2">
                                    <div class="col-md-6">
                                        <label>Server Host</label>
                                        <input type="text" class="form-control" placeholder="node1.libernet.tld" v-model="config.temp.modes[3].profile.host" @input="resolveServerHost" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Server IP</label>
                                        <input type="text" class="form-control" placeholder="192.168.1.1" v-model="config.temp.modes[3].profile.ip" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Server Port</label>
                                        <input type="number" class="form-control" placeholder="443" v-model.number="config.temp.modes[3].profile.port" required>
                                    </div>
                                </div>
                                <div class="form-row pb-lg-2">
                                    <div class="col-md-6">
                                        <label>Trojan Password</label>
                                        <input type="text" class="form-control" placeholder="StrongPassword" v-model="config.temp.modes[3].profile.password" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-6">
                                        <label>SNI</label>
                                        <input type="text" class="form-control" placeholder="unblocked-web.tld" v-model.number="config.temp.modes[3].profile.sni" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>UDPGW Port</label>
                                        <input type="number" class="form-control" placeholder="7300" v-model.number="config.temp.modes[3].profile.udpgw.port" required>
                                    </div>
                                </div>
                            </div>

                            <div v-if="config.temp.mode === 4" class="shadowsocks pb-lg-2">
                                <div class="form-row pt-lg-2 pb-lg-2">
                                    <div class="col">
                                        <label>Import Shadowsocks from URL</label>
                                        <div class="d-flex">
                                            <input type="text" class="form-control mr-1" placeholder="ss://xxxxxxxxxxxx" v-model="config.temp.modes[4].import_url">
                                            <button type="button" class="btn btn-primary ml-1" @click="importShadowsocksConfig">Import</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-row pb-lg-2">
                                    <div class="col-md-6">
                                        <label>Server Host</label>
                                        <input type="text" class="form-control" placeholder="node1.libernet.tld" v-model="config.temp.modes[4].profile.host" @input="resolveServerHost" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Server IP</label>
                                        <input type="text" class="form-control" placeholder="192.168.1.1" v-model="config.temp.modes[4].profile.ip" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Server Port</label>
                                        <input type="number" class="form-control" placeholder="443" v-model.number="config.temp.modes[4].profile.port" required>
                                    </div>
                                </div>
                                <div class="form-row pb-lg-2">
                                    <div class="col-md-8">
                                        <label>Password</label>
                                        <input type="text" class="form-control" placeholder="StrongPassword" v-model="config.temp.modes[4].profile.password" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Method</label>
                                        <select class="form-control" v-model="config.temp.modes[4].profile.method" required>
                                            <option v-for="method in config.temp.modes[4].methods" :value="method">{{ method }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="col-md-3">
                                        <label>Plugin</label>
                                        <select class="form-control" v-model="config.temp.modes[4].profile.plugin" required>
                                            <option v-for="plugin in config.temp.modes[4].plugins" :value="plugin.value">{{ plugin.name }}</option>
                                        </select>
                                    </div>
                                    <div v-if="config.temp.modes[4].profile.plugin == 'obfs-local'" class="obfs-local col-md-2">
                                        <label>OBFS</label>
                                        <select class="form-control" v-model="config.temp.modes[4].profile.simple_obfs" required>
                                            <option v-for="obfs in config.temp.modes[4].plugins[1].obfs" :value="obfs.value">{{ obfs.name }}</option>
                                        </select>
                                    </div>
                                    <div v-if="config.temp.modes[4].profile.plugin == 'ck-client'" class="ck-client col-md-9 form-row">
                                        <div  class="col-md-5">
                                            <label>UID</label>
                                            <input type="text" class="form-control" placeholder="zvnyHMjSCf8qzK3Z2Zz6Cg==" v-model="config.temp.modes[4].profile.cloak.uid" required>
                                        </div>
                                        <div  class="col-md-7">
                                            <label>Public Key</label>
                                            <input type="text" class="form-control" placeholder="XN8kNqokmV4d72F90PXQZr8AL242PiSF4mI/EykMWWM=" v-model="config.temp.modes[4].profile.cloak.public_key" required>
                                        </div>
                                    </div>
                                    <div v-if="config.temp.modes[4].profile.plugin !== 'none' && config.temp.modes[4].profile.plugin.trim().length > 0" class="col-md-5">
                                        <label>SNI</label>
                                        <input type="text" class="form-control" placeholder="unblocked-web.tld" v-model.number="config.temp.modes[4].profile.sni" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>UDPGW Port</label>
                                        <input type="number" class="form-control" placeholder="7300" v-model.number="config.temp.modes[4].profile.udpgw.port" required>
                                    </div>
                                </div>
                            </div>

                            <div v-if="config.temp.mode === 5" class="openvpn pb-lg-2">
                                <div class="form-row pb-lg-2">
                                    <label>Import OVPN from file</label>
                                    <div class="col-md-12 custom-file">
                                        <input type="file" class="custom-file-input" accept=".ovpn, .conf" id="ovpn-file" @change="importOvpnConfig">
                                        <label class="custom-file-label" for="ovpn-file">Choose file</label>
                                    </div>
                                </div>
                                <div class="form-row pb-lg-2">
                                    <div class="col-md-12">
                                        <label>OVPN</label>
                                        <textarea class="form-control" rows="10" v-model="config.temp.modes[5].profile.ovpn" required></textarea>
                                    </div>
                                </div>
                                <div v-if="openvpn_auth_user_pass" class="form-row pb-lg-2">
                                    <div class="col-md-6">
                                        <label>Username</label>
                                        <input type="text" class="form-control" placeholder="libernet" v-model="config.temp.modes[5].profile.username" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Password</label>
                                        <input type="text" class="form-control" placeholder="StrongPassword" v-model="config.temp.modes[5].profile.password" required>
                                    </div>
                                </div>
                                <div class="form-row pb-lg-2">
                                    <div class="col-md-12 pl-4">
                                        <input class="form-check-input" type="checkbox" v-model="config.temp.modes[5].profile.ssl" id="enable-ssl">
                                        <label class="form-check-label" for="enable-ssl">
                                            Enable SSL
                                        </label>
                                    </div>
                                </div>
                                <div v-if="config.temp.modes[5].profile.ssl" class="form-row pb-lg-2">
                                    <div class="col-md-4">
                                        <label>SNI</label>
                                        <input type="text" class="form-control" placeholder="unblocked-web.tld" v-model="config.temp.modes[5].profile.sni" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group pb-lg-2 text-center">
                                <label>Config Name</label>
                                <input type="text" class="form-control text-center" placeholder="bypass-filter" v-model="config.temp.profile" required>
                            </div>
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary form-control">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php include('footer.php'); ?>
    </div>
</div>
<?php include("javascript.php"); ?>
<script src="js/config.js"></script>
</body>
</html>