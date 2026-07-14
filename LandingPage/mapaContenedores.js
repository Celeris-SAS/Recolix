document.addEventListener('DOMContentLoaded', function () {
  const map = L.map('mapa-principal', {
    zoomControl: true,
    scrollWheelZoom: true,
    attributionControl: false
  }).setView([-34.901, -56.165], 12);

  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  setTimeout(() => map.invalidateSize(), 250);
  window.addEventListener('load', () => map.invalidateSize());
  window.addEventListener('resize', () => map.invalidateSize());

  const API_URL = 'http://localhost/recolix/API_Gestion/contenedores';

  const COLORES = {
    funcional: '#4CAF50',
    inactivo: '#e3b23c',
    desbordado: '#c0392b',
    roto: '#2b2b2b',
    en_deposito: '#4a5568'
  };

  const counts = { funcional: 0, inactivo: 0, desbordado: 0, roto: 0, en_deposito: 0 };
  const markers = [];

  const addPoint = (lat, lon, estado, info) => {
    if (!estado || !COLORES[estado]) estado = 'funcional';
    counts[estado] = (counts[estado] || 0) + 1;

    const marker = L.circleMarker([lat, lon], {
      radius: 6,
      color: '#fff',
      opacity: 0.7,
      weight: 1.4,
      fillColor: COLORES[estado],
      fillOpacity: 0.9
    }).addTo(map);

    const filas = [];
    if (info && info.calle) filas.push(`<b>Calle:</b> ${info.calle}${info.num_calle ? ' ' + info.num_calle : ''}`);
    if (info && info.capacidad) filas.push(`<b>Capacidad:</b> ${info.capacidad} L`);
    if (info && info.nivel_llenado != null) filas.push(`<b>Nivel de llenado:</b> ${info.nivel_llenado}%`);
    filas.push(`<b>Estado:</b> ${estado.replace(/_/g, ' ')}`);
    marker.bindPopup(filas.join('<br>'), { maxWidth: 240 });

    markers.push(marker);
  };

  const resetPuntos = () => {
    markers.forEach((m) => map.removeLayer(m));
    markers.length = 0;
    Object.keys(counts).forEach((k) => { counts[k] = 0; });
  };

  const renderSummary = (total) => {
    document.getElementById('countActivos').textContent = counts.funcional;
    document.getElementById('countInactivos').textContent = counts.inactivo;
    document.getElementById('countDesbordados').textContent = counts.desbordado;

    const hora = new Date().toLocaleTimeString('es-UY', { hour: '2-digit', minute: '2-digit' });
    document.getElementById('mapSummaryValue').textContent = `${total} puntos · ${hora}`;
  };

  async function cargarDatosDesdeBD() {
    const resp = await fetch(API_URL, { signal: AbortSignal.timeout(5000) });
    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
    const json = await resp.json();
    const puntos = Array.isArray(json) ? json : json && json.data;
    if (!Array.isArray(puntos)) throw new Error('Respuesta inesperada de la API');
    return puntos;
  }

  async function cargarDatos() {
    resetPuntos();

    let puntos = [];
    try {
      puntos = await cargarDatosDesdeBD();
    } catch (_) {
      puntos = [];
    }

    const bounds = [];
    for (const p of puntos) {
      const lat = parseFloat(p.latitud);
      const lon = parseFloat(p.longitud);
      if (Number.isNaN(lat) || Number.isNaN(lon)) continue;
      addPoint(lat, lon, p.estado, p);
      bounds.push([lat, lon]);
    }

    renderSummary(bounds.length);
    if (bounds.length) map.fitBounds(bounds, { padding: [40, 40] });
  }

  cargarDatos();

  const btn = document.getElementById('hamburgerBtn');
  const nav = document.getElementById('mainNav');
  if (btn && nav) {
    btn.addEventListener('click', () => {
      const abierto = nav.classList.toggle('is-open');
      btn.classList.toggle('is-open', abierto);
      btn.textContent = abierto ? '✕' : '☰';
    });
  }
});
