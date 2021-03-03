<?php
    session_start();

    function set_session($usename, $password) {
        $_SESSION['username'] = $usename;
        $_SESSION['password'] = $password;
        header("Location: index.php");
    }

    function check_session() {
        if ((!isset($_SESSION['username'])) && (!isset($_SESSION['password']))) {
            header("Location: login.php");
        }
    }

    function remove_session() {
        unset($_SESSION['username']);
        unset($_SESSION['password']);
        session_unset();
        session_destroy();
        header("Location: login.php");
    }
?>