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
                                        <input class="form-check-input" type="checkbox" v-model="config.temp.modes[0].profile.enable_http" checked>
                                        <label class="form-check-label">
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
                                            <label>Port</label>
                                            <input type="number" class="form-control" placeholder="8080" v-model.number="config.temp.modes[0].profile.http.proxy.port" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Payload</label>
                                        <textarea class="form-control" v-model="config.temp.modes[0].profile.http.payload" rows="5" placeholder="GET http://libernet/ HTTP/1.1[crlf][crlf]CONNECT [host_port] HTTP/1.1[crlf]Connection: keep-allive[crlf][crlf]" required></textarea>
                                    </div>
                                </div>
                                <div class="form-row pb-lg-2">
                                    <div class="col-md-3">
                                        <label>IP</label>
                                        <input type="text" class="form-control" placeholder="192.168.1.1" v-model="config.temp.modes[0].profile.ip" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Host</label>
                                        <input type="text" class="form-control" placeholder="node1.libernet.tld" v-model="config.temp.modes[0].profile.host" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Port</label>
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
                                <div class="form-row pb-lg-2">
                                    <div class="col-md-3">
                                        <label>IP</label>
                                        <input type="text" class="form-control" placeholder="192.168.1.1" v-model="config.temp.modes[1].profile.ip" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label>Host</label>
                                        <input type="text" class="form-control" placeholder="node1.libernet.tld" v-model="config.temp.modes[1].profile.host" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Port</label>
                                        <input type="number" class="form-control" placeholder="443" v-model.number="config.temp.modes[1].profile.port" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label>UDPGW Port</label>
                                        <input type="number" class="form-control" placeholder="7300" v-model.number="config.temp.modes[1].profile.udpgw.port" required>
                                    </div>
                                </div>
                                <div v-if="config.temp.modes[1].profile.protocol === config.temp.modes[1].protocols[1].value" class="form-row pb-3">
                                    <div class="col-md-6">
                                        <label>Password</label>
                                        <input type="text" class="form-control" placeholder="StrongPassword" v-model="config.temp.modes[1].profile.password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>SNI</label>
                                        <input type="text" class="form-control" placeholder="unblocked-web.tld" v-model="config.temp.modes[1].profile.sni" required>
                                    </div>
                                </div>
                                <div v-if="config.temp.modes[1].profile.protocol === config.temp.modes[1].protocols[0].value" class="form-row pb-3">
                                    <div class="col-md-5">
                                        <label>ID</label>
                                        <input type="text" class="form-control" placeholder="900c42c7-a23d-46dd-a1a0-72c37edf8a03" v-model="config.temp.modes[1].profile.id" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Security</label>
                                        <select class="custom-select" v-model="config.temp.modes[1].profile.security" required>
                                            <option v-for="security in config.temp.modes[1].protocols[0].security" :value="security">{{ security }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>SNI</label>
                                        <input type="text" class="form-control" placeholder="unblocked-web.tld" v-model="config.temp.modes[1].profile.sni" required>
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
<script src="lib/vendor/jquery/jquery-3.6.0.slim.min.js"></script>
<script src="lib/vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="lib/vendor/vuejs/vue.min.js"></script>
<script src="lib/vendor/axios/axios.min.js"></script>
<script src="lib/vendor/sweetalert2/sweetalert2.all.min.js"></script>
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
                                    port: 443,
                                    username: "",
                                    password: "",
                                    udpgw: {
                                        ip: "127.0.0.1",
                                        port: 7300
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
                                            port: 8080
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
                                        security: [
                                            "auto",
                                            "aes-128-gcm",
                                            "chacha20-poly1305",
                                            "none"
                                        ]
                                    },
                                    {
                                        name: "Trojan",
                                        value: "trojan"
                                    }
                                ],
                                profile: {
                                    protocol: "",
                                    ip: "",
                                    host: "",
                                    port: 443,
                                    username: "",
                                    password: "",
                                    id: "",
                                    level: 0,
                                    security: "",
                                    sni: "",
                                    udpgw: {
                                        ip: "127.0.0.1",
                                        port: 7300
                                    }
                                }
                            }
                        ]
                    },
                    system: {
                        tunnel: {
                            mode: 1,
                            profile: {
                                v2ray: "",
                                ssh: ""
                            }
                        },
                        server: "",
                        dns_servers: [
                            "8.8.8.8",
                            "8.8.4.4",
                            "1.1.1.1",
                            "1.1.0.0",
                            "208.67.222.222",
                            "208.67.220.220",
                            "94.140.14.14",
                            "94.140.15.15"
                        ],
                        proxy_servers: [],
                        tun2socks: {
                            legacy: false,
                            dev: "tun1",
                            mtu: 1420,
                            address: "10.0.0.2",
                            gateway: "10.0.0.1",
                            netmask: "255.255.255.0",
                            socks: {
                                ip: "127.0.0.1",
                                port: 1080
                            },
                            udpgw: {
                                ip: "127.0.0.1",
                                port: 7300
                            }
                        },
                        system: {
                            username: "",
                            password: ""
                        }
                    }
                }
            }
        },
        watch: {
            'config.mode': function (mode) {
                this.getProfiles(mode)
                this.config.profile = ""
            }
        },
        methods: {
            getProfiles(mode) {
                if (mode === 0) {
                    this.getSshProfiles()
                } else if (mode === 1) {
                    this.getV2rayProfiles()
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
            getConfig() {
                this.getSystemConfig().then((res) => {
                    this.config.system = res
                    if (this.config.mode === 0) {
                        this.getSshConfig()
                    } else if (this.config.mode === 1) {
                        this.getV2rayConfig()
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
                        }).then((res) => {
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
                    temp.mode = 1
                    temp.profile = this.config.profile
                    temp.modes[1].profile.protocol = protocol
                    temp.modes[1].profile.udpgw.port = res.data.data.etc.udpgw.port
                    if (protocol === temp.modes[1].protocols[0].value) {
                        const server = res.data.data.outbounds[0].settings.vnext[0]
                        const sni = res.data.data.outbounds[0].streamSettings.tlsSettings.serverName
                        profile.ip = this.config.system.server
                        profile.host = server.address
                        profile.port = server.port
                        profile.id = server.users[0].id
                        profile.level = server.users[0].level
                        for (let i=0; i<this.config.temp.modes[1].protocols[0].security.length; i++) {
                            if (server.users[0].security === this.config.temp.modes[1].protocols[0].security[i]) {
                                profile.security = this.config.temp.modes[1].protocols[0].security[i]
                            }
                        }
                        profile.sni = sni
                    } else if (protocol === temp.modes[1].protocols[1].value) {
                        const server = res.data.data.outbounds[0].settings.servers[0]
                        const sni = res.data.data.outbounds[0].streamSettings.tlsSettings.serverName
                        profile.ip = this.config.system.server
                        profile.host = server.address
                        profile.port = server.port
                        profile.password = server.password
                        profile.level = server.level
                        profile.sni = sni
                    }
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
                if (this.config.temp.mode === 0) {
                    this.saveSshConfig()
                } else if (this.config.temp.mode === 1) {
                    this.saveV2rayConfig()
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
            }
        },
        created() {
            this.getProfiles(0)
        }
    })
</script>
</body>
</html>