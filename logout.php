<?php
require_once 'config.php';
require_once 'functions.php';

if (!empty($_SESSION['user_id'])) {
    log_activity($pdo, 'users', (int) $_SESSION['user_id'], 'LOGOUT', 'User logged out.', (int) $_SESSION['user_id']);
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
}
session_destroy();
header('Location: login.php?logout=1');
exit;
?>
