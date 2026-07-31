<?php require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Circuitos — Recolix</title>
    <link rel="stylesheet" href="styleRecoleccion.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css">
    <style>
        .mapa-box { height: 320px; border-radius: 12px; margin-top: 10px; z-index: 0; }
        .mapa-hint { font-size: 0.85rem; color: #666; margin-top: 6px; }
        .mapa-modal-actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 14px; }
        .mapa-ruta-form { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-top: 14px; }
        .mapa-ruta-form .form-group { flex: 1; min-width: 160px; margin: 0; }
        .mapa-resultado { margin-top: 12px; font-size: 0.9rem; }
    </style>
</head>
<body>

<header class="navbar">
    <div class="nav-inner">
        <div class="nav-left">
            <div class="brand">
                <img src="logoRecolix.png" alt="Recolix" class="brand-logo">
                <span class="brand-name">Recolix</span>
            </div>
            <nav class="nav-links">
                <a href="../../Backoffice/indexBackoffice.php">Inicio</a>
                <a href="camiones.php">Camiones</a>
                <a href="#" class="nav-active">Circuitos</a>
                <a href="rutas.php">Rutas</a>
                <a href="#">Cuadrillas</a>
                <a href="../../API_Gestion/Vista/contenedores.php">Contenedores</a>
                <a href="#">Centros de Acopio</a>
                <a href="../../Backoffice/mapa.php">Mapa</a>
                <a href="#">Reportes</a>
                <a href="#">Incidencias</a>
                <a href="../../API_Usuarios/Vista/usuarios.php">Usuarios</a>
            </nav>
        </div>
        <div class="nav-right">
            <a href="/recolix/API_Usuarios/perfil.php" class="btn-user" title="Perfil de usuario">
                <img src="img/user.svg" alt="Usuario" class="nav-user-icon">
            </a>
            <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="Menú">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
    <nav class="nav-mobile" id="navMobile">
        <a href="../../Backoffice/indexBackoffice.php">Inicio</a>
        <a href="camiones.php">Camiones</a>
        <a href="#" class="nav-active">Circuitos</a>
        <a href="rutas.php">Rutas</a>
        <a href="#">Cuadrillas</a>
        <a href="../../API_Gestion/Vista/contenedores.php">Contenedores</a>
        <a href="#">Centros de Acopio</a>
        <a href="../../Backoffice/mapa.php">Mapa</a>
        <a href="#">Reportes</a>
        <a href="#">Incidencias</a>
        <a href="../../API_Usuarios/Vista/usuarios.php">Usuarios</a>
    </nav>
    <div class="nav-hr"></div>
</header>

<section class="hero">
    <h1 class="hero-title">Circuitos</h1>
    <p class="hero-sub">Gestioná los circuitos de recolección dentro de cada municipio.</p>
</section>

<section class="cards-section">
    <div class="cards-grid">

        <div class="card-operar" onclick="abrirModalAgregar()">
            <div class="card-content">
                <div class="card-icon"><img src="img/plus.svg" alt="" class="card-svg-icon"></div>
                <h3>Agregar Circuito</h3>
                <p>Registrar un nuevo circuito</p>
                <button class="card-btn">Agregar</button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleFiltros()">
            <div class="card-content">
                <div class="card-icon"><img src="img/loupe.svg" alt="" class="card-svg-icon"></div>
                <h3>Filtrar</h3>
                <p>Buscar por municipio, turno...</p>
                <button class="card-btn">Filtrar</button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleBusquedaId()">
            <div class="card-content">
                <div class="card-icon"><img src="img/question.svg" alt="" class="card-svg-icon"></div>
                <h3>Buscar por ID</h3>
                <p>Obtener un circuito específico</p>
                <button class="card-btn">Buscar</button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleModificar()">
            <div class="card-content">
                <div class="card-icon"><img src="img/refresh.svg" alt="" class="card-svg-icon"></div>
                <h3>Modificar Circuito</h3>
                <p>Editar datos de un circuito existente</p>
                <button class="card-btn">Modificar</button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleDarDeBaja()">
            <div class="card-content">
                <div class="card-icon"><img src="img/minus.svg" alt="" class="card-svg-icon"></div>
                <h3>Eliminar Circuito</h3>
                <p>Quitar un circuito del registro</p>
                <button class="card-btn">Eliminar</button>
            </div>
        </div>

    </div>
</section>

<section class="control-bar" id="filterBar" style="display:none">
    <div class="control-inner">
        <select id="filtroTipo" class="ctrl-select" onchange="actualizarFiltroValor()">
            <option value="">— Filtrar por —</option>
            <option value="municipioId">Municipio</option>
            <option value="turno">Turno</option>
            <option value="actividad">Actividad</option>
            <option value="frecuencia">Frecuencia</option>
        </select>
        <input type="text" id="filtroValor" class="ctrl-input" placeholder="Valor del filtro..." style="display:none">
        <select id="filtroValorSelect" class="ctrl-select" style="display:none"></select>
        <button class="btn-primary" onclick="aplicarFiltro()">Aplicar</button>
        <button class="btn-secondary" onclick="listarCircuitos()">Limpiar</button>
    </div>
</section>

<section class="control-bar" id="searchBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="buscarId" class="ctrl-input"
               placeholder="ID del circuito..." min="1" style="max-width:200px">
        <button class="btn-primary" onclick="buscarPorId()">Buscar</button>
        <button class="btn-secondary" onclick="listarCircuitos()">Ver Todos</button>
    </div>
</section>

<section class="control-bar" id="modificarBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="modificarId" class="ctrl-input"
               placeholder="ID del circuito..." min="1" style="max-width:120px">
        <select id="modificarCampo" class="ctrl-select" onchange="actualizarPlaceholderModificar()">
            <option value="">— Campo —</option>
            <option value="municipioId">Municipio</option>
            <option value="nombre">Nombre</option>
            <option value="codigo">Código</option>
            <option value="actividad">Actividad</option>
            <option value="turno">Turno</option>
            <option value="frecuencia">Frecuencia</option>
        </select>
        <input type="text" id="modificarValor" class="ctrl-input" style="display:none" placeholder="Seleccioná un campo...">
        <select id="modificarValorSelect" class="ctrl-select" style="display:none"></select>
        <button class="btn-primary" onclick="modificarCircuito()">Modificar</button>
        <button class="btn-secondary" onclick="ocultarControles()">Cancelar</button>
    </div>
</section>

<section class="control-bar" id="deleteBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="deleteId" class="ctrl-input"
               placeholder="ID del circuito a eliminar..." min="1" style="max-width:240px">
        <button class="btn-danger" onclick="darDeBajaCircuito()">Eliminar</button>
        <button class="btn-secondary" onclick="ocultarControles()">Cancelar</button>
    </div>
</section>

<section class="table-section">
    <div class="table-header">
        <h2 class="table-title" id="tableTitle">Circuitos</h2>
        <span class="table-count" id="tableCount"></span>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Municipio</th>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th>Actividad</th>
                    <th>Turno</th>
                    <th>Frecuencia</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="circuitosBody"></tbody>
        </table>
        <div id="noResultados" class="empty-state" style="display:none">
            Sin resultados para mostrar.
        </div>
        <div id="loadingState" class="loading-state">
            Cargando...
        </div>
    </div>
</section>

<div class="modal-overlay" id="modalForm" style="display:none"
     onclick="onOverlayClick(event,'modalForm')">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Agregar Circuito</h3>
            <button class="modal-close" onclick="cerrarModal()">✕</button>
        </div>
        <form id="circuitoForm" onsubmit="guardarCircuito(event)">
            <input type="hidden" id="circuitoId">
            <div class="form-row">
                <div class="form-group">
                    <label for="municipioId">Municipio</label>
                    <select id="municipioId" class="form-input" required>
                        <option value="">— Seleccionar —</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" class="form-input"
                           placeholder="Circuito Centro" maxlength="100" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="codigo">Código</label>
                    <input type="text" id="codigo" class="form-input"
                           placeholder="CIR-01" maxlength="20" required>
                </div>
                <div class="form-group">
                    <label for="actividad">Actividad</label>
                    <input type="text" id="actividad" class="form-input"
                           placeholder="Recolección domiciliaria" maxlength="60" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="turno">Turno</label>
                    <select id="turno" class="form-input">
                        <option value="mañana" selected>Mañana</option>
                        <option value="tarde">Tarde</option>
                        <option value="noche">Noche</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="frecuencia">Frecuencia</label>
                    <input type="text" id="frecuencia" class="form-input"
                           placeholder="Lunes, Miércoles, Viernes" maxlength="40" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label>Área del circuito (opcional)</label>
                    <div id="formMapa" class="mapa-box"></div>
                    <p class="mapa-hint">Dibujá el polígono con la herramienta de la izquierda del mapa. Podés borrarlo y volver a dibujarlo.</p>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalDelete" style="display:none"
     onclick="onOverlayClick(event,'modalDelete')">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3>Eliminar circuito</h3>
            <button class="modal-close" onclick="cerrarModalDelete()">✕</button>
        </div>
        <p class="delete-msg">
            ¿Confirmás eliminar el circuito <strong id="deleteNombre"></strong>?
        </p>
        <div class="modal-actions">
            <button class="btn-secondary" onclick="cerrarModalDelete()">Cancelar</button>
            <button class="btn-danger" onclick="confirmarDelete()">Eliminar</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalMapa" style="display:none"
     onclick="onOverlayClick(event,'modalMapa')">
    <div class="modal">
        <div class="modal-header">
            <h3 id="mapaModalTitle">Mapa del circuito</h3>
            <button class="modal-close" onclick="cerrarModalMapa()">✕</button>
        </div>
        <div id="verMapa" class="mapa-box"></div>
        <div class="mapa-modal-actions">
            <button type="button" class="btn-secondary" onclick="cargarContenedoresEnMapa()">Cargar contenedores</button>
        </div>
        <div class="mapa-ruta-form">
            <div class="form-group">
                <label for="rutaNombre">Nombre de la ruta</label>
                <input type="text" id="rutaNombre" class="form-input" placeholder="Ruta óptima" maxlength="100">
            </div>
            <div class="form-group">
                <label for="rutaActividad">Actividad</label>
                <input type="text" id="rutaActividad" class="form-input" placeholder="Recolección domiciliaria" maxlength="60">
            </div>
            <button type="button" class="btn-primary" onclick="generarRutaOptima()">Generar ruta óptima</button>
        </div>
        <div id="mapaResultado" class="mapa-resultado"></div>
    </div>
</div>

<div id="toast" class="toast"></div>

<footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <div class="footer-logo">
                <img src="logoRecolix.png" alt="Recolix" class="footer-logo-img">
                <span class="footer-logo-name">Recolix</span>
            </div>
            <p>Plataforma de gestión de residuos urbanos para Montevideo.</p>
        </div>
        <div class="footer-col">
            <h3>Navegación</h3>
            <ul>
                <li><a href="../../LandingPage/index_LandingPage.html">Página principal</a></li>
                <li><a href="../../LandingPage/mapa.html">Mapa público</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-hr"></div>
    <p class="footer-copy">&copy; 2026 Recolix. Todos los derechos reservados.</p>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
<script>
const API_BASE = '/recolix/API_Recoleccion';
let circuitoIdParaEliminar = null;
let toastTimeout;
let reglas = {};
const MUNICIPIOS = [
    { id: 1, nombre: 'Municipio A' },
    { id: 2, nombre: 'Municipio B' },
    { id: 3, nombre: 'Municipio C' },
    { id: 4, nombre: 'Municipio CH' },
    { id: 5, nombre: 'Municipio D' },
    { id: 6, nombre: 'Municipio E' },
    { id: 7, nombre: 'Municipio F' },
    { id: 8, nombre: 'Municipio G' }
];

function poblarSelectMunicipio() {
    const select = document.getElementById('municipioId');
    select.innerHTML = '<option value="">— Seleccionar —</option>' +
        MUNICIPIOS.map(m => `<option value="${m.id}">${m.nombre}</option>`).join('');
}

function nombreMunicipio(id) {
    const municipio = MUNICIPIOS.find(m => m.id === parseInt(id));
    return municipio ? municipio.nombre : id;
}

document.addEventListener('DOMContentLoaded', async () => {
    await cargarReglas();
    poblarSelectMunicipio();
    listarCircuitos();
});

function reglasFallback() {
    return {
        municipioId: { tipo: 'entero', min: 1, label: 'ID del municipio' },
        nombre:      { max: 100, label: 'Nombre' },
        codigo:      { max: 20, label: 'Código' },
        actividad:   { max: 60, label: 'Actividad' },
        turno:       { valores: ['mañana', 'tarde', 'noche'], label: 'Turno' },
        frecuencia:  { max: 40, label: 'Frecuencia' }
    };
}

async function cargarReglas() {
    try {
        const res = await fetch(`${API_BASE}/circuitos/reglasValidacion`);
        const json = await res.json();
        if (json.status === 'success') {
            reglas = json.data;
        } else {
            reglas = reglasFallback();
        }
    } catch {
        reglas = reglasFallback();
    }
}

async function listarCircuitos() {
    ocultarControles();
    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/circuitos`);
        const json = await res.json();
        if (json.status === 'success') {
            renderTabla(json.data);
            setTableTitle('Todos los Circuitos');
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast('Error al conectar con la API', 'error');
    } finally {
        setLoading(false);
    }
}

async function buscarPorId() {
    const id = document.getElementById('buscarId').value.trim();
    if (!id) { mostrarToast('Ingresá un ID', 'warning'); return; }
    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/circuitos/${id}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            renderTabla([json.data]);
            setTableTitle(`Circuito #${id}`);
        } else {
            renderTabla([]);
            mostrarToast(json.message || 'Circuito no encontrado', 'error');
        }
    } catch {
        mostrarToast('Error al conectar con la API', 'error');
    } finally {
        setLoading(false);
    }
}

function actualizarFiltroValor() {
    const tipo   = document.getElementById('filtroTipo').value;
    const input  = document.getElementById('filtroValor');
    const select = document.getElementById('filtroValorSelect');

    input.value  = '';
    select.value = '';
    input.style.display  = 'none';
    select.style.display = 'none';

    if (!tipo) return;

    if (tipo === 'municipioId') {
        select.innerHTML = '<option value="">— Seleccionar —</option>' +
            MUNICIPIOS.map(m => `<option value="${m.id}">${m.nombre}</option>`).join('');
        select.style.display = '';
        return;
    }

    const listas = {
        turno: ['mañana', 'tarde', 'noche']
    };

    if (listas[tipo]) {
        select.innerHTML = '<option value="">— Seleccionar —</option>' +
            listas[tipo].map(v => `<option value="${v}">${formatearValor(v)}</option>`).join('');
        select.style.display = '';
    } else {
        const placeholders = {
            actividad:  'ej: Recolección domiciliaria',
            frecuencia: 'ej: Lunes, Miércoles, Viernes'
        };
        input.placeholder = placeholders[tipo] || 'Valor del filtro...';
        input.style.display = '';
    }
}

async function aplicarFiltro() {
    const tipo   = document.getElementById('filtroTipo').value;
    const input  = document.getElementById('filtroValor');
    const select = document.getElementById('filtroValorSelect');
    if (!tipo) { mostrarToast('Seleccioná un filtro', 'warning'); return; }

    const valor = (select.style.display !== 'none' ? select.value : input.value).trim();
    if (!valor) {
        mostrarToast('Ingresá un valor para el filtro', 'warning');
        return;
    }

    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/circuitos?${tipo}=${encodeURIComponent(valor)}`);
        const json = await res.json();
        if (json.status === 'success') {
            renderTabla(json.data);
            const labels = {
                municipioId: `Municipio: ${nombreMunicipio(valor)}`, turno: `Turno: ${formatearValor(valor)}`,
                actividad: `Actividad: ${valor}`, frecuencia: `Frecuencia: ${valor}`
            };
            setTableTitle(`Filtro — ${labels[tipo] || tipo}`);
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast('Error al conectar con la API', 'error');
    } finally {
        setLoading(false);
    }
}

const CENTRO_MAPA = [-34.901, -56.165];

let formMap = null;
let formDrawnItems = null;

function initFormMap() {
    if (formMap) return;
    formMap = L.map('formMapa').setView(CENTRO_MAPA, 12);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(formMap);

    formDrawnItems = new L.FeatureGroup();
    formMap.addLayer(formDrawnItems);

    const drawControl = new L.Control.Draw({
        draw: {
            polygon: { allowIntersection: false, showArea: false },
            polyline: false, rectangle: false, circle: false, circlemarker: false, marker: false
        },
        edit: { featureGroup: formDrawnItems, remove: true }
    });
    formMap.addControl(drawControl);

    formMap.on(L.Draw.Event.CREATED, (e) => {
        formDrawnItems.clearLayers();
        formDrawnItems.addLayer(e.layer);
    });
}

function cargarPoligonoEnFormMap(poligonoJson) {
    formDrawnItems.clearLayers();
    if (!poligonoJson) return;
    let puntos;
    try { puntos = JSON.parse(poligonoJson); } catch { return; }
    if (!Array.isArray(puntos) || puntos.length < 3) return;
    const layer = L.polygon(puntos);
    formDrawnItems.addLayer(layer);
    formMap.fitBounds(layer.getBounds(), { padding: [20, 20] });
}

function obtenerPoligonoDelFormMap() {
    const layers = formDrawnItems.getLayers();
    if (layers.length === 0) return null;
    return layers[0].getLatLngs()[0].map(ll => [ll.lat, ll.lng]);
}

function abrirModalAgregar() {
    document.getElementById('modalTitle').textContent = 'Agregar Circuito';
    document.getElementById('circuitoId').value = '';
    document.getElementById('circuitoForm').reset();
    document.getElementById('modalForm').style.display = 'flex';
    setTimeout(() => {
        initFormMap();
        formDrawnItems.clearLayers();
        formMap.invalidateSize();
        formMap.setView(CENTRO_MAPA, 12);
    }, 100);
}

function abrirModalEditar(circuito) {
    document.getElementById('modalTitle').textContent = 'Editar Circuito';
    document.getElementById('circuitoId').value  = circuito.id_circuito;
    document.getElementById('municipioId').value = circuito.id_municipio;
    document.getElementById('nombre').value       = circuito.nombre;
    document.getElementById('codigo').value       = circuito.codigo;
    document.getElementById('actividad').value    = circuito.actividad;
    document.getElementById('frecuencia').value   = circuito.frecuencia;

    const turnoEl = document.getElementById('turno');
    turnoEl.value = circuito.turno;
    if (!turnoEl.value) turnoEl.selectedIndex = 0;

    document.getElementById('modalForm').style.display = 'flex';
    setTimeout(() => {
        initFormMap();
        formMap.invalidateSize();
        cargarPoligonoEnFormMap(circuito.poligono);
        if (!circuito.poligono) formMap.setView(CENTRO_MAPA, 12);
    }, 100);
}

async function guardarCircuito(e) {
    e.preventDefault();
    const id = document.getElementById('circuitoId').value;

    const campos = {
        municipioId: document.getElementById('municipioId').value,
        nombre:      document.getElementById('nombre').value,
        codigo:      document.getElementById('codigo').value,
        actividad:   document.getElementById('actividad').value,
        turno:       document.getElementById('turno').value,
        frecuencia:  document.getElementById('frecuencia').value
    };

    for (const [campo, valor] of Object.entries(campos)) {
        const regla = reglas[campo];
        if (!regla) continue;
        if (regla.valores && !regla.valores.includes(valor)) {
            mostrarToast(`${regla.label} no válido`, 'warning');
            return;
        }
        if (regla.max !== undefined && String(valor).length > regla.max) {
            mostrarToast(`${regla.label} no puede superar los ${regla.max} caracteres`, 'warning');
            return;
        }
        if (regla.tipo === 'entero') {
            const num = parseInt(valor);
            if (isNaN(num)) {
                mostrarToast(`${regla.label} debe ser un número entero`, 'warning');
                return;
            }
            if (regla.min !== undefined && num < regla.min) {
                mostrarToast(`${regla.label} no puede ser menor que ${regla.min}`, 'warning');
                return;
            }
        }
    }

    const body = {
        municipioId: parseInt(campos.municipioId),
        nombre:      campos.nombre,
        codigo:      campos.codigo,
        actividad:   campos.actividad,
        turno:       campos.turno,
        frecuencia:  campos.frecuencia,
        poligono:    obtenerPoligonoDelFormMap()
    };

    try {
        const res = await fetch(
            id ? `${API_BASE}/circuitos/${id}` : `${API_BASE}/circuitos`,
            {
                method:  id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(body)
            }
        );
        const json = await res.json();
        if (json.status === 'success') {
            mostrarToast(json.message, 'success');
            cerrarModal();
            listarCircuitos();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast('Error al guardar', 'error');
    }
}

function confirmarBorrar(id, nombre) {
    circuitoIdParaEliminar = id;
    document.getElementById('deleteNombre').textContent = nombre;
    document.getElementById('modalDelete').style.display = 'flex';
}

async function confirmarDelete() {
    try {
        const res  = await fetch(`${API_BASE}/circuitos/${circuitoIdParaEliminar}`, { method: 'DELETE' });
        const json = await res.json();
        if (json.status === 'success') {
            mostrarToast('Circuito eliminado correctamente', 'success');
            cerrarModalDelete();
            listarCircuitos();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast('Error al eliminar', 'error');
    }
}

function actualizarPlaceholderModificar() {
    const campo = document.getElementById('modificarCampo').value;
    const input = document.getElementById('modificarValor');
    const select = document.getElementById('modificarValorSelect');

    input.value = '';
    select.value = '';
    input.style.display = 'none';
    select.style.display = 'none';

    if (campo === 'municipioId') {
        select.innerHTML = MUNICIPIOS.map((m, i) =>
            `<option value="${m.id}"${i === 0 ? ' selected' : ''}>${m.nombre}</option>`
        ).join('');
        select.style.display = '';
        return;
    }

    const listas = {
        turno: ['mañana', 'tarde', 'noche']
    };

    if (listas[campo]) {
        select.innerHTML = listas[campo].map((v, i) =>
            `<option value="${v}"${i === 0 ? ' selected' : ''}>${formatearValor(v)}</option>`
        ).join('');
        select.style.display = '';
    } else {
        const placeholders = {
            nombre:     'Circuito Centro',
            codigo:     'CIR-01',
            actividad:  'Recolección domiciliaria',
            frecuencia: 'Lunes, Miércoles, Viernes'
        };
        const maxlengths = {
            nombre: 100, codigo: 20, actividad: 60, frecuencia: 40
        };
        input.placeholder = placeholders[campo] || 'Seleccioná un campo...';
        input.maxLength = maxlengths[campo] || '';
        input.style.display = '';
    }
}

async function modificarCircuito() {
    const id = document.getElementById('modificarId').value.trim();
    if (!id) { mostrarToast('Ingresá un ID', 'warning'); return; }
    const campo = document.getElementById('modificarCampo').value;
    if (!campo) { mostrarToast('Seleccioná un campo a modificar', 'warning'); return; }

    const input = document.getElementById('modificarValor');
    const select = document.getElementById('modificarValorSelect');
    const valor = (select.style.display !== 'none' ? select : input).value.trim();
    if (!valor) { mostrarToast('Ingresá el nuevo valor', 'warning'); return; }

    const regla = reglas[campo];
    if (regla) {
        if (regla.valores && !regla.valores.includes(valor)) {
            mostrarToast(`${regla.label} no válido`, 'warning');
            return;
        }
        if (regla.max !== undefined && valor.length > regla.max) {
            mostrarToast(`${regla.label} no puede superar los ${regla.max} caracteres`, 'warning');
            return;
        }
        if (regla.tipo === 'entero') {
            const num = parseInt(valor);
            if (isNaN(num)) {
                mostrarToast(`${regla.label} debe ser un número entero`, 'warning');
                return;
            }
            if (regla.min !== undefined && num < regla.min) {
                mostrarToast(`${regla.label} no puede ser menor que ${regla.min}`, 'warning');
                return;
            }
        }
    }

    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/circuitos/${id}`);
        const json = await res.json();
        if (json.status !== 'success' || !json.data) {
            mostrarToast(json.message || 'Circuito no encontrado', 'error');
            return;
        }
        const circuito = json.data;

        const campoMap = {
            municipioId: 'id_municipio',
            nombre:      'nombre',
            codigo:      'codigo',
            actividad:   'actividad',
            turno:       'turno',
            frecuencia:  'frecuencia'
        };

        circuito[campoMap[campo]] = valor;

        const putRes = await fetch(`${API_BASE}/circuitos/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                municipioId: parseInt(circuito.id_municipio),
                nombre:      circuito.nombre,
                codigo:      circuito.codigo,
                actividad:   circuito.actividad,
                turno:       circuito.turno,
                frecuencia:  circuito.frecuencia
            })
        });
        const putJson = await putRes.json();
        if (putJson.status === 'success') {
            mostrarToast(putJson.message, 'success');
            ocultarControles();
            listarCircuitos();
        } else {
            mostrarToast(putJson.message, 'error');
        }
    } catch {
        mostrarToast('Error al modificar', 'error');
    } finally {
        setLoading(false);
    }
}

async function darDeBajaCircuito() {
    const id = document.getElementById('deleteId').value.trim();
    if (!id) { mostrarToast('Ingresá un ID', 'warning'); return; }
    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/circuitos/${id}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            confirmarBorrar(json.data.id_circuito, json.data.nombre);
            ocultarControles();
        } else {
            mostrarToast(json.message || 'Circuito no encontrado', 'error');
        }
    } catch {
        mostrarToast('Error al conectar con la API', 'error');
    } finally {
        setLoading(false);
    }
}

function renderTabla(circuitos) {
    const tbody = document.getElementById('circuitosBody');
    const noRes = document.getElementById('noResultados');
    const count = document.getElementById('tableCount');

    tbody.innerHTML = '';

    if (!circuitos || circuitos.length === 0) {
        noRes.style.display = 'flex';
        count.textContent   = '';
        return;
    }

    noRes.style.display = 'none';
    count.textContent   = `${circuitos.length} resultado${circuitos.length !== 1 ? 's' : ''}`;

    circuitos.forEach(c => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="td-id" data-label="ID">${c.id_circuito}</td>
            <td data-label="Municipio">${nombreMunicipio(c.id_municipio)}</td>
            <td data-label="Nombre"><strong>${c.nombre}</strong></td>
            <td data-label="Código">${c.codigo ?? ''}</td>
            <td data-label="Actividad">${c.actividad ?? ''}</td>
            <td data-label="Turno"><span class="badge badge-${badgeClass(c.turno)}">${formatearValor(c.turno ?? '')}</span></td>
            <td data-label="Frecuencia">${c.frecuencia ?? ''}</td>
            <td class="td-actions" data-label="Acciones">
                <button class="btn-action" onclick='abrirModalEditar(${JSON.stringify(c)})' title="Editar">✏️</button>
                <button class="btn-action" onclick='abrirModalMapa(${JSON.stringify(c)})' title="Mapa y ruta óptima">🗺️</button>
                <button class="btn-action" onclick="confirmarBorrar(${c.id_circuito},'${c.nombre}')" title="Eliminar">🗑️</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function formatearValor(v) {
    return v.replace(/_/g, ' ');
}

function badgeClass(turno) {
    const map = { 'mañana': 'info', 'tarde': 'warning', 'noche': 'neutral' };
    return map[turno] ?? 'neutral';
}

function toggleFiltros() {
    const fb = document.getElementById('filterBar');
    const sb = document.getElementById('searchBar');
    const mb = document.getElementById('modificarBar');
    const db = document.getElementById('deleteBar');
    sb.style.display = 'none';
    mb.style.display = 'none';
    db.style.display = 'none';
    fb.style.display = fb.style.display === 'none' ? 'block' : 'none';
}

function toggleBusquedaId() {
    const sb = document.getElementById('searchBar');
    const fb = document.getElementById('filterBar');
    const mb = document.getElementById('modificarBar');
    const db = document.getElementById('deleteBar');
    fb.style.display = 'none';
    mb.style.display = 'none';
    db.style.display = 'none';
    sb.style.display = sb.style.display === 'none' ? 'block' : 'none';
}

function ocultarControles() {
    document.getElementById('filterBar').style.display = 'none';
    document.getElementById('searchBar').style.display = 'none';
    document.getElementById('modificarBar').style.display = 'none';
    document.getElementById('deleteBar').style.display = 'none';
}

function toggleModificar() {
    const mb = document.getElementById('modificarBar');
    const fb = document.getElementById('filterBar');
    const sb = document.getElementById('searchBar');
    const db = document.getElementById('deleteBar');
    fb.style.display = 'none';
    sb.style.display = 'none';
    db.style.display = 'none';
    mb.style.display = mb.style.display === 'none' ? 'block' : 'none';
}

function toggleDarDeBaja() {
    const db = document.getElementById('deleteBar');
    const fb = document.getElementById('filterBar');
    const sb = document.getElementById('searchBar');
    const mb = document.getElementById('modificarBar');
    fb.style.display = 'none';
    sb.style.display = 'none';
    mb.style.display = 'none';
    db.style.display = db.style.display === 'none' ? 'block' : 'none';
}

function cerrarModal() {
    document.getElementById('modalForm').style.display = 'none';
}

function cerrarModalDelete() {
    document.getElementById('modalDelete').style.display = 'none';
    circuitoIdParaEliminar = null;
}

function onOverlayClick(e, modalId) {
    if (e.target.id !== modalId) return;
    if (modalId === 'modalForm') cerrarModal();
    else if (modalId === 'modalMapa') cerrarModalMapa();
    else cerrarModalDelete();
}

function setTableTitle(title) {
    document.getElementById('tableTitle').textContent = title;
}

function setLoading(show) {
    document.getElementById('loadingState').style.display = show ? 'flex' : 'none';
    document.getElementById('circuitosBody').style.opacity = show ? '0.3' : '1';
}

function toggleMobileMenu() {
    document.getElementById('navMobile').classList.toggle('nav-mobile-open');
}

let verMap = null;
let verMapaCapaPoligono = null;
let verMapaMarkers = [];
let verMapaPolyline = null;
let circuitoActualMapa = null;

const COLORES_CONTENEDOR = {
    funcional: '#4CAF50',
    inactivo: '#e3b23c',
    desbordado: '#c0392b',
    roto: '#2b2b2b',
    en_deposito: '#4a5568'
};

function initVerMap() {
    if (verMap) return;
    verMap = L.map('verMapa').setView(CENTRO_MAPA, 12);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(verMap);
}

function limpiarCapasVerMapa() {
    if (verMapaCapaPoligono) { verMap.removeLayer(verMapaCapaPoligono); verMapaCapaPoligono = null; }
    verMapaMarkers.forEach(m => verMap.removeLayer(m));
    verMapaMarkers = [];
    if (verMapaPolyline) { verMap.removeLayer(verMapaPolyline); verMapaPolyline = null; }
}

function dibujarPoligonoVerMapa(poligonoJson) {
    if (!poligonoJson) return;
    let puntos;
    try { puntos = JSON.parse(poligonoJson); } catch { return; }
    if (!Array.isArray(puntos) || puntos.length < 3) return;
    verMapaCapaPoligono = L.polygon(puntos, { color: '#2e7d32', weight: 2, fillOpacity: 0.08 }).addTo(verMap);
    verMap.fitBounds(verMapaCapaPoligono.getBounds(), { padding: [20, 20] });
}

function abrirModalMapa(circuito) {
    circuitoActualMapa = circuito;
    document.getElementById('mapaModalTitle').textContent = `Mapa — ${circuito.nombre}`;
    document.getElementById('rutaNombre').value = `Ruta óptima ${circuito.nombre}`;
    document.getElementById('rutaActividad').value = circuito.actividad || '';
    document.getElementById('mapaResultado').innerHTML = '';
    document.getElementById('modalMapa').style.display = 'flex';
    setTimeout(() => {
        initVerMap();
        limpiarCapasVerMapa();
        dibujarPoligonoVerMapa(circuito.poligono);
        verMap.invalidateSize();
        if (!circuito.poligono) verMap.setView(CENTRO_MAPA, 12);
    }, 100);
}

function cerrarModalMapa() {
    document.getElementById('modalMapa').style.display = 'none';
    circuitoActualMapa = null;
}

async function cargarContenedoresEnMapa() {
    if (!circuitoActualMapa) return;
    try {
        const res  = await fetch(`${API_BASE}/circuitos/${circuitoActualMapa.id_circuito}/obtenerContenedores`);
        const json = await res.json();
        if (json.status !== 'success') {
            mostrarToast(json.message || 'No se pudieron cargar los contenedores', 'error');
            return;
        }

        verMapaMarkers.forEach(m => verMap.removeLayer(m));
        verMapaMarkers = [];

        const bounds = [];
        (json.data || []).forEach(c => {
            const lat = parseFloat(c.latitud);
            const lon = parseFloat(c.longitud);
            if (Number.isNaN(lat) || Number.isNaN(lon)) return;
            const color = COLORES_CONTENEDOR[c.estado] || COLORES_CONTENEDOR.funcional;
            const marker = L.circleMarker([lat, lon], {
                radius: 6, color: '#fff', weight: 1.4, opacity: 0.7,
                fillColor: color, fillOpacity: 0.9
            }).bindPopup(`<b>#${c.id_contenedor}</b> ${c.calle ?? ''} ${c.num_calle ?? ''}<br>Llenado: ${c.nivel_llenado}%<br>Estado: ${c.estado}`);
            marker.addTo(verMap);
            verMapaMarkers.push(marker);
            bounds.push([lat, lon]);
        });

        if (bounds.length && !verMapaCapaPoligono) {
            verMap.fitBounds(bounds, { padding: [30, 30] });
        }
        mostrarToast(`${verMapaMarkers.length} contenedores cargados`, 'success');
    } catch {
        mostrarToast('Error al cargar los contenedores', 'error');
    }
}

async function generarRutaOptima() {
    if (!circuitoActualMapa) return;
    const nombre = document.getElementById('rutaNombre').value.trim();
    const actividad = document.getElementById('rutaActividad').value.trim();
    if (!nombre || !actividad) {
        mostrarToast('Completá nombre y actividad de la ruta', 'warning');
        return;
    }

    try {
        const res = await fetch(`${API_BASE}/rutas/generarOptima`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ circuitoId: circuitoActualMapa.id_circuito, nombre, actividad })
        });
        const json = await res.json();
        if (json.status !== 'success') {
            mostrarToast(json.message || 'No se pudo generar la ruta', 'error');
            return;
        }

        const { ruta, contenedores, distancia_total_km } = json.data;
        if (verMapaPolyline) { verMap.removeLayer(verMapaPolyline); verMapaPolyline = null; }

        const puntosRuta = (contenedores || [])
            .map(c => [parseFloat(c.latitud), parseFloat(c.longitud)])
            .filter(([lat, lon]) => !Number.isNaN(lat) && !Number.isNaN(lon));

        if (puntosRuta.length > 1) {
            verMapaPolyline = L.polyline(puntosRuta, { color: '#c0392b', weight: 4 }).addTo(verMap);
            verMap.fitBounds(verMapaPolyline.getBounds(), { padding: [30, 30] });
        }

        document.getElementById('mapaResultado').innerHTML =
            `Ruta <strong>#${ruta.id_ruta}</strong> generada — ${contenedores.length} contenedores, ${distancia_total_km} km aprox.`;
        mostrarToast('Ruta óptima generada', 'success');
    } catch {
        mostrarToast('Error al generar la ruta óptima', 'error');
    }
}

function mostrarToast(msg, tipo = 'info') {
    const toast = document.getElementById('toast');
    clearTimeout(toastTimeout);
    toast.textContent = msg;
    toast.className = `toast toast-${tipo} toast-show`;
    toastTimeout = setTimeout(() => toast.classList.remove('toast-show'), 3500);
}
</script>

</body>
</html>
