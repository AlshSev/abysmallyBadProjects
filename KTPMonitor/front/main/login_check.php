<?php
    $logged_in = false;
    if (isset($_COOKIE['fingerprint'])) {
        $res = exec("python3 ../../back/redis-utils/check_fingerprint.py " . $_COOKIE['fingerprint']);
        if (intval($res) > 0)
            $logged_in = true;
    }
    if ($logged_in == false && isset($_GET['login']) && isset($_GET['password'])) {
        $res = exec("python3 ../../back/redis-utils/login.py " . $_GET['login'] . " " . $_GET['password']);
        if ($res == "-1") {
            //error
        }
        else if ($res == "0") {
            //no such user/password
        }
        else {
            setcookie('fingerprint', $res, 2592000 + time());
            $query = $_GET;
            unset($query['login']);
            unset($query['password']);
            header("Refresh:0; url=" . basename($_SERVER['PHP_SELF']) . "?" . http_build_query($query));
        }
    }
?>