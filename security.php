<?php
// includes/security.php

function secure_session_start(): void {
    if (session_status() !== PHP_SESSION_NONE) return;

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_start();

    // Timeout de inactividad: 30 minutos
    $timeout = 30 * 60;
    $now     = time();

    if (isset($_SESSION['_last_activity']) && ($now - (int)$_SESSION['_last_activity']) > $timeout) {
        session_unset();
        session_destroy();
        // Iniciar nueva sesión limpia para poder mostrar el mensaje
        session_start();
        $_SESSION['flash_error'] = 'Su sesión ha expirado. Por favor inicie sesión nuevamente.';
        header('Location: login.php');
        exit;
    }

    $_SESSION['_last_activity'] = $now;
}
