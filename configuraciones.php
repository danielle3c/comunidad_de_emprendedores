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

try {
  $pdo = getConnection();

  $action = $_POST['action'] ?? '';
  $idConf  = 1; // configuración global

  if ($action === 'theme') {
    $theme = ($_POST['value'] ?? 'dark') === 'light' ? 'light' : 'dark';
    $stmt = $pdo->prepare("UPDATE configuraciones SET modo_tema = ? WHERE id = ?");
    $stmt->execute([$theme, $idConf]);
    echo json_encode(['ok' => true, 'modo_tema' => $theme], JSON_UNESCAPED_UNICODE);
    exit;
  }

  if ($action === 'lang') {
    $lang = preg_replace('/[^a-z\-]/i', '', $_POST['value'] ?? 'es');
    if ($lang === '') $lang = 'es';
    $stmt = $pdo->prepare("UPDATE configuraciones SET idioma = ? WHERE id = ?");
    $stmt->execute([$lang, $idConf]);
    echo json_encode(['ok' => true, 'idioma' => $lang], JSON_UNESCAPED_UNICODE);
    exit;
  }

  if ($action === 'bg') {
    $bg = (int)($_POST['value'] ?? 1) ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE configuraciones SET fondo_activo = ? WHERE id = ?");
    $stmt->execute([$bg, $idConf]);
    echo json_encode(['ok' => true, 'fondo_activo' => $bg], JSON_UNESCAPED_UNICODE);
    exit;
  }

  if ($action === 'color') {
    $h = (int)($_POST['h'] ?? 107);
    $s = (int)($_POST['s'] ?? 62);
    $l = (int)($_POST['l'] ?? 43);

    // límites razonables
    $h = max(0, min(360, $h));
    $s = max(0, min(100, $s));
    $l = max(0, min(100, $l));

    $stmt = $pdo->prepare("UPDATE configuraciones SET pick_h=?, pick_s=?, pick_l=? WHERE id=?");
    $stmt->execute([$h, $s, $l, $idConf]);

    echo json_encode(['ok'=>true, 'pick_h'=>$h, 'pick_s'=>$s, 'pick_l'=>$l], JSON_UNESCAPED_UNICODE);
    exit;
  }

  http_response_code(400);
  echo json_encode(['error' => 'bad_request'], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
  http_response_code(500);
  echo json_encode(['error'=>'db_error','detalle'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}