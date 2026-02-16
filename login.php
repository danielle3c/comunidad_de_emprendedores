<?php
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/csrf.php';

secure_session_start();

if (isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}

$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>


