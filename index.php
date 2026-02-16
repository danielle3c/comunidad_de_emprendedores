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
      margin-top: 4px;
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

    /* ===== Secciones inferiores ===== */
    .panel-box{
      border: none;
      border-radius: var(--radius);
      background: var(--card);
      box-shadow: 0 6px 18px rgba(15,23,42,.06);
    }

    /* ===== Layout con Sidebar ===== */
    .layout{
      display:flex;
      min-height: calc(100vh - 60px);
    }

    .sidebar{
      width: 280px;
      background: #0b1220;
      color: #e5e7eb;
      padding: 14px 12px;
      border-right: 1px solid rgba(255,255,255,0.06);
    }
    .sidebar-head{
      padding: 10px 10px 14px;
      border-bottom: 1px solid rgba(255,255,255,0.08);
      margin-bottom: 10px;
    }
    .sidebar-brand{ display:flex; align-items:center; gap:12px; }
    .sidebar-badge{
      width:38px; height:38px;
      border-radius: 12px;
      display:flex; align-items:center; justify-content:center;
      background: rgba(59,130,246,0.18);
      outline: 1px solid rgba(59,130,246,0.22);
    }
    .sidebar-title{ font-weight: 800; font-size: 1rem; }
    .sidebar-subtitle{ font-size: .8rem; color: rgba(229,231,235,0.65); }

    .sidebar-section{
      margin: 14px 10px 6px;
      font-size: .75rem;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: rgba(229,231,235,0.55);
    }

    .sidebar-nav{ display:flex; flex-direction:column; gap:4px; }
    .sidebar-link{
      display:flex; align-items:center; gap:10px;
      padding: 10px 10px;
      border-radius: 12px;
      color: rgba(229,231,235,0.92);
      text-decoration:none;
      transition: all .15s ease;
    }
    .sidebar-link i{ font-size: 1.1rem; width: 22px; text-align:center; opacity:.95; }
    .sidebar-link:hover{ background: rgba(255,255,255,0.08); color:#fff; }
    .sidebar-link.active{
      background: rgba(59,130,246,0.18);
      outline: 1px solid rgba(59,130,246,0.25);
      color:#fff;
    }

    .content{
      flex:1;
      padding: 18px 18px 28px;
    }

    /* Responsive */
    @media (max-width: 992px){
      .sidebar{ display:none; } /* se oculta fijo */
      .content{ padding: 14px; }
      .search-box{ max-width: 100%; }
    }

    /* Sidebar dentro del offcanvas */
    .offcanvas .sidebar{
      display:block !important;
      width:100%;
      border-right:none;
    }
  </style>
</head>

<body>

  <!-- ===== TOPBAR ===== -->
  <nav class="navbar navbar-expand-lg navbar-dark topbar">
    <div class="container-fluid">

      <!-- Botón menú (solo móvil) -->
      <button class="btn btn-outline-light d-lg-none me-2" type="button"
              data-bs-toggle="offcanvas" data-bs-target="#mobileMenu"
              aria-controls="mobileMenu" aria-label="Abrir menú">
        <i class="bi bi-list"></i>
      </button>

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

  <!-- ===== OFFCANVAS (MENÚ MÓVIL) ===== -->
  <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="mobileMenuLabel">Menú</h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body p-0">
      <!-- Sidebar dentro del offcanvas -->
      <aside class="sidebar">
        <div class="sidebar-head">
          <div class="sidebar-brand">
            <div class="sidebar-badge"><i class="bi bi-building"></i></div>
            <div>
              <div class="sidebar-title">Menú</div>
              <div class="sidebar-subtitle">Gestión del sistema</div>
            </div>
          </div>
        </div>

        <nav class="sidebar-nav">
          <a class="sidebar-link active" href="index.php"><i class="bi bi-speedometer2"></i><span>Panel</span></a>

          <div class="sidebar-section">Registros</div>
          <a class="sidebar-link" href="personas.php"><i class="bi bi-people"></i><span>Personas</span></a>
          <a class="sidebar-link" href="emprendedores.php"><i class="bi bi-briefcase"></i><span>Emprendedores</span></a>

          <div class="sidebar-section">Finanzas</div>
          <a class="sidebar-link" href="contratos.php"><i class="bi bi-file-earmark-text"></i><span>Contratos</span></a>
          <a class="sidebar-link" href="creditos.php"><i class="bi bi-cash-stack"></i><span>Créditos</span></a>
          <a class="sidebar-link" href="cobranzas.php"><i class="bi bi-receipt"></i><span>Cobranzas</span></a>

          <div class="sidebar-section">Actividades</div>
          <a class="sidebar-link" href="talleres.php"><i class="bi bi-calendar-event"></i><span>Talleres</span></a>
          <a class="sidebar-link" href="inscripciones_talleres.php"><i class="bi bi-person-check"></i><span>Inscripciones</span></a>
          <a class="sidebar-link" href="jornadas.php"><i class="bi bi-geo-alt"></i><span>Jornadas</span></a>
          <a class="sidebar-link" href="carritos.php"><i class="bi bi-shop"></i><span>Carritos</span></a>

          <div class="sidebar-section">Herramientas</div>
          <a class="sidebar-link" href="encuestas.php"><i class="bi bi-ui-checks"></i><span>Encuestas</span></a>
          <a class="sidebar-link" href="documentos.php"><i class="bi bi-folder2-open"></i><span>Documentos</span></a>
          <a class="sidebar-link" href="auditoria.php"><i class="bi bi-shield-check"></i><span>Auditoría</span></a>
          <a class="sidebar-link" href="configuraciones.php"><i class="bi bi-gear"></i><span>Configuración</span></a>
          <a class="sidebar-link" href="usuarios.php"><i class="bi bi-person-gear"></i><span>Usuarios</span></a>
        </nav>
      </aside>
    </div>
  </div>

  <!-- ===== LAYOUT (SIDEBAR PC + CONTENT) ===== -->
  <div class="layout">

    <!-- Sidebar PC -->
    <aside class="sidebar">
      <div class="sidebar-head">
        <div class="sidebar-brand">
          <div class="sidebar-badge"><i class="bi bi-building"></i></div>
          <div>
            <div class="sidebar-title">Menú</div>
            <div class="sidebar-subtitle">Gestión del sistema</div>
          </div>
        </div>
      </div>

      <nav class="sidebar-nav">
        <a class="sidebar-link active" href="index.php"><i class="bi bi-speedometer2"></i><span>Panel</span></a>

        <div class="sidebar-section">Registros</div>
        <a class="sidebar-link" href="personas.php"><i class="bi bi-people"></i><span>Personas</span></a>
        <a class="sidebar-link" href="emprendedores.php"><i class="bi bi-briefcase"></i><span>Emprendedores</span></a>

        <div class="sidebar-section">Finanzas</div>
        <a class="sidebar-link" href="contratos.php"><i class="bi bi-file-earmark-text"></i><span>Contratos</span></a>
        <a class="sidebar-link" href="creditos.php"><i class="bi bi-cash-stack"></i><span>Créditos</span></a>
        <a class="sidebar-link" href="cobranzas.php"><i class="bi bi-receipt"></i><span>Cobranzas</span></a>

        <div class="sidebar-section">Actividades</div>
        <a class="sidebar-link" href="talleres.php"><i class="bi bi-calendar-event"></i><span>Talleres</span></a>
        <a class="sidebar-link" href="inscripciones_talleres.php"><i class="bi bi-person-check"></i><span>Inscripciones</span></a>
        <a class="sidebar-link" href="jornadas.php"><i class="bi bi-geo-alt"></i><span>Jornadas</span></a>
        <a class="sidebar-link" href="carritos.php"><i class="bi bi-shop"></i><span>Carritos</span></a>

        <div class="sidebar-section">Herramientas</div>
        <a class="sidebar-link" href="encuestas.php"><i class="bi bi-ui-checks"></i><span>Encuestas</span></a>
        <a class="sidebar-link" href="documentos.php"><i class="bi bi-folder2-open"></i><span>Documentos</span></a>
        <a class="sidebar-link" href="auditoria.php"><i class="bi bi-shield-check"></i><span>Auditoría</span></a>
        <a class="sidebar-link" href="configuraciones.php"><i class="bi bi-gear"></i><span>Configuración</span></a>
        <a class="sidebar-link" href="usuarios.php"><i class="bi bi-person-gear"></i><span>Usuarios</span></a>
      </nav>
    </aside>

    <!-- Contenido -->
    <main class="content">
      <div class="container-fluid">

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

        <!-- Sección inferior -->
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
    </main>

  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
