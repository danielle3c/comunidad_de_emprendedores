<?php
define('FROM_LAYOUT', true);
if (!function_exists('getFlash')) require_once __DIR__ . '/helpers.php';
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle ?? 'Corporación de Fomento La Granja', ENT_QUOTES, 'UTF-8') ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/app.css" rel="stylesheet">
</head>
<body>

<!-- Fondo hexagonal animado -->
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
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="5"/>
      <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
      <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
      <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
      <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
    </svg>
    <span id="sysThemeLabel">Claro</span>
  </button>

  <button class="sys-btn" onclick="document.getElementById('colorPickerGlobal').click()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/>
      <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/>
      <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>
    </svg>
    Color
    <input type="color" id="colorPickerGlobal" value="#43b02a" oninput="sysApplyColor(this.value)"
           style="position:absolute;opacity:0;width:0;height:0;pointer-events:none">
  </button>

  <button class="sys-btn" onclick="sysCopyBg()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <rect x="9" y="9" width="13" height="13" rx="2"/>
      <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
    </svg>
    Fondo
  </button>

  <button class="sys-btn" onclick="sysExportPDF()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
      <polyline points="14 2 14 8 20 8"/>
      <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
    </svg>
    PDF
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
  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--text2)">
    <circle cx="12" cy="12" r="5"/>
    <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
  </svg>
</div>

<div class="sys-toast" id="sysToast"></div>

<div class="d-flex">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="main-content flex-grow-1">
    <?php include __DIR__ . '/header.php'; ?>
    <?= $content ?? '' ?>
    <?php include __DIR__ . '/footer.php'; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ---- init desde localStorage ---- */
(function(){
  const t=localStorage.getItem('cfg_theme')||'dark';
  const c=localStorage.getItem('cfg_color')||'#43b02a';
  const b=localStorage.getItem('cfg_bright')||'100';
  document.documentElement.dataset.theme=t;
  document.body.style.filter='brightness('+b+'%)';
  const bar=document.getElementById('sysBrightnessBar');
  if(bar) bar.value=b;
  const lbl=document.getElementById('sysThemeLabel');
  if(lbl) lbl.textContent=t==='dark'?'Claro':'Oscuro';
  if(c!=='#43b02a') sysApplyColor(c);
  const cp=document.getElementById('colorPickerGlobal');
  if(cp) cp.value=c;
  const sel=document.getElementById('sysLangSelect');
  if(sel) sel.value=localStorage.getItem('cfg_lang')||'es';
})();

function sysToggleTheme(){
  const h=document.documentElement;
  const next=h.dataset.theme==='dark'?'light':'dark';
  h.dataset.theme=next;
  localStorage.setItem('cfg_theme',next);
  const lbl=document.getElementById('sysThemeLabel');
  if(lbl) lbl.textContent=next==='dark'?'Claro':'Oscuro';
  sysToast(next==='light'?'☀️ Modo claro':'🌙 Modo oscuro');
}
function sysApplyColor(hex){
  const[h,s,l]=hexToHsl(hex);
  const r=document.documentElement;
  r.style.setProperty('--pick-h',h);
  r.style.setProperty('--pick-s',s+'%');
  r.style.setProperty('--pick-l',l+'%');
  r.style.setProperty('--brand-pick','hsl('+h+','+s+'%,'+l+'%)');
  r.style.setProperty('--brand-dim','hsla('+h+','+s+'%,'+l+'%,0.15)');
  r.style.setProperty('--brand-glow','hsla('+h+','+s+'%,'+l+'%,0.35)');
  const hs=document.getElementById('hexStroke');
  if(hs) hs.setAttribute('stroke',hex);
  localStorage.setItem('cfg_color',hex);
}
function hexToHsl(hex){
  let r=parseInt(hex.slice(1,3),16)/255,g=parseInt(hex.slice(3,5),16)/255,b=parseInt(hex.slice(5,7),16)/255;
  const max=Math.max(r,g,b),min=Math.min(r,g,b);
  let h,s,l=(max+min)/2;
  if(max===min){h=s=0;}else{
    const d=max-min;s=l>.5?d/(2-max-min):d/(max+min);
    switch(max){case r:h=((g-b)/d+(g<b?6:0))/6;break;case g:h=((b-r)/d+2)/6;break;case b:h=((r-g)/d+4)/6;break;}
  }
  return[Math.round(h*360),Math.round(s*100),Math.round(l*100)];
}
function hexToRgb(hex){return parseInt(hex.slice(1,3),16)+','+parseInt(hex.slice(3,5),16)+','+parseInt(hex.slice(5,7),16);}
function sysCopyBg(){
  const t=document.documentElement.dataset.theme;
  const hex=localStorage.getItem('cfg_color')||'#43b02a';
  const css=t==='dark'
    ?'background: radial-gradient(ellipse 70% 55% at 10% 15%, rgba('+hexToRgb(hex)+',.35), transparent 55%), #080e08;'
    :'background: radial-gradient(ellipse 70% 55% at 10% 15%, rgba('+hexToRgb(hex)+',.15), transparent 55%), #f0f7f0;';
  navigator.clipboard.writeText(css).then(()=>sysToast('✓ CSS copiado'));
}
function sysExportPDF(){sysToast('⏳ Preparando PDF...');setTimeout(()=>window.print(),500);}
function sysToast(msg){
  const t=document.getElementById('sysToast');
  if(!t)return;
  t.textContent=msg;t.classList.add('show');
  clearTimeout(t._t);t._t=setTimeout(()=>t.classList.remove('show'),2600);
}
function sysSetLang(lang){
  localStorage.setItem('cfg_lang',lang);
  const si=document.getElementById('smartSearch');
  const ph={es:'Buscar persona por RUT o nombre...',en:'Search by ID or name...',pt:'Buscar por CPF ou nome...'};
  if(si&&ph[lang])si.placeholder=ph[lang];
  sysToast(lang==='es'?'🇨🇱 Español':lang==='en'?'🇺🇸 English':'🇧🇷 Português');
}
document.addEventListener('DOMContentLoaded',function(){
  document.querySelectorAll('.btn-delete').forEach(function(btn){
    btn.addEventListener('click',function(e){
      if(!confirm('¿Eliminar este registro? Esta acción no se puede deshacer.'))e.preventDefault();
    });
  });
  // Smart search
  const input=document.getElementById('smartSearch');
  const results=document.getElementById('smartResults');
  if(!input||!results)return;
  let timer;
  input.addEventListener('input',function(){
    clearTimeout(timer);
    const q=input.value.trim();
    if(q.length<2){results.style.display='none';return;}
    timer=setTimeout(()=>{
      fetch('includes/personas_search_api.php?q='+encodeURIComponent(q))
        .then(r=>r.json())
        .then(data=>{
          if(!data.length){results.style.display='none';return;}
          results.innerHTML=data.map(p=>{
            const b=[
              p.contrato_activo?'<span class="badge bg-success ms-1">Contrato</span>':'',
              p.credito_activo?'<span class="badge bg-primary ms-1">Crédito</span>':'',
              p.talleres?'<span class="badge bg-info ms-1">Talleres</span>':'',
            ].join('');
            return '<a href="persona_detalle.php?id='+p.id+'" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"><span><strong>'+p.nombre+'</strong> <small class="text-muted ms-1">'+p.rut+'</small></span><span>'+b+'</span></a>';
          }).join('');
          results.style.display='block';
        }).catch(()=>{results.style.display='none';});
    },280);
  });
  document.addEventListener('click',function(e){
    if(!results.contains(e.target)&&e.target!==input)results.style.display='none';
  });
});
</script>
</body>
</html>
