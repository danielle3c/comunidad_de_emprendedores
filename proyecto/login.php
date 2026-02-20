<?php
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/csrf.php';

secure_session_start();

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesión | Corporación de Fomento La Granja</title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/app.css" rel="stylesheet">
  <style>
    body { display:flex; flex-direction:column; min-height:100vh; align-items:center; justify-content:center; padding:1rem 1rem 1rem 4rem; }
    .login-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 22px;
      padding: 2.4rem 2.1rem;
      width: 100%; max-width: 440px;
      box-shadow: 0 24px 60px var(--shadow);
      backdrop-filter: blur(20px);
      position: relative;
      overflow: hidden;
      animation: cardIn .55s cubic-bezier(.22,1,.36,1) both;
    }
    .login-card::before {
      content:'';position:absolute;top:0;left:0;right:0;height:2px;
      background:linear-gradient(90deg,transparent,var(--brand-pick),transparent);
    }
    @keyframes cardIn { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
    .logo-badge {
      width:52px;height:52px;background:var(--brand-dim);border:1.5px solid var(--brand-pick);
      border-radius:15px;display:flex;align-items:center;justify-content:center;
      box-shadow:0 0 18px var(--brand-glow);transition:border-color .3s,box-shadow .3s;
    }
    .logo-badge svg { width:28px;height:28px;fill:var(--brand-pick);filter:drop-shadow(0 0 5px var(--brand-glow)); }
    .field label { display:block;font-size:.73rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--text2);margin-bottom:.4rem; }
    .field { margin-bottom:1rem; }
    .input-wrap { position:relative; }
    .input-wrap input {
      width:100%;background:var(--input-bg);border:1px solid var(--border);border-radius:11px;
      padding:.7rem .9rem .7rem 2.6rem;color:var(--text);font-family:'Barlow',sans-serif;font-size:.9rem;
      outline:none;transition:border-color .2s,box-shadow .2s;
    }
    .input-wrap input::placeholder { color:var(--text2);opacity:.7; }
    .input-wrap input:focus { border-color:var(--brand-pick);box-shadow:0 0 0 3px var(--brand-dim); }
    .input-wrap .ico { position:absolute;left:.8rem;top:50%;transform:translateY(-50%);color:var(--text2);pointer-events:none;font-size:.95rem;transition:color .2s; }
    .input-wrap:focus-within .ico { color:var(--brand-pick); }
    .toggle-pass { position:absolute;right:.8rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text2);cursor:pointer;padding:0;font-size:.95rem;transition:color .2s; }
    .toggle-pass:hover { color:var(--brand-pick); }
    .btn-login {
      width:100%;padding:.82rem;background:var(--brand-pick);border:none;border-radius:11px;
      color:#fff;font-family:'Barlow Condensed',sans-serif;font-size:1rem;font-weight:700;
      letter-spacing:.10em;text-transform:uppercase;cursor:pointer;margin-top:1.2rem;
      box-shadow:0 4px 18px var(--brand-glow);transition:all .2s;
    }
    .btn-login:hover { filter:brightness(1.1);transform:translateY(-1px);box-shadow:0 6px 24px var(--brand-glow); }
    .btn-login:active { transform:scale(.98); }
  </style>
</head>
<body>

<!-- Fondo hexagonal -->
<div class="bg-canvas">
  <svg class="hex-svg" viewBox="0 0 200 200" preserveAspectRatio="xMidYMid slice">
    <defs>
      <pattern id="hexPat" x="0" y="0" width="30" height="26" patternUnits="userSpaceOnUse">
        <polygon points="15,1 28,8 28,22 15,29 2,22 2,8" fill="none" stroke="rgb(67,176,42)" stroke-width=".7" id="hexStroke"/>
      </pattern>
    </defs>
    <rect width="100%" height="100%" fill="url(#hexPat)"/>
  </svg>
</div>

<!-- Controles flotantes -->
<div class="sys-controls">
  <button class="sys-btn" onclick="sysToggleTheme()" id="sysThemeBtn">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px">
      <circle cx="12" cy="12" r="5"/>
      <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
      <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
      <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
    </svg>
    <span id="sysThemeLabel">Claro</span>
  </button>
  <button class="sys-btn" onclick="document.getElementById('colorPickerGlobal').click()">
    <i class="bi bi-palette"></i> Color
    <input type="color" id="colorPickerGlobal" value="#43b02a" oninput="sysApplyColor(this.value)"
           style="position:absolute;opacity:0;width:0;height:0;pointer-events:none">
  </button>
  <select class="lang-select" onchange="sysSetLang(this.value)" id="sysLangSelect">
    <option value="es">🇨🇱 ES</option>
    <option value="en">🇺🇸 EN</option>
    <option value="pt">🇧🇷 PT</option>
  </select>
</div>

<!-- Barra de brillo -->
<div class="brightness-rail">
  <span class="rail-label">Brillo</span>
  <input type="range" id="sysBrightnessBar" min="40" max="140" value="100"
         oninput="document.body.style.filter='brightness('+this.value+'%)';localStorage.setItem('cfg_bright',this.value)">
  <i class="bi bi-brightness-high" style="color:var(--text2);font-size:.75rem"></i>
</div>

<div class="sys-toast" id="sysToast"></div>

<!-- Card de login -->
<div class="login-card">

  <div class="d-flex align-items-center gap-3 mb-4">
    <div class="logo-badge">
      <svg viewBox="0 0 100 100"><polygon points="50,5 90,27.5 90,72.5 50,95 10,72.5 10,27.5" fill="none" stroke="currentColor" stroke-width="8"/><polygon points="50,20 76,35 76,65 50,80 24,65 24,35" fill="currentColor" opacity=".3"/><circle cx="50" cy="50" r="12" fill="currentColor"/></svg>
    </div>
    <div>
      <div style="color:#fff;font-family:'Barlow Condensed',sans-serif;font-weight:900;font-size:.95rem;text-transform:uppercase;letter-spacing:.04em;line-height:1.2">
        Corporación de Fomento<br>La Granja
      </div>
      <div style="color:var(--brand-pick);font-size:.68rem;letter-spacing:.12em;text-transform:uppercase;margin-top:.2rem" id="corp-sub">
        Sistema de gestión · 2025
      </div>
    </div>
  </div>

  <h2 style="font-family:'Barlow Condensed',sans-serif;font-size:1.55rem;font-weight:700;margin-bottom:.3rem" id="t-title">Iniciar sesión</h2>
  <p style="color:var(--text2);font-size:.84rem;margin-bottom:1.6rem" id="t-sub">Ingrese sus credenciales para acceder al panel.</p>

  <?php if ($error): ?>
    <div class="alert alert-danger mb-3"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <form action="auth.php" method="POST">
    <?= csrf_field() ?>

    <div class="field">
      <label id="t-user">Usuario</label>
      <div class="input-wrap">
        <input type="text" name="username" placeholder="nombre.usuario" autocomplete="username" required>
        <i class="bi bi-person ico"></i>
      </div>
    </div>

    <div class="field">
      <label id="t-pass">Contraseña</label>
      <div class="input-wrap">
        <input type="password" name="password" id="passInput" placeholder="••••••••" autocomplete="current-password" required>
        <i class="bi bi-lock ico"></i>
        <button type="button" class="toggle-pass" onclick="togglePass()">
          <i class="bi bi-eye" id="eyeIcon"></i>
        </button>
      </div>
    </div>

    <button type="submit" class="btn-login" id="t-btn">Acceder al sistema</button>
  </form>

  <div style="display:flex;gap:.4rem;margin-top:1.3rem;border-top:1px solid var(--border);padding-top:1.1rem;justify-content:center">
    <button onclick="setLangLogin('es',this)" class="sys-btn active-lang" id="lb-es">🇨🇱 ES</button>
    <button onclick="setLangLogin('en',this)" class="sys-btn" id="lb-en">🇺🇸 EN</button>
    <button onclick="setLangLogin('pt',this)" class="sys-btn" id="lb-pt">🇧🇷 PT</button>
  </div>
</div>

<script>
/* Init tema/color/brillo */
(function(){
  const t=localStorage.getItem('cfg_theme')||'dark';
  const c=localStorage.getItem('cfg_color')||'#43b02a';
  const b=localStorage.getItem('cfg_bright')||'100';
  document.documentElement.dataset.theme=t;
  document.body.style.filter='brightness('+b+'%)';
  const bar=document.getElementById('sysBrightnessBar');if(bar)bar.value=b;
  const lbl=document.getElementById('sysThemeLabel');if(lbl)lbl.textContent=t==='dark'?'Claro':'Oscuro';
  if(c!=='#43b02a')sysApplyColor(c);
  const cp=document.getElementById('colorPickerGlobal');if(cp)cp.value=c;
  const lang=localStorage.getItem('cfg_lang')||'es';
  const sel=document.getElementById('sysLangSelect');if(sel)sel.value=lang;
  setLangLogin(lang);
})();

function sysToggleTheme(){
  const h=document.documentElement;
  const next=h.dataset.theme==='dark'?'light':'dark';
  h.dataset.theme=next;localStorage.setItem('cfg_theme',next);
  const lbl=document.getElementById('sysThemeLabel');if(lbl)lbl.textContent=next==='dark'?'Claro':'Oscuro';
  sysToast(next==='light'?'☀️ Modo claro':'🌙 Modo oscuro');
}
function sysApplyColor(hex){
  const[h,s,l]=hexToHsl(hex);
  const r=document.documentElement;
  r.style.setProperty('--pick-h',h);r.style.setProperty('--pick-s',s+'%');r.style.setProperty('--pick-l',l+'%');
  r.style.setProperty('--brand-pick','hsl('+h+','+s+'%,'+l+'%)');
  r.style.setProperty('--brand-dim','hsla('+h+','+s+'%,'+l+'%,0.15)');
  r.style.setProperty('--brand-glow','hsla('+h+','+s+'%,'+l+'%,0.35)');
  const hs=document.getElementById('hexStroke');if(hs)hs.setAttribute('stroke',hex);
  localStorage.setItem('cfg_color',hex);
}
function hexToHsl(hex){
  let r=parseInt(hex.slice(1,3),16)/255,g=parseInt(hex.slice(3,5),16)/255,b=parseInt(hex.slice(5,7),16)/255;
  const max=Math.max(r,g,b),min=Math.min(r,g,b);let h,s,l=(max+min)/2;
  if(max===min){h=s=0;}else{const d=max-min;s=l>.5?d/(2-max-min):d/(max+min);switch(max){case r:h=((g-b)/d+(g<b?6:0))/6;break;case g:h=((b-r)/d+2)/6;break;case b:h=((r-g)/d+4)/6;break;}}
  return[Math.round(h*360),Math.round(s*100),Math.round(l*100)];
}
function sysSetLang(lang){localStorage.setItem('cfg_lang',lang);setLangLogin(lang);}
function sysToast(msg){const t=document.getElementById('sysToast');if(!t)return;t.textContent=msg;t.classList.add('show');clearTimeout(t._t);t._t=setTimeout(()=>t.classList.remove('show'),2600);}
function togglePass(){const i=document.getElementById('passInput'),ic=document.getElementById('eyeIcon');i.type=i.type==='password'?'text':'password';ic.className='bi bi-eye'+(i.type==='text'?'-slash':'');}

const i18n={
  es:{sub:'Sistema de gestión · 2025',title:'Iniciar sesión',desc:'Ingrese sus credenciales para acceder al panel.',user:'Usuario',pass:'Contraseña',btn:'Acceder al sistema'},
  en:{sub:'Management system · 2025',title:'Sign in',desc:'Enter your credentials to access the panel.',user:'Username',pass:'Password',btn:'Access system'},
  pt:{sub:'Sistema de gestão · 2025',title:'Entrar',desc:'Insira suas credenciais para acessar o painel.',user:'Usuário',pass:'Senha',btn:'Acessar sistema'},
};
function setLangLogin(lang,btn){
  localStorage.setItem('cfg_lang',lang);
  const t=i18n[lang]||i18n.es;
  const ids={sub:'corp-sub',title:'t-title',desc:'t-sub',user:'t-user',pass:'t-pass',btn:'t-btn'};
  for(const[k,id]of Object.entries(ids)){const el=document.getElementById(id);if(el)el.textContent=t[k]||el.textContent;}
  ['es','en','pt'].forEach(l=>{const b=document.getElementById('lb-'+l);if(b)b.style.borderColor=l===lang?'var(--brand-pick)':'var(--border)';});
}
</script>
</body>
</html>
