<?php
    include('auth.php');
    check_session();
?>
<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="lib/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">

    <title>Libernet | Configuration</title>
</head>
<body>
<div id="app">
    <?php include('navbar.php'); ?>
    <div class="container">
        <div class="row py-2">
            <div class="col-lg-8 col-md-12 mx-auto">
                <div class="text-center">
                    <h3>Configuration</h3>
                </div>
                <div class="card">
                    <div class="card-header">
                        <form @submit.prevent="getConfig">
                            <div class="form-group form-row my-auto">
                                <label class="my-auto mx-1">Mode</label>
                                <select class="form-control w-25 mx-1" v-model.number="config.mode" required>
                                    <option v-for="mode in config.temp.modes" :value="mode.value">{{ mode.name }}</option>
                                </select>
                                <label class="my-auto mx-1">Config</label>
                                <select class="form-control w-25 mx-1" v-model="config.profile" required>
                                    <option v-for="profile in config.profiles" :value="profile">{{ profile }}</option>
                                </select>
                                <button type="submit" class="btn btn-secondary mx-1">Load</button>
                                <button type="button" class="btn btn-danger mx-1" @click="deleteConfig">Delete</button>
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
                                <div v-if="config.temp.mode === 0" class="col-md-6 pt-lg-4 pl-lg-3 my-lg-auto">
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
                                        <label>UDPGW Port</label>
                                        <input type="number" class="form-control" placeholder="7300" v-model.number="config.temp.modes[2].profile.udpgw.port" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>SNI</label>
                                        <input type="text" class="form-control" placeholder="unblocked-web.tld" v-model.number="config.temp.modes[2].profile.sni" required>
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
                                        <label>UDPGW Port</label>
                                        <input type="number" class="form-control" placeholder="7300" v-model.number="config.temp.modes[3].profile.udpgw.port" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>SNI</label>
                                        <input type="text" class="form-control" placeholder="unblocked-web.tld" v-model.number="config.temp.modes[3].profile.sni" required>
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
<?php include('js.php'); ?>
<script>
    let vm = new Vue({
        el: '#app',
        data() {
            return {
                config: {
                    mode: 0,
                    profile: "",
                    profiles: [],
                    temp: {
                        mode: 0,
                        profile: "",
                        modes: [
                            {
                                value: 0,
                                name: "SSH",
                                profile: {
                                    ip: "",
                                    host: "",
                                    port: null,
                                    username: "",
                                    password: "",
                                    udpgw: {
                                        ip: "127.0.0.1",
                                        port: null
                                    },
                                    enable_http: true,
                                    http: {
                                        buffer: 32767,
                                        ip: "127.0.0.1",
                                        port: 9876,
                                        info: "HTTP Proxy",
                                        payload: "",
                                        proxy: {
                                            ip: "",
                                            port: null
                                        }
                                    }
                                }
                            },
                            {
                                value: 1,
                                name: "V2Ray",
                                protocols: [
                                    {
                                        name: "VMess",
                                        value: "vmess",
                                        securities: [
                                            "auto",
                                            "aes-128-gcm",
                                            "chacha20-poly1305",
                                            "none"
                                        ]
                                    },
                                    {
                                        name: "VLESS",
                                        value: "vless"
                                    },
                                    {
                                        name: "Trojan",
                                        value: "trojan"
                                    }
                                ],
                                networks: [
                                    {
                                        name: "TCP",
                                        value: "tcp"
                                    },
                                    {
                                        name: "WebSocket",
                                        value: "ws"
                                    },
                                    {
                                        name: "HTTP",
                                        value: "http"
                                    }
                                ],
                                securities: [
                                    {
                                        name: "None",
                                        value: "none"
                                    },
                                    {
                                        name: "TLS",
                                        value: "tls"
                                    }
                                ],
                                import_url: "",
                                profile: {
                                    protocol: "",
                                    network: "",
                                    security: "",
                                    server: {
                                        host: "",
                                        port: null,
                                        user: {
                                            level: 0,
                                            vmess: {
                                                id: "",
                                                security: ""
                                            },
                                            vless: {
                                                id: ""
                                            },
                                            trojan: {
                                                password: ""
                                            }
                                        }
                                    },
                                    stream: {
                                        sni: "",
                                        path: ""
                                    },
                                    etc: {
                                        ip: "",
                                        udpgw: {
                                            ip: "127.0.0.1",
                                            port: null
                                        }
                                    }
                                }
                            },
                            {
                                value: 2,
                                name: "SSH-SSL",
                                profile: {
                                    ip: "",
                                    host: "",
                                    port: null,
                                    username: "",
                                    password: "",
                                    sni: "",
                                    udpgw: {
                                        ip: "127.0.0.1",
                                        port: null
                                    }
                                }
                            },
                            {
                                value: 3,
                                name: "Trojan",
                                profile: {
                                    ip: "",
                                    host: "",
                                    port: null,
                                    password: "",
                                    sni: "",
                                    udpgw: {
                                        ip: "127.0.0.1",
                                        port: null
                                    }
                                },
                                import_url: ""
                            }
                        ]
                    },
                    system: {}
                }
            }
        },
        watch: {
            'config.mode': function (mode) {
                this.getProfiles(mode)
                this.config.profile = ""
            },
            'config.temp.profile': function (val) {
                this.config.temp.profile = val.split(' ').join('_')
            }
        },
        methods: {
            decodePath: _.debounce(function () {
                this.config.temp.modes[1].profile.stream.path = decodeURIComponent(JSON.parse('"' + this.config.temp.modes[1].profile.stream.path + '"'))
            }, 500),
            getProfiles(mode) {
                switch (mode) {
                    case 0:
                        this.getSshProfiles()
                        break
                    case 1:
                        this.getV2rayProfiles()
                        break
                    case 2:
                        this.getSshSslProfiles()
                        break
                    case 3:
                        this.getTrojanProfiles()
                        break
                }
            },
            getSshProfiles() {
                axios.post('api.php', {
                    action: "get_ssh_configs"
                }).then((res) => {
                    this.config.profiles = res.data.data
                })
            },
            getV2rayProfiles() {
                axios.post('api.php', {
                    action: "get_v2ray_configs"
                }).then((res) => {
                    this.config.profiles = res.data.data
                })
            },
            getSshSslProfiles() {
                axios.post('api.php', {
                    action: "get_sshl_configs"
                }).then((res) => {
                    this.config.profiles = res.data.data
                })
            },
            getTrojanProfiles() {
                axios.post('api.php', {
                    action: "get_trojan_configs"
                }).then((res) => {
                    this.config.profiles = res.data.data
                })
            },
            getConfig() {
                this.getSystemConfig().then((res) => {
                    this.config.system = res
                    switch (this.config.mode) {
                        case 0:
                            this.getSshConfig()
                            break
                        case 1:
                            this.getV2rayConfig()
                            break
                        case 2:
                            this.getSshSslConfig()
                            break
                        case 3:
                            this.getTrojanConfig()
                            break
                    }
                })
            },
            deleteConfig() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    reverseButtons: true,
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        axios.post('api.php', {
                            action: "delete_config",
                            data: {
                                mode: this.config.mode,
                                profile: this.config.profile
                            }
                        }).then(() => {
                            Swal.fire({
                                position: 'center',
                                icon: 'success',
                                title: 'Config has been removed',
                                showConfirmButton: false,
                                timer: 1500
                            })
                            this.config.profile = ""
                            this.getProfiles(this.config.mode)
                        })
                    }
                })
            },
            getSshConfig() {
                axios.post('api.php', {
                    action: "get_ssh_config",
                    profile: this.config.profile
                }).then((res) => {
                    const temp = this.config.temp
                    temp.mode = 0
                    temp.profile = this.config.profile
                    temp.modes[0].profile = res.data.data
                })
            },
            getV2rayConfig() {
                axios.post('api.php', {
                    action: "get_v2ray_config",
                    profile: this.config.profile
                }).then((res) => {
                    const temp = this.config.temp
                    const profile = temp.modes[1].profile
                    const protocol = res.data.data.outbounds[0].protocol
                    const network = res.data.data.outbounds[0].streamSettings.network
                    const security = res.data.data.outbounds[0].streamSettings.security
                    let remote
                    let sni
                    let path = ""

                    // set mode & profile
                    temp.mode = 1
                    temp.profile = this.config.profile

                    profile.protocol = protocol
                    profile.network = network
                    profile.security = security
                    switch (protocol) {
                        // vmess
                        case "vmess":
                            remote = res.data.data.outbounds[0].settings.vnext[0]
                            profile.server.host = remote.address
                            profile.server.port = remote.port
                            profile.server.user.level = remote.users[0].level
                            profile.server.user.vmess.id = remote.users[0].id
                            profile.server.user.vmess.security = remote.users[0].security
                            break
                        // vless
                        case "vless":
                            remote = res.data.data.outbounds[0].settings.vnext[0]
                            profile.server.host = remote.address
                            profile.server.port = remote.port
                            profile.server.user.level = remote.users[0].level
                            profile.server.user.vless.id = remote.users[0].id
                            break
                        // trojan
                        case "trojan":
                            remote = res.data.data.outbounds[0].settings.servers[0]
                            profile.server.host = remote.address
                            profile.server.port = remote.port
                            profile.level = remote.level
                            profile.server.user.trojan.password = remote.password
                            break
                    }
                    switch (network) {
                        // tcp
                        case "tcp":
                            sni = res.data.data.outbounds[0].streamSettings.tlsSettings.serverName
                            break
                        // ws
                        case "ws":
                            sni = res.data.data.outbounds[0].streamSettings.wsSettings.headers.Host
                            path = res.data.data.outbounds[0].streamSettings.wsSettings.path
                            break
                        // http
                        case "http":
                            sni = res.data.data.outbounds[0].streamSettings.httpSettings.host[0]
                            path = res.data.data.outbounds[0].streamSettings.httpSettings.path
                            break
                    }
                    profile.stream.sni = sni
                    profile.stream.path = path
                    profile.etc.ip = res.data.data.etc.ip
                    profile.etc.udpgw.ip = res.data.data.etc.udpgw.ip
                    profile.etc.udpgw.port = res.data.data.etc.udpgw.port
                })
            },
            getSshSslConfig() {
                axios.post('api.php', {
                    action: "get_sshl_config",
                    profile: this.config.profile
                }).then((res) => {
                    const temp = this.config.temp
                    temp.mode = 2
                    temp.profile = this.config.profile
                    temp.modes[2].profile = res.data.data
                })
            },
            getTrojanConfig() {
                axios.post('api.php', {
                    action: "get_trojan_config",
                    profile: this.config.profile
                }).then((res) => {
                    const temp = this.config.temp
                    const profile = temp.modes[3].profile
                    const data = res.data.data
                    temp.mode = 3
                    temp.profile = this.config.profile
                    profile.ip = data.etc.ip
                    profile.host = data.remote_addr
                    profile.port = data.remote_port
                    profile.password = data.password[0]
                    profile.sni = data.ssl.sni
                    profile.udpgw.port = data.etc.udpgw.port
                })
            },
            getSystemConfig() {
                return new Promise((resolve) => {
                    axios.post('api.php', {
                        action: "get_system_config"
                    }).then((res) => {
                        resolve(res.data.data)
                    })
                })
            },
            saveConfig() {
                switch (this.config.temp.mode) {
                    case 0:
                        this.saveSshConfig()
                        break
                    case 1:
                        this.saveV2rayConfig()
                        break
                    case 2:
                        this.saveSshSslConfig()
                        break
                    case 3:
                        this.saveTrojanConfig()
                        break
                }
            },
            saveSshConfig() {
                axios.post('api.php', {
                    action: "save_config",
                    data: {
                        mode: this.config.temp.mode,
                        profile: this.config.temp.profile,
                        config: this.config.temp.modes[0].profile
                    }
                }).then(() => {
                    console.log("SSH config saved")
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'SSH config has been saved',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    this.config.profile = ""
                    this.getProfiles(this.config.mode)
                })
            },
            saveV2rayConfig() {
                axios.post('api.php', {
                    action: "save_config",
                    data: {
                        mode: this.config.temp.mode,
                        profile: this.config.temp.profile,
                        config: this.config.temp.modes[1].profile
                    }
                }).then(() => {
                    console.log("V2Ray config saved")
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'V2Ray config has been saved',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    this.config.profile = ""
                    this.getProfiles(this.config.mode)
                })
            },
            saveSshSslConfig() {
                axios.post('api.php', {
                    action: "save_config",
                    data: {
                        mode: this.config.temp.mode,
                        profile: this.config.temp.profile,
                        config: this.config.temp.modes[2].profile
                    }
                }).then(() => {
                    console.log("SSH-SSL config saved")
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'SSH-SSL config has been saved',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    this.config.profile = ""
                    this.getProfiles(this.config.mode)
                })
            },
            saveTrojanConfig() {
                axios.post('api.php', {
                    action: "save_config",
                    data: {
                        mode: this.config.temp.mode,
                        profile: this.config.temp.profile,
                        config: this.config.temp.modes[3].profile
                    }
                }).then(() => {
                    console.log("Trojan config saved")
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Trojan config has been saved',
                        showConfirmButton: false,
                        timer: 1500
                    })
                    this.config.profile = ""
                    this.getProfiles(this.config.mode)
                })
            },
            importV2rayConfig() {
                const protocol = this.config.temp.modes[1].profile.protocol
                const importUrl = this.config.temp.modes[1].import_url
                const config = JSON.parse(atob(importUrl.split("://")[1]))
                const profile = this.config.temp.modes[1].profile
                switch (protocol) {
                    case "vmess":
                        const host = config.ps
                        const port = config.port
                        const network = config.net
                        const security = config.tls
                        const alterId = config.aid
                        const vmess_id = config.id
                        const vmess_security = config.type
                        const sni = config.host
                        const path = config.path
                        profile.server.host = host
                        profile.server.port = parseInt(port)
                        profile.network = network
                        profile.security = security
                        profile.server.user.level = parseInt(alterId)
                        profile.server.user.vmess.id = vmess_id
                        profile.server.user.vmess.security = vmess_security
                        profile.stream.sni = sni
                        profile.stream.path = path
                        break
                }
                this.resolveServerHost()
            },
            importTrojanConfig() {
                const importUrl = this.config.temp.modes[3].import_url
                const config = importUrl.split("://")[1]
                const profile = this.config.temp.modes[3].profile
                const host = config.split("@")[1].split(":")[0]
                const port = config.split("@")[1].split(":")[1].split("/")[0]
                const password = config.split("@")[0]
                const sni = config.split("@")[1].split(":")[1].split("/")[1]
                profile.host = host
                profile.port = parseInt(port)
                profile.password = password
                profile.sni = sni
                this.resolveServerHost()
            },
            resolveServerHost: _.debounce(function () {
                switch (this.config.temp.mode) {
                    case 0:
                        axios.post('api.php', {
                            action: 'resolve_host',
                            host: this.config.temp.modes[0].profile.host
                        }).then((res) => {
                            this.config.temp.modes[0].profile.ip = res.data.data[0]
                        })
                        break
                    // v2ray
                    case 1:
                        axios.post('api.php', {
                            action: 'resolve_host',
                            host: this.config.temp.modes[1].profile.server.host
                        }).then((res) => {
                            this.config.temp.modes[1].profile.etc.ip = res.data.data[0]
                        })
                        break
                    // ssh-ssl
                    case 2:
                        axios.post('api.php', {
                            action: 'resolve_host',
                            host: this.config.temp.modes[2].profile.host
                        }).then((res) => {
                            this.config.temp.modes[2].profile.ip = res.data.data[0]
                        })
                        break
                    // trojan
                    case 3:
                        axios.post('api.php', {
                            action: 'resolve_host',
                            host: this.config.temp.modes[3].profile.host
                        }).then((res) => {
                            this.config.temp.modes[3].profile.ip = res.data.data[0]
                        })
                        break
                }
            }, 500)
        },
        created() {
            this.getProfiles(0)
        }
    })
</script>
</body>
</html>