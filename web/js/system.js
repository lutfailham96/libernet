const app = new Vue({
    el: "#app",
    data() {
        return {
            username: "",
            password: "",
            password_confirmation: ""
        }
    },
    computed: {
        status() {
            return (this.password === this.password_confirmation) && (this.password_confirmation.trim().length > 0)
        }
    },
    methods: {
        getSystemConfig() {
            return new Promise((resolve) => {
                axios.post('api.php', {
                    action: "get_system_config"
                }).then((res) => {
                    resolve(res.data.data)
                })
            })
        },
        changePassword() {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                reverseButtons: true,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change password!'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('api.php', {
                        action: "change_password",
                        password: this.password_confirmation
                    }).then(() => {
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: 'Password has been changed!',
                            showConfirmButton: false,
                            timer: 1500
                        })
                        this.password = ""
                        this.password_confirmation = ""
                    })
                }
            })
        }
    },
    created() {
        this.getSystemConfig().then((res) => {
            this.username = res.system.username
        })
    }
})