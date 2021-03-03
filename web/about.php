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
                        <p>
                            Libernet is open source web app for tunneling internet using SSH, V2Ray on OpenWRT with ease.
                        </p>
                        <span>Working features:</span>
                        <ul class="m-2">
                            <li>SSH with proxy</li>
                            <li>V2Ray trojan</li>
                            <li>V2Ray vmess</li>
                        </ul>
                        <p>
                            Some features still under development!
                        </p>
                        <p class="text-right m-0"><a href="https://facebook.com/lutfailham">Report bug</a></p>
                        <p class="text-right m-0">Author: <a href="https://facebook.com/lutfailham"><i>Lutfa Ilham</i></a></p>
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
</body>
</html>