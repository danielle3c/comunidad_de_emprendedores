<?php
// ============================================
// Funciones auxiliares compartidas
// ============================================

require_once __DIR__ . '/../config/database.php';

function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function setFlash(string $type, string $message): void {
    session_start();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    session_start();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function formatDate(?string $date): string {
    if (!$date) return '-';
    return date('d/m/Y', strtotime($date));
}

function formatDateTime(?string $dt): string {
    if (!$dt) return '-';
    return date('d/m/Y H:i', strtotime($dt));
}

function formatMoney(?float $amount): string {
    if ($amount === null) return '$0';
    return '$' . number_format($amount, 0, ',', '.');
}

function badgeEstado(string $estado, string $tipo = 'general'): string {
    $map = [
        'Activo'       => 'success',
        'Finalizado'   => 'secondary',
        'Cancelado'    => 'danger',
        'Pagado'       => 'info',
        'Vencido'      => 'warning',
        'Programado'   => 'primary',
        'En Curso'     => 'warning',
        'Planificada'  => 'primary',
        'En Ejecución' => 'warning',
        'Finalizada'   => 'secondary',
        'Confirmada'   => 'success',
        'Pendiente'    => 'warning',
        '1'            => 'success',
        '0'            => 'danger',
    ];
    $color = $map[$estado] ?? 'secondary';
    $label = ($estado === '1') ? 'Activo' : (($estado === '0') ? 'Inactivo' : $estado);
    return "<span class=\"badge bg-$color\">$label</span>";
}

function getPaginationData(int $total, int $page, int $perPage = 15): array {
    $totalPages = (int) ceil($total / $perPage);
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $perPage;
    return ['total' => $total, 'page' => $page, 'perPage' => $perPage, 'totalPages' => $totalPages, 'offset' => $offset];
}

function renderPagination(array $pag, string $baseUrl): string {
    if ($pag['totalPages'] <= 1) return '';
    $html = '<nav><ul class="pagination justify-content-center">';
    $prev = $pag['page'] - 1;
    $next = $pag['page'] + 1;
    $disabled = $pag['page'] <= 1 ? 'disabled' : '';
    $html .= "<li class=\"page-item $disabled\"><a class=\"page-link\" href=\"{$baseUrl}&page=$prev\">‹</a></li>";
    for ($i = 1; $i <= $pag['totalPages']; $i++) {
        $active = ($i === $pag['page']) ? 'active' : '';
        $html .= "<li class=\"page-item $active\"><a class=\"page-link\" href=\"{$baseUrl}&page=$i\">$i</a></li>";
    }
    $disabled2 = $pag['page'] >= $pag['totalPages'] ? 'disabled' : '';
    $html .= "<li class=\"page-item $disabled2\"><a class=\"page-link\" href=\"{$baseUrl}&page=$next\">›</a></li>";
    $html .= '</ul></nav>';
    return $html;
}
