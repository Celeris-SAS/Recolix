const API_GESTION = '/recolix/API_Gestion';

document.addEventListener('DOMContentLoaded', () => {
  const header = document.getElementById('siteHeader');
  window.addEventListener('scroll', () => {
    header.classList.toggle('is-scrolled', window.scrollY > 40);
  });

  const btn = document.getElementById('hamburgerBtn');
  const nav = document.getElementById('mainNav');
  btn.addEventListener('click', () => {
    const abierto = nav.classList.toggle('is-open');
    btn.classList.toggle('is-open', abierto);
    btn.textContent = abierto ? '✕' : '☰';
  });

  cargarReportes();
});

function formatearVolumen(kg) {
  if (kg >= 1000) {
    return `${(kg / 1000).toLocaleString(I18N_LOCALE, { maximumFractionDigits: 1 })} t`;
  }
  return `${kg.toLocaleString(I18N_LOCALE, { maximumFractionDigits: 0 })} kg`;
}

async function cargarReportes() {
  try {
    const resp = await fetch(`${API_GESTION}/reportes/getResumenPublico`, { signal: AbortSignal.timeout(8000) });
    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
    const data = (await resp.json()).data;

    document.querySelectorAll('#gridResueltas [data-periodo]').forEach((el) => {
      el.textContent = data[el.dataset.periodo].incidenciasResueltas.toLocaleString(I18N_LOCALE);
    });
    document.querySelectorAll('#gridVolumen [data-periodo]').forEach((el) => {
      el.textContent = formatearVolumen(data[el.dataset.periodo].volumenKg);
    });

    const hora = new Date().toLocaleTimeString(I18N_LOCALE, { hour: '2-digit', minute: '2-digit' });
    document.getElementById('reportesActualizado').textContent = `${I18N.actualizado}: ${hora}`;
  } catch (_) {
    document.getElementById('reportesError').style.display = '';
  }
}
