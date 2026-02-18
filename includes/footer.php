<?php // includes/footer.php ?>
    </div><!-- /.content-area -->
  </div><!-- /.main-content -->
<?php if (!defined('FROM_LAYOUT')): ?>
</div><!-- /.d-flex -->
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function() {
  const t = localStorage.getItem('cfg_theme') || 'dark';
  const c = localStorage.getItem('cfg_color') || '#43b02a';
  const b = localStorage.getItem('cfg_bright') || '100';
  document.documentElement.dataset.theme = t;
  document.body.style.filter = 'brightness(' + b + '%)';
  const bar = document.getElementById('sysBrightnessBar');
  if (bar) bar.value = b;
  const lbl = document.getElementById('sysThemeLabel');
  if (lbl) lbl.textContent = t === 'dark' ? 'Claro' : 'Oscuro';
  if (c !== '#43b02a') sysApplyColor(c);
  const cp = document.getElementById('colorPickerGlobal');
  if (cp) cp.value = c;
})();

function sysToggleTheme() {
  const html = document.documentElement;
  const next = html.dataset.theme === 'dark' ? 'light' : 'dark';
  html.dataset.theme = next;
  localStorage.setItem('cfg_theme', next);
  const lbl = document.getElementById('sysThemeLabel');
  if (lbl) lbl.textContent = next === 'dark' ? 'Claro' : 'Oscuro';
  sysToast(next === 'light' ? '☀️ Modo claro' : '🌙 Modo oscuro');
}

function sysApplyColor(hex) {
  const [h, s, l] = hexToHsl(hex);
  const r = document.documentElement;
  r.style.setProperty('--pick-h', h);
  r.style.setProperty('--pick-s', s + '%');
  r.style.setProperty('--pick-l', l + '%');
  r.style.setProperty('--brand-pick', 'hsl(' + h + ',' + s + '%,' + l + '%)');
  r.style.setProperty('--brand-dim', 'hsla(' + h + ',' + s + '%,' + l + '%,0.15)');
  r.style.setProperty('--brand-glow', 'hsla(' + h + ',' + s + '%,' + l + '%,0.35)');
  const hs = document.getElementById('hexStroke');
  if (hs) hs.setAttribute('stroke', hex);
  localStorage.setItem('cfg_color', hex);
}

function hexToHsl(hex) {
  let r = parseInt(hex.slice(1,3),16)/255;
  let g = parseInt(hex.slice(3,5),16)/255;
  let b = parseInt(hex.slice(5,7),16)/255;
  const max = Math.max(r,g,b), min = Math.min(r,g,b);
  let h, s, l = (max+min)/2;
  if (max === min) { h = s = 0; }
  else {
    const d = max-min;
    s = l > 0.5 ? d/(2-max-min) : d/(max+min);
    switch(max) {
      case r: h=((g-b)/d+(g<b?6:0))/6; break;
      case g: h=((b-r)/d+2)/6; break;
      case b: h=((r-g)/d+4)/6; break;
    }
  }
  return [Math.round(h*360), Math.round(s*100), Math.round(l*100)];
}

function hexToRgb(hex) {
  return parseInt(hex.slice(1,3),16)+','+parseInt(hex.slice(3,5),16)+','+parseInt(hex.slice(5,7),16);
}

function sysCopyBg() {
  const t = document.documentElement.dataset.theme;
  const hex = localStorage.getItem('cfg_color') || '#43b02a';
  const css = t === 'dark'
    ? 'background: radial-gradient(ellipse 70% 55% at 10% 15%, rgba(' + hexToRgb(hex) + ',.35), transparent 55%), #080e08;'
    : 'background: radial-gradient(ellipse 70% 55% at 10% 15%, rgba(' + hexToRgb(hex) + ',.15), transparent 55%), #f0f7f0;';
  navigator.clipboard.writeText(css).then(() => sysToast('✓ CSS copiado'));
}

function sysExportPDF() {
  sysToast('⏳ Preparando PDF...');
  setTimeout(() => window.print(), 500);
}

function sysToast(msg) {
  const t = document.getElementById('sysToast');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(t._t);
  t._t = setTimeout(() => t.classList.remove('show'), 2600);
}

function sysSetLang(lang) {
  localStorage.setItem('cfg_lang', lang);
  const si = document.getElementById('smartSearch');
  const ph = {es:'Buscar persona por RUT o nombre...', en:'Search by ID or name...', pt:'Buscar por CPF ou nome...'};
  if (si && ph[lang]) si.placeholder = ph[lang];
  sysToast(lang === 'es' ? '🇨🇱 Español' : lang === 'en' ? '🇺🇸 English' : '🇧🇷 Português');
}

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.btn-delete').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      if (!confirm('¿Eliminar este registro? Esta acción no se puede deshacer.')) e.preventDefault();
    });
  });
});
</script>

<?php if (!defined('FROM_LAYOUT')): ?>
</body>
</html>
<?php endif; ?>