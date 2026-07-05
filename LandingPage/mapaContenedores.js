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

  const COLORES = {
    funcional: '#4CAF50',
    inactivo: '#e3b23c',
    desbordado: '#c0392b',
    roto: '#2b2b2b',
    en_deposito: '#4a5568'
  };

  const pool = [
    ...Array(68).fill('funcional'),
    ...Array(14).fill('inactivo'),
    ...Array(10).fill('desbordados'),
    ...Array(5).fill('roto'),
    ...Array(3).fill('en_deposito')
  ];

  const fallbackPoints = [];

  const counts = { funcional: 0, inactivo: 0, desbordado: 0, roto: 0, en_deposito: 0 };

  const addPoint = (lat, lon, estado) => {
    if (!estado || !COLORES[estado]) return;

    counts[estado] = (counts[estado] || 0) + 1;

    const marker = L.circleMarker([lat, lon], {
      radius: 6,
      color: '#fff',
      opacity: 0.7,
      weight: 1.4,
      fillColor: COLORES[estado],
      fillOpacity: 0.9
    }).addTo(map);

    marker.bindPopup(`<strong>${estado.replace(/_/g, ' ')}</strong><br>Contenedor registrado`);
  };

  const renderSummary = () => {
    document.getElementById('countActivos').textContent = counts.funcional;
    document.getElementById('countInactivos').textContent = counts.inactivo;
    document.getElementById('countDesbordados').textContent = counts.desbordado;
    document.getElementById('mapSummaryValue').textContent = `${Object.values(counts).reduce((a, b) => a + b, 0)} puntos`; 
  };

  fallbackPoints.forEach((point) => addPoint(point.lat, point.lon, point.estado));
  renderSummary();

  const loadFromServer = async () => {
    try {
      if (window.proj4) {
        proj4.defs('EPSG:32721', '+proj=utm +zone=21 +south +datum=WGS84 +units=m +no_defs');
      }

      const response = await fetch('https://localhost/APIS%proyecto/convertirDatosContenedores.php', {
        signal: AbortSignal.timeout(5000)
      });
      const puntos = await response.json();

      if (Array.isArray(puntos)) {
        map.eachLayer((layer) => {
          if (layer instanceof L.CircleMarker) {
            map.removeLayer(layer);
          }
        });

        Object.keys(counts).forEach((key) => {
          counts[key] = 0;
        });

        puntos.forEach((p) => {
          if (p.x == null || p.y == null) return;
          try {
            const [lon, lat] = window.proj4
              ? proj4('EPSG:32721', 'EPSG:4326', [p.x, p.y])
              : [p.x, p.y];
            const estado = pool[Math.floor(Math.random() * pool.length)];
            addPoint(lat, lon, estado);
          } catch (_) {}
        });

        renderSummary();
      }
    } catch (_) {}
  };

  loadFromServer();

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
