<?php
// includes/sidebar.php
$current = basename($_SERVER['PHP_SELF']);
function navActive(string $file, string $current): string {
    return $file === $current ? 'active' : '';
}
?>
<div class="sidebar d-flex flex-column">
  <div class="brand">
    <div class="title"><i class="bi bi-people-fill me-2"></i>Emprendedores</div>
    <div class="sub">Panel de administración</div>
  </div>

  <nav class="flex-grow-1 py-2">
    <div class="nav-section">Principal</div>
    <a href="index.php" class="nav-link <?= navActive('index.php', $current) ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <div class="nav-section">Personas</div>
    <a href="personas.php" class="nav-link <?= navActive('personas.php', $current) ?>">
      <i class="bi bi-person"></i> Personas
    </a>
    <a href="emprendedores.php" class="nav-link <?= navActive('emprendedores.php', $current) ?>">
      <i class="bi bi-briefcase"></i> Emprendedores
    </a>

    <div class="nav-section">Finanzas</div>
    <a href="contratos.php" class="nav-link <?= navActive('contratos.php', $current) ?>">
      <i class="bi bi-file-earmark-text"></i> Contratos
    </a>
    <a href="creditos.php" class="nav-link <?= navActive('creditos.php', $current) ?>">
      <i class="bi bi-credit-card"></i> Créditos
    </a>
    <a href="cobranzas.php" class="nav-link <?= navActive('cobranzas.php', $current) ?>">
      <i class="bi bi-cash-coin"></i> Cobranzas
    </a>

    <div class="nav-section">Actividades</div>
    <a href="talleres.php" class="nav-link <?= navActive('talleres.php', $current) ?>">
      <i class="bi bi-book"></i> Talleres
    </a>
    <a href="inscripciones_talleres.php" class="nav-link <?= navActive('inscripciones_talleres.php', $current) ?>">
      <i class="bi bi-journal-check"></i> Inscripciones
    </a>
    <a href="jornadas.php" class="nav-link <?= navActive('jornadas.php', $current) ?>">
      <i class="bi bi-calendar-event"></i> Jornadas
    </a>
    <a href="carritos.php" class="nav-link <?= navActive('carritos.php', $current) ?>">
      <i class="bi bi-cart3"></i> Carritos
    </a>

    <div class="nav-section">Otros</div>
    <a href="encuestas.php" class="nav-link <?= navActive('encuestas.php', $current) ?>">
      <i class="bi bi-clipboard-data"></i> Encuestas
    </a>
    <a href="documentos.php" class="nav-link <?= navActive('documentos.php', $current) ?>">
      <i class="bi bi-folder"></i> Documentos
    </a>
    <a href="tarjetas_presentacion.php" class="nav-link <?= navActive('tarjetas_presentacion.php', $current) ?>">
      <i class="bi bi-person-vcard"></i> Tarjetas
    </a>
    <a href="usuarios.php" class="nav-link <?= navActive('usuarios.php', $current) ?>">
      <i class="bi bi-people"></i> Usuarios
    </a>
    <a href="auditoria.php" class="nav-link <?= navActive('auditoria.php', $current) ?>">
      <i class="bi bi-shield-check"></i> Auditoría
    </a>
    <a href="configuraciones.php" class="nav-link <?= navActive('configuraciones.php', $current) ?>">
      <i class="bi bi-gear"></i> Configuración
    </a>
  </nav>

  <div class="p-3" style="border-top:1px solid rgba(255,255,255,.10)">
    <div class="d-flex align-items-center gap-2">
      <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width:32px;height:32px;min-width:32px">
        <i class="bi bi-person-fill text-dark" style="font-size:.9rem"></i>
      </div>
      <div class="flex-grow-1 overflow-hidden">
        <div style="color:#fff;font-size:.82rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
          <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Usuario', ENT_QUOTES, 'UTF-8') ?>
        </div>
        <div style="color:rgba(255,255,255,.5);font-size:.72rem">
          <?= htmlspecialchars(ucfirst($_SESSION['user']['rol'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        </div>
      </div>
      <a href="logout.php" title="Cerrar sesión" style="color:rgba(255,255,255,.5)" class="d-flex">
        <i class="bi bi-box-arrow-right"></i>
      </a>
    </div>
  </div>
</div>