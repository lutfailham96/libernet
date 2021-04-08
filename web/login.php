<?php
    include('config.inc.php');
    include('auth.php');
    $loginError = false;
    if ((isset($_SESSION['username'])) && (isset($_SESSION['password']))) {
        header("Location: index.php");
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $system_config = file_get_contents($libernet_dir.'/system/config.json');
        $system_config = json_decode($system_config);
        if (($system_config->system->username === $username) && ($system_config->system->password === $password)) {
            set_session($username, $password);
        } else {
            $loginError = true;
        }
    }
?>
<!doctype html>
<html lang="en">
<head>
    <?php
        $title = "Login";
        include("head.php");
    ?>
</head>
<body>
<div id="app">
    <div class="container">
        <div class="row login">
            <div class="col-lg-6 col-md-12 mx-auto">
                <div class="card mb-2">
                    <div class="card-header">
                        <h3 class="text-center">Libernet</h3>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <?php
                                if ($loginError) {
                                    echo '<div class="alert alert-danger" role="alert">Invalid username & password combination!</div>';
                                }
                            ?>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                                    </div>
                                    <input type="text" class="form-control" placeholder="Username" name="username" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                                    </div>
                                    <input type="password" class="form-control" placeholder="Password" name="password" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-sign-in"></i> Login</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php include('footer.php'); ?>
            </div>
        </div>
    </div>
</div>
<?php include("javascript.php"); ?>