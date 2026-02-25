<?php // includes/footer.php ?>
    </div><!-- /.content-area -->
  </div><!-- /.main-content -->
<?php if (!defined('FROM_LAYOUT')): ?>
</div><!-- /.d-flex -->
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ============================================================
   SISTEMA GLOBAL — init desde localStorage
   ============================================================ */
(function(){
  var t=localStorage.getItem('cfg_theme')||'dark';
  var c=localStorage.getItem('cfg_color')||'#43b02a';
  var b=localStorage.getItem('cfg_bright')||'100';
  document.documentElement.dataset.theme=t;
  document.body.style.filter='brightness('+b+'%)';
  var bar=document.getElementById('sysBrightnessBar');
  if(bar) bar.value=b;
  var lbl=document.getElementById('sysThemeLabel');
  if(lbl) lbl.textContent=(t==='dark'?'Claro':'Oscuro');
  if(c!=='#43b02a') sysApplyColor(c);
  var cp=document.getElementById('colorPickerGlobal');
  if(cp) cp.value=c;
  var sel=document.getElementById('sysLangSelect');
  if(sel) sel.value=localStorage.getItem('cfg_lang')||'es';
})();

/* ---- Tema ---- */
function sysToggleTheme(){
  var h=document.documentElement;
  var next=h.dataset.theme==='dark'?'light':'dark';
  h.dataset.theme=next;
  localStorage.setItem('cfg_theme',next);
  var lbl=document.getElementById('sysThemeLabel');
  if(lbl) lbl.textContent=(next==='dark'?'Claro':'Oscuro');
  sysToast(next==='light'?'☀️ Modo claro':'🌙 Modo oscuro');
}

/* ---- Color ---- */
function sysApplyColor(hex){
  var hsl=hexToHsl(hex);
  var h=hsl[0],s=hsl[1],l=hsl[2];
  var r=document.documentElement;
  r.style.setProperty('--pick-h',h);
  r.style.setProperty('--pick-s',s+'%');
  r.style.setProperty('--pick-l',l+'%');
  r.style.setProperty('--brand-pick','hsl('+h+','+s+'%,'+l+'%)');
  r.style.setProperty('--brand-dim','hsla('+h+','+s+'%,'+l+'%,0.15)');
  r.style.setProperty('--brand-glow','hsla('+h+','+s+'%,'+l+'%,0.35)');
  var hs=document.getElementById('hexStroke');
  if(hs) hs.setAttribute('stroke',hex);
  localStorage.setItem('cfg_color',hex);
}
function hexToHsl(hex){
  var r=parseInt(hex.slice(1,3),16)/255,g=parseInt(hex.slice(3,5),16)/255,b=parseInt(hex.slice(5,7),16)/255;
  var max=Math.max(r,g,b),min=Math.min(r,g,b),h,s,l=(max+min)/2;
  if(max===min){h=s=0;}
  else{var d=max-min;s=l>0.5?d/(2-max-min):d/(max+min);
    if(max===r) h=((g-b)/d+(g<b?6:0))/6;
    else if(max===g) h=((b-r)/d+2)/6;
    else h=((r-g)/d+4)/6;
  }
  return[Math.round(h*360),Math.round(s*100),Math.round(l*100)];
}
function hexToRgb(hex){
  return parseInt(hex.slice(1,3),16)+','+parseInt(hex.slice(3,5),16)+','+parseInt(hex.slice(5,7),16);
}

/* ---- Brillo ---- */
var _bBar=document.getElementById('sysBrightnessBar');
if(_bBar) _bBar.addEventListener('input',function(){
  document.body.style.filter='brightness('+this.value+'%)';
  localStorage.setItem('cfg_bright',this.value);
});

/* ---- Copiar fondo ---- */
function sysCopyBg(){
  var t=document.documentElement.dataset.theme;
  var hex=localStorage.getItem('cfg_color')||'#43b02a';
  var rgb=hexToRgb(hex);
  var css=t==='dark'
    ?'background: radial-gradient(ellipse 70% 55% at 10% 15%, rgba('+rgb+',.35), transparent 55%), #080e08;'
    :'background: radial-gradient(ellipse 70% 55% at 10% 15%, rgba('+rgb+',.15), transparent 55%), #f0f7f0;';
  navigator.clipboard.writeText(css).then(function(){sysToast('✓ CSS del fondo copiado');});
}

/* ---- PDF ---- */
function sysExportPDF(){
  sysToast('⏳ Preparando PDF...');
  setTimeout(function(){window.print();},500);
}

/* ---- Toast ---- */
function sysToast(msg){
  var t=document.getElementById('sysToast');
  if(!t) return;
  t.textContent=msg;
  t.classList.add('show');
  clearTimeout(t._timer);
  t._timer=setTimeout(function(){t.classList.remove('show');},2600);
}

/* ---- Idioma ---- */
function sysSetLang(lang){
  localStorage.setItem('cfg_lang',lang);
  var si=document.getElementById('smartSearch');
  var ph={es:'Buscar persona por RUT o nombre...',en:'Search by ID or name...',pt:'Buscar por CPF ou nome...'};
  if(si && ph[lang]) si.placeholder=ph[lang];
  sysToast(lang==='es'?'🇨🇱 Español':lang==='en'?'🇺🇸 English':'🇧🇷 Português');
}
// Restaurar idioma
(function(){
  var lang=localStorage.getItem('cfg_lang')||'es';
  sysSetLang(lang);
})();

/* ---- Confirmación eliminar ---- */
document.addEventListener('DOMContentLoaded',function(){
  document.querySelectorAll('.btn-delete').forEach(function(btn){
    btn.addEventListener('click',function(e){
      if(!confirm('¿Eliminar este registro? Esta acción no se puede deshacer.')) e.preventDefault();
    });
  });
});

/* ============================================================
   SMART SEARCH — buscador inteligente de personas
   La ruta al API se lee del atributo data-api del input,
   generado por PHP con la ruta absoluta correcta.
   ============================================================ */
(function(){
  var input   = document.getElementById('smartSearch');
  var results = document.getElementById('smartResults');
  if(!input || !results) return;

  // Leer la ruta desde el atributo data-api (calculado por PHP en header.php)
  var apiUrl = input.dataset.api || 'includes/search_global_api.php';

  var timer;

  input.addEventListener('input', function(){
    clearTimeout(timer);
    var q = input.value.trim();

    if(q.length < 2){
      results.style.display = 'none';
      results.innerHTML = '';
      return;
    }

    // Mostrar spinner mientras carga
    results.innerHTML = '<div class="list-group-item text-muted" style="font-size:.82rem"><i class="bi bi-arrow-repeat spin me-2"></i>Buscando...</div>';
    results.style.display = 'block';

    timer = setTimeout(function(){
      fetch(apiUrl + '?q=' + encodeURIComponent(q), {
        credentials: 'same-origin',  // Enviar cookies de sesión
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(function(response){
        if(response.status === 401){
          results.innerHTML = '<a href="login.php" class="list-group-item list-group-item-action text-warning"><i class="bi bi-lock me-2"></i>Sesión expirada — clic para iniciar sesión</a>';
          results.style.display = 'block';
          return null;
        }
        // Leer el JSON sin importar el status code (puede ser 500 con JSON de diagnóstico)
        return response.json().then(function(json){ return {status: response.status, data: json}; });
      })
      .then(function(result){
        if(result === null) return;
        var status = result.status;
        var data   = result.data;

        // Error con mensaje del servidor
        if(status !== 200 && data && data.error){
          var msg = data.detalle || data.mensaje || data.error;
          results.innerHTML = '<div class="list-group-item text-danger" style="font-size:.8rem"><i class="bi bi-exclamation-triangle me-1"></i><strong>Error DB:</strong> '+escHtml(msg)+'</div>';
          results.style.display = 'block';
          return;
        }

        if(!Array.isArray(data) || data.length === 0){
          results.innerHTML = '<div class="list-group-item text-muted" style="font-size:.82rem"><i class="bi bi-search me-2"></i>Sin resultados para "'+escHtml(q)+'"</div>';
          results.style.display = 'block';
          return;
        }

        results.innerHTML = data.map(function(p){
          var badges = '';
          if(p.es_emprendedor)  badges += '<span class="badge bg-light ms-1" style="color:var(--text2);font-size:.65rem">Emprend.</span>';
          if(p.contrato_activo) badges += '<span class="badge bg-success ms-1" style="font-size:.65rem">Contrato</span>';
          if(p.credito_activo)  badges += '<span class="badge bg-primary ms-1" style="font-size:.65rem">Crédito</span>';
          if(p.talleres)        badges += '<span class="badge bg-info ms-1" style="font-size:.65rem">Talleres</span>';
          if(p.tiene_pagos)     badges += '<span class="badge bg-warning ms-1" style="font-size:.65rem">Pagos</span>';
          var sub = p.telefono ? '<i class="bi bi-telephone" style="font-size:.7rem;opacity:.6"></i> '+escHtml(p.telefono)+' ' : '';
          sub    += p.email    ? '<i class="bi bi-envelope"  style="font-size:.7rem;opacity:.6"></i> '+escHtml(p.email)    : '';
          return '<a href="persona_detalle.php?id='+p.id+'" class="list-group-item list-group-item-action py-2">'
            + '<div class="d-flex justify-content-between align-items-start">'
            + '<div><strong>'+escHtml(p.nombre)+'</strong><small class="text-muted ms-2">'+escHtml(p.rut)+'</small>'
            + (sub ? '<br><small class="text-muted" style="font-size:.72rem">'+sub+'</small>' : '')
            + '</div><div class="d-flex flex-wrap gap-1 ms-2">'+badges+'</div></div></a>';
        }).join('');
        results.style.display = 'block';
      })
      .catch(function(err){
        results.innerHTML = '<div class="list-group-item text-danger" style="font-size:.82rem"><i class="bi bi-wifi-off me-2"></i>Sin conexión o error de red</div>';
        console.error('Smart search error:', err);
      });
    }, 300);
  });

  // Cerrar al hacer clic afuera
  document.addEventListener('click', function(e){
    if(!results.contains(e.target) && e.target !== input){
      results.style.display = 'none';
    }
  });

  // Cerrar con Escape
  input.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
      results.style.display = 'none';
      input.blur();
    }
  });

  function escHtml(s){
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
})();
</script>

<!-- CSS para spinner del buscador -->
<style>
@keyframes spin { to { transform: rotate(360deg); } }
.spin { display: inline-block; animation: spin .8s linear infinite; }
</style>

<?php if (!defined('FROM_LAYOUT')): ?>

<!-- DataTables + Buttons + PDF -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>


<!-- DataTables + Buttons + PDF (genérico para listados) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
  if (!window.jQuery) return;

  // Inicializar DataTables en cualquier tabla marcada con .dt-export
  jQuery('table.dt-export').each(function(){
    const $t = jQuery(this);
    const title = $t.data('title') || 'Listado';

    // evitar doble init
    if (jQuery.fn.dataTable.isDataTable(this)) return;

    $t.DataTable({
      dom: 'Bfrtip',
      pageLength: 15,
      buttons: [
        {
          extend: 'pdfHtml5',
          text: 'Exportar PDF',
          title: title,
          exportOptions: { columns: ':visible:not(:last-child)' }
        },
        {
          extend: 'print',
          text: 'Imprimir',
          title: title,
          exportOptions: { columns: ':visible:not(:last-child)' }
        }
      ],
      language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json', emptyTable: 'Sin registros' }
    });
  });
});
</script>

</body>
</html>
<?php endif; ?>
