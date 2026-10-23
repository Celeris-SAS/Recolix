
const COLORES_CONTENEDOR = {
  funcional: '#4CAF50',
  inactivo: '#e3b23c',
  desbordado: '#c0392b',
  roto: '#2b2b2b',
  en_deposito: '#4a5568'
};

async function cargarContenedoresEnMapa(map, opciones = {}) {
  const {
    apiBase = '/recolix/API_Gestion',
    radius = 6,
    tooltip = null,
    popup = null,
    onClick = null
  } = opciones;

  const marcadores = [];
  let contenedores = [];
  try {
    const resp = await fetch(`${apiBase}/contenedores`, { signal: AbortSignal.timeout(8000) });
    const json = await resp.json();
    contenedores = Array.isArray(json) ? json : (json && json.data) || [];
  } catch (_) {
    contenedores = [];
  }

  contenedores.forEach((c) => {
    const lat = parseFloat(c.latitud);
    const lon = parseFloat(c.longitud);
    if (Number.isNaN(lat) || Number.isNaN(lon)) return;

    const estado = COLORES_CONTENEDOR[c.estado] ? c.estado : 'funcional';
    const marker = L.circleMarker([lat, lon], {
      radius,
      color: '#fff',
      opacity: 0.7,
      weight: 1.4,
      fillColor: COLORES_CONTENEDOR[estado],
      fillOpacity: 0.9
    }).addTo(map);

    if (tooltip) marker.bindTooltip(tooltip(c));
    if (popup) marker.bindPopup(popup(c), { maxWidth: 240 });
    if (onClick) marker.on('click', () => onClick(marker, c));

    marker.contenedor = c;
    marcadores.push(marker);
  });

  return marcadores;
}
