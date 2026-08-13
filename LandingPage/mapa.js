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

  const API = {
    contenedores: '/recolix/API_Gestion/contenedores',
    centros: '/recolix/API_Gestion/centros-acopio',
    camiones: '/recolix/API_Recoleccion/camiones/obtenerEnOperativa'
  };

  const ICONO_CAMION = L.icon({
    iconUrl: '/recolix/API_Recoleccion/Vista/img/truck-svgrepo-com.svg',
    iconSize: [26, 26],
    iconAnchor: [13, 13],
    popupAnchor: [0, -13]
  });

  const COLORES_CONTENEDOR = {
    funcional: '#4CAF50',
    inactivo: '#e3b23c',
    desbordado: '#c0392b',
    roto: '#2b2b2b',
    en_deposito: '#4a5568'
  };

  const COLORES_CENTRO = {
    operativo: '#9b59b6',
    saturado: '#8e44ad',
    cerrado: '#5b2c6f'
  };

  const ETIQUETAS_TIPO = {
    ecocentro: 'Ecocentro',
    planta_clasificacion: 'Planta de clasificación',
    relleno_sanitario: 'Relleno sanitario',
    vertedero: 'Vertedero'
  };

  const ETIQUETAS_ESTADO_CONTENEDOR = {
    funcional: 'Funcional',
    inactivo: 'Inactivo',
    desbordado: 'Desbordado',
    roto: 'Roto',
    en_deposito: 'En depósito'
  };

  const ETIQUETAS_ESTADO_CENTRO = {
    operativo: 'Operativo',
    saturado: 'Saturado',
    cerrado: 'Cerrado'
  };

  const counts = {
    contenedores: { funcional: 0, inactivo: 0, desbordado: 0, roto: 0, en_deposito: 0 },
    centros: { operativo: 0, saturado: 0, cerrado: 0 },
    camiones: { total: 0, conEta: 0, sinEta: 0 }
  };

  const grupo = L.layerGroup().addTo(map);
  let vistaActual = 'contenedores';

  const etiqueta = (mapa, clave, fallback) => {
    const valor = mapa[clave] || (clave || '').replace(/_/g, ' ');
    return valor || fallback || '—';
  };

  const buildPopup = (info, vista) => {
    const filas = [];
    if (vista === 'centros') {
      filas.push(`<b>${info.nombre}</b>`);
      filas.push(`<b>Tipo:</b> ${etiqueta(ETIQUETAS_TIPO, info.tipo)}`);
      filas.push(`<b>Estado:</b> ${etiqueta(ETIQUETAS_ESTADO_CENTRO, info.estado)}`);
      if (info.calle) filas.push(`<b>Dirección:</b> ${info.calle}${info.num_calle ? ' ' + info.num_calle : ''}`);
      if (info.municipio) filas.push(`<b>Municipio:</b> ${info.municipio}`);
      if (info.capacidad_total != null) filas.push(`<b>Capacidad:</b> ${Number(info.capacidad_total).toLocaleString()}`);
    } else {
      if (info && info.calle) filas.push(`<b>Calle:</b> ${info.calle}${info.num_calle ? ' ' + info.num_calle : ''}`);
      if (info && info.capacidad) filas.push(`<b>Capacidad:</b> ${info.capacidad} L`);
      if (info && info.nivel_llenado != null) filas.push(`<b>Nivel de llenado:</b> ${info.nivel_llenado}%`);
      filas.push(`<b>Estado:</b> ${etiqueta(ETIQUETAS_ESTADO_CONTENEDOR, info.estado)}`);
    }
    return filas.join('<br>');
  };

  const addPoint = (lat, lon, estado, info, vista) => {
    const colores = vista === 'centros' ? COLORES_CENTRO : COLORES_CONTENEDOR;
    let color = colores[estado];
    if (!color) color = '#4CAF50';
    counts[vista][estado] = (counts[vista][estado] || 0) + 1;

    const marker = L.circleMarker([lat, lon], {
      radius: 6,
      color: '#fff',
      opacity: 0.7,
      weight: 1.4,
      fillColor: color,
      fillOpacity: 0.9
    }).addTo(grupo);

    marker.bindPopup(buildPopup(info, vista), { maxWidth: 240 });
  };

  const addCamionPoint = (camion) => {
    const { latitud: lat, longitud: lon } = camion.ubicacion;
    counts.camiones.total++;
    if (camion.eta) counts.camiones.conEta++; else counts.camiones.sinEta++;

    const marker = L.marker([lat, lon], { icon: ICONO_CAMION }).addTo(grupo);

    const filas = [`<b>Camión ${camion.matricula}</b>`];
    if (camion.modelo) filas.push(`<b>Modelo:</b> ${camion.modelo}`);
    filas.push(`<b>Última posición:</b> ${camion.ubicacion.calle || 'sin dirección'}${camion.ubicacion.num_calle ? ' ' + camion.ubicacion.num_calle : ''}`);
    if (camion.proxima_parada) {
      filas.push(`<b>Próxima parada:</b> ${camion.proxima_parada.calle || 'sin dirección'}${camion.proxima_parada.num_calle ? ' ' + camion.proxima_parada.num_calle : ''}`);
      filas.push(`<b>Tiempo hasta próximo contenedor:</b> ${camion.eta.texto} (${camion.eta.distancia_km} km a ${camion.eta.velocidad_kmh} km/h)`);
    } else {
      filas.push(`<b>Próxima parada:</b> sin pendientes en este levante`);
    }
    marker.bindPopup(filas.join('<br>'), { maxWidth: 260 });
    return [lat, lon];
  };

  const renderSummary = (vista, total) => {
    const c = counts[vista];
    if (vista === 'centros') {
      document.getElementById('countOperativos').textContent = c.operativo;
      document.getElementById('countSaturados').textContent = c.saturado;
      document.getElementById('countCerrados').textContent = c.cerrado;
    } else if (vista === 'camiones') {
      document.getElementById('countCamionesTotal').textContent = c.total;
      document.getElementById('countCamionesConEta').textContent = c.conEta;
      document.getElementById('countCamionesSinEta').textContent = c.sinEta;
    } else {
      document.getElementById('countActivos').textContent = c.funcional;
      document.getElementById('countInactivos').textContent = c.inactivo;
      document.getElementById('countDesbordados').textContent = c.desbordado;
    }

    const hora = new Date().toLocaleTimeString('es-UY', { hour: '2-digit', minute: '2-digit' });
    const etiquetaTotal = vista === 'centros' ? 'centros' : (vista === 'camiones' ? 'camiones' : 'puntos');
    document.getElementById('mapSummaryValue').textContent = `${total} ${etiquetaTotal} · ${hora}`;
  };

  async function cargarVista(vista) {
    vistaActual = vista;
    grupo.clearLayers();
    Object.keys(counts[vista]).forEach((k) => { counts[vista][k] = 0; });

    document.getElementById('indicadores-contenedores').style.display = vista === 'contenedores' ? '' : 'none';
    document.getElementById('indicadores-centros').style.display = vista === 'centros' ? '' : 'none';
    document.getElementById('leyenda-contenedores').style.display = vista === 'contenedores' ? '' : 'none';
    document.getElementById('leyenda-centros').style.display = vista === 'centros' ? '' : 'none';
    const indicadoresCamiones = document.getElementById('indicadores-camiones');
    const leyendaCamiones = document.getElementById('leyenda-camiones');
    if (indicadoresCamiones) indicadoresCamiones.style.display = vista === 'camiones' ? '' : 'none';
    if (leyendaCamiones) leyendaCamiones.style.display = vista === 'camiones' ? '' : 'none';

    if (vista === 'camiones') {
      let camiones = [];
      try {
        const resp = await fetch(API.camiones, { signal: AbortSignal.timeout(5000) });
        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
        const json = await resp.json();
        camiones = Array.isArray(json.data) ? json.data : [];
      } catch (_) {
        camiones = [];
      }

      const bounds = [];
      for (const camion of camiones) {
        if (!camion.ubicacion) continue;
        bounds.push(addCamionPoint(camion));
      }
      renderSummary('camiones', bounds.length);
      if (bounds.length) map.fitBounds(bounds, { padding: [40, 40] });
      return;
    }

    let puntos = [];
    try {
      const resp = await fetch(API[vista], { signal: AbortSignal.timeout(5000) });
      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      const json = await resp.json();
      puntos = Array.isArray(json) ? json : json && json.data;
      if (!Array.isArray(puntos)) throw new Error('Respuesta inesperada de la API');
    } catch (_) {
      puntos = [];
    }

    const bounds = [];
    for (const p of puntos) {
      const lat = parseFloat(p.latitud);
      const lon = parseFloat(p.longitud);
      if (Number.isNaN(lat) || Number.isNaN(lon)) continue;
      addPoint(lat, lon, p.estado, p, vista);
      bounds.push([lat, lon]);
    }

    renderSummary(vista, bounds.length);
    if (bounds.length) map.fitBounds(bounds, { padding: [40, 40] });
  }

  document.querySelectorAll('.mapa-tab').forEach((btn) => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.mapa-tab').forEach((b) => b.classList.toggle('is-active', b === btn));
      cargarVista(btn.dataset.tipo);
    });
  });

  cargarVista('contenedores');

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
