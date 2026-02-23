<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/helpers.php';

secure_session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthenticated']);
    exit;
}

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $pdo   = getConnection();
    $qLike = '%' . $q . '%';

    // PDO con named params NO permite repetir el mismo :param más de una vez.
    // Solución: usar ? (positional) o dar un nombre distinto a cada ocurrencia.
    $sql = "
        SELECT
            p.idpersonas                                          AS id,
            p.rut,
            CONCAT(p.nombres, ' ', p.apellidos)                   AS nombre,
            p.telefono,
            p.email,
            EXISTS (
                SELECT 1 FROM emprendedores e
                WHERE e.personas_idpersonas = p.idpersonas
            ) AS es_emprendedor,
            EXISTS (
                SELECT 1 FROM emprendedores e
                JOIN Contratos c ON c.emprendedores_idemprendedores = e.idemprendedores
                WHERE e.personas_idpersonas = p.idpersonas AND c.estado = 'Activo'
            ) AS contrato_activo,
            EXISTS (
                SELECT 1 FROM emprendedores e
                JOIN creditos cr ON cr.emprendedores_idemprendedores = e.idemprendedores
                WHERE e.personas_idpersonas = p.idpersonas AND cr.estado = 'Activo'
            ) AS credito_activo,
            EXISTS (
                SELECT 1 FROM emprendedores e
                JOIN inscripciones_talleres it ON it.emprendedores_idemprendedores = e.idemprendedores
                WHERE e.personas_idpersonas = p.idpersonas
            ) AS talleres,
            EXISTS (
                SELECT 1 FROM emprendedores e
                JOIN creditos cr ON cr.emprendedores_idemprendedores = e.idemprendedores
                JOIN cobranzas cb ON cb.creditos_idcreditos = cr.idcreditos
                WHERE e.personas_idpersonas = p.idpersonas
            ) AS tiene_pagos
        FROM personas p
        WHERE p.estado = 1
          AND (
                p.rut                             LIKE ?
             OR p.nombres                         LIKE ?
             OR p.apellidos                       LIKE ?
             OR CONCAT(p.nombres,' ',p.apellidos) LIKE ?
          )
        ORDER BY p.apellidos, p.nombres
        LIMIT 10
    ";

    // Pasar el mismo valor 4 veces como positional params (uno por cada ?)
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$qLike, $qLike, $qLike, $qLike]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = array_map(fn($r) => [
        'id'              => (int)  $r['id'],
        'rut'             =>        $r['rut'],
        'nombre'          =>        $r['nombre'],
        'telefono'        =>        $r['telefono'] ?? '',
        'email'           =>        $r['email']    ?? '',
        'es_emprendedor'  => (bool) $r['es_emprendedor'],
        'contrato_activo' => (bool) $r['contrato_activo'],
        'credito_activo'  => (bool) $r['credito_activo'],
        'talleres'        => (bool) $r['talleres'],
        'tiene_pagos'     => (bool) $r['tiene_pagos'],
    ], $rows);

    echo json_encode($out, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log('[SmartSearch] ' . $e->getMessage());

    $msg = $e->getMessage();
    preg_match("/Table '([^']+)' doesn't exist/i", $msg, $mT);
    preg_match("/Unknown column '([^']+)'/i",       $msg, $mC);
    $detalle = !empty($mT[1]) ? "Tabla no existe: {$mT[1]}"
             : (!empty($mC[1]) ? "Columna no existe: {$mC[1]}"
             : $msg);

    http_response_code(500);
    echo json_encode(['error' => 'db_error', 'detalle' => $detalle]);
}