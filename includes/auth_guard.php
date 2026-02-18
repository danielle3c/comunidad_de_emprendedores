<?php
// includes/auth_guard.php
require_once __DIR__ . '/security.php';
secure_session_start();

if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
    header('Location: login.php');
    exit;
}
?>