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

    // PASO 1: buscar personas — SQL simple sin subqueries, cero problemas de parámetros
    $stmt = $pdo->prepare("
        SELECT idpersonas AS id, rut,
               CONCAT(nombres, ' ', apellidos) AS nombre,
               telefono, email
        FROM   personas
        WHERE  estado = 1
          AND  (rut     LIKE ?
            OR  nombres LIKE ?
            OR  apellidos LIKE ?
            OR  CONCAT(nombres,' ',apellidos) LIKE ?)
        ORDER BY apellidos, nombres
        LIMIT 10
    ");
    $stmt->execute([$qLike, $qLike, $qLike, $qLike]);
    $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($personas)) {
        echo json_encode([]);
        exit;
    }

    // PASO 2: para cada persona, buscar badges con queries simples separadas
    // Así evitamos TODOS los problemas de parámetros en subqueries
    $out = [];
    foreach ($personas as $p) {
        $pid = (int)$p['id'];

        // ¿Es emprendedor?
        $s = $pdo->prepare("SELECT idemprendedores FROM emprendedores WHERE personas_idpersonas = ? LIMIT 1");
        $s->execute([$pid]);
        $emp = $s->fetch();
        $eid = $emp ? (int)$emp['idemprendedores'] : null;

        $contrato = false;
        $credito  = false;
        $talleres = false;
        $pagos    = false;

        if ($eid) {
            // ¿Contrato activo?
            $s = $pdo->prepare("SELECT 1 FROM Contratos WHERE emprendedores_idemprendedores = ? AND estado = 'Activo' LIMIT 1");
            $s->execute([$eid]);
            $contrato = (bool)$s->fetchColumn();

            // ¿Crédito activo?
            $s = $pdo->prepare("SELECT idcreditos FROM creditos WHERE emprendedores_idemprendedores = ? AND estado = 'Activo' LIMIT 1");
            $s->execute([$eid]);
            $cred_row = $s->fetch();
            $credito  = (bool)$cred_row;

            // ¿Talleres?
            $s = $pdo->prepare("SELECT 1 FROM inscripciones_talleres WHERE emprendedores_idemprendedores = ? LIMIT 1");
            $s->execute([$eid]);
            $talleres = (bool)$s->fetchColumn();

            // ¿Pagos? — solo si tiene crédito
            if ($cred_row) {
                $s = $pdo->prepare("SELECT 1 FROM cobranzas WHERE creditos_idcreditos = ? LIMIT 1");
                $s->execute([$cred_row['idcreditos']]);
                $pagos = (bool)$s->fetchColumn();
            }
        }

        $out[] = [
            'id'              => $pid,
            'rut'             => $p['rut'],
            'nombre'          => $p['nombre'],
            'telefono'        => $p['telefono'] ?? '',
            'email'           => $p['email']    ?? '',
            'es_emprendedor'  => (bool)$eid,
            'contrato_activo' => $contrato,
            'credito_activo'  => $credito,
            'talleres'        => $talleres,
            'tiene_pagos'     => $pagos,
        ];
    }

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