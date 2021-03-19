const app = new Vue({
    el: "#app",
    data() {
        return {
            status: 0
        }
    },
    methods: {
        checkUpdate() {
            return new Promise((resolve) => {
                axios.post('api.php', {
                    action: 'check_update'
                }).then((res) => {
                    this.status = parseInt(res.data.data)
                    resolve(res)
                })
            })
        },
        intervalCheckUpdate() {
            setInterval(() => {
                this.checkUpdate()
            }, 1000)
        },
        updateLibernet() {
            if (this.status !== 1) {
                axios.post('api.php', {
                    action: 'update_libernet'
                })
            }
        }
    },
    created() {
        this.checkUpdate().then(() => this.intervalCheckUpdate())
    }
})