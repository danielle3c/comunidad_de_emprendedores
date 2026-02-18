<?php // includes/footer.php ?>
    </div><!-- /.content-area -->
  </div><!-- /.main-content -->
<?php if (!defined('FROM_LAYOUT')): ?>
</div><!-- /.d-flex -->
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ============================================================
   SISTEMA GLOBAL — Corporación de Fomento La Granja
   Modo oscuro/claro · Color RGB · Brillo · PDF · Idioma
   ============================================================ */

/* ---- PREFERENCIA GUARDADA ---- */
(function() {
  const t = localStorage.getItem('cfg_theme') || 'dark';
  const c = localStorage.getItem('cfg_color') || '#43b02a';
  const b = localStorage.getItem('cfg_bright') || '100';
  document.documentElement.dataset.theme = t;
  document.body.style.filter = 'brightness(' + b + '%)';
  const bar = document.getElementById('sysBrightnessBar');
  if (bar) bar.value = b;
  if (t === 'dark') {
    const lbl = document.getElementById('sysThemeLabel');
    if (lbl) lbl.textContent = 'Claro';
  } else {
    const lbl = document.getElementById('sysThemeLabel');
    if (lbl) lbl.textContent = 'Oscuro';
  }
  if (c !== '#43b02a') sysApplyColor(c);
  const cp = document.getElementById('colorPickerGlobal');
  if (cp) cp.value = c;
})();

/* ---- TEMA ---- */
function sysToggleTheme() {
  const html = document.documentElement;
  const isDark = html.dataset.theme === 'dark';
  const next = isDark ? 'light' : 'dark';
  html.dataset.theme = next;
  localStorage.setItem('cfg_theme', next);
  const lbl = document.getElementById('sysThemeLabel');
  if (lbl) lbl.textContent = isDark ? 'Oscuro' : 'Claro';
  sysToast(isDark ? '☀️ Modo claro activado' : '🌙 Modo oscuro activado');
}

/* ---- COLOR ---- */
function sysApplyColor(hex) {
  const [h, s, l] = hexToHsl(hex);
  const r = document.documentElement;
  r.style.setProperty('--pick-h', h);
  r.style.setProperty('--pick-s', s + '%');
  r.style.setProperty('--pick-l', l + '%');
  r.style.setProperty('--brand-pick',     'hsl(' + h + ',' + s + '%,' + l + '%)');
  r.style.setProperty('--brand-dim',      'hsla(' + h + ',' + s + '%,' + l + '%, 0.15)');
  r.style.setProperty('--brand-glow',     'hsla(' + h + ',' + s + '%,' + l + '%, 0.35)');
  // Actualizar hexágono SVG de fondo
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

/* ---- BRILLO ---- */
document.getElementById('sysBrightnessBar')?.addEventListener('input', function() {
  document.body.style.filter = 'brightness(' + this.value + '%)';
  localStorage.setItem('cfg_bright', this.value);
});

/* ---- COPIAR FONDO ---- */
function sysCopyBg() {
  const t = document.documentElement.dataset.theme;
  const hex = localStorage.getItem('cfg_color') || '#43b02a';
  const css = t === 'dark'
    ? 'background: radial-gradient(ellipse 70% 55% at 10% 15%, rgba(' + hexToRgb(hex) + ',.35), transparent 55%), radial-gradient(ellipse 55% 65% at 92% 85%, rgba(' + hexToRgb(hex) + ',.10), transparent 55%), #080e08;'
    : 'background: radial-gradient(ellipse 70% 55% at 10% 15%, rgba(' + hexToRgb(hex) + ',.15), transparent 55%), radial-gradient(ellipse 55% 65% at 92% 85%, rgba(' + hexToRgb(hex) + ',.06), transparent 55%), #f0f7f0;';
  navigator.clipboard.writeText(css).then(() => sysToast('✓ CSS del fondo copiado'));
}
function hexToRgb(hex) {
  return parseInt(hex.slice(1,3),16)+','+parseInt(hex.slice(3,5),16)+','+parseInt(hex.slice(5,7),16);
}

/* ---- EXPORTAR PDF ---- */
function sysExportPDF() {
  sysToast('⏳ Preparando PDF...');
  setTimeout(() => window.print(), 500);
}

/* ---- TOAST ---- */
function sysToast(msg) {
  const t = document.getElementById('sysToast');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), 2600);
}

/* ---- CONFIRMACIÓN ELIMINAR ---- */
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.btn-delete').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      if (!confirm('¿Eliminar este registro? Esta acción no se puede deshacer.')) e.preventDefault();
    });
  });
});

/* ---- IDIOMA ---- */
const sysI18n = {
  es: { search: 'Buscar persona por RUT o nombre...' },
  en: { search: 'Search person by ID or name...' },
  pt: { search: 'Buscar pessoa por CPF ou nome...' },
};
function sysSetLang(lang) {
  localStorage.setItem('cfg_lang', lang);
  const si = document.getElementById('smartSearch');
  if (si && sysI18n[lang]) si.placeholder = sysI18n[lang].search;
  sysToast(lang === 'es' ? '🇨🇱 Español' : lang === 'en' ? '🇺🇸 English' : '🇧🇷 Português');
}
// Restaurar idioma
(function() {
  const lang = localStorage.getItem('cfg_lang') || 'es';
  const sel = document.getElementById('sysLangSelect');
  if (sel) sel.value = lang;
  sysSetLang(lang);
})();

/* ---- SMART SEARCH ---- */
(function() {
  const input = document.getElementById('smartSearch');
  const results = document.getElementById('smartResults');
  if (!input || !results) return;
  let timer;
  input.addEventListener('input', function() {
    clearTimeout(timer);
    const q = input.value.trim();
    if (q.length < 2) { results.style.display = 'none'; return; }
    timer = setTimeout(() => {
      fetch('includes/personas_search_api.php?q=' + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {
          if (!data.length) { results.style.display = 'none'; return; }
          results.innerHTML = data.map(p => {
            const badges = [
              p.contrato_activo ? '<span class="badge bg-success ms-1">Contrato</span>' : '',
              p.credito_activo  ? '<span class="badge bg-primary ms-1">Crédito</span>'  : '',
              p.talleres        ? '<span class="badge bg-info ms-1">Talleres</span>'     : '',
            ].join('');
            return '<a href="persona_detalle.php?id='+p.id+'" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">'
              + '<span><strong>'+p.nombre+'</strong> <small class="text-muted ms-1">'+p.rut+'</small></span>'
              + '<span>'+badges+'</span></a>';
          }).join('');
          results.style.display = 'block';
        }).catch(() => { results.style.display = 'none'; });
    }, 280);
  });
  document.addEventListener('click', function(e) {
    if (!results.contains(e.target) && e.target !== input) results.style.display = 'none';
  });
})();
</script>

<?php if (!defined('FROM_LAYOUT')): ?>
</body>
</html>
<?php endif; ?>
