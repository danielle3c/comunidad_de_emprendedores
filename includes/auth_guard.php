<?php
require_once __DIR__ . '/security.php';
secure_session_start();

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
