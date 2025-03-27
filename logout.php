<?php
require_once 'config/db.php';
require_once 'config/functions.php';

// ลบ Session
session_unset();
session_destroy();

// ลบ Remember Token
if(isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

redirect(BASE_URL . 'login.php');
?>
