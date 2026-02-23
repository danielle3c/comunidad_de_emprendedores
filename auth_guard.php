<?php
// includes/auth_guard.php
// Protege todas las páginas que requieren sesión activa

require_once __DIR__ . '/security.php';
secure_session_start();

if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
    // Guardar la URL actual para redirigir después del login (opcional)
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
    header('Location: ../login.php');
    exit;
}
