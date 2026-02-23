<?php
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/helpers.php';

secure_session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user']['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthenticated'], JSON_UNESCAPED_UNICODE);
    exit;
}

$q = trim($_GET['q'] ?? '');
if (mb_strlen($q) < 2) {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo   = getConnection();
    $qLike = '%' . $q . '%';

    /* =========================
       1) PERSONAS (máx 10)
    ========================= */
    $stmt = $pdo->prepare(
        "SELECT
            p.idpersonas AS id,
            p.rut,
            CONCAT(p.nombres, ' ', p.apellidos) AS nombre,
            p.telefono,
            p.email
         FROM personas p
         WHERE p.estado = 1
           AND (
                p.rut LIKE ?
             OR p.nombres LIKE ?
             OR p.apellidos LIKE ?
           )
         ORDER BY nombre
         LIMIT 10"
    );
    $stmt->execute([$qLike, $qLike, $qLike]);
    $personas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$personas) {
        echo json_encode([], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pids = array_map(fn($p) => (int)$p['id'], $personas);

    /* =========================
       2) persona -> emprendedor
    ========================= */
    $inP = implode(',', array_fill(0, count($pids), '?'));
    $stmt = $pdo->prepare(
        "SELECT personas_idpersonas AS pid, idemprendedores AS eid
         FROM emprendedores
         WHERE personas_idpersonas IN ($inP)
         LIMIT 1000"
    );
    $stmt->execute($pids);

    $empByPid = []; // pid => eid
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $empByPid[(int)$row['pid']] = (int)$row['eid'];
    }

    $eids = array_values($empByPid);

    // Flags por emprendedor
    $hasContrato = [];
    $hasCredito  = [];
    $hasTalleres = [];
    $hasPagos    = [];

    $creditosPorEid = []; // eid => [idcreditos...]

    if ($eids) {
        $inE = implode(',', array_fill(0, count($eids), '?'));

        /* =========================
           3) CONTRATOS activos
           OJO: tu tabla se llama "Contratos" (C mayúscula)
        ========================= */
        $stmt = $pdo->prepare(
            "SELECT emprendedores_idemprendedores AS eid
             FROM Contratos
             WHERE emprendedores_idemprendedores IN ($inE)
               AND estado = 'Activo'
             GROUP BY emprendedores_idemprendedores"
        );
        $stmt->execute($eids);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $hasContrato[(int)$r['eid']] = true;
        }

        /* =========================
           4) CREDITOS activos
        ========================= */
        $stmt = $pdo->prepare(
            "SELECT emprendedores_idemprendedores AS eid, idcreditos
             FROM creditos
             WHERE emprendedores_idemprendedores IN ($inE)
               AND estado = 'Activo'"
        );
        $stmt->execute($eids);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $eid = (int)$r['eid'];
            $cid = (int)$r['idcreditos'];
            $hasCredito[$eid] = true;
            $creditosPorEid[$eid][] = $cid;
        }

        /* =========================
           5) TALLERES (si tiene inscripciones)
        ========================= */
        $stmt = $pdo->prepare(
            "SELECT emprendedores_idemprendedores AS eid
             FROM inscripciones_talleres
             WHERE emprendedores_idemprendedores IN ($inE)
             GROUP BY emprendedores_idemprendedores"
        );
        $stmt->execute($eids);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $hasTalleres[(int)$r['eid']] = true;
        }

        /* =========================
           6) PAGOS (cobranzas) para créditos activos
        ========================= */
        $allCreds = [];
        foreach ($creditosPorEid as $arr) {
            foreach ($arr as $cid) $allCreds[] = $cid;
        }

        if ($allCreds) {
            $inC = implode(',', array_fill(0, count($allCreds), '?'));
            $stmt = $pdo->prepare(
                "SELECT DISTINCT creditos_idcreditos AS cid
                 FROM cobranzas
                 WHERE creditos_idcreditos IN ($inC)"
            );
            $stmt->execute($allCreds);
            $credConPago = array_map(fn($r) => (int)$r['cid'], $stmt->fetchAll(PDO::FETCH_ASSOC));

            $setCredConPago = array_flip($credConPago);
            foreach ($creditosPorEid as $eid => $cids) {
                foreach ($cids as $cid) {
                    if (isset($setCredConPago[$cid])) {
                        $hasPagos[(int)$eid] = true;
                        break;
                    }
                }
            }
        }
    }

    /* =========================
       7) SALIDA FINAL
    ========================= */
    $out = [];
    foreach ($personas as $p) {
        $pid = (int)$p['id'];
        $eid = $empByPid[$pid] ?? null;

        $out[] = [
            'id'              => $pid,
            'rut'             => $p['rut'],
            'nombre'          => $p['nombre'],
            'telefono'        => $p['telefono'] ?? '',
            'email'           => $p['email'] ?? '',
            'es_emprendedor'  => (bool)$eid,
            'contrato_activo' => $eid ? !empty($hasContrato[$eid]) : false,
            'credito_activo'  => $eid ? !empty($hasCredito[$eid])  : false,
            'talleres'        => $eid ? !empty($hasTalleres[$eid]) : false,
            'tiene_pagos'     => $eid ? !empty($hasPagos[$eid])    : false,
        ];
    }

    echo json_encode($out, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log('[SmartSearch] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'error'   => 'db_error',
        'detalle' => $e->getMessage(),
        'codigo'  => $e->getCode(),
        'linea'   => $e->getLine(),
        'archivo' => basename($e->getFile()),
    ], JSON_UNESCAPED_UNICODE);
}