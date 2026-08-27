<?php require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuadrillas — Recolix</title>
    <link rel="stylesheet" href="styleCuadrillas.css">
    <link rel="stylesheet" href="../../Backoffice/styleBackoffice.css">
</head>
<body>

<!-- ── BARRA DE NAVEGACIÓN ─────────────────────────────────────────── -->
<header class="navbar">
    <div class="nav-inner">
        <div class="nav-left">
            <a href="../../Backoffice/indexBackoffice.php" class="brand">
                <img src="../../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="brand-logo">
                <span class="brand-name">Recolix</span>
            </a>
            <nav class="nav-links">
                <a href="../../Backoffice/indexBackoffice.php">Inicio</a>
                <a href="../../API_Recoleccion/Vista/camiones.php">Camiones</a>
                <a href="../../API_Recoleccion/Vista/circuitos.php">Circuitos</a>
                <a href="../../API_Recoleccion/Vista/rutas.php">Rutas</a>
                <a href="#" class="nav-active">Cuadrillas</a>
                <a href="../../API_Gestion/Vista/contenedores.php">Contenedores</a>
                <a href="../../API_Gestion/Vista/centrosAcopio.php">Centros de Acopio</a>
                <a href="../../Backoffice/mapa.php">Mapa</a>
                <a href="#">Reportes</a>
                <a href="#">Incidencias</a>
                <a href="usuarios.php">Usuarios</a>
            </nav>
        </div>
        <div class="nav-right">
            <a href="../perfil.php" class="btn-user" title="Perfil de usuario">
                <img src="../../API_Recoleccion/Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
            </a>
            <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="Menú">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
    <nav class="nav-mobile" id="navMobile">
        <a href="../../Backoffice/indexBackoffice.php">Inicio</a>
        <a href="../../API_Recoleccion/Vista/camiones.php">Camiones</a>
        <a href="../../API_Recoleccion/Vista/circuitos.php">Circuitos</a>
        <a href="../../API_Recoleccion/Vista/rutas.php">Rutas</a>
        <a href="#" class="nav-active">Cuadrillas</a>
        <a href="../../API_Gestion/Vista/contenedores.php">Contenedores</a>
        <a href="../../API_Gestion/Vista/centrosAcopio.php">Centros de Acopio</a>
        <a href="../../Backoffice/mapa.php">Mapa</a>
        <a href="#">Reportes</a>
        <a href="#">Incidencias</a>
        <a href="usuarios.php">Usuarios</a>
    </nav>
    <div class="nav-hr"></div>
</header>

<!-- ── HERO (ENCABEZADO) ───────────────────────────────────────────── -->
<section class="hero">
    <h1 class="hero-title">Cuadrillas</h1>
    <p class="hero-sub">Gestioná las cuadrillas de recolección de residuos.</p>
</section>

<!-- ── TARJETAS ─────────────────────────────────────────────────────── -->
<section class="cards-section">
    <div class="cards-grid">

        <div class="card-operar" onclick="abrirModalAgregar()">
            <div class="card-content">
                <div class="card-icon"><img src="img/plus.svg" alt="" class="card-svg-icon"></div>
                <h3>Agregar Cuadrilla</h3>
                <p>Registrar una nueva cuadrilla</p>
                <button class="card-btn">Agregar</button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleFiltros()">
            <div class="card-content">
                <div class="card-icon"><img src="img/loupe.svg" alt="" class="card-svg-icon"></div>
                <h3>Filtrar</h3>
                <p>Buscar por estado o municipio...</p>
                <button class="card-btn">Filtrar</button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleBusquedaId()">
            <div class="card-content">
                <div class="card-icon"><img src="img/question.svg" alt="" class="card-svg-icon"></div>
                <h3>Buscar por ID</h3>
                <p>Obtener una cuadrilla específica</p>
                <button class="card-btn">Buscar</button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleModificar()">
            <div class="card-content">
                <div class="card-icon"><img src="img/refresh.svg" alt="" class="card-svg-icon"></div>
                <h3>Modificar Cuadrilla</h3>
                <p>Editar datos de una cuadrilla existente</p>
                <button class="card-btn">Modificar</button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleDarDeBaja()">
            <div class="card-content">
                <div class="card-icon"><img src="img/minus.svg" alt="" class="card-svg-icon"></div>
                <h3>Dar de Baja</h3>
                <p>Desactivar una cuadrilla del registro</p>
                <button class="card-btn">Dar de Baja</button>
            </div>
        </div>

    </div>
</section>

<!-- ── BARRA DE FILTROS ─────────────────────────────────────────────── -->
<section class="control-bar" id="filterBar" style="display:none">
    <div class="control-inner">
        <select id="filtroTipo" class="ctrl-select" onchange="actualizarFiltroValor()">
            <option value="">— Filtrar por —</option>
            <option value="estado">Estado</option>
            <option value="municipio">Municipio</option>
            <option value="disponibilidad">Activas</option>
        </select>
        <input type="text" id="filtroValor" class="ctrl-input" placeholder="Valor del filtro..." style="display:none">
        <select id="filtroValorSelect" class="ctrl-select" style="display:none"></select>
        <button class="btn-primary" onclick="aplicarFiltro()">Aplicar</button>
        <button class="btn-secondary" onclick="listarCuadrillas()">Limpiar</button>
    </div>
</section>

<!-- ── BARRA DE BÚSQUEDA POR ID ─────────────────────────────────────── -->
<section class="control-bar" id="searchBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="buscarId" class="ctrl-input"
               placeholder="ID de la cuadrilla..." min="1" style="max-width:200px">
        <button class="btn-primary" onclick="buscarPorId()">Buscar</button>
        <button class="btn-secondary" onclick="listarCuadrillas()">Ver Todas</button>
    </div>
</section>

<!-- ── BARRA DE MODIFICACIÓN ──────────────────────────────────────── -->
<section class="control-bar" id="modificarBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="modificarId" class="ctrl-input"
               placeholder="ID de la cuadrilla..." min="1" style="max-width:140px">
        <select id="modificarCampo" class="ctrl-select" onchange="actualizarPlaceholderModificar()">
            <option value="">— Campo —</option>
            <option value="municipio">Municipio</option>
            <option value="estado">Estado</option>
        </select>
        <input type="text" id="modificarValor" class="ctrl-input" style="display:none" placeholder="Seleccioná un campo...">
        <select id="modificarValorSelect" class="ctrl-select" style="display:none"></select>
        <button class="btn-primary" onclick="modificarCuadrilla()">Modificar</button>
        <button class="btn-secondary" onclick="ocultarControles()">Cancelar</button>
    </div>
</section>

<!-- ── BARRA DE ELIMINACIÓN ───────────────────────────────────────── -->
<section class="control-bar" id="deleteBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="deleteId" class="ctrl-input"
               placeholder="ID de la cuadrilla a dar de baja..." min="1" style="max-width:260px">
        <button class="btn-danger" onclick="darDeBajaCuadrilla()">Dar de Baja</button>
        <button class="btn-secondary" onclick="ocultarControles()">Cancelar</button>
    </div>
</section>

<!-- ── TABLA ────────────────────────────────────────────────────────── -->
<section class="table-section">
    <div class="table-header">
        <h2 class="table-title" id="tableTitle">Cuadrillas</h2>
        <span class="table-count" id="tableCount"></span>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Municipio</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="cuadrillasBody"></tbody>
        </table>
        <div id="noResultados" class="empty-state" style="display:none">
            Sin resultados para mostrar.
        </div>
        <div id="loadingState" class="loading-state">
            Cargando...
        </div>
    </div>
</section>

<!-- ── MODAL AGREGAR / MODIFICAR ────────────────────────────────────── -->
<div class="modal-overlay" id="modalForm" style="display:none"
     onclick="onOverlayClick(event,'modalForm')">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Agregar Cuadrilla</h3>
            <button class="modal-close" onclick="cerrarModal()">✕</button>
        </div>
        <form id="cuadrillaForm" onsubmit="guardarCuadrilla(event)">
            <input type="hidden" id="cuadrillaId">
            <div class="form-row">
                <div class="form-group">
                    <label for="municipio">Municipio</label>
                    <select id="municipio" class="form-input" required></select>
                </div>
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" class="form-input">
                        <option value="activa" selected>Activa</option>
                        <option value="inactiva">Inactiva</option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- ── MODAL CONFIRMAR BAJA ─────────────────────────────────────────── -->
<div class="modal-overlay" id="modalDelete" style="display:none"
     onclick="onOverlayClick(event,'modalDelete')">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3>Dar de baja</h3>
            <button class="modal-close" onclick="cerrarModalDelete()">✕</button>
        </div>
        <p class="delete-msg">
            ¿Confirmás dar de baja la cuadrilla <strong id="deleteNombre"></strong>?
        </p>
        <div class="modal-actions">
            <button class="btn-secondary" onclick="cerrarModalDelete()">Cancelar</button>
            <button class="btn-danger" onclick="confirmarDelete()">Eliminar</button>
        </div>
    </div>
</div>

<!-- ── NOTIFICACIÓN (TOAST) ─────────────────────────────────────────── -->
<div id="toast" class="toast"></div>

<!-- ── PIE DE PÁGINA ────────────────────────────────────────────────── -->
<footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <div class="footer-logo">
                <img src="../../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="footer-logo-img">
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
/*
    Asunciones sobre el backend (ajustar si tu implementación real difiere):
    - Las cuadrillas se gestionan en API_Recoleccion, igual que camiones:
        GET  /cuadrillas                     → listado completo
        GET  /cuadrillas?estado=activa        → filtro por estado
        GET  /cuadrillas?idMunicipio=3        → filtro por municipio
        GET  /cuadrillas/filtrarDisponibilidad → solo activas
        GET  /cuadrillas/{id}                 → una cuadrilla
        POST /cuadrillas                      → alta
        PUT  /cuadrillas/{id}                 → modificación
        DELETE /cuadrillas/{id}               → baja lógica (pasa estado a "inactiva")
        GET  /cuadrillas/reglasValidacion      → reglas de validación (con fallback si no existe)
    - Los municipios se listan desde API_Gestion: GET /municipios → [{ id_municipio, nombre }, ...]
      Si ese endpoint todavía no existe, se usa una lista fija de los 8 municipios de Montevideo
      (A–G, CH) con IDs de ejemplo 1–8: reemplazá esos IDs por los reales de tu tabla MUNICIPIO
      apenas tengas el endpoint, o cargalos manualmente acá.
*/

const API_BASE       = '/Recolix/API_Usuarios';
const MUNICIPIOS_API = '/Recolix/API_Gestion';

let cuadrillaIdParaEliminar = null;
let toastTimeout;
let reglas = {};
let municipios = [];

document.addEventListener('DOMContentLoaded', async () => {
    await cargarMunicipios();
    await cargarReglas();
    listarCuadrillas();
});

// ── REGLAS DE VALIDACIÓN ────────────────────────────────────────────────
function reglasFallback() {
    return {
        idMunicipio: { tipo: 'entero', min: 1, label: 'Municipio' },
        estado: { valores: ['activa', 'inactiva'], max: 20, label: 'Estado' }
    };
}

async function cargarReglas() {
    try {
        const res  = await fetch(`${API_BASE}/cuadrillas/reglasValidacion`);
        const json = await res.json();
        reglas = (json.status === 'success') ? json.data : reglasFallback();
    } catch {
        reglas = reglasFallback();
    }
}

// ── MUNICIPIOS ───────────────────────────────────────────────────────────
function municipiosFallback() {
    return [
        { id_municipio: 1, nombre: 'Municipio A' },
        { id_municipio: 2, nombre: 'Municipio B' },
        { id_municipio: 3, nombre: 'Municipio C' },
        { id_municipio: 4, nombre: 'Municipio CH' },
        { id_municipio: 5, nombre: 'Municipio D' },
        { id_municipio: 6, nombre: 'Municipio E' },
        { id_municipio: 7, nombre: 'Municipio F' },
        { id_municipio: 8, nombre: 'Municipio G' }
    ];
}

async function cargarMunicipios() {
    try {
        const res  = await fetch(`${MUNICIPIOS_API}/municipios`);
        const json = await res.json();
        municipios = (json.status === 'success' && Array.isArray(json.data) && json.data.length)
            ? json.data
            : municipiosFallback();
    } catch {
        municipios = municipiosFallback();
    }
    const select = document.getElementById('municipio');
    select.innerHTML = municipios.map(m => `<option value="${m.id_municipio}">${m.nombre}</option>`).join('');
}

function nombreMunicipio(id) {
    const m = municipios.find(m => String(m.id_municipio) === String(id));
    return m ? m.nombre : `Municipio #${id}`;
}

// ── LISTAR TODAS ─────────────────────────────────────────────────────────
async function listarCuadrillas() {
    ocultarControles();
    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/cuadrillas`);
        const json = await res.json();
        if (json.status === 'success') {
            renderTabla(json.data);
            setTableTitle('Todas las Cuadrillas');
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast('Error al conectar con la API', 'error');
    } finally {
        setLoading(false);
    }
}

// ── BUSCAR POR ID ─────────────────────────────────────────────────────────
async function buscarPorId() {
    const id = document.getElementById('buscarId').value.trim();
    if (!id) { mostrarToast('Ingresá un ID', 'warning'); return; }
    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/cuadrillas/${id}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            renderTabla([json.data]);
            setTableTitle(`Cuadrilla #${id}`);
        } else {
            renderTabla([]);
            mostrarToast(json.message || 'Cuadrilla no encontrada', 'error');
        }
    } catch {
        mostrarToast('Error al conectar con la API', 'error');
    } finally {
        setLoading(false);
    }
}

// ── FILTRAR ──────────────────────────────────────────────────────────────
function actualizarFiltroValor() {
    const tipo   = document.getElementById('filtroTipo').value;
    const input  = document.getElementById('filtroValor');
    const select = document.getElementById('filtroValorSelect');

    input.value  = '';
    select.value = '';
    input.style.display  = 'none';
    select.style.display = 'none';

    const sinValor = ['disponibilidad'];
    if (sinValor.includes(tipo) || !tipo) return;

    if (tipo === 'estado') {
        select.innerHTML = '<option value="">— Seleccionar —</option>' +
            ['activa', 'inactiva'].map(v => `<option value="${v}">${formatearValor(v)}</option>`).join('');
        select.style.display = '';
    } else if (tipo === 'municipio') {
        select.innerHTML = '<option value="">— Seleccionar —</option>' +
            municipios.map(m => `<option value="${m.id_municipio}">${m.nombre}</option>`).join('');
        select.style.display = '';
    }
}

async function aplicarFiltro() {
    const tipo   = document.getElementById('filtroTipo').value;
    const input  = document.getElementById('filtroValor');
    const select = document.getElementById('filtroValorSelect');
    if (!tipo) { mostrarToast('Seleccioná un filtro', 'warning'); return; }

    const sinValor  = ['disponibilidad'];
    const accionMap = { estado: 'filtrarEstado', municipio: 'filtrarMunicipio', disponibilidad: 'filtrarDisponibilidad' };
    const paramMap  = { estado: 'estado', municipio: 'idMunicipio' };

    const valor = sinValor.includes(tipo) ? '' :
        (select.style.display !== 'none' ? select.value : input.value).trim();

    if (!sinValor.includes(tipo) && !valor) {
        mostrarToast('Seleccioná un valor para el filtro', 'warning');
        return;
    }

    const url = sinValor.includes(tipo)
        ? `${API_BASE}/cuadrillas/${accionMap[tipo]}`
        : `${API_BASE}/cuadrillas/${accionMap[tipo]}?${paramMap[tipo]}=${encodeURIComponent(valor)}`;

    setLoading(true);
    try {
        const res  = await fetch(url);
        const json = await res.json();
        if (json.status === 'success') {
            renderTabla(json.data);
            const labels = {
                estado: `Estado: ${formatearValor(valor)}`,
                municipio: `Municipio: ${nombreMunicipio(valor)}`,
                disponibilidad: 'Activas'
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

// ── AGREGAR ───────────────────────────────────────────────────────────────
function abrirModalAgregar() {
    document.getElementById('modalTitle').textContent = 'Agregar Cuadrilla';
    document.getElementById('cuadrillaId').value = '';
    document.getElementById('cuadrillaForm').reset();
    document.getElementById('modalForm').style.display = 'flex';
}

// ── MODAL EDITAR ─────────────────────────────────────────────────────────
function abrirModalEditar(cuadrilla) {
    document.getElementById('modalTitle').textContent = 'Editar Cuadrilla';
    document.getElementById('cuadrillaId').value = cuadrilla.id_cuadrilla;

    const municipioEl = document.getElementById('municipio');
    municipioEl.value = cuadrilla.id_municipio;

    const estadoEl = document.getElementById('estado');
    estadoEl.value = cuadrilla.estado;
    if (!estadoEl.value) estadoEl.selectedIndex = 0;

    document.getElementById('modalForm').style.display = 'flex';
}

// ── GUARDAR (POST / PUT) ────────────────────────────────────────────────
async function guardarCuadrilla(e) {
    e.preventDefault();
    const id = document.getElementById('cuadrillaId').value;

    const idMunicipio = document.getElementById('municipio').value;
    const estado      = document.getElementById('estado').value;

    if (!idMunicipio) {
        mostrarToast('Seleccioná un municipio', 'warning');
        return;
    }

    const campos = { idMunicipio, estado };
    for (const [campo, valor] of Object.entries(campos)) {
        const regla = reglas[campo];
        if (!regla) continue;
        if (regla.valores && !regla.valores.includes(valor)) {
            mostrarToast(`${regla.label} no válido`, 'warning');
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
        idMunicipio: parseInt(idMunicipio),
        estado
    };

    try {
        const res = await fetch(
            id ? `${API_BASE}/cuadrillas/${id}` : `${API_BASE}/cuadrillas`,
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
            listarCuadrillas();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast('Error al guardar', 'error');
    }
}

// ── ELIMINAR (BAJA LÓGICA) ───────────────────────────────────────────────
function confirmarBorrar(id, etiqueta) {
    cuadrillaIdParaEliminar = id;
    document.getElementById('deleteNombre').textContent = `#${id} — ${etiqueta}`;
    document.getElementById('modalDelete').style.display = 'flex';
}

async function confirmarDelete() {
    try {
        const res  = await fetch(`${API_BASE}/cuadrillas/${cuadrillaIdParaEliminar}`, { method: 'DELETE' });
        const json = await res.json();
        if (json.status === 'success') {
            mostrarToast('Cuadrilla dada de baja correctamente', 'success');
            cerrarModalDelete();
            listarCuadrillas();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast('Error al eliminar', 'error');
    }
}

async function darDeBajaCuadrilla() {
    const id = document.getElementById('deleteId').value.trim();
    if (!id) { mostrarToast('Ingresá un ID', 'warning'); return; }
    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/cuadrillas/${id}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            confirmarBorrar(json.data.id_cuadrilla, nombreMunicipio(json.data.id_municipio));
            ocultarControles();
        } else {
            mostrarToast(json.message || 'Cuadrilla no encontrada', 'error');
        }
    } catch {
        mostrarToast('Error al conectar con la API', 'error');
    } finally {
        setLoading(false);
    }
}

// ── MODIFICAR CAMPO SUELTO ───────────────────────────────────────────────
function actualizarPlaceholderModificar() {
    const campo  = document.getElementById('modificarCampo').value;
    const input  = document.getElementById('modificarValor');
    const select = document.getElementById('modificarValorSelect');

    input.value  = '';
    select.value = '';
    input.style.display  = 'none';
    select.style.display = 'none';

    if (campo === 'estado') {
        select.innerHTML = ['activa', 'inactiva'].map((v, i) =>
            `<option value="${v}"${i === 0 ? ' selected' : ''}>${formatearValor(v)}</option>`
        ).join('');
        select.style.display = '';
    } else if (campo === 'municipio') {
        select.innerHTML = municipios.map((m, i) =>
            `<option value="${m.id_municipio}"${i === 0 ? ' selected' : ''}>${m.nombre}</option>`
        ).join('');
        select.style.display = '';
    } else {
        input.placeholder = 'Seleccioná un campo...';
        input.style.display = '';
    }
}

async function modificarCuadrilla() {
    const id = document.getElementById('modificarId').value.trim();
    if (!id) { mostrarToast('Ingresá un ID', 'warning'); return; }
    const campo = document.getElementById('modificarCampo').value;
    if (!campo) { mostrarToast('Seleccioná un campo a modificar', 'warning'); return; }

    const input  = document.getElementById('modificarValor');
    const select = document.getElementById('modificarValorSelect');
    const valor  = (select.style.display !== 'none' ? select : input).value.trim();
    if (!valor) { mostrarToast('Ingresá el nuevo valor', 'warning'); return; }

    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/cuadrillas/${id}`);
        const json = await res.json();
        if (json.status !== 'success' || !json.data) {
            mostrarToast(json.message || 'Cuadrilla no encontrada', 'error');
            return;
        }
        const cuadrilla = json.data;

        const campoMap = { municipio: 'id_municipio', estado: 'estado' };
        cuadrilla[campoMap[campo]] = valor;

        const putRes = await fetch(`${API_BASE}/cuadrillas/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                idMunicipio: parseInt(cuadrilla.id_municipio),
                estado: cuadrilla.estado
            })
        });
        const putJson = await putRes.json();
        if (putJson.status === 'success') {
            mostrarToast(putJson.message, 'success');
            ocultarControles();
            listarCuadrillas();
        } else {
            mostrarToast(putJson.message, 'error');
        }
    } catch {
        mostrarToast('Error al modificar', 'error');
    } finally {
        setLoading(false);
    }
}

// ── RENDERIZAR TABLA ─────────────────────────────────────────────────────
function renderTabla(cuadrillas) {
    const tbody = document.getElementById('cuadrillasBody');
    const noRes = document.getElementById('noResultados');
    const count = document.getElementById('tableCount');

    tbody.innerHTML = '';

    if (!cuadrillas || cuadrillas.length === 0) {
        noRes.style.display = 'flex';
        count.textContent   = '';
        return;
    }

    noRes.style.display = 'none';
    count.textContent   = `${cuadrillas.length} resultado${cuadrillas.length !== 1 ? 's' : ''}`;

    cuadrillas.forEach(c => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="td-id" data-label="ID">${c.id_cuadrilla}</td>
            <td data-label="Municipio"><strong>${nombreMunicipio(c.id_municipio)}</strong></td>
            <td data-label="Estado"><span class="badge badge-${badgeClass(c.estado)}">${formatearValor(c.estado)}</span></td>
            <td class="td-actions" data-label="Acciones">
                <button class="btn-action" onclick='abrirModalEditar(${JSON.stringify(c)})' title="Editar">✏️</button>
                <button class="btn-action" onclick="confirmarBorrar(${c.id_cuadrilla},'${nombreMunicipio(c.id_municipio)}')" title="Eliminar">🗑️</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function formatearValor(v) {
    return String(v).replace(/_/g, ' ');
}

function badgeClass(estado) {
    const map = { 'activa': 'success', 'inactiva': 'danger' };
    return map[estado] ?? 'neutral';
}

// ── UTILIDADES ───────────────────────────────────────────────────────────
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
    cuadrillaIdParaEliminar = null;
}

function onOverlayClick(e, modalId) {
    if (e.target.id === modalId) {
        modalId === 'modalForm' ? cerrarModal() : cerrarModalDelete();
    }
}

function setTableTitle(title) {
    document.getElementById('tableTitle').textContent = title;
}

function setLoading(show) {
    document.getElementById('loadingState').style.display = show ? 'flex' : 'none';
    document.getElementById('cuadrillasBody').style.opacity = show ? '0.3' : '1';
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