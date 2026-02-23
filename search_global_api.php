<?php
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/helpers.php';

secure_session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user']['id'])) {
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
  $pdo = getConnection();
  $qLike = '%' . $q . '%';

  $out = [];

  // PERSONAS
  $stmt = $pdo->prepare("
    SELECT idpersonas AS id, rut,
           CONCAT(nombres,' ',apellidos) AS titulo
    FROM personas
    WHERE estado = 1
      AND (rut LIKE ? OR nombres LIKE ? OR apellidos LIKE ? OR email LIKE ?)
    ORDER BY titulo
    LIMIT 10
  ");
  $stmt->execute([$qLike, $qLike, $qLike, $qLike]);
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $p) {
    $out[] = [
      'tipo'   => 'persona',
      'titulo' => $p['titulo'],
      'sub'    => 'RUT: ' . ($p['rut'] ?? ''),
      'url'    => 'persona_detalle.php?id=' . (int)$p['id'],
    ];
  }

  // CARRITOS
  $stmt = $pdo->prepare("
    SELECT idcarritos AS id, nombre_carrito, nombre_responsable, telefono_responsable
    FROM carritos
    WHERE estado = 1
      AND (nombre_carrito LIKE ? OR nombre_responsable LIKE ? OR telefono_responsable LIKE ?)
    ORDER BY idcarritos DESC
    LIMIT 10
  ");
  $stmt->execute([$qLike, $qLike, $qLike]);
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
    $out[] = [
      'tipo'   => 'carrito',
      'titulo' => ($c['nombre_carrito'] ?? '(Sin nombre)'),
      'sub'    => 'Resp.: ' . ($c['nombre_responsable'] ?? '-') . ' | Tel: ' . ($c['telefono_responsable'] ?? '-'),
      'url'    => 'carritos.php?action=edit&id=' . (int)$c['id'],
    ];
  }

  // TARJETAS
  $stmt = $pdo->prepare("
    SELECT idtarjeta AS id, nombre, cantidad, valor
    FROM tarjetas_presentacion
    WHERE nombre LIKE ?
    ORDER BY idtarjeta DESC
    LIMIT 10
  ");
  $stmt->execute([$qLike]);
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $t) {
    $out[] = [
      'tipo'   => 'tarjeta',
      'titulo' => ($t['nombre'] ?? '(Sin nombre)'),
      'sub'    => 'Cant: ' . ($t['cantidad'] ?? '-') . ' | Valor: ' . ($t['valor'] ?? '-'),
      'url'    => 'tarjetas_presentacion.php?action=edit&id=' . (int)$t['id'],
    ];
  }

  echo json_encode(array_slice($out, 0, 20), JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  error_log('[search_global_api] ' . $e->getMessage());
  http_response_code(500);
  echo json_encode(['error' => 'server_error', 'detalle' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
