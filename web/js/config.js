const app = new Vue({
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
                        },
                        {
                            value: 4,
                            name: "Shadowsocks",
                            plugins: [
                                {
                                    value: "none",
                                    name: "None"
                                },
                                {
                                    value: "obfs-local",
                                    name: "Simple-OBFS",
                                    obfs: [
                                        {
                                            value: "http",
                                            name: "HTTP"
                                        },
                                        {
                                            value: "tls",
                                            name: "TLS"
                                        }
                                    ]
                                },
                                {
                                    value: "ck-client",
                                    name: "Cloak"
                                }
                            ],
                            methods: [
                                "chacha20-ietf-poly1305",
                                "aes-256-gcm",
                                "aes-128-gcm",
                                "aes-128-ctr",
                                "aes-192-ctr",
                                "aes-256-ctr",
                                "aes-128-cfb",
                                "aes-192-cfb",
                                "aes-256-cfb",
                                "camellia-128-cfb",
                                "camellia-192-cfb",
                                "camellia-256-cfb",
                                "chacha20-ietf",
                                "bf-cfb",
                                "chacha20",
                                "salsa20",
                                "rc4-md5"
                            ],
                            profile: {
                                ip: "",
                                host: "",
                                port: null,
                                password: "",
                                method: "",
                                plugin: "",
                                simple_obfs: "",
                                sni: "",
                                cloak: {
                                    uid: "",
                                    public_key: ""
                                },
                                udpgw: {
                                    ip: "127.0.0.1",
                                    port: null
                                }
                            },
                            import_url: ""
                        },
                        {
                            value: 5,
                            name: "OpenVPN",
                            profile: {
                                status: 0,
                                ovpn: "",
                                username: "",
                                password: "",
                                ssl: false,
                                sni: ""
                            }
                        }
                    ]
                },
                system: {}
            }
        }
    },
    computed: {
        openvpn_auth_user_pass() {
            return this.config.temp.modes[5].profile.ovpn.includes("auth-user-pass")
        },
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
            let action
            switch (mode) {
                case 0:
                    action = "get_ssh_configs"
                    break
                case 1:
                    action = "get_v2ray_configs"
                    break
                case 2:
                    action = "get_sshl_configs"
                    break
                case 3:
                    action = "get_trojan_configs"
                    break
                case 4:
                    action = "get_shadowsocks_configs"
                    break
                case 5:
                    action = "get_openvpn_configs"
                    break
            }
            axios.post('api.php', {
                action: action
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
                    case 4:
                        this.getShadowsocksConfig()
                        break
                    case 5:
                        this.getOpenvpnConfig()
                        break
                }
                // resolve server host
                this.resolveServerHost()
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
        getShadowsocksConfig() {
            axios.post('api.php', {
                action: "get_shadowsocks_config",
                profile: this.config.profile
            }).then((res) => {
                const temp = this.config.temp
                const profile = temp.modes[4].profile
                const data = res.data.data
                temp.mode = 4
                temp.profile = this.config.profile
                profile.ip = data.etc.ip
                profile.host = data.server
                profile.port = data.server_port
                profile.password = data.password
                profile.method = data.method
                profile.udpgw.port = data.etc.udpgw.port
                switch (data.plugin) {
                    case 'obfs-local':
                        profile.plugin = data.plugin
                        profile.simple_obfs = data.plugin_opts.split('obfs=')[1].split(';')[0]
                        profile.sni = data.plugin_opts.split('obfs-host=')[1]
                        break;
                    case 'ck-client':
                        profile.plugin = data.plugin
                        profile.cloak.uid = data.plugin_opts.split("UID=")[1].split(";")[0]
                        profile.cloak.public_key = data.plugin_opts.split("PublicKey=")[1].split(";")[0]
                        profile.sni = data.plugin_opts.split("ServerName=")[1].split(";")[0]
                        break;
                    default:
                        profile.plugin = 'none'
                        break;
                }
            })
        },
        getOpenvpnConfig() {
            axios.post('api.php', {
                action: "get_openvpn_config",
                profile: this.config.profile
            }).then((res) => {
                const temp = this.config.temp
                const profile = temp.modes[5].profile
                const data = res.data.data
                temp.mode = 5
                temp.profile = this.config.profile
                profile.ovpn = data.ovpn
                profile.username = data.username
                profile.password = data.password
                profile.ssl = data.ssl
                profile.sni = data.sni
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
            const configMode = this.config.temp.mode
            const configProfile = this.config.temp.profile
            let config, title
            switch (configMode) {
                case 0:
                    config = this.config.temp.modes[0].profile
                    title = "SSH config has been saved"
                    break
                case 1:
                    config = this.config.temp.modes[1].profile
                    title = "V2Ray config has been saved"
                    break
                case 2:
                    config = this.config.temp.modes[2].profile
                    title = "SSH-SSL config has been saved"
                    break
                case 3:
                    config = this.config.temp.modes[3].profile
                    title = "Trojan config has been saved"
                    break
                case 4:
                    config = this.config.temp.modes[4].profile
                    title = "Shadowsocks config has been saved"
                    break
                case 5:
                    config = this.config.temp.modes[5].profile
                    title = "OpenVPN config has been saved"
                    break
            }
            axios.post('api.php', {
                action: "save_config",
                data: {
                    mode: configMode,
                    profile: configProfile,
                    config: config
                }
            }).then(() => {
                console.log(title)
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: title,
                    showConfirmButton: false,
                    timer: 1500
                })
                // reload config menu
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
            const profile = this.config.temp.modes[3].profile
            const importUrl = this.config.temp.modes[3].import_url
            const config = importUrl.split("://")[1]
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
        importShadowsocksConfig() {
            const profile = this.config.temp.modes[4].profile
            const importUrl = this.config.temp.modes[4].import_url
            const config = atob(importUrl.split("://")[1].split("#")[0]).split("@")
            const server = config[config.length - 1].split(":")
            const host = server[0]
            const port = server[1]
            const user = config.splice(0, config.length - 1).join("@").split(":")
            const method = user[0]
            const password = user[1]
            profile.host = host
            profile.port = parseInt(port)
            profile.method = method
            profile.password = password
            this.resolveServerHost()
        },
        importOvpnConfig(event) {
            const file = event.target.files[0]
            const reader = new FileReader()
            reader.readAsText(file)
            reader.onload = e => {
                this.$emit("load", e.target.result)
                this.config.temp.modes[5].profile.ovpn = e.target.result
            }
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
                // shadowsocks
                case 4:
                    axios.post('api.php', {
                        action: 'resolve_host',
                        host: this.config.temp.modes[4].profile.host
                    }).then((res) => {
                        this.config.temp.modes[4].profile.ip = res.data.data[0]
                    })
                    break
            }
        }, 500)
    },
    created() {
        this.getProfiles(0)
    }
})