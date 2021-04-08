<?php
    include('auth.php');
    check_session();
?>
<!doctype html>
<html lang="en">
<head>
    <?php
        $title = "System";
        include("head.php");
    ?>
</head>
<body>
<div id="app">
    <?php include('navbar.php'); ?>
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-12 mx-auto mt-4 mb-2">
                <div class="card">
                    <div class="card-header">
                        <div class="text-center">
                            <h3><i class="fa fa-user"></i> System</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="changePassword">
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" class="form-control" placeholder="admin" v-model="username" disabled required>
                            </div>
                            <div class="row">
                                <div class="col-lg-6 col-md-12">
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" class="form-control" placeholder="StrongPassword" v-model="password">
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-12">
                                    <div class="form-group">
                                        <label>Password Confirmation</label>
                                        <input type="password" class="form-control" placeholder="StrongPassword" v-model="password_confirmation">
                                    </div>
                                </div>
                                <div class="col text-center">
                                    <button type="submit" class="btn btn-primary" :disabled="!status">Change Password</button>
                                </div>
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
<script src="js/system.js">
</script>
</body>
</html>