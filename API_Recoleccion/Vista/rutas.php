<?php require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rutas — Recolix</title>
    <link rel="stylesheet" href="styleRecoleccion.css">
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
    <p class="hero-sub">Gestioná las rutas de recolección dentro de cada circuito.</p>
</section>

<section class="cards-section">
    <div class="cards-grid">

        <div class="card-operar" onclick="abrirModalAgregar()">
            <div class="card-content">
                <div class="card-icon"><img src="img/plus.svg" alt="" class="card-svg-icon"></div>
                <h3>Agregar Ruta</h3>
                <p>Registrar una nueva ruta</p>
                <button class="card-btn">Agregar</button>
            </div>
        </div>

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

        <div class="card-operar" onclick="toggleModificar()">
            <div class="card-content">
                <div class="card-icon"><img src="img/refresh.svg" alt="" class="card-svg-icon"></div>
                <h3>Modificar Ruta</h3>
                <p>Editar datos de una ruta existente</p>
                <button class="card-btn">Modificar</button>
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

<section class="control-bar" id="modificarBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="modificarId" class="ctrl-input"
               placeholder="ID de la ruta..." min="1" style="max-width:120px">
        <select id="modificarCampo" class="ctrl-select" onchange="actualizarPlaceholderModificar()">
            <option value="">— Campo —</option>
            <option value="circuitoId">Circuito (ID)</option>
            <option value="nombre">Nombre</option>
            <option value="actividad">Actividad</option>
        </select>
        <input type="text" id="modificarValor" class="ctrl-input" style="display:none" placeholder="Seleccioná un campo...">
        <button class="btn-primary" onclick="modificarRuta()">Modificar</button>
        <button class="btn-secondary" onclick="ocultarControles()">Cancelar</button>
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

<div class="modal-overlay" id="modalForm" style="display:none"
     onclick="onOverlayClick(event,'modalForm')">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Agregar Ruta</h3>
            <button class="modal-close" onclick="cerrarModal()">✕</button>
        </div>
        <form id="rutaForm" onsubmit="guardarRuta(event)">
            <input type="hidden" id="rutaId">
            <div class="form-row">
                <div class="form-group">
                    <label for="circuitoId">Circuito (ID)</label>
                    <input type="number" id="circuitoId" class="form-input"
                           placeholder="1" min="1" required>
                </div>
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" class="form-input"
                           placeholder="Ruta Norte" maxlength="100" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="actividad">Actividad</label>
                    <input type="text" id="actividad" class="form-input"
                           placeholder="Recolección domiciliaria" maxlength="60" required>
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

async function listarRutas() {
    ocultarControles();
    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/rutas`);
        const json = await res.json();
        if (json.status === 'success') {
            renderTabla(json.data);
            setTableTitle('Todas las Rutas');
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

function abrirModalAgregar() {
    document.getElementById('modalTitle').textContent = 'Agregar Ruta';
    document.getElementById('rutaId').value = '';
    document.getElementById('rutaForm').reset();
    document.getElementById('modalForm').style.display = 'flex';
}

function abrirModalEditar(ruta) {
    document.getElementById('modalTitle').textContent = 'Editar Ruta';
    document.getElementById('rutaId').value     = ruta.id_ruta;
    document.getElementById('circuitoId').value = ruta.id_circuito;
    document.getElementById('nombre').value     = ruta.nombre;
    document.getElementById('actividad').value  = ruta.actividad;
    document.getElementById('modalForm').style.display = 'flex';
}

async function guardarRuta(e) {
    e.preventDefault();
    const id = document.getElementById('rutaId').value;

    const campos = {
        circuitoId: document.getElementById('circuitoId').value,
        nombre:     document.getElementById('nombre').value,
        actividad:  document.getElementById('actividad').value
    };

    for (const [campo, valor] of Object.entries(campos)) {
        const regla = reglas[campo];
        if (!regla) continue;
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
        circuitoId: parseInt(campos.circuitoId),
        nombre:     campos.nombre,
        actividad:  campos.actividad
    };

    try {
        const res = await fetch(
            id ? `${API_BASE}/rutas/${id}` : `${API_BASE}/rutas`,
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
            listarRutas();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast('Error al guardar', 'error');
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

function actualizarPlaceholderModificar() {
    const campo = document.getElementById('modificarCampo').value;
    const input = document.getElementById('modificarValor');

    input.value = '';
    input.style.display = 'none';

    const placeholders = {
        circuitoId: 'ej: 1',
        nombre:     'Ruta Norte',
        actividad:  'Recolección domiciliaria'
    };
    const maxlengths = { nombre: 100, actividad: 60 };
    input.placeholder = placeholders[campo] || 'Seleccioná un campo...';
    input.maxLength = maxlengths[campo] || '';
    input.style.display = '';
}

async function modificarRuta() {
    const id = document.getElementById('modificarId').value.trim();
    if (!id) { mostrarToast('Ingresá un ID', 'warning'); return; }
    const campo = document.getElementById('modificarCampo').value;
    if (!campo) { mostrarToast('Seleccioná un campo a modificar', 'warning'); return; }

    const valor = document.getElementById('modificarValor').value.trim();
    if (!valor) { mostrarToast('Ingresá el nuevo valor', 'warning'); return; }

    const regla = reglas[campo];
    if (regla) {
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
        const res  = await fetch(`${API_BASE}/rutas/${id}`);
        const json = await res.json();
        if (json.status !== 'success' || !json.data) {
            mostrarToast(json.message || 'Ruta no encontrada', 'error');
            return;
        }
        const ruta = json.data;

        const campoMap = {
            circuitoId: 'id_circuito',
            nombre:     'nombre',
            actividad:  'actividad'
        };

        ruta[campoMap[campo]] = valor;

        const putRes = await fetch(`${API_BASE}/rutas/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                circuitoId: parseInt(ruta.id_circuito),
                nombre:     ruta.nombre,
                actividad:  ruta.actividad
            })
        });
        const putJson = await putRes.json();
        if (putJson.status === 'success') {
            mostrarToast(putJson.message, 'success');
            ocultarControles();
            listarRutas();
        } else {
            mostrarToast(putJson.message, 'error');
        }
    } catch {
        mostrarToast('Error al modificar', 'error');
    } finally {
        setLoading(false);
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

    rutas.forEach(r => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="td-id" data-label="ID">${r.id_ruta}</td>
            <td data-label="Circuito">${r.id_circuito}</td>
            <td data-label="Nombre"><strong>${r.nombre}</strong></td>
            <td data-label="Actividad">${r.actividad ?? ''}</td>
            <td class="td-actions" data-label="Acciones">
                <button class="btn-action" onclick='abrirModalEditar(${JSON.stringify(r)})' title="Editar">✏️</button>
                <button class="btn-action" onclick="confirmarBorrar(${r.id_ruta},'${r.nombre}')" title="Eliminar">🗑️</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
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
    rutaIdParaEliminar = null;
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
