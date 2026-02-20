<?php
function require_role(array $allowedRoles): void {
    $role = $_SESSION['user']['rol'] ?? 'usuario';
    if (!in_array($role, $allowedRoles, true)) {
        http_response_code(403);
        echo "Acceso denegado (403).";
        exit;
    }
}

