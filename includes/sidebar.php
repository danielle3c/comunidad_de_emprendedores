<?php
// Define $activePage en cada página: 'index', 'personas', 'emprendedores', etc.
$activePage = $activePage ?? '';
function activeClass($key, $activePage){
  return $key === $activePage ? 'active' : '';
}
?>

<aside class="sidebar">
  <div class="sidebar-head">
    <div class="sidebar-title">Menú</div>
    <div class="sidebar-subtitle">Gestión del sistema</div>
  </div>

  <nav class="sidebar-nav">

    <a class="sidebar-link <?= activeClass('index',$activePage) ?>" href="index.php">
      <i class="bi bi-speedometer2"></i><span>Panel</span>
    </a>

    <div class="sidebar-section">Registros</div>

    <a class="sidebar-link <?= activeClass('personas',$activePage) ?>" href="personas.php">
      <i class="bi bi-people"></i><span>Personas</span>
    </a>

    <a class="sidebar-link <?= activeClass('emprendedores',$activePage) ?>" href="emprendedores.php">
      <i class="bi bi-briefcase"></i><span>Emprendedores</span>
    </a>

    <div class="sidebar-section">Finanzas</div>

    <a class="sidebar-link <?= activeClass('contratos',$activePage) ?>" href="contratos.php">
      <i class="bi bi-file-earmark-text"></i><span>Contratos</span>
    </a>

    <a class="sidebar-link <?= activeClass('creditos',$activePage) ?>" href="creditos.php">
      <i class="bi bi-cash-stack"></i><span>Créditos</span>
    </a>

    <a class="sidebar-link <?= activeClass('cobranzas',$activePage) ?>" href="cobranzas.php">
      <i class="bi bi-receipt"></i><span>Cobranzas</span>
    </a>

    <div class="sidebar-section">Actividades</div>

    <a class="sidebar-link <?= activeClass('talleres',$activePage) ?>" href="talleres.php">
      <i class="bi bi-calendar-event"></i><span>Talleres</span>
    </a>

    <a class="sidebar-link <?= activeClass('inscripciones',$activePage) ?>" href="inscripciones_talleres.php">
      <i class="bi bi-person-check"></i><span>Inscripciones</span>
    </a>

    <a class="sidebar-link <?= activeClass('jornadas',$activePage) ?>" href="jornadas.php">
      <i class="bi bi-geo-alt"></i><span>Jornadas</span>
    </a>

    <a class="sidebar-link <?= activeClass('carritos',$activePage) ?>" href="carritos.php">
      <i class="bi bi-shop"></i><span>Carritos</span>
    </a>

    <div class="sidebar-section">Herramientas</div>

    <a class="sidebar-link <?= activeClass('encuestas',$activePage) ?>" href="encuestas.php">
      <i class="bi bi-ui-checks"></i><span>Encuestas</span>
    </a>

    <a class="sidebar-link <?= activeClass('documentos',$activePage) ?>" href="documentos.php">
      <i class="bi bi-folder2-open"></i><span>Documentos</span>
    </a>

    <a class="sidebar-link <?= activeClass('auditoria',$activePage) ?>" href="auditoria.php">
      <i class="bi bi-shield-check"></i><span>Auditoría</span>
    </a>

    <a class="sidebar-link <?= activeClass('config',$activePage) ?>" href="configuraciones.php">
      <i class="bi bi-gear"></i><span>Configuración</span>
    </a>

    <a class="sidebar-link <?= activeClass('usuarios',$activePage) ?>" href="usuarios.php">
      <i class="bi bi-person-gear"></i><span>Usuarios</span>
    </a>

  </nav>
</aside>
