<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Corporación de Fomento La Granja</title>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@300;400;600;700;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root {
  --brand: rgb(67, 176, 42);
  --brand-dim: rgba(67, 176, 42, 0.18);
  --brand-glow: rgba(67, 176, 42, 0.35);
  --brand-dark: rgb(45, 130, 28);
  --pick-h: 107;
  --pick-s: 62%;
  --pick-l: 43%;
  --brand-pick: hsl(var(--pick-h), var(--pick-s), var(--pick-l));
  --brand-pick-dim: hsla(var(--pick-h), var(--pick-s), var(--pick-l), 0.18);
  --brand-pick-glow: hsla(var(--pick-h), var(--pick-s), var(--pick-l), 0.4);
}

[data-theme="dark"] {
  --bg:         #080e08;
  --bg2:        #0d160d;
  --surface:    rgba(15, 24, 15, 0.95);
  --surface2:   rgba(20, 32, 20, 0.8);
  --border:     rgba(67, 176, 42, 0.15);
  --text:       #e8f5e8;
  --text2:      rgba(180, 220, 180, 0.65);
  --input-bg:   rgba(8, 14, 8, 0.8);
  --shadow:     rgba(0, 0, 0, 0.6);
}

[data-theme="light"] {
  --bg:         #f0f7f0;
  --bg2:        #e4f0e4;
  --surface:    rgba(255, 255, 255, 0.97);
  --surface2:   rgba(240, 250, 240, 0.9);
  --border:     rgba(67, 176, 42, 0.25);
  --text:       #0d1a0d;
  --text2:      rgba(30, 70, 30, 0.65);
  --input-bg:   rgba(240, 250, 240, 0.8);
  --shadow:     rgba(0, 80, 0, 0.12);
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Barlow', sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  transition: background .4s, color .4s;
  overflow-x: hidden;
}

.bg-canvas {
  position: fixed;
  inset: 0;
  z-index: 0;
  overflow: hidden;
  pointer-events: none;
}
.bg-canvas::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse 80% 60% at 15% 20%, var(--brand-pick-glow), transparent 55%),
    radial-gradient(ellipse 60% 70% at 85% 80%, rgba(67,176,42,0.12), transparent 55%);
  transition: opacity .4s;
}
[data-theme="light"] .bg-canvas::before {
  background:
    radial-gradient(ellipse 80% 60% at 15% 20%, var(--brand-pick-dim), transparent 55%),
    radial-gradient(ellipse 60% 70% at 85% 80%, rgba(67,176,42,0.08), transparent 55%);
}

.hex-grid {
  position: absolute;
  inset: 0;
  opacity: 0.04;
}
[data-theme="light"] .hex-grid { opacity: 0.06; }

.hex-grid svg { width: 100%; height: 100%; }

.controls {
  position: fixed;
  top: 1.2rem;
  right: 1.2rem;
  z-index: 100;
  display: flex;
  gap: .55rem;
  align-items: center;
}

.ctrl-btn {
  background: var(--surface2);
  border: 1px solid var(--border);
  color: var(--text);
  border-radius: 10px;
  padding: .45rem .7rem;
  cursor: pointer;
  font-size: .78rem;
  font-family: 'Barlow Condensed', sans-serif;
  font-weight: 600;
  letter-spacing: .04em;
  text-transform: uppercase;
  display: flex;
  align-items: center;
  gap: .35rem;
  transition: all .2s;
  backdrop-filter: blur(8px);
}
.ctrl-btn:hover {
  border-color: var(--brand-pick);
  color: var(--brand-pick);
  transform: translateY(-1px);
}
.ctrl-btn svg { width: 14px; height: 14px; }

#colorPicker {
  position: absolute;
  opacity: 0;
  width: 0;
  height: 0;
  pointer-events: none;
}

.brightness-bar {
  position: fixed;
  left: 1.2rem;
  top: 50%;
  transform: translateY(-50%);
  z-index: 100;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: .6rem;
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: .9rem .55rem;
  backdrop-filter: blur(8px);
}
.brightness-bar label {
  font-size: .68rem;
  font-family: 'Barlow Condensed', sans-serif;
  font-weight: 700;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: var(--text2);
  writing-mode: vertical-rl;
  transform: rotate(180deg);
}
.brightness-bar input[type="range"] {
  -webkit-appearance: none;
  appearance: none;
  writing-mode: vertical-lr;
  direction: rtl;
  width: 6px;
  height: 120px;
  background: transparent;
  cursor: pointer;
}
.brightness-bar input[type="range"]::-webkit-slider-runnable-track {
  background: linear-gradient(to top, var(--brand-pick) 0%, var(--brand-pick-dim) 100%);
  border-radius: 3px;
  width: 6px;
}
.brightness-bar input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: var(--brand-pick);
  border: 2px solid var(--bg);
  box-shadow: 0 0 8px var(--brand-pick-glow);
  margin-left: -6px;
  transition: transform .15s;
}
.brightness-bar input[type="range"]::-webkit-slider-thumb:hover {
  transform: scale(1.2);
}

.main {
  position: relative;
  z-index: 10;
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem 2rem 5rem;
  min-height: 100vh;
}

.card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 24px;
  padding: 2.5rem 2.2rem;
  width: 100%;
  max-width: 480px;
  box-shadow: 0 24px 60px var(--shadow), 0 0 0 1px var(--border);
  backdrop-filter: blur(20px);
  animation: cardIn .6s cubic-bezier(.22,1,.36,1) both;
  position: relative;
  overflow: hidden;
}
.card::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, transparent, var(--brand-pick), transparent);
  border-radius: 24px 24px 0 0;
}

@keyframes cardIn {
  from { opacity: 0; transform: translateY(24px) scale(.98); }
  to   { opacity: 1; transform: translateY(0) scale(1); }
}

.logo-wrap {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}
.logo-badge {
  width: 56px; height: 56px;
  background: var(--brand-dim);
  border: 2px solid var(--brand-pick);
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 0 20px var(--brand-pick-glow);
  flex-shrink: 0;
  transition: border-color .3s, box-shadow .3s;
}
.logo-badge svg {
  width: 32px; height: 32px;
  fill: var(--brand-pick);
  filter: drop-shadow(0 0 6px var(--brand-pick-glow));
  transition: fill .3s, filter .3s;
}
.logo-text h1 {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 1.05rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: .06em;
  line-height: 1.15;
  color: var(--text);
}
.logo-text span {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: .72rem;
  font-weight: 400;
  text-transform: uppercase;
  letter-spacing: .14em;
  color: var(--brand-pick);
  transition: color .3s;
}

.section-title {
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 1.6rem;
  font-weight: 700;
  letter-spacing: -.01em;
  margin-bottom: .35rem;
  color: var(--text);
}
.section-sub {
  font-size: .84rem;
  color: var(--text2);
  margin-bottom: 1.8rem;
}

.field {
  margin-bottom: 1.1rem;
}
.field label {
  display: block;
  font-size: .78rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .08em;
  color: var(--text2);
  margin-bottom: .45rem;
}
.input-wrap {
  position: relative;
}
.input-wrap input {
  width: 100%;
  background: var(--input-bg);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: .75rem 1rem .75rem 2.8rem;
  color: var(--text);
  font-family: 'Barlow', sans-serif;
  font-size: .92rem;
  transition: border-color .2s, box-shadow .2s;
  outline: none;
}
.input-wrap input::placeholder { color: var(--text2); opacity: .7; }
.input-wrap input:focus {
  border-color: var(--brand-pick);
  box-shadow: 0 0 0 3px var(--brand-pick-dim);
}
.input-wrap .icon {
  position: absolute;
  left: .9rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text2);
  font-size: 1rem;
  pointer-events: none;
  transition: color .2s;
}
.input-wrap input:focus + .icon,
.input-wrap:focus-within .icon { color: var(--brand-pick); }
.icon { position: absolute; left: .9rem; top: 50%; transform: translateY(-50%); pointer-events: none; }

.toggle-pass {
  position: absolute;
  right: .9rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: var(--text2);
  cursor: pointer;
  padding: 0;
  font-size: 1rem;
  transition: color .2s;
}
.toggle-pass:hover { color: var(--brand-pick); }

.btn-primary {
  width: 100%;
  padding: .85rem;
  background: var(--brand-pick);
  border: none;
  border-radius: 12px;
  color: #fff;
  font-family: 'Barlow Condensed', sans-serif;
  font-size: 1rem;
  font-weight: 700;
  letter-spacing: .1em;
  text-transform: uppercase;
  cursor: pointer;
  margin-top: 1.4rem;
  transition: all .2s;
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 20px var(--brand-pick-glow);
}
.btn-primary::after {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(255,255,255,0);
  transition: background .2s;
}
.btn-primary:hover::after { background: rgba(255,255,255,.1); }
.btn-primary:active { transform: scale(.98); }
.btn-primary:hover { box-shadow: 0 6px 28px var(--brand-pick-glow); transform: translateY(-1px); }

.error-message {
  background: rgba(255, 80, 80, 0.15);
  border: 1px solid rgba(255, 80, 80, 0.3);
  color: #ff8a8a;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  font-size: 0.9rem;
  text-align: center;
}

.lang-toggle {
  display: flex;
  gap: .4rem;
  margin-top: 1.4rem;
  border-top: 1px solid var(--border);
  padding-top: 1.2rem;
  justify-content: center;
}
.lang-btn {
  padding: .35rem .75rem;
  border-radius: 8px;
  border: 1px solid var(--border);
  background: transparent;
  color: var(--text2);
  font-family: 'Barlow Condensed', sans-serif;
  font-size: .78rem;
  font-weight: 700;
  letter-spacing: .08em;
  cursor: pointer;
  transition: all .2s;
}
.lang-btn.active,
.lang-btn:hover {
  background: var(--brand-pick-dim);
  border-color: var(--brand-pick);
  color: var(--brand-pick);
}
</style>
</head>
<body>

<?php
session_start();
$error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>

<div class="bg-canvas">
  <div class="hex-grid">
    <svg viewBox="0 0 200 200" preserveAspectRatio="xMidYMid slice">
      <defs>
        <pattern id="hex" x="0" y="0" width="30" height="26" patternUnits="userSpaceOnUse">
          <polygon points="15,1 28,8 28,22 15,29 2,22 2,8" fill="none" stroke="rgb(67,176,42)" stroke-width=".8"/>
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#hex)"/>
    </svg>
  </div>
</div>

<div class="controls">
  <button class="ctrl-btn" id="themeBtn" onclick="toggleTheme()" title="Cambiar modo">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
      <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
      <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
      <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
    </svg>
    <span id="themeLabel">Claro</span>
  </button>

  <button class="ctrl-btn" onclick="triggerColor()" title="Cambiar color">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/>
      <circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/>
      <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>
    </svg>
    Color
    <input type="color" id="colorPicker" value="#43b02a" oninput="applyColor(this.value)">
  </button>

  <button class="ctrl-btn" onclick="copyBackground()" title="Copiar fondo CSS">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
    </svg>
    Fondo
  </button>
</div>

<div class="brightness-bar">
  <label>Brillo</label>
  <input type="range" id="brightnessBar" min="30" max="150" value="100"
         oninput="applyBrightness(this.value)" title="Ajustar brillo">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--text2)">
    <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
  </svg>
</div>

<main class="main">
  <div class="card" id="mainCard">

    <div class="logo-wrap">
      <div class="logo-badge">
        <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
          <polygon points="50,5 90,27.5 90,72.5 50,95 10,72.5 10,27.5" fill="none" stroke="currentColor" stroke-width="8"/>
          <polygon points="50,20 76,35 76,65 50,80 24,65 24,35" fill="currentColor" opacity=".3"/>
          <circle cx="50" cy="50" r="12" fill="currentColor"/>
        </svg>
      </div>
      <div class="logo-text">
        <h1 id="corp-name">Corporación de Fomento<br>La Granja</h1>
        <span id="corp-sub">Sistema de gestión · 2025</span>
      </div>
    </div>

    <h2 class="section-title" id="t-title">Iniciar sesión</h2>
    <p class="section-sub" id="t-sub">Ingrese sus credenciales para acceder al panel.</p>

    <?php if ($error): ?>
    <div class="error-message"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form id="loginForm" method="POST" action="auth.php">
      <?php
      require_once __DIR__ . '/includes/csrf.php';
      echo csrf_field();
      ?>
      
      <div class="field">
        <label id="t-user">Usuario</label>
        <div class="input-wrap">
          <input type="text" name="username" id="username" placeholder="nombre.usuario" autocomplete="username" required>
          <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
      </div>

      <div class="field">
        <label id="t-pass">Contraseña</label>
        <div class="input-wrap">
          <input type="password" name="password" id="password" placeholder="••••••••" autocomplete="current-password" required>
          <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <button type="button" class="toggle-pass" onclick="togglePass()" id="eyeBtn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="eyeIcon">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-primary" id="t-btn">Acceder al sistema</button>
    </form>

    <div class="lang-toggle">
      <button class="lang-btn active" onclick="setLang('es', this)" type="button">🇨🇱 ES</button>
      <button class="lang-btn" onclick="setLang('en', this)" type="button">🇺🇸 EN</button>
      <button class="lang-btn" onclick="setLang('pt', this)" type="button">🇧🇷 PT</button>
    </div>

  </div>
</main>

<script>
function toggleTheme() {
  const html = document.documentElement;
  const isDark = html.dataset.theme === 'dark';
  html.dataset.theme = isDark ? 'light' : 'dark';
  document.getElementById('themeLabel').textContent = isDark ? 'Oscuro' : 'Claro';
}

function applyBrightness(val) {
  document.body.style.filter = `brightness(${val}%)`;
}

function triggerColor() {
  document.getElementById('colorPicker').click();
}

function applyColor(hex) {
  const [h, s, l] = hexToHsl(hex);
  const root = document.documentElement;
  root.style.setProperty('--pick-h', h);
  root.style.setProperty('--pick-s', s + '%');
  root.style.setProperty('--pick-l', l + '%');
  root.style.setProperty('--brand-pick', `hsl(${h},${s}%,${l}%)`);
  root.style.setProperty('--brand-pick-dim', `hsla(${h},${s}%,${l}%,0.18)`);
  root.style.setProperty('--brand-pick-glow', `hsla(${h},${s}%,${l}%,0.4)`);
  document.querySelector('#hex polygon').setAttribute('stroke', hex);
}

function hexToHsl(hex) {
  let r = parseInt(hex.slice(1,3),16)/255;
  let g = parseInt(hex.slice(3,5),16)/255;
  let b = parseInt(hex.slice(5,7),16)/255;
  const max = Math.max(r,g,b), min = Math.min(r,g,b);
  let h, s, l = (max+min)/2;
  if (max === min) { h = s = 0; }
  else {
    const d = max - min;
    s = l > 0.5 ? d/(2-max-min) : d/(max+min);
    switch(max) {
      case r: h = ((g-b)/d + (g<b?6:0))/6; break;
      case g: h = ((b-r)/d + 2)/6; break;
      case b: h = ((r-g)/d + 4)/6; break;
    }
  }
  return [Math.round(h*360), Math.round(s*100), Math.round(l*100)];
}

function copyBackground() {
  const theme = document.documentElement.dataset.theme;
  const css = theme === 'dark'
    ? `background: radial-gradient(ellipse 80% 60% at 15% 20%, var(--brand-pick-glow), transparent 55%), radial-gradient(ellipse 60% 70% at 85% 80%, rgba(67,176,42,0.12), transparent 55%), #080e08;`
    : `background: radial-gradient(ellipse 80% 60% at 15% 20%, var(--brand-pick-dim), transparent 55%), radial-gradient(ellipse 60% 70% at 85% 80%, rgba(67,176,42,0.08), transparent 55%), #f0f7f0;`;
  navigator.clipboard.writeText(css).then(() => {
    showToast('✓ CSS del fondo copiado');
  });
}

function showToast(msg) {
  const t = document.createElement('div');
  t.textContent = msg;
  Object.assign(t.style, {
    position:'fixed', bottom:'1.5rem', left:'50%', transform:'translateX(-50%)',
    background:'var(--surface)', border:'1px solid var(--border)', color:'var(--text)',
    padding:'.6rem 1.2rem', borderRadius:'10px', fontSize:'.85rem',
    fontFamily:'Barlow Condensed, sans-serif', fontWeight:'600',
    letterSpacing:'.06em', zIndex:'9999', backdropFilter:'blur(10px)',
    boxShadow:'0 8px 24px var(--shadow)',
    animation:'fadeInUp .3s ease'
  });
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 2500);
}

function togglePass() {
  const inp = document.getElementById('password');
  const icon = document.getElementById('eyeIcon');
  if (inp.type === 'password') {
    inp.type = 'text';
    icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
  } else {
    inp.type = 'password';
    icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  }
}

let currentLang = 'es';
const translations = {
  es: {
    'corp-sub': 'Sistema de gestión · 2025',
    't-title':  'Iniciar sesión',
    't-sub':    'Ingrese sus credenciales para acceder al panel.',
    't-user':   'Usuario',
    't-pass':   'Contraseña',
    't-btn':    'Acceder al sistema',
  },
  en: {
    'corp-sub': 'Management system · 2025',
    't-title':  'Sign in',
    't-sub':    'Enter your credentials to access the panel.',
    't-user':   'Username',
    't-pass':   'Password',
    't-btn':    'Access system',
  },
  pt: {
    'corp-sub': 'Sistema de gestão · 2025',
    't-title':  'Entrar',
    't-sub':    'Insira suas credenciais para acessar o painel.',
    't-user':   'Usuário',
    't-pass':   'Senha',
    't-btn':    'Acessar sistema',
  },
};

function setLang(lang, btn) {
  currentLang = lang;
  document.querySelectorAll('.lang-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  const t = translations[lang];
  for (const [id, val] of Object.entries(t)) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  }
  document.getElementById('password').placeholder = '••••••••';
  document.getElementById('username').placeholder = lang === 'en' ? 'user.name' : lang === 'pt' ? 'nome.usuario' : 'nombre.usuario';
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelector('.card').style.animationPlayState = 'running';
});

const printStyle = document.createElement('style');
printStyle.textContent = `
@media print {
  .controls, .brightness-bar, .bg-canvas { display: none !important; }
  body { background: white !important; filter: none !important; }
  .main { padding: 0 !important; }
  .card { box-shadow: none !important; border: 1px solid #ccc !important; max-width: 100% !important; }
}`;
document.head.appendChild(printStyle);
</script>
</body>
</html>