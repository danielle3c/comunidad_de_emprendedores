<?php
if (session_status() === PHP_SESSION_NONE) session_start();

/* Login obligatorio */
if (!isset($_SESSION['usuario'])) {
  header("Location: login.php");
  exit;
}

function role(): string {
  return $_SESSION['rol'] ?? 'usuario';
}

function canCreate(): bool {
  return in_array(role(), ['admin','usuario'], true); // usuarios pueden crear
}

function canEdit(): bool {
  return in_array(role(), ['admin','usuario','moderador'], true); // usuarios pueden editar
}

function canDelete(): bool {
  return role() === 'admin'; // solo admin elimina
}

/* Bloqueo real según acción */
function guardAction(string $action): void {
  $action = strtolower($action);

  // acciones comunes
  $createActions = ['create','store','new','add'];
  $editActions   = ['edit','update','save'];
  $deleteActions = ['delete','destroy','remove'];

  if (in_array($action, $createActions, true) && !canCreate()) {
    header("Location: index.php?error=sin_permisos");
    exit;
  }

  if (in_array($action, $editActions, true) && !canEdit()) {
    header("Location: index.php?error=sin_permisos");
    exit;
  }

  if (in_array($action, $deleteActions, true) && !canDelete()) {
    header("Location: index.php?error=sin_permisos");
    exit;
  }
}
