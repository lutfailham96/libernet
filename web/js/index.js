const app = new Vue({
    el: '#app',
    data() {
        return {
            status: false,
            connection: 0,
            log: "",
            connected: {
                timestamp: 0,
                days: 0,
                hours: 0,
                minutes: 0,
                seconds: 0
            },
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
                        },
                        {
                            value: 4,
                            name: "Shadowsocks"
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
                    },
                    system: {
                        memory_cleaner: false
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
        },
        connectedTime() {
            return '[' + this.pad(this.connected.days, 2) + ':' + this.pad(this.connected.hours, 2) + ':' + this.pad(this.connected.minutes, 2) + ':' + this.pad(this.connected.seconds, 2) + ']'
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
            }
            axios.post('api.php', {
                action: action
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
                        dns_resolver: this.config.system.tunnel.dns_resolver,
                        memory_cleaner: this.config.system.system.memory_cleaner
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
            return new Promise((resolve) => {
                axios.get('http://ip-api.com/json?fields=query').then((res) => {
                    this.wan_ip = res.data.query
                    resolve(res)
                })
            })
        },
        intervalGetWanIp() {
            setInterval(() => {
                this.getWanIp()
            }, 5000)
        },
        getDashboardInfo() {
            return new Promise((resolve) => {
                axios.post('api.php', {
                    action: "get_dashboard_info"
                }).then((res) => {
                    this.status = res.data.data.status !== 0
                    this.connection = res.data.data.status
                    if (parseFloat(res.data.data.connected) > 0) {
                        this.connected.timestamp = res.data.data.connected
                        this.updateConnectedTime()
                    }
                    this.log = res.data.data.log
                    this.$refs.log.scrollTop = this.$refs.log.scrollHeight
                    resolve(res)
                })
            })
        },
        intervalGetDashboardInfo() {
            setInterval(() => {
                this.getDashboardInfo()
            }, 1000)
        },
        updateConnectedTime() {
            const now = Math.round(new Date().getTime() / 1000)
            let difference = Math.abs(now - this.connected.timestamp)
            const daysDifference = Math.floor(difference/60/60/24)
            difference -= daysDifference*60*60*24
            const hoursDifference = Math.floor(difference/60/60)
            difference -= hoursDifference*60*60
            const minutesDifference = Math.floor(difference/60)
            difference -= minutesDifference*60
            const secondsDifference = Math.floor(difference)
            this.connected.days = daysDifference
            this.connected.hours = hoursDifference
            this.connected.minutes = minutesDifference
            this.connected.seconds = secondsDifference
        },
        pad(n, width, z) {
            z = z || '0'
            n = n + ''
            return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n
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
                    break
                case 4:
                    this.config.profile = res.tunnel.profile.shadowsocks
                    break
            }
        })
        this.getDashboardInfo().then(() => this.intervalGetDashboardInfo())
        this.getWanIp().then(() => this.intervalGetWanIp())
    }
})