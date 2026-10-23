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
    camiones: '/recolix/API_Recoleccion/camiones/obtenerEnOperativa',
    incidencias: '/recolix/API_Gestion/incidencias/getActivasConUbicacion',
    proyeccion: '/recolix/API_Gestion/predicciones/proyectarLlenadoIA'
  };

  const ICONO_CAMION = L.icon({
    iconUrl: '/recolix/API_Recoleccion/Vista/img/truck-svgrepo-com.svg',
    iconSize: [26, 26],
    iconAnchor: [13, 13],
    popupAnchor: [0, -13]
  });


  const COLORES_CENTRO = {
    operativo: '#9b59b6',
    saturado: '#8e44ad',
    cerrado: '#5b2c6f'
  };

  const COLORES_PRIORIDAD_INCIDENCIA = {
    critica: '#c0392b',
    alta: '#e3b23c',
    media: '#2c4022',
    baja: '#4b5446'
  };

  const ETIQUETAS_TIPO = {
    ecocentro: I18N.tipoEcocentro,
    planta_clasificacion: I18N.tipoPlantaClasificacion,
    relleno_sanitario: I18N.tipoRellenoSanitario,
    vertedero: I18N.tipoVertedero
  };

  const ETIQUETAS_ESTADO_CONTENEDOR = {
    funcional: I18N.estadoFuncional,
    inactivo: I18N.estadoInactivo,
    desbordado: I18N.estadoDesbordado,
    roto: I18N.estadoRoto,
    en_deposito: I18N.estadoEnDeposito
  };

  const ETIQUETAS_ESTADO_CENTRO = {
    operativo: I18N.estadoOperativo,
    saturado: I18N.estadoSaturado,
    cerrado: I18N.estadoCerrado
  };

  const ETIQUETAS_PRIORIDAD = {
    critica: I18N.prioridadCritica,
    alta: I18N.prioridadAlta,
    media: I18N.prioridadMedia,
    baja: I18N.prioridadBaja
  };

  const ETIQUETAS_TIPO_INCIDENCIA = {
    rotura: I18N.tipoIncRotura,
    desborde: I18N.tipoIncDesborde,
    falta_recoleccion: I18N.tipoIncFaltaRecoleccion,
    vandalismo: I18N.tipoIncVandalismo,
    otro: I18N.tipoIncOtro
  };

  const ETIQUETAS_ESTADO_INCIDENCIA = {
    abierta: I18N.estadoIncAbierta,
    asignada: I18N.estadoIncAsignada,
    en_curso: I18N.estadoIncEnCurso
  };

  const counts = {
    contenedores: { funcional: 0, inactivo: 0, desbordado: 0, roto: 0, en_deposito: 0 },
    centros: { operativo: 0, saturado: 0, cerrado: 0 },
    camiones: { total: 0, conEta: 0, sinEta: 0 },
    incidencias: { critica: 0, alta: 0, media: 0, baja: 0 }
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
      filas.push(`<b>${I18N.popupTipo}</b> ${etiqueta(ETIQUETAS_TIPO, info.tipo)}`);
      filas.push(`<b>${I18N.popupEstado}</b> ${etiqueta(ETIQUETAS_ESTADO_CENTRO, info.estado)}`);
      if (info.calle) filas.push(`<b>${I18N.popupDireccion}</b> ${info.calle}${info.num_calle ? ' ' + info.num_calle : ''}`);
      if (info.municipio) filas.push(`<b>${I18N.popupMunicipio}</b> ${info.municipio}`);
      if (info.capacidad_total != null) filas.push(`<b>${I18N.popupCapacidad}</b> ${Number(info.capacidad_total).toLocaleString()}`);
    } else {
      if (info && info.calle) filas.push(`<b>${I18N.popupDireccion}</b> ${info.calle}${info.num_calle ? ' ' + info.num_calle : ''}`);
      if (info && info.capacidad) filas.push(`<b>${I18N.popupCapacidad}</b> ${info.capacidad} L`);
      if (info && info.nivel_llenado != null) filas.push(`<b>${I18N.popupNivelLlenado}</b> ${info.nivel_llenado}%`);
      filas.push(`<b>${I18N.popupEstado}</b> ${etiqueta(ETIQUETAS_ESTADO_CONTENEDOR, info.estado)}`);
      if (info && info.id_contenedor != null) {
        filas.push(`<b>${I18N.popupProyeccion}</b> <span id="proy-${info.id_contenedor}">${I18N.proyeccionCalculando}</span>`);
      }
    }
    return filas.join('<br>');
  };

  // Mini modelo de IA (regresión lineal simple sobre el historial de levantes,
  // ver API_Gestion/Modelo/prediccion_llenado.py): se pide al abrir el popup,
  // no al dibujar el mapa, para no disparar un proceso Python por cada
  // contenedor visible.
  async function cargarProyeccion(idContenedor) {
    const el = document.getElementById(`proy-${idContenedor}`);
    if (!el) return;
    try {
      const resp = await fetch(`${API.proyeccion}/${idContenedor}`, { signal: AbortSignal.timeout(6000) });
      const json = await resp.json();
      if (json.status !== 'success' || !json.data || !Array.isArray(json.data.proyeccion)) {
        throw new Error(I18N.respuestaInesperada);
      }
      const en3dias = json.data.proyeccion.find((p) => p.dias === 3) || json.data.proyeccion[0];
      if (!en3dias) {
        el.textContent = I18N.proyeccionSinDatos;
        return;
      }
      el.textContent = I18N.proyeccionTexto
        .replace('{nivel}', en3dias.nivel_proyectado)
        .replace('{dias}', en3dias.dias);
    } catch (_) {
      el.textContent = I18N.proyeccionSinDatos;
    }
  }

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
    if (vista === 'contenedores' && info && info.id_contenedor != null) {
      marker.on('popupopen', () => cargarProyeccion(info.id_contenedor));
    }
  };

  const addCamionPoint = (camion) => {
    const { latitud: lat, longitud: lon } = camion.ubicacion;
    counts.camiones.total++;
    if (camion.eta) counts.camiones.conEta++; else counts.camiones.sinEta++;

    const marker = L.marker([lat, lon], { icon: ICONO_CAMION }).addTo(grupo);

    const escapar = (t) => String(t).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    let tiempo = I18N.sinPendientes;
    if (camion.eta) {
      const min = camion.eta.minutos;
      tiempo = min < 60
        ? `≈ ${min} ${I18N.min}`
        : `≈ ${Math.floor(min / 60)} h ${String(min % 60).padStart(2, '0')} ${I18N.min}`;
    }

    let parada = '';
    if (camion.proxima_parada) {
      const p = camion.proxima_parada;
      parada = escapar(`${p.calle || I18N.sinDireccion}${p.num_calle ? ' ' + p.num_calle : ''}`);
    }

    const etiqueta = parada ? `${parada}<br><b>${tiempo}</b>` : `<b>${tiempo}</b>`;
    marker.bindTooltip(etiqueta, { permanent: true, direction: 'top', offset: [0, -14], className: 'camion-eta-etiqueta' });

    const filas = [];
    if (parada) filas.push(`<b>${I18N.popupProximaParada}</b> ${parada}`);
    filas.push(`<b>${camion.eta ? I18N.popupTiempoProximo : I18N.popupProximaParada}</b> ${tiempo}`);
    marker.bindPopup(filas.join('<br>'), { maxWidth: 260 });
    return [lat, lon];
  };

  const addIncidenciaPoint = (inc) => {
    const lat = parseFloat(inc.latitud);
    const lon = parseFloat(inc.longitud);
    if (Number.isNaN(lat) || Number.isNaN(lon)) return null;

    const color = COLORES_PRIORIDAD_INCIDENCIA[inc.prioridad] || COLORES_PRIORIDAD_INCIDENCIA.baja;
    counts.incidencias[inc.prioridad] = (counts.incidencias[inc.prioridad] || 0) + 1;

    const icono = L.divIcon({
      className: 'mapa-marcador-incidencia-wrap',
      html: `<div class="mapa-marcador-incidencia" style="background:${color}"><span>!</span></div>`,
      iconSize: [22, 22],
      iconAnchor: [11, 22]
    });
    const marker = L.marker([lat, lon], { icon: icono }).addTo(grupo);

    const filas = [`<b>${etiqueta(ETIQUETAS_TIPO_INCIDENCIA, inc.tipo)}</b>`];
    filas.push(`<b>${I18N.popupPrioridad}</b> ${etiqueta(ETIQUETAS_PRIORIDAD, inc.prioridad)}`);
    filas.push(`<b>${I18N.popupEstado}</b> ${etiqueta(ETIQUETAS_ESTADO_INCIDENCIA, inc.estado)}`);
    if (inc.calle) filas.push(`<b>${I18N.popupDireccion}</b> ${inc.calle}${inc.num_calle ? ' ' + inc.num_calle : ''}`);
    filas.push(`<b>${I18N.popupFechaApertura}</b> ${inc.fecha_apertura}`);
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
    } else if (vista === 'incidencias') {
      document.getElementById('countIncCritica').textContent = c.critica;
      document.getElementById('countIncAlta').textContent = c.alta;
      document.getElementById('countIncMedia').textContent = c.media;
      document.getElementById('countIncBaja').textContent = c.baja;
    } else {
      document.getElementById('countActivos').textContent = c.funcional;
      document.getElementById('countInactivos').textContent = c.inactivo;
      document.getElementById('countDesbordados').textContent = c.desbordado;
    }

    const hora = new Date().toLocaleTimeString(I18N_LOCALE, { hour: '2-digit', minute: '2-digit' });
    let etiquetaTotal = I18N.unidadPuntos;
    if (vista === 'centros') etiquetaTotal = I18N.unidadCentros;
    else if (vista === 'camiones') etiquetaTotal = I18N.unidadCamiones;
    else if (vista === 'incidencias') etiquetaTotal = I18N.unidadIncidencias;
    document.getElementById('mapSummaryValue').textContent = `${total} ${etiquetaTotal} · ${hora}`;
  };

  async function cargarVista(vista, silencioso) {
    vistaActual = vista;
    grupo.clearLayers();
    Object.keys(counts[vista]).forEach((k) => { counts[vista][k] = 0; });

    const indicadoresContenedores = document.getElementById('indicadores-contenedores');
    const indicadoresCentros = document.getElementById('indicadores-centros');
    const leyendaContenedores = document.getElementById('leyenda-contenedores');
    const leyendaCentros = document.getElementById('leyenda-centros');
    const indicadoresCamiones = document.getElementById('indicadores-camiones');
    const leyendaCamiones = document.getElementById('leyenda-camiones');

    if (indicadoresContenedores) indicadoresContenedores.style.display = vista === 'contenedores' ? '' : 'none';
    if (indicadoresCentros) indicadoresCentros.style.display = vista === 'centros' ? '' : 'none';
    if (leyendaContenedores) leyendaContenedores.style.display = vista === 'contenedores' ? '' : 'none';
    if (leyendaCentros) leyendaCentros.style.display = vista === 'centros' ? '' : 'none';
    if (indicadoresCamiones) indicadoresCamiones.style.display = vista === 'camiones' ? '' : 'none';
    if (leyendaCamiones) leyendaCamiones.style.display = vista === 'camiones' ? '' : 'none';
    const indicadoresIncidencias = document.getElementById('indicadores-incidencias');
    const leyendaIncidencias = document.getElementById('leyenda-incidencias');
    if (indicadoresIncidencias) indicadoresIncidencias.style.display = vista === 'incidencias' ? '' : 'none';
    if (leyendaIncidencias) leyendaIncidencias.style.display = vista === 'incidencias' ? '' : 'none';

    if (vista === 'incidencias') {
      let incidencias = [];
      try {
        const resp = await fetch(API.incidencias, { signal: AbortSignal.timeout(5000) });
        if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
        const json = await resp.json();
        incidencias = Array.isArray(json.data) ? json.data : [];
      } catch (_) {
        incidencias = [];
      }

      const bounds = [];
      for (const inc of incidencias) {
        const punto = addIncidenciaPoint(inc);
        if (punto) bounds.push(punto);
      }
      renderSummary('incidencias', bounds.length);
      if (bounds.length) map.fitBounds(bounds, { padding: [40, 40] });
      return;
    }

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
      if (bounds.length && !silencioso) map.fitBounds(bounds, { padding: [40, 40] });
      return;
    }

    let puntos = [];
    try {
      const resp = await fetch(API[vista], { signal: AbortSignal.timeout(5000) });
      if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
      const json = await resp.json();
      puntos = Array.isArray(json) ? json : json && json.data;
      if (!Array.isArray(puntos)) throw new Error(I18N.respuestaInesperada);
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

  setInterval(() => { if (vistaActual === 'camiones') cargarVista('camiones', true); }, 30000);

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
