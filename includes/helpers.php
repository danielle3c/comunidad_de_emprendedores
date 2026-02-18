<?php
// includes/helpers.php

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function formatDate(?string $date): string {
    if (!$date || $date === '0000-00-00') return '-';
    return date('d/m/Y', strtotime($date));
}

function formatDateTime(?string $dt): string {
    if (!$dt || $dt === '0000-00-00 00:00:00') return '-';
    return date('d/m/Y H:i', strtotime($dt));
}

function formatMoney($amount): string {
    if ($amount === null || $amount === '') return '$0';
    return '$' . number_format((float)$amount, 0, ',', '.');
}

function badgeEstado(string $estado): string {
    $map = [
        'Activo'          => ['success',   'Activo'],
        'Finalizado'      => ['secondary', 'Finalizado'],
        'Cancelado'       => ['danger',    'Cancelado'],
        'Cancelada'       => ['danger',    'Cancelada'],
        'Pagado'          => ['info',      'Pagado'],
        'Vencido'         => ['warning',   'Vencido'],
        'Programado'      => ['primary',   'Programado'],
        'En Curso'        => ['warning',   'En Curso'],
        'Planificada'     => ['primary',   'Planificada'],
        'En Ejecución'    => ['warning',   'En Ejecución'],
        'Finalizada'      => ['secondary', 'Finalizada'],
        'Confirmada'      => ['success',   'Confirmada'],
        'Pendiente'       => ['warning',   'Pendiente'],
        '1'               => ['success',   'Activo'],
        '0'               => ['danger',    'Inactivo'],
    ];
    [$color, $label] = $map[$estado] ?? ['secondary', htmlspecialchars($estado, ENT_QUOTES, 'UTF-8')];
    return "<span class=\"badge bg-$color\">" . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "</span>";
}

function getPaginationData(int $total, int $page, int $perPage = 15): array {
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page       = max(1, min($page, $totalPages));
    $offset     = ($page - 1) * $perPage;
    return [
        'total'      => $total,
        'page'       => $page,
        'perPage'    => $perPage,
        'totalPages' => $totalPages,
        'offset'     => $offset,
    ];
}

function renderPagination(array $pag, string $baseUrl): string {
    if ($pag['totalPages'] <= 1) return '';
    $p     = $pag['page'];
    $total = $pag['totalPages'];

    $html = '<nav aria-label="Paginación"><ul class="pagination justify-content-center mb-0">';

    $dis = $p <= 1 ? ' disabled' : '';
    $html .= "<li class=\"page-item$dis\"><a class=\"page-link\" href=\"{$baseUrl}&page=" . ($p - 1) . "\">‹</a></li>";

    $range = [];
    for ($i = 1; $i <= $total; $i++) {
        if ($i === 1 || $i === $total || abs($i - $p) <= 2) {
            $range[] = $i;
        }
    }
    $prev = null;
    foreach ($range as $i) {
        if ($prev !== null && $i - $prev > 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
        $active = $i === $p ? ' active' : '';
        $html .= "<li class=\"page-item$active\"><a class=\"page-link\" href=\"{$baseUrl}&page=$i\">$i</a></li>";
        $prev = $i;
    }

    $dis = $p >= $total ? ' disabled' : '';
    $html .= "<li class=\"page-item$dis\"><a class=\"page-link\" href=\"{$baseUrl}&page=" . ($p + 1) . "\">›</a></li>";

    $html .= '</ul></nav>';
    return $html;
}
?>