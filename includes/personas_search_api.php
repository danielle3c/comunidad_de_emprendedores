<?php
require_once 'includes/helpers.php';
header('Content-Type: application/json; charset=utf-8');

$pdo = getConnection();
$q = trim($_GET['q'] ?? '');

if (mb_strlen($q) < 2) {
  echo json_encode([]);
  exit;
}

$qLike = "%$q%";

/*
  Semáforos reales según tu estructura:
  personas -> emprendedores -> contratos/creditos/inscripciones_talleres
*/
$sql = "
SELECT
  p.idpersonas AS id,
  p.rut,
  CONCAT(p.nombres,' ',p.apellidos) AS nombre,
  /* existe emprendedor */
  EXISTS(SELECT 1 FROM emprendedores e WHERE e.personas_idpersonas = p.idpersonas) AS es_emprendedor,

  /* contrato activo (si existe emprendedor) */
  EXISTS(
    SELECT 1
    FROM emprendedores e
    JOIN Contratos c ON c.emprendedores_idemprendedores = e.idemprendedores
    WHERE e.personas_idpersonas = p.idpersonas
      AND c.estado = 'Activo'
  ) AS contrato_activo,

  /* crédito activo */
  EXISTS(
    SELECT 1
    FROM emprendedores e
    JOIN creditos cr ON cr.emprendedores_idemprendedores = e.idemprendedores
    WHERE e.personas_idpersonas = p.idpersonas
      AND cr.estado = 'Activo'
  ) AS credito_activo,

  /* talleres (inscripciones) */
  EXISTS(
    SELECT 1
    FROM emprendedores e
    JOIN inscripciones_talleres it ON it.emprendedores_idemprendedores = e.idemprendedores
    WHERE e.personas_idpersonas = p.idpersonas
  ) AS talleres

FROM personas p
WHERE p.estado = 1
  AND (
    p.rut LIKE :q
    OR p.nombres LIKE :q
    OR p.apellidos LIKE :q
    OR CONCAT(p.nombres,' ',p.apellidos) LIKE :q
  )
ORDER BY p.apellidos, p.nombres
LIMIT 10
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':q' => $qLike]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// convertir 0/1 a boolean
$out = array_map(function($r){
  return [
    'id' => (int)$r['id'],
    'rut' => $r['rut'],
    'nombre' => $r['nombre'],
    'contrato_activo' => (bool)$r['contrato_activo'],
    'credito_activo'  => (bool)$r['credito_activo'],
    'talleres'        => (bool)$r['talleres'],
  ];
}, $rows);

echo json_encode($out);
