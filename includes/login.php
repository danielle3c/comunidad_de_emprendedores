<?php
session_start();
if (isset($_SESSION['user'])) {
  header('Location: index.php');
  exit;
}
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar sesión | Comunidad de Emprendedores</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --bg1:#0f172a;
      --bg2:#111c33;
      --card:#ffffff;
      --muted:#64748b;
      --border:#e7eef7;
      --shadow: 0 18px 45px rgba(15,23,42,.20);
      --radius: 18px;
    }
    body{
      min-height:100vh;
      background: radial-gradient(1200px 600px at 20% 10%, rgba(59,130,246,.25), transparent 60%),
                  radial-gradient(900px 500px at 90% 40%, rgba(34,197,94,.18), transparent 55%),
                  linear-gradient(135deg, var(--bg1), var(--bg2));
      display:flex;
      align-items:center;
      justify-content:center;
      padding: 24px;
    }
    .auth-wrap{ width:100%; max-width: 980px; }
    .auth-card{
      background: rgba(255,255,255,.92);
      border: 1px solid rgba(231,238,247,.9);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      overflow:hidden;
      backdrop-filter: blur(8px);
    }
    .left{
      background: linear-gradient(135deg, #0f172a, #1e293b);
      color:#fff;
      padding: 38px;
      height:100%;
    }
    .badge-soft{
      display:inline-flex; gap:8px; align-items:center;
      padding: 8px 12px;
      border-radius: 999px;
      background: rgba(255,255,255,.12);
      border: 1px solid rgba(255,255,255,.18);
      font-size: .9rem;
    }
    .brand{
      font-weight: 800;
      letter-spacing: .2px;
      line-height:1.1;
    }
    .brand small{ display:block; font-weight:600; opacity:.85; margin-top:6px;}
    .right{ padding: 38px; }
    .form-label{ font-weight: 600; }
    .input-group-text{ background:#f8fafc; border-color: var(--border); }
    .form-control{ border-color: var(--border); }
    .form-control:focus{ box-shadow:none; border-color:#93c5fd; }
    .btn-primary{ background:#0f172a; border-color:#0f172a; }
    .btn-primary:hover{ background:#111c33; border-color:#111c33; }
    .helper{ color: var(--muted); font-size: .92rem;}
    .divider{
      display:flex; align-items:center; gap:14px; color: var(--muted);
      margin: 14px 0 0;
    }
    .divider:before, .divider:after{
      content:""; height:1px; flex:1; background: rgba(100,116,139,.25);
    }
  </style>
</head>
<body>
  <div class="auth-wrap">
    <div class="auth-card">
      <div class="row g-0">
        <!-- Lado izquierdo (branding) -->
        <div class="col-lg-5 d-none d-lg-block">
          <div class="left">
            <div class="badge-soft mb-4">
              <i class="bi bi-shield-lock"></i> Acceso seguro
            </div>

            <h2 class="brand mb-3">
              Comunidad de Emprendedores
              <small>Panel de administración</small>
            </h2>

            <p class="mb-4" style="opacity:.9">
              Inicie sesión para acceder al dashboard, finanzas, talleres, carritos y administración de usuarios.
            </p>

            <ul class="list-unstyled m-0" style="opacity:.9">
              <li class="mb-2"><i class="bi bi-check2-circle me-2"></i> Estadísticas en tiempo real</li>
              <li class="mb-2"><i class="bi bi-check2-circle me-2"></i> Control de créditos y cobranzas</li>
              <li class="mb-2"><i class="bi bi-check2-circle me-2"></i> Gestión de talleres e inscripciones</li>
            </ul>

            <div class="mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,.15); opacity:.85">
              <small>Versión 2.0 · Uso interno</small>
            </div>
          </div>
        </div>

        <!-- Lado derecho (formulario) -->
        <div class="col-lg-7">
          <div class="right">
            <h4 class="mb-1">Iniciar sesión</h4>
            <p class="helper mb-4">Ingrese su usuario y contraseña para continuar.</p>

            <?php if ($error): ?>
              <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?= htmlspecialchars($error) ?></div>
              </div>
            <?php endif; ?>

            <form method="POST" action="auth.php" autocomplete="off">
              <div class="mb-3">
                <label class="form-label">Usuario</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-person"></i></span>
                  <input name="username" type="text" class="form-control" placeholder="Ej: admin" required>
                </div>
              </div>

              <div class="mb-2">
                <label class="form-label">Contraseña</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="bi bi-key"></i></span>
                  <input id="password" name="password" type="password" class="form-control" placeholder="••••••••" required>
                  <button class="btn btn-outline-secondary" type="button" id="togglePass" aria-label="Mostrar contraseña">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                  <label class="form-check-label" for="remember">Recordarme</label>
                </div>
                <a href="#" class="text-decoration-none">¿Olvidó su contraseña?</a>
              </div>

              <button class="btn btn-primary w-100 mt-4">
                <i class="bi bi-box-arrow-in-right me-2"></i> Entrar
              </button>

              <div class="divider"><small>o</small></div>

              <div class="mt-3 helper">
                <i class="bi bi-info-circle me-1"></i>
                Si no tiene credenciales, solicítelas al administrador del sistema.
              </div>
            </form>

            <div class="mt-4 pt-3 text-center helper" style="border-top:1px solid rgba(100,116,139,.18)">
              <small>© <?= date('Y') ?> Comunidad de Emprendedores</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const btn = document.getElementById('togglePass');
    const pass = document.getElementById('password');
    btn.addEventListener('click', () => {
      const isPwd = pass.getAttribute('type') === 'password';
      pass.setAttribute('type', isPwd ? 'text' : 'password');
      btn.innerHTML = isPwd ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
    });
  </script>
</body>
</html>
