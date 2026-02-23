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

    // Una sola query, sin subqueries, sin CONCAT en WHERE
    // Buscar por campos separados y unir con UNION para evitar HY093
    $stmt = $pdo->prepare(
        "SELECT idpersonas AS id, rut,
                CONCAT(nombres, ' ', apellidos) AS nombre,
                telefono, email
         FROM   personas
         WHERE  estado = 1 AND rut LIKE ?
         UNION
         SELECT idpersonas, rut,
                CONCAT(nombres, ' ', apellidos),
                telefono, email
         FROM   personas
         WHERE  estado = 1 AND nombres LIKE ?
         UNION
         SELECT idpersonas, rut,
                CONCAT(nombres, ' ', apellidos),
                telefono, email
         FROM   personas
         WHERE  estado = 1 AND apellidos LIKE ?
         ORDER  BY nombre
         LIMIT  10"
    );
    $stmt->execute([$qLike, $qLike, $qLike]);
    $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($personas)) {
        echo json_encode([]);
        exit;
    }

    $out = [];
    foreach ($personas as $p) {
        $pid = (int)$p['id'];

        // ¿Emprendedor?
        $s = $pdo->prepare("SELECT idemprendedores FROM emprendedores WHERE personas_idpersonas = ? LIMIT 1");
        $s->execute([$pid]);
        $emp = $s->fetch(PDO::FETCH_ASSOC);
        $eid = $emp ? (int)$emp['idemprendedores'] : null;

        $contrato = false;
        $credito  = false;
        $talleres = false;
        $pagos    = false;

        if ($eid) {
            $s = $pdo->prepare("SELECT 1 FROM Contratos WHERE emprendedores_idemprendedores = ? AND estado = 'Activo' LIMIT 1");
            $s->execute([$eid]);
            $contrato = (bool)$s->fetchColumn();

            $s = $pdo->prepare("SELECT idcreditos FROM creditos WHERE emprendedores_idemprendedores = ? AND estado = 'Activo' LIMIT 1");
            $s->execute([$eid]);
            $cred = $s->fetch(PDO::FETCH_ASSOC);
            $credito = (bool)$cred;

            $s = $pdo->prepare("SELECT 1 FROM inscripciones_talleres WHERE emprendedores_idemprendedores = ? LIMIT 1");
            $s->execute([$eid]);
            $talleres = (bool)$s->fetchColumn();

            if ($cred) {
                $s = $pdo->prepare("SELECT 1 FROM cobranzas WHERE creditos_idcreditos = ? LIMIT 1");
                $s->execute([$cred['idcreditos']]);
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
    http_response_code(500);
    // Devolver el error COMPLETO para poder diagnosticar
    echo json_encode([
        'error'   => 'db_error',
        'detalle' => $e->getMessage(),
        'codigo'  => $e->getCode(),
        'linea'   => $e->getLine(),
        'archivo' => basename($e->getFile()),
    ]);
}