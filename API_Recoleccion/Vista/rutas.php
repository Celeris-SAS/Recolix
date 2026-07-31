<?php require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rutas — Recolix</title>
    <link rel="stylesheet" href="styleRecoleccion.css">
    <style>
        .resumen-section {
            padding: 20px 32px 8px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }
        .resumen-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px 22px;
        }
        .resumen-card-head { display: flex; align-items: center; gap: 9px; margin-bottom: 2px; }
        .resumen-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
        .resumen-dot.activas     { background: var(--status-ok); }
        .resumen-dot.pendientes  { background: var(--status-warn); }
        .resumen-dot.prioritarias{ background: var(--status-crit); }
        .resumen-card h3 {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .resumen-count { font-size: 2rem; font-weight: 800; line-height: 1; color: var(--text); margin: 6px 0 16px; }
        .resumen-hint { font-size: 0.78rem; color: var(--muted); margin: 4px 0 16px; line-height: 1.4; }
        .resumen-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 10px; max-height: 230px; overflow-y: auto; }
        .resumen-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            font-size: 0.85rem;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--bg2);
        }
        .resumen-item:last-child { border-bottom: none; padding-bottom: 0; }
        .resumen-item strong { color: var(--text); }
        .resumen-item small { color: var(--muted); display: block; margin-top: 2px; font-size: 0.76rem; }
        .resumen-llenado { font-weight: 700; color: var(--moss-dark); white-space: nowrap; font-size: 0.85rem; }
        .resumen-empty { color: var(--muted); font-size: 0.85rem; font-style: italic; }

        @media (max-width: 1024px) { .resumen-section { padding: 16px 20px 4px; } }
        @media (max-width: 768px)  { .resumen-section { grid-template-columns: 1fr; padding: 16px 16px 4px; gap: 14px; } }
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
                <a href="circuitos.php">Circuitos</a>
                <a href="#" class="nav-active">Rutas</a>
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
        <a href="circuitos.php">Circuitos</a>
        <a href="#" class="nav-active">Rutas</a>
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
    <h1 class="hero-title">Rutas</h1>
    <p class="hero-sub">Las rutas se generan automáticamente desde cada circuito según el nivel de llenado de sus contenedores.</p>
</section>

<section class="resumen-section">
    <div class="resumen-card">
        <div class="resumen-card-head"><span class="resumen-dot activas"></span><h3>Rutas activas</h3></div>
        <div class="resumen-count" id="countActivas">—</div>
        <ul class="resumen-list" id="listaActivas"></ul>
    </div>
    <div class="resumen-card">
        <div class="resumen-card-head"><span class="resumen-dot pendientes"></span><h3>Rutas pendientes</h3></div>
        <div class="resumen-count" id="countPendientes">—</div>
        <ul class="resumen-list" id="listaPendientes"></ul>
    </div>
    <div class="resumen-card">
        <div class="resumen-card-head"><span class="resumen-dot prioritarias"></span><h3>Rutas prioritarias</h3></div>
        <p class="resumen-hint">Ordenadas por nivel de llenado promedio de sus contenedores</p>
        <ul class="resumen-list" id="listaPrioritarias"></ul>
    </div>
</section>

<section class="cards-section">
    <div class="cards-grid">

        <div class="card-operar" onclick="toggleFiltros()">
            <div class="card-content">
                <div class="card-icon"><img src="img/loupe.svg" alt="" class="card-svg-icon"></div>
                <h3>Filtrar</h3>
                <p>Buscar por circuito, nombre...</p>
                <button class="card-btn">Filtrar</button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleBusquedaId()">
            <div class="card-content">
                <div class="card-icon"><img src="img/question.svg" alt="" class="card-svg-icon"></div>
                <h3>Buscar por ID</h3>
                <p>Obtener una ruta específica</p>
                <button class="card-btn">Buscar</button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleDarDeBaja()">
            <div class="card-content">
                <div class="card-icon"><img src="img/minus.svg" alt="" class="card-svg-icon"></div>
                <h3>Eliminar Ruta</h3>
                <p>Quitar una ruta del registro</p>
                <button class="card-btn">Eliminar</button>
            </div>
        </div>

    </div>
</section>

<section class="control-bar" id="filterBar" style="display:none">
    <div class="control-inner">
        <select id="filtroTipo" class="ctrl-select" onchange="actualizarFiltroValor()">
            <option value="">— Filtrar por —</option>
            <option value="circuitoId">Circuito (ID)</option>
            <option value="actividad">Actividad</option>
            <option value="nombre">Nombre</option>
        </select>
        <input type="text" id="filtroValor" class="ctrl-input" placeholder="Valor del filtro..." style="display:none">
        <select id="filtroValorSelect" class="ctrl-select" style="display:none"></select>
        <button class="btn-primary" onclick="aplicarFiltro()">Aplicar</button>
        <button class="btn-secondary" onclick="listarRutas()">Limpiar</button>
    </div>
</section>

<section class="control-bar" id="searchBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="buscarId" class="ctrl-input"
               placeholder="ID de la ruta..." min="1" style="max-width:200px">
        <button class="btn-primary" onclick="buscarPorId()">Buscar</button>
        <button class="btn-secondary" onclick="listarRutas()">Ver Todas</button>
    </div>
</section>

<section class="control-bar" id="deleteBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="deleteId" class="ctrl-input"
               placeholder="ID de la ruta a eliminar..." min="1" style="max-width:240px">
        <button class="btn-danger" onclick="darDeBajaRuta()">Eliminar</button>
        <button class="btn-secondary" onclick="ocultarControles()">Cancelar</button>
    </div>
</section>

<section class="table-section">
    <div class="table-header">
        <h2 class="table-title" id="tableTitle">Rutas</h2>
        <span class="table-count" id="tableCount"></span>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Circuito</th>
                    <th>Nombre</th>
                    <th>Actividad</th>
                    <th>Estado</th>
                    <th>Llenado prom.</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="rutasBody"></tbody>
        </table>
        <div id="noResultados" class="empty-state" style="display:none">
            Sin resultados para mostrar.
        </div>
        <div id="loadingState" class="loading-state">
            Cargando...
        </div>
    </div>
</section>

<div class="modal-overlay" id="modalDelete" style="display:none"
     onclick="onOverlayClick(event,'modalDelete')">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3>Eliminar ruta</h3>
            <button class="modal-close" onclick="cerrarModalDelete()">✕</button>
        </div>
        <p class="delete-msg">
            ¿Confirmás eliminar la ruta <strong id="deleteNombre"></strong>?
        </p>
        <div class="modal-actions">
            <button class="btn-secondary" onclick="cerrarModalDelete()">Cancelar</button>
            <button class="btn-danger" onclick="confirmarDelete()">Eliminar</button>
        </div>
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

<script>
const API_BASE = '/recolix/API_Recoleccion';
let rutaIdParaEliminar = null;
let toastTimeout;
let reglas = {};

document.addEventListener('DOMContentLoaded', async () => {
    await cargarReglas();
    listarRutas();
});

function reglasFallback() {
    return {
        circuitoId: { tipo: 'entero', min: 1, label: 'ID del circuito' },
        nombre:     { max: 100, label: 'Nombre' },
        actividad:  { max: 60, label: 'Actividad' }
    };
}

async function cargarReglas() {
    try {
        const res = await fetch(`${API_BASE}/rutas/reglasValidacion`);
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

async function cargarResumen() {
    try {
        const res  = await fetch(`${API_BASE}/rutas/obtenerResumen`);
        const json = await res.json();
        if (json.status !== 'success') return [];

        const rutas = json.data || [];
        renderResumenCard('listaActivas', 'countActivas', rutas.filter(r => r.estado === 'activa'));

        const pendientes = rutas.filter(r => r.estado === 'pendiente');
        renderResumenCard('listaPendientes', 'countPendientes', pendientes);

        const prioritarias = rutas
            .filter(r => r.llenado_promedio !== null)
            .sort((a, b) => b.llenado_promedio - a.llenado_promedio)
            .slice(0, 5);
        renderResumenCard('listaPrioritarias', null, prioritarias);

        return rutas;
    } catch {
        return [];
    }
}

function renderResumenCard(listaId, countId, rutas) {
    if (countId) document.getElementById(countId).textContent = rutas.length;
    const ul = document.getElementById(listaId);
    if (!rutas.length) {
        ul.innerHTML = '<li class="resumen-empty">Sin rutas para mostrar.</li>';
        return;
    }
    ul.innerHTML = rutas.map(r => `
        <li class="resumen-item">
            <span><strong>#${r.id_ruta}</strong> ${r.nombre}<br><small>Circuito ${r.id_circuito} · ${r.contenedores_count} contenedor${r.contenedores_count !== 1 ? 'es' : ''}</small></span>
            <span class="resumen-llenado">${r.llenado_promedio !== null ? r.llenado_promedio + '%' : '—'}</span>
        </li>
    `).join('');
}

async function listarRutas() {
    ocultarControles();
    setLoading(true);
    try {
        const rutas = await cargarResumen();
        renderTabla(rutas || []);
        setTableTitle('Todas las Rutas');
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
        const res  = await fetch(`${API_BASE}/rutas/${id}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            renderTabla([json.data]);
            setTableTitle(`Ruta #${id}`);
        } else {
            renderTabla([]);
            mostrarToast(json.message || 'Ruta no encontrada', 'error');
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

    const placeholders = {
        circuitoId: 'ej: 1',
        actividad:  'ej: Recolección domiciliaria',
        nombre:     'ej: Ruta Norte'
    };
    input.placeholder = placeholders[tipo] || 'Valor del filtro...';
    input.style.display = '';
}

async function aplicarFiltro() {
    const tipo  = document.getElementById('filtroTipo').value;
    const input = document.getElementById('filtroValor');
    if (!tipo) { mostrarToast('Seleccioná un filtro', 'warning'); return; }

    const valor = input.value.trim();
    if (!valor) {
        mostrarToast('Ingresá un valor para el filtro', 'warning');
        return;
    }

    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/rutas?${tipo}=${encodeURIComponent(valor)}`);
        const json = await res.json();
        if (json.status === 'success') {
            renderTabla(json.data);
            const labels = {
                circuitoId: `Circuito: ${valor}`, actividad: `Actividad: ${valor}`, nombre: `Nombre: ${valor}`
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

function confirmarBorrar(id, nombre) {
    rutaIdParaEliminar = id;
    document.getElementById('deleteNombre').textContent = nombre;
    document.getElementById('modalDelete').style.display = 'flex';
}

async function confirmarDelete() {
    try {
        const res  = await fetch(`${API_BASE}/rutas/${rutaIdParaEliminar}`, { method: 'DELETE' });
        const json = await res.json();
        if (json.status === 'success') {
            mostrarToast('Ruta eliminada correctamente', 'success');
            cerrarModalDelete();
            listarRutas();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast('Error al eliminar', 'error');
    }
}

async function darDeBajaRuta() {
    const id = document.getElementById('deleteId').value.trim();
    if (!id) { mostrarToast('Ingresá un ID', 'warning'); return; }
    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/rutas/${id}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            confirmarBorrar(json.data.id_ruta, json.data.nombre);
            ocultarControles();
        } else {
            mostrarToast(json.message || 'Ruta no encontrada', 'error');
        }
    } catch {
        mostrarToast('Error al conectar con la API', 'error');
    } finally {
        setLoading(false);
    }
}

function renderTabla(rutas) {
    const tbody = document.getElementById('rutasBody');
    const noRes = document.getElementById('noResultados');
    const count = document.getElementById('tableCount');

    tbody.innerHTML = '';

    if (!rutas || rutas.length === 0) {
        noRes.style.display = 'flex';
        count.textContent   = '';
        return;
    }

    noRes.style.display = 'none';
    count.textContent   = `${rutas.length} resultado${rutas.length !== 1 ? 's' : ''}`;

    const badges = { activa: 'info', pendiente: 'warning', finalizada: 'neutral' };

    rutas.forEach(r => {
        const tr = document.createElement('tr');
        const estadoBadge = r.estado
            ? `<span class="badge badge-${badges[r.estado] ?? 'neutral'}">${r.estado}</span>`
            : '—';
        tr.innerHTML = `
            <td class="td-id" data-label="ID">${r.id_ruta}</td>
            <td data-label="Circuito">${r.id_circuito}</td>
            <td data-label="Nombre"><strong>${r.nombre}</strong></td>
            <td data-label="Actividad">${r.actividad ?? ''}</td>
            <td data-label="Estado">${estadoBadge}</td>
            <td data-label="Llenado prom.">${r.llenado_promedio !== undefined && r.llenado_promedio !== null ? r.llenado_promedio + '%' : '—'}</td>
            <td class="td-actions" data-label="Acciones">
                <button class="btn-action" onclick="confirmarBorrar(${r.id_ruta},'${r.nombre}')" title="Eliminar">🗑️</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function toggleFiltros() {
    const fb = document.getElementById('filterBar');
    const sb = document.getElementById('searchBar');
    const db = document.getElementById('deleteBar');
    sb.style.display = 'none';
    db.style.display = 'none';
    fb.style.display = fb.style.display === 'none' ? 'block' : 'none';
}

function toggleBusquedaId() {
    const sb = document.getElementById('searchBar');
    const fb = document.getElementById('filterBar');
    const db = document.getElementById('deleteBar');
    fb.style.display = 'none';
    db.style.display = 'none';
    sb.style.display = sb.style.display === 'none' ? 'block' : 'none';
}

function ocultarControles() {
    document.getElementById('filterBar').style.display = 'none';
    document.getElementById('searchBar').style.display = 'none';
    document.getElementById('deleteBar').style.display = 'none';
}

function toggleDarDeBaja() {
    const db = document.getElementById('deleteBar');
    const fb = document.getElementById('filterBar');
    const sb = document.getElementById('searchBar');
    fb.style.display = 'none';
    sb.style.display = 'none';
    db.style.display = db.style.display === 'none' ? 'block' : 'none';
}

function cerrarModalDelete() {
    document.getElementById('modalDelete').style.display = 'none';
    rutaIdParaEliminar = null;
}

function onOverlayClick(e, modalId) {
    if (e.target.id === modalId) {
        cerrarModalDelete();
    }
}

function setTableTitle(title) {
    document.getElementById('tableTitle').textContent = title;
}

function setLoading(show) {
    document.getElementById('loadingState').style.display = show ? 'flex' : 'none';
    document.getElementById('rutasBody').style.opacity = show ? '0.3' : '1';
}

function toggleMobileMenu() {
    document.getElementById('navMobile').classList.toggle('nav-mobile-open');
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
