<script>
function postConfig(data){
  return fetch('configuraciones_api.php', {
    method: 'POST',
    credentials: 'include',
    headers: {'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'},
    body: new URLSearchParams(data).toString()
  }).then(r => r.json());
}

function setToast(msg){
  let t = document.querySelector('.sys-toast');
  if (!t) {
    t = document.createElement('div');
    t.className = 'sys-toast';
    document.body.appendChild(t);
  }
  t.textContent = msg;
  t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'), 1800);
}

document.addEventListener('DOMContentLoaded', () => {
  const btnTheme = document.getElementById('btnTheme');
  const btnBg    = document.getElementById('btnBg');
  const btnColor = document.getElementById('btnColor');
  const picker   = document.getElementById('colorPickerGlobal');
  const langSel  = document.getElementById('langSelect');

  // 1) TEMA
  btnTheme?.addEventListener('click', async () => {
    const html = document.documentElement;
    const current = html.getAttribute('data-theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';

    html.setAttribute('data-theme', next);
    const resp = await postConfig({action:'theme', value: next});
    if (resp.ok) setToast('Tema guardado');
  });

  // 2) FONDO (activa/desactiva y recarga)
  btnBg?.addEventListener('click', async () => {
    const resp = await postConfig({action:'bg', value: 1}); // default
    // calculamos según si existe bg-canvas
    const hasBg = !!document.querySelector('.bg-canvas');
    const next = hasBg ? 0 : 1;

    const r2 = await postConfig({action:'bg', value: next});
    if (r2.ok) {
      setToast('Fondo guardado');
      location.reload();
    }
  });

  // 3) IDIOMA (guarda y recarga)
  if (langSel) {
    langSel.value = document.documentElement.lang || 'es';
    langSel.addEventListener('change', async () => {
      const v = langSel.value;
      const resp = await postConfig({action:'lang', value: v});
      if (resp.ok) {
        setToast('Idioma guardado');
        location.reload();
      }
    });
  }

  // 4) COLOR (marca)
  btnColor?.addEventListener('click', () => picker?.click());

  picker?.addEventListener('input', async () => {
    // convertir HEX a HSL
    const hex = picker.value;
    const rgb = hex.match(/[A-Fa-f0-9]{2}/g).map(x => parseInt(x,16));
    const [r,g,b] = rgb.map(v => v/255);

    const max = Math.max(r,g,b), min = Math.min(r,g,b);
    let h=0, s=0;
    const l = (max + min) / 2;

    if(max !== min){
      const d = max - min;
      s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
      switch(max){
        case r: h = (g - b) / d + (g < b ? 6 : 0); break;
        case g: h = (b - r) / d + 2; break;
        case b: h = (r - g) / d + 4; break;
      }
      h *= 60;
    }

    const H = Math.round(h);
    const S = Math.round(s * 100);
    const L = Math.round(l * 100);

    document.documentElement.style.setProperty('--pick-h', H);
    document.documentElement.style.setProperty('--pick-s', S + '%');
    document.documentElement.style.setProperty('--pick-l', L + '%');

    const resp = await postConfig({action:'color', h:H, s:S, l:L});
    if (resp.ok) setToast('Color guardado');
  });
});
</script>