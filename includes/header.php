<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Título por página (si no lo defines, usa uno por defecto)
$pageTitle = $pageTitle ?? 'Sistema Comunidad de Emprendedores';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Fuente -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg:#f4f6f9; --text:#0f172a; --muted:#64748b; --card:#ffffff;
      --radius:16px; --shadow:0 10px 25px rgba(0,0,0,0.08);
    }
    body{ font-family:'Inter', sans-serif; background:var(--bg); color:var(--text); }

    .topbar{
      background: linear-gradient(90deg, #0d47a1, #1565c0);
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      padding: 10px 16px;
    }
    .topbar .navbar-brand{ font-size: 1rem; letter-spacing:.3px; display:flex; align-items:center; gap:.5rem; }
    .search-box{ max-width: 520px; width: 100%; }
    .search-box input{ border-radius: 20px; padding-left: 14px; border: 1px solid rgba(255,255,255,0.25); }
    .search-box button{ border-radius: 20px; border: 1px solid rgba(255,255,255,0.25); }
    .dropdown-menu{ border-radius: 12px; }

    .container-page{ padding-top: 18px; padding-bottom: 24px; }

    .dashboard-card{
      border:none; border-radius:var(--radius); background:var(--card);
      transition:all .25s ease; box-shadow:0 6px 18px rgba(15,23,42,.06);
    }
    .dashboard-card:hover{ transform:translateY(-5px); box-shadow:var(--shadow); }
  </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark topbar">
  <div class="container-fluid">
    <a class="navbar-brand fw-semibold" href="index.php">
      <i class="bi bi-people-fill"></i>
      Comunidad de Emprendedores
    </a>

    <form class="d-flex mx-auto search-box my-2 my-lg-0" method="GET" action="personas.php">
      <input class="form-control me-2" type="search" name="q" placeholder="Buscar por RUT, nombre o apellido">
      <button class="btn btn-light" type="submit" title="Buscar">
        <i class="bi bi-search"></i>
      </button>
    </form>

    <div class="dropdown">
      <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="dropdown">
        <i class="bi bi-person-circle me-1"></i>
        <?= htmlspecialchars($_SESSION['usuario'] ?? 'Admin') ?>
      </a>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="usuarios.php"><i class="bi bi-person-gear me-2"></i>Usuarios</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container container-page">
