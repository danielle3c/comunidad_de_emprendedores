<?php
require_once __DIR__ . '/security.php';
secure_session_start();

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string {
    $t = csrf_token();
    return '<input type="hidden" name="_csrf" value="'.htmlspecialchars($t, ENT_QUOTES, 'UTF-8').'">';
}

function csrf_verify(): void {
    $sent = $_POST['_csrf'] ?? '';
    $real = $_SESSION['_csrf'] ?? '';
    if (!$sent || !$real || !hash_equals($real, $sent)) {
        http_response_code(419);
        echo "Token CSRF inválido (419).";
        exit;
    }
}
