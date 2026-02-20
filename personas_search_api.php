<?php
// includes/personas_search_api.php
// API JSON protegida — responde siempre JSON, nunca redirect

require_once __DIR__ . '/security.php';
require_once __DIR__ . '/helpers.php';

secure_session_start();

header('Content-Type: application/json; charset=utf-8');

// Si no hay sesión → JSON de error (no redirect, porque fetch() lo seguiría silenciosamente)
if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthenticated']);
    exit;
}

$pdo = getConnection();
$q   = trim($_GET['q'] ?? '');

if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$qLike = '%' . $q . '%';

$sql = "
SELECT
    p.idpersonas AS id,
    p.rut,
    CONCAT(p.nombres, ' ', p.apellidos) AS nombre,
    EXISTS(
        SELECT 1
        FROM emprendedores e
        JOIN Contratos c ON c.emprendedores_idemprendedores = e.idemprendedores
        WHERE e.personas_idpersonas = p.idpersonas AND c.estado = 'Activo'
    ) AS contrato_activo,
    EXISTS(
        SELECT 1
        FROM emprendedores e
        JOIN creditos cr ON cr.emprendedores_idemprendedores = e.idemprendedores
        WHERE e.personas_idpersonas = p.idpersonas AND cr.estado = 'Activo'
    ) AS credito_activo,
    EXISTS(
        SELECT 1
        FROM emprendedores e
        JOIN inscripciones_talleres it ON it.emprendedores_idemprendedores = e.idemprendedores
        WHERE e.personas_idpersonas = p.idpersonas
    ) AS talleres
FROM personas p
WHERE p.estado = 1
  AND (
    p.rut        LIKE :q
    OR p.nombres LIKE :q
    OR p.apellidos LIKE :q
    OR CONCAT(p.nombres, ' ', p.apellidos) LIKE :q
  )
ORDER BY p.apellidos, p.nombres
LIMIT 10
";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':q' => $qLike]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = array_map(fn($r) => [
        'id'              => (int)$r['id'],
        'rut'             => $r['rut'],
        'nombre'          => $r['nombre'],
        'contrato_activo' => (bool)$r['contrato_activo'],
        'credito_activo'  => (bool)$r['credito_activo'],
        'talleres'        => (bool)$r['talleres'],
    ], $rows);

    echo json_encode($out, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'db_error']);
}
