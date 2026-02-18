<?php
// includes/csrf.php

function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string {
    $t = csrf_token();
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_verify(): bool {
    $sent = $_POST['_csrf'] ?? '';
    $real = $_SESSION['_csrf'] ?? '';
    if (!$sent || !$real || !hash_equals($real, $sent)) {
        return false;
    }
    return true;
}
?>