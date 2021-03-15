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

    <title>Libernet | About</title>
</head>
<body>
<div id="app">
    <?php include('navbar.php'); ?>
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-6 mx-auto mt-4 mb-2">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">About Libernet</h3>
                    </div>
                    <div class="card-body">
                        <div>
                            <p>
                                Libernet is open source web app for tunneling internet using SSH, V2Ray on OpenWRT with ease.
                            </p>
                            <span>Working features:</span>
                            <ul class="m-2">
                                <li>SSH with proxy</li>
                                <li>SSH-SSL</li>
                                <li>V2Ray VMess</li>
                                <li>V2Ray VLESS</li>
                                <li>V2Ray Trojan</li>
                                <li>Trojan</li>
                                <li>Shadowsocks</li>
                            </ul>
                            <p>
                                Some features still under development!
                            </p>
                            <p class="text-right m-0"><a href="https://facebook.com/lutfailham">Report bug</a></p>
                            <p class="text-right m-0">Author: <a href="https://facebook.com/lutfailham"><i>Lutfa Ilham</i></a></p>
                        </div>
                        <div class="text-center">
                            <p v-if="status === 3" class="text-danger mt-0 mb-1">Update failed!</p>
                            <p v-else-if="status === 2" class="text-success mt-0 mb-1">Updated to latest version!</p>
                            <p v-else-if="status === 1" class="text-secondary mt-0 mb-1">Updating ...</p>
                            <button class="btn btn-primary" :disabled="status === 1" @click="updateLibernet">Update</button>
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
</script>
</body>
</html>