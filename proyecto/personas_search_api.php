<?php
// includes/personas_search_api.php
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

    // Nombres de tablas y columnas verificados contra los PHP originales:
    //   personas              → idpersonas, nombres, apellidos, rut, estado (1=activo, 0=inactivo)
    //   emprendedores         → idemprendedores, personas_idpersonas
    //   Contratos (C mayúsc.) → idContratos, emprendedores_idemprendedores, estado
    //   creditos              → idcreditos, emprendedores_idemprendedores, estado
    //   cobranzas             → idcobranzas, creditos_idcreditos, monto
    //   inscripciones_talleres→ idinscripcion, emprendedores_idemprendedores, talleres_idtalleres
    //   talleres              → idtalleres, nombre_taller
    //   carritos              → idcarritos (sin FK directa a personas — se omite en badge)
    //   tarjetas_presentacion → idtarjeta (sin FK directa a personas — se omite en badge)

    $sql = "
        SELECT
            p.idpersonas                                            AS id,
            p.rut,
            CONCAT(p.nombres, ' ', p.apellidos)                     AS nombre,
            p.telefono,
            p.email,
            /* ¿Es emprendedor? */
            EXISTS (
                SELECT 1 FROM emprendedores e
                WHERE e.personas_idpersonas = p.idpersonas
            )                                                       AS es_emprendedor,
            /* ¿Tiene contrato activo? */
            EXISTS (
                SELECT 1 FROM emprendedores e
                JOIN Contratos c ON c.emprendedores_idemprendedores = e.idemprendedores
                WHERE e.personas_idpersonas = p.idpersonas
                  AND c.estado = 'Activo'
            )                                                       AS contrato_activo,
            /* ¿Tiene crédito activo? */
            EXISTS (
                SELECT 1 FROM emprendedores e
                JOIN creditos cr ON cr.emprendedores_idemprendedores = e.idemprendedores
                WHERE e.personas_idpersonas = p.idpersonas
                  AND cr.estado = 'Activo'
            )                                                       AS credito_activo,
            /* ¿Tiene inscripciones a talleres? */
            EXISTS (
                SELECT 1 FROM emprendedores e
                JOIN inscripciones_talleres it ON it.emprendedores_idemprendedores = e.idemprendedores
                WHERE e.personas_idpersonas = p.idpersonas
            )                                                       AS talleres,
            /* ¿Tiene pagos registrados? */
            EXISTS (
                SELECT 1 FROM emprendedores e
                JOIN creditos cr ON cr.emprendedores_idemprendedores = e.idemprendedores
                JOIN cobranzas cb ON cb.creditos_idcreditos = cr.idcreditos
                WHERE e.personas_idpersonas = p.idpersonas
            )                                                       AS tiene_pagos
        FROM personas p
        WHERE p.estado = 1
          AND (
                p.rut                               LIKE :q
             OR p.nombres                           LIKE :q
             OR p.apellidos                         LIKE :q
             OR CONCAT(p.nombres,' ',p.apellidos)   LIKE :q
          )
        ORDER BY p.apellidos, p.nombres
        LIMIT 10
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':q' => $qLike]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $out = array_map(fn($r) => [
        'id'              => (int)  $r['id'],
        'rut'             =>        $r['rut'],
        'nombre'          =>        $r['nombre'],
        'telefono'        =>        $r['telefono'] ?? '',
        'email'           =>        $r['email'] ?? '',
        'es_emprendedor'  => (bool) $r['es_emprendedor'],
        'contrato_activo' => (bool) $r['contrato_activo'],
        'credito_activo'  => (bool) $r['credito_activo'],
        'talleres'        => (bool) $r['talleres'],
        'tiene_pagos'     => (bool) $r['tiene_pagos'],
    ], $rows);

    echo json_encode($out, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log('[SmartSearch] ' . $e->getMessage());

    // Detectar qué falló para dar mensaje útil
    $msg = $e->getMessage();
    preg_match("/Table '([^']+)' doesn't exist/i", $msg, $mT);
    preg_match("/Unknown column '([^']+)'/i",       $msg, $mC);
    $detalle = !empty($mT[1]) ? "Tabla no existe: {$mT[1]}"
             : (!empty($mC[1]) ? "Columna no existe: {$mC[1]}"
             : $msg);

    http_response_code(500);
    echo json_encode(['error' => 'db_error', 'detalle' => $detalle]);
}
