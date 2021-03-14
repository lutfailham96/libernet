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

    <title>Libernet | Home</title>
</head>
<body>
<div id="app">
    <?php include('navbar.php'); ?>
    <div class="container">
        <div class="row py-2">
            <div class="col-lg-8 col-md-12 mx-auto">
                <div class="text-center">
                    <h3>Home</h3>
                </div>
                <div class="card">
                    <div class="card-header">
                        <form @submit.prevent="runLibernet">
                            <div class="form-group form-row my-auto">
                                <label class="my-auto mx-1">Mode</label>
                                <select class="form-control w-25 mx-1" v-model.number="config.mode" :disabled="status === true" required>
                                    <option v-for="mode in config.temp.modes" :value="mode.value">{{ mode.name }}</option>
                                </select>
                                <label class="my-auto mx-1">Config</label>
                                <select class="form-control w-25 mx-1" v-model="config.profile" :disabled="status === true" required>
                                    <option v-for="profile in config.profiles" :value="profile">{{ profile }}</option>
                                </select>
                                <button type="submit" class="btn mx-1" :class="{ 'btn-danger': status, 'btn-primary': !status }">{{ statusText }}</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="card-body py-0">
                            <div class="row">
                                <div class="col-lg-6 col-md-6 pb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" v-model="config.system.tun2socks.legacy" :disabled="status === true" id="tun2socks-legacy">
                                        <label class="form-check-label" for="tun2socks-legacy">
                                            Use tun2socks legacy
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 pb-2">
                                    <div class="form-check float-right">
                                        <input class="form-check-input" type="checkbox" v-model="config.system.tunnel.autostart" :disabled="status === true" id="autostart">
                                        <label class="form-check-label" for="autostart">
                                            Auto start Libernet on boot
                                        </label>
                                    </div>
                                </div>
                                <div class="col-lg-12 col-md-6 pb-2">
                                    <div class="form-check float-left">
                                        <input class="form-check-input" type="checkbox" v-model="config.system.tunnel.dns_resolver" :disabled="status === true" id="dns-resolver">
                                        <label class="form-check-label" for="dns-resolver">
                                            DNS resolver
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12 row pb-2 pr-0">
                                    <div class="col-md-6">
                                        <div class="float-left">
                                            <span>Status: </span><span :class="{ 'text-secondary': connection === 0, 'text-warning': connection === 1, 'text-success': connection === 2, 'text-info': connection === 3 }">{{ connectionText }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 pr-0 mr-0">
                                        <div class="float-right">
                                            <span>WAN IP: {{ wan_ip }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <pre ref="log" v-html="log" class="form-control text-left" style="height: 15rem; background-color: #e9ecef"></pre>
                                </div>
                            </div>
                        </div>
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
                status: false,
                connection: 0,
                log: "",
                wan_ip: "",
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
                                name: "SSH"
                            },
                            {
                                value: 1,
                                name: "V2Ray"
                            },
                            {
                                value: 2,
                                name: "SSH-SSL"
                            },
                            {
                                value: 3,
                                name: "Trojan"
                            }
                        ]
                    },
                    system: {
                        tunnel: {
                            autostart: false,
                            dns_resolver: false
                        },
                        tun2socks: {
                            legacy: false
                        }
                    }
                }
            }
        },
        computed: {
            statusText() {
                return this.status === true ? 'Stop' : 'Start'
            },
            connectionText() {
                switch (this.connection) {
                    case 0:
                        return 'ready'
                    case 1:
                        return 'connecting'
                    case 2:
                        return 'connected'
                    case 3:
                        return  'stopping'
                }
            }
        },
        watch: {
            'config.mode': function (mode) {
                this.getProfiles(mode)
                // this.config.profile = ""
            }
        },
        methods: {
            runLibernet() {
                // this.status = !this.status
                if (!this.status) {
                    // this.status = true
                    this.applyConfig().then(() => {
                        axios.post('api.php', {
                            action: "start_libernet"
                        }).then(() => {
                            console.log('Libernet service started!')
                        })
                        // set auto start Libernet
                        axios.post('api.php', {
                            action: "set_auto_start",
                            status: this.config.system.tunnel.autostart
                        })
                    })
                } else {
                    switch (this.connection) {
                        case 1:
                            axios.post('api.php', {
                                action: "cancel_libernet"
                            }).then(() => {
                                console.log('Libernet service canceled!')
                            })
                            break
                        case 2:
                            axios.post('api.php', {
                                action: "stop_libernet"
                            }).then(() => {
                                console.log('Libernet service stopped!')
                            })
                            break
                    }
                }
            },
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
            applyConfig() {
                return new Promise((resolve) => {
                    axios.post('api.php', {
                        action: "apply_config",
                        data: {
                            mode: this.config.mode,
                            profile: this.config.profile,
                            tun2socks_legacy: this.config.system.tun2socks.legacy,
                            dns_resolver: this.config.system.tunnel.dns_resolver
                        }
                    }).then((res) => {
                        resolve(res)
                    })
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
            getWanIp() {
                setInterval(() => {
                    axios.get('http://ip-api.com/json').then((res) => {
                        this.wan_ip = res.data.query
                    })
                }, 5000)
            },
            getDashboardInfo() {
                setInterval(() => {
                    axios.post('api.php', {
                        action: "get_dashboard_info"
                    }).then((res) => {
                        this.status = res.data.data.status !== 0
                        this.connection = res.data.data.status
                        this.log = res.data.data.log
                        this.$refs.log.scrollTop = this.$refs.log.scrollHeight
                    })
                }, 1000)
            }
        },
        created() {
            this.getSystemConfig().then((res) => {
                const mode = res.tunnel.mode
                this.config.system = res
                this.config.mode = mode
                this.getProfiles(mode)
                switch (mode) {
                    case 0:
                        this.config.profile = res.tunnel.profile.ssh
                        break
                    case 1:
                        this.config.profile = res.tunnel.profile.v2ray
                        break
                    case 2:
                        this.config.profile = res.tunnel.profile.ssh_ssl
                        break
                    case 3:
                        this.config.profile = res.tunnel.profile.trojan
                }
            })
            this.getDashboardInfo()
            this.getWanIp()
        }
    })
</script>
</body>
</html>