<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel Principal | Comunidad de Emprendedores</title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Fuente -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg:#f4f6f9;
      --text:#0f172a;
      --muted:#64748b;
      --card:#ffffff;
      --radius:16px;
      --shadow:0 10px 25px rgba(0,0,0,0.08);
    }

    body{
      font-family:'Inter', sans-serif;
      background:var(--bg);
      color:var(--text);
    }

    /* ===== Barra superior (Topbar) ===== */
    .topbar{
      background: linear-gradient(90deg, #0d47a1, #1565c0);
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      padding: 10px 16px;
    }
    .topbar .navbar-brand{
      font-size: 1rem;
      letter-spacing: .3px;
      display:flex;
      align-items:center;
      gap:.5rem;
    }
    .search-box{
      max-width: 520px;
      width: 100%;
    }
    .search-box input{
      border-radius: 20px;
      padding-left: 14px;
      border: 1px solid rgba(255,255,255,0.25);
    }
    .search-box button{
      border-radius: 20px;
      border: 1px solid rgba(255,255,255,0.25);
    }
    .dropdown-menu{
      border-radius: 12px;
    }

    /* ===== Encabezado del panel ===== */
    .page-title{
      margin-top: 20px;
      margin-bottom: 10px;
    }

    /* ===== Tarjetas dashboard ===== */
    .dashboard-card{
      border: none;
      border-radius: var(--radius);
      background: var(--card);
      transition: all .25s ease;
      box-shadow: 0 6px 18px rgba(15,23,42,.06);
    }
    .dashboard-card:hover{
      transform: translateY(-5px);
      box-shadow: var(--shadow);
    }
    .dashboard-card h6{
      font-size: .85rem;
      color: var(--muted);
      margin-bottom: 6px;
    }
    .dashboard-card h3{
      font-size: 1.7rem;
      margin: 0;
      font-weight: 800;
    }
    .dashboard-icon{
      font-size: 2.2rem;
    }

    /* ===== Secciones inferiores (listas/tabla) ===== */
    .panel-box{
      border: none;
      border-radius: var(--radius);
      background: var(--card);
      box-shadow: 0 6px 18px rgba(15,23,42,.06);
    }
  </style>
</head>

<body>

  <!-- ===== TOPBAR ===== -->
  <nav class="navbar navbar-expand-lg navbar-dark topbar">
    <div class="container-fluid">

      <!-- Logo + Nombre -->
      <a class="navbar-brand fw-semibold" href="index.php">
        <i class="bi bi-people-fill"></i>
        Comunidad de Emprendedores
      </a>

      <!-- Buscador -->
      <form class="d-flex mx-auto search-box my-2 my-lg-0" method="GET" action="personas.php">
        <input class="form-control me-2" type="search" name="q"
          placeholder="Buscar por RUT, nombre o apellido">
        <button class="btn btn-light" type="submit" title="Buscar">
          <i class="bi bi-search"></i>
        </button>
      </form>

      <!-- Usuario -->
      <div class="dropdown">
        <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="dropdown">
          <i class="bi bi-person-circle me-1"></i> Admin
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="usuarios.php"><i class="bi bi-person-gear me-2"></i>Usuarios</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a></li>
        </ul>
      </div>

    </div>
  </nav>

  <!-- ===== CONTENIDO ===== -->
  <div class="container py-4">

    <!-- Encabezado -->
    <div class="page-title">
      <h4 class="fw-bold mb-1">Panel de Gestión Comunitaria</h4>
      <p class="text-muted mb-0">Resumen general del estado del sistema</p>
    </div>

    <!-- Tarjetas principales -->
    <div class="row g-4 mt-2">

      <div class="col-12 col-md-6 col-lg-3">
        <div class="card dashboard-card">
          <div class="card-body text-center py-4">
            <i class="bi bi-people dashboard-icon text-primary"></i>
            <h6 class="mt-2">Personas</h6>
            <h3>150</h3>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <div class="card dashboard-card">
          <div class="card-body text-center py-4">
            <i class="bi bi-briefcase dashboard-icon text-success"></i>
            <h6 class="mt-2">Emprendedores Activos</h6>
            <h3>85</h3>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <div class="card dashboard-card">
          <div class="card-body text-center py-4">
            <i class="bi bi-cash-stack dashboard-icon text-warning"></i>
            <h6 class="mt-2">Créditos Activos</h6>
            <h3>40</h3>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-lg-3">
        <div class="card dashboard-card">
          <div class="card-body text-center py-4">
            <i class="bi bi-calendar-event dashboard-icon text-danger"></i>
            <h6 class="mt-2">Próximos Talleres</h6>
            <h3>6</h3>
          </div>
        </div>
      </div>

    </div>

    <!-- Sección inferior (ejemplo) -->
    <div class="row g-4 mt-3">
      <div class="col-12 col-lg-6">
        <div class="panel-box p-3">
          <h6 class="fw-bold mb-3"><i class="bi bi-graph-up-arrow me-2"></i>Recaudación mensual</h6>
          <p class="text-muted mb-0">Aquí puedes mostrar el total del mes y/o un gráfico después.</p>
        </div>
      </div>

      <div class="col-12 col-lg-6">
        <div class="panel-box p-3">
          <h6 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Últimos pagos registrados</h6>
          <p class="text-muted mb-0">Aquí puedes listar los últimos pagos (tabla o lista).</p>
        </div>
      </div>
    </div>

  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
