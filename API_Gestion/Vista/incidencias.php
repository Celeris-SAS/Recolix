<?php
require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php';
require_once __DIR__ . '/../../i18n/i18n.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($RECOLIX_IDIOMA) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('incidencias.title') ?></title>
    <link rel="stylesheet" href="styleContenedores.css">
</head>

<body>

    <header class="navbar">
        <div class="nav-inner">
            <div class="nav-left">
                <a href="../../Backoffice/indexBackoffice.php" class="brand">
                    <img src="logoRecolix.png" alt="Recolix" class="brand-logo">
                    <span class="brand-name">Recolix</span>
                </a>
                <nav class="nav-links">
                    <a href="../../Backoffice/indexBackoffice.php"><?= t('backoffice.nav_inicio') ?></a>
                    <a href="../../API_Recoleccion/Vista/camiones.php"><?= t('backoffice.nav_camiones') ?></a>
                    <a href="../../API_Recoleccion/Vista/circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
                    <a href="../../API_Recoleccion/Vista/rutas.php"><?= t('backoffice.nav_rutas') ?></a>
                    <a href="../../API_Usuarios/Vista/cuadrillas.php"><?= t('backoffice.nav_cuadrillas') ?></a>
                    <a href="contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
                    <a href="centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
                    <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
                    <a href="#"><?= t('backoffice.nav_reportes') ?></a>
                    <a href="incidencias.php" class="nav-active"><?= t('backoffice.nav_incidencias') ?></a>
                    <a href="../../API_Usuarios/Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
                </nav>
            </div>
            <div class="nav-right">
                <?php include __DIR__ . '/../../i18n/switcher.php'; ?>
                <a href="/recolix/API_Usuarios/perfil.php" class="btn-user" title="<?= htmlspecialchars(t('backoffice.nav_perfil_usuario')) ?>">
                    <img src="user.svg" alt="Usuario" class="nav-user-icon">
                </a>
                <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="<?= htmlspecialchars(t('backoffice.nav_menu')) ?>">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
        <nav class="nav-mobile" id="navMobile">
            <a href="../../Backoffice/indexBackoffice.php"><?= t('backoffice.nav_inicio') ?></a>
            <a href="../../API_Recoleccion/Vista/camiones.php"><?= t('backoffice.nav_camiones') ?></a>
            <a href="../../API_Recoleccion/Vista/circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
            <a href="../../API_Recoleccion/Vista/rutas.php"><?= t('backoffice.nav_rutas') ?></a>
            <a href="../../API_Usuarios/Vista/cuadrillas.php"><?= t('backoffice.nav_cuadrillas') ?></a>
            <a href="contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
            <a href="centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
            <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
            <a href="#"><?= t('backoffice.nav_reportes') ?></a>
            <a href="incidencias.php" class="nav-active"><?= t('backoffice.nav_incidencias') ?></a>
            <a href="../../API_Usuarios/Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
        </nav>
        <div class="nav-hr"></div>
    </header>

    <section class="hero">
        <h1 class="hero-title">Incidencias</h1>
        <p class="hero-sub">Seguí el ciclo de vida de las incidencias reportadas y asigná cuadrillas de recolección.</p>
    </section>

    <section class="cards-section">
        <div class="cards-grid">

            <div class="card-operar" onclick="toggleFiltros()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/loupe.svg" alt="" class="card-svg-icon"></div>
                    <h3>Filtrar</h3>
                    <p>Buscar por estado, tipo o prioridad...</p>
                    <button class="card-btn">Filtrar</button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleBusquedaId()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/question.svg" alt="" class="card-svg-icon"></div>
                    <h3>Buscar por ID</h3>
                    <p>Obtener una incidencia específica</p>
                    <button class="card-btn">Buscar</button>
                </div>
            </div>

            <div class="card-operar" onclick="listarUrgentes()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/refresh.svg" alt="" class="card-svg-icon"></div>
                    <h3>Ver Urgentes</h3>
                    <p>Contenedores que requieren recolección urgente</p>
                    <button class="card-btn">Ver Urgentes</button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleAsignar()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/plus.svg" alt="" class="card-svg-icon"></div>
                    <h3>Asignar Cuadrilla</h3>
                    <p>Asignar cuadrilla a una incidencia abierta</p>
                    <button class="card-btn">Asignar</button>
                </div>
            </div>

        </div>
    </section>

    <section class="control-bar" id="filterBar" style="display:none">
        <div class="control-inner">
            <select id="filtroCampo" class="ctrl-select" onchange="actualizarFiltroValor()">
                <option value="">— Filtrar por —</option>
                <option value="estado">Estado</option>
                <option value="tipo">Tipo</option>
                <option value="prioridad">Prioridad</option>
            </select>
            <select id="filtroValorSelect" class="ctrl-select" style="display:none"></select>
            <button class="btn-primary" onclick="aplicarFiltro()">Aplicar</button>
            <button class="btn-secondary" onclick="listarIncidencias()">Limpiar</button>
        </div>
    </section>

    <section class="control-bar" id="searchBar" style="display:none">
        <div class="control-inner">
            <input type="number" id="buscarId" class="ctrl-input" placeholder="ID de la incidencia..." min="1"
                style="max-width:200px">
            <button class="btn-primary" onclick="buscarPorId()">Buscar</button>
            <button class="btn-secondary" onclick="listarIncidencias()">Ver Todas</button>
        </div>
    </section>

    <section class="control-bar" id="asignarBar" style="display:none">
        <div class="control-inner">
            <input type="number" id="asignarBarraIncidenciaId" class="ctrl-input" placeholder="ID de la incidencia..."
                min="1" style="max-width:200px">
            <input type="number" id="asignarBarraIdCuadrilla" class="ctrl-input" placeholder="ID de cuadrilla..."
                min="1" style="max-width:200px">
            <button class="btn-primary" onclick="asignarCuadrillaDesdeBarra()">Asignar</button>
            <button class="btn-secondary" onclick="ocultarControles()">Cancelar</button>
        </div>
    </section>

    <section class="table-section">
        <div class="table-header">
            <h2 class="table-title" id="tableTitle">Incidencias</h2>
            <span class="table-count" id="tableCount"></span>
            <button class="btn-secondary" id="btnMostrarTodas" style="display:none" onclick="listarIncidencias()">Mostrar Todas</button>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Foto</th>
                        <th>Prioridad</th>
                        <th>Urgente</th>
                        <th>Estado</th>
                        <th>Cuadrilla</th>
                        <th>Apertura</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="incidenciasBody"></tbody>
            </table>
            <div id="noResultados" class="empty-state" style="display:none">
                Sin resultados para mostrar.
            </div>
            <div id="loadingState" class="loading-state">
                Cargando...
            </div>
        </div>
    </section>

    <div class="modal-overlay" id="modalAsignar" style="display:none" onclick="onOverlayClick(event,'modalAsignar')">
        <div class="modal modal-sm">
            <div class="modal-header">
                <h3>Asignar cuadrilla</h3>
                <button class="modal-close" onclick="cerrarModalAsignar()">✕</button>
            </div>
            <form id="asignarForm" onsubmit="guardarAsignacion(event)">
                <input type="hidden" id="asignarIncidenciaId">
                <div class="form-row">
                    <div class="form-group">
                        <label for="asignarIdCuadrilla">ID de cuadrilla</label>
                        <input type="number" id="asignarIdCuadrilla" class="form-input" placeholder="1" min="1" required>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="cerrarModalAsignar()">Cancelar</button>
                    <button type="submit" class="btn-primary">Asignar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalCerrar" style="display:none" onclick="onOverlayClick(event,'modalCerrar')">
        <div class="modal modal-sm">
            <div class="modal-header">
                <h3>Cerrar incidencia</h3>
                <button class="modal-close" onclick="cerrarModalCerrar()">✕</button>
            </div>
            <p class="delete-msg">
                ¿Confirmás resolver la incidencia <strong id="cerrarIncidenciaId"></strong>? Esta acción no se puede deshacer.
            </p>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="cerrarModalCerrar()">Cancelar</button>
                <button class="btn-danger" onclick="confirmarCierre()">Cerrar incidencia</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalContenedores" style="display:none" onclick="onOverlayClick(event,'modalContenedores')">
        <div class="modal">
            <div class="modal-header">
                <h3>Contenedores vinculados — Incidencia <span id="contenedoresIncidenciaId"></span></h3>
                <button class="modal-close" onclick="cerrarModalContenedores()">✕</button>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Calle</th>
                            <th>Nº Puerta</th>
                            <th>Estado</th>
                            <th>Llenado</th>
                        </tr>
                    </thead>
                    <tbody id="contenedoresVinculadosBody"></tbody>
                </table>
                <div id="contenedoresVinculadosVacio" class="empty-state" style="display:none">
                    Esta incidencia no tiene contenedores vinculados.
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalFoto" style="display:none" onclick="onOverlayClick(event,'modalFoto')">
        <div class="modal modal-sm">
            <div class="modal-header">
                <h3>Foto — Incidencia <span id="fotoIncidenciaId"></span></h3>
                <button class="modal-close" onclick="cerrarModalFoto()">✕</button>
            </div>
            <img id="fotoIncidenciaImg" src="" alt="Foto de la incidencia" class="modal-foto-img">
            <div class="modal-actions">
                <button class="btn-secondary" onclick="cerrarModalFoto()">Volver</button>
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
        const API_BASE = '/recolix/API_Gestion';
        let toastTimeout;
        let reglas = {};
        let incidenciasActuales = [];
        let vistaActual = 'todas';

        const LABELS_TIPO = {
            rotura: 'Rotura',
            desborde: 'Desborde',
            falta_recoleccion: 'Falta de recolección',
            vandalismo: 'Vandalismo',
            otro: 'Otro'
        };
        const LABELS_PRIORIDAD = { baja: 'Baja', media: 'Media', alta: 'Alta', critica: 'Crítica' };
        const LABELS_ESTADO = { abierta: 'Abierta', asignada: 'Asignada', en_curso: 'En curso', resuelta: 'Resuelta' };

        function reglasFallback() {
            return {
                idCuadrilla: { tipo: 'entero', min: 1, label: 'Cuadrilla' },
                tipo: { valores: Object.keys(LABELS_TIPO), label: 'Tipo' },
                prioridad: { valores: Object.keys(LABELS_PRIORIDAD), label: 'Prioridad' },
                estado: { valores: Object.keys(LABELS_ESTADO), label: 'Estado' }
            };
        }

        async function cargarReglas() {
            try {
                const res = await fetch(`${API_BASE}/incidencias/reglasValidacion`);
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

        document.addEventListener('DOMContentLoaded', async () => {
            await cargarReglas();
            document.getElementById('incidenciasBody').addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const inc = incidenciasActuales.find(x => x.id_incidencia == btn.dataset.id);
                if (!inc) return;
                if (btn.dataset.action === 'asignar') abrirModalAsignar(inc);
                if (btn.dataset.action === 'cerrar') abrirModalCerrar(inc.id_incidencia);
                if (btn.dataset.action === 'contenedores') abrirModalContenedores(inc.id_incidencia);
                if (btn.dataset.action === 'foto') abrirModalFoto(inc);
            });
            const params = new URLSearchParams(window.location.search);
            if (params.get('vista') === 'abiertas') {
                listarAbiertas();
            } else {
                listarIncidencias();
            }
        });

        async function listarIncidencias() {
            vistaActual = 'todas';
            document.getElementById('btnMostrarTodas').style.display = 'none';
            ocultarControles();
            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/incidencias`);
                const json = await res.json();
                if (json.status === 'success') {
                    renderTabla(json.data);
                    setTableTitle('Todas las Incidencias');
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast('Error al conectar con la API', 'error');
            } finally {
                setLoading(false);
            }
        }

        async function listarAbiertas() {
            vistaActual = 'abiertas';
            document.getElementById('btnMostrarTodas').style.display = '';
            ocultarControles();
            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/incidencias?estado=abierta`);
                const json = await res.json();
                if (json.status === 'success') {
                    renderTabla(json.data);
                    setTableTitle('Incidencias Abiertas');
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast('Error al conectar con la API', 'error');
            } finally {
                setLoading(false);
            }
        }

        async function listarUrgentes() {
            vistaActual = 'urgentes';
            document.getElementById('btnMostrarTodas').style.display = '';
            ocultarControles();
            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/incidencias/listarUrgentes`);
                const json = await res.json();
                if (json.status === 'success') {
                    renderTabla(json.data);
                    setTableTitle('Incidencias Urgentes');
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast('Error al conectar con la API', 'error');
            } finally {
                setLoading(false);
            }
        }

        function refrescarListado() {
            if (vistaActual === 'urgentes') return listarUrgentes();
            if (vistaActual === 'abiertas') return listarAbiertas();
            listarIncidencias();
        }

        async function buscarPorId() {
            const id = document.getElementById('buscarId').value.trim();
            if (!id) { mostrarToast('Ingresá un ID', 'warning'); return; }
            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/incidencias/${id}`);
                const json = await res.json();
                if (json.status === 'success' && json.data) {
                    renderTabla([json.data]);
                    setTableTitle(`Incidencia #${id}`);
                } else {
                    renderTabla([]);
                    mostrarToast(json.message || 'Incidencia no encontrada', 'error');
                }
            } catch {
                mostrarToast('Error al conectar con la API', 'error');
            } finally {
                setLoading(false);
            }
        }

        function actualizarFiltroValor() {
            const campo = document.getElementById('filtroCampo').value;
            const select = document.getElementById('filtroValorSelect');
            if (campo) {
                select.style.display = 'inline-block';
            } else {
                select.style.display = 'none';
            }
            if (!campo) return;
            const labels = { estado: LABELS_ESTADO, tipo: LABELS_TIPO, prioridad: LABELS_PRIORIDAD }[campo];
            const valores = (reglas[campo] && reglas[campo].valores) || Object.keys(labels);
            select.innerHTML = valores.map(v => `<option value="${v}">${labels[v] || v}</option>`).join('');
        }

        async function aplicarFiltro() {
            const campo = document.getElementById('filtroCampo').value;
            const valor = document.getElementById('filtroValorSelect').value;
            if (!campo) { mostrarToast('Seleccioná un filtro', 'warning'); return; }
            if (!valor) { mostrarToast('Seleccioná un valor para el filtro', 'warning'); return; }

            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/incidencias?${campo}=${encodeURIComponent(valor)}`);
                const json = await res.json();
                if (json.status === 'success') {
                    renderTabla(json.data);
                    const labels = { estado: LABELS_ESTADO, tipo: LABELS_TIPO, prioridad: LABELS_PRIORIDAD }[campo];
                    setTableTitle(`Filtro — ${reglas[campo]?.label || campo}: ${labels[valor] || valor}`);
                    document.getElementById('btnMostrarTodas').style.display = '';
                    vistaActual = 'filtro';
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast('Error al conectar con la API', 'error');
            } finally {
                setLoading(false);
            }
        }

        function escapeHtml(str) {
            return String(str ?? '').replace(/[&<>"']/g, s => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[s]));
        }

        function badgeEstado(estado) {
            const map = { abierta: 'info', asignada: 'neutral', en_curso: 'warning', resuelta: 'success' };
            return map[estado] ?? 'neutral';
        }

        function badgePrioridad(prioridad) {
            const map = { baja: 'neutral', media: 'info', alta: 'warning', critica: 'danger' };
            return map[prioridad] ?? 'neutral';
        }

        function formatearFecha(fechaStr) {
            if (!fechaStr) return '—';
            const fecha = new Date(fechaStr.replace(' ', 'T'));
            if (isNaN(fecha)) return fechaStr;
            return fecha.toLocaleDateString('es-UY', { day: '2-digit', month: '2-digit', year: 'numeric' }) +
                ' ' + fecha.toLocaleTimeString('es-UY', { hour: '2-digit', minute: '2-digit' });
        }

        function accionesPorEstado(inc) {
            const botones = [`<button class="btn-action" data-action="contenedores" data-id="${inc.id_incidencia}" title="Ver contenedores vinculados">🔗</button>`];
            if (inc.estado === 'abierta') {
                botones.unshift(`<button class="btn-action" data-action="asignar" data-id="${inc.id_incidencia}" title="Asignar cuadrilla">👷</button>`);
            } else if (inc.estado === 'en_curso') {
                botones.unshift(`<button class="btn-action" data-action="cerrar" data-id="${inc.id_incidencia}" title="Cerrar incidencia">✅</button>`);
            }
            return botones.join('');
        }

        function renderTabla(incidencias) {
            const tbody = document.getElementById('incidenciasBody');
            const noRes = document.getElementById('noResultados');
            const count = document.getElementById('tableCount');

            incidenciasActuales = incidencias || [];
            tbody.innerHTML = '';

            if (incidenciasActuales.length === 0) {
                noRes.style.display = 'flex';
                count.textContent = '';
                return;
            }

            noRes.style.display = 'none';
            let sufijoResultado;
            if (incidenciasActuales.length !== 1) {
                sufijoResultado = 's';
            } else {
                sufijoResultado = '';
            }
            count.textContent = `${incidenciasActuales.length} resultado${sufijoResultado}`;

            incidenciasActuales.forEach(inc => {
                let celdaFoto;
                if (inc.url_imagen) {
                    celdaFoto = `<button type="button" class="td-thumb-btn" data-action="foto" data-id="${inc.id_incidencia}" title="Ver foto"><img src="${escapeHtml(inc.url_imagen)}" alt="Foto de la incidencia" class="td-thumb"></button>`;
                } else {
                    celdaFoto = '—';
                }
                let celdaUrgente;
                if (Number(inc.urgencia) === 1) {
                    celdaUrgente = 'Sí';
                } else {
                    celdaUrgente = 'No';
                }
                let celdaCuadrilla;
                if (inc.id_cuadrilla) {
                    celdaCuadrilla = '#' + escapeHtml(inc.id_cuadrilla);
                } else {
                    celdaCuadrilla = '—';
                }

                const tr = document.createElement('tr');
                tr.innerHTML = `
    <td class="td-id" data-label="ID">${escapeHtml(inc.id_incidencia)}</td>
    <td data-label="Tipo">${escapeHtml(LABELS_TIPO[inc.tipo] || inc.tipo)}</td>
    <td data-label="Foto">${celdaFoto}</td>
    <td data-label="Prioridad"><span class="badge badge-${badgePrioridad(inc.prioridad)}">${escapeHtml(LABELS_PRIORIDAD[inc.prioridad] || inc.prioridad)}</span></td>
    <td data-label="Urgente">${celdaUrgente}</td>
    <td data-label="Estado"><span class="badge badge-${badgeEstado(inc.estado)}">${escapeHtml(LABELS_ESTADO[inc.estado] || inc.estado)}</span></td>
    <td data-label="Cuadrilla">${celdaCuadrilla}</td>
    <td data-label="Apertura">${formatearFecha(inc.fecha_apertura)}</td>
    <td class="td-actions" data-label="Acciones">${accionesPorEstado(inc)}</td>
`;
                tbody.appendChild(tr);
            });
        }

        function abrirModalAsignar(inc) {
            document.getElementById('asignarIncidenciaId').value = inc.id_incidencia;
            document.getElementById('asignarForm').reset();
            document.getElementById('modalAsignar').style.display = 'flex';
        }

        function cerrarModalAsignar() {
            document.getElementById('modalAsignar').style.display = 'none';
        }

        async function guardarAsignacion(e) {
            e.preventDefault();
            const id = document.getElementById('asignarIncidenciaId').value;
            const idCuadrilla = parseInt(document.getElementById('asignarIdCuadrilla').value, 10);

            const regla = reglas.idCuadrilla;
            if (regla && regla.min !== undefined && idCuadrilla < regla.min) {
                mostrarToast(`${regla.label} no puede ser menor que ${regla.min}`, 'warning');
                return;
            }

            try {
                const res = await fetch(`${API_BASE}/incidencias/asignarCuadrilla/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ idCuadrilla })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    mostrarToast('Cuadrilla asignada', 'success');
                    cerrarModalAsignar();
                    refrescarListado();
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast('Error al asignar la cuadrilla', 'error');
            }
        }

        let incidenciaIdParaCerrar = null;

        function abrirModalCerrar(id) {
            incidenciaIdParaCerrar = id;
            document.getElementById('cerrarIncidenciaId').textContent = `#${id}`;
            document.getElementById('modalCerrar').style.display = 'flex';
        }

        function cerrarModalCerrar() {
            document.getElementById('modalCerrar').style.display = 'none';
            incidenciaIdParaCerrar = null;
        }

        async function confirmarCierre() {
            if (!incidenciaIdParaCerrar) return;
            try {
                const res = await fetch(`${API_BASE}/incidencias/cerrar/${incidenciaIdParaCerrar}`, { method: 'PUT' });
                const json = await res.json();
                if (json.status === 'success') {
                    mostrarToast('Incidencia resuelta', 'success');
                    cerrarModalCerrar();
                    refrescarListado();
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast('Error al cerrar la incidencia', 'error');
            }
        }

        async function abrirModalContenedores(id) {
            document.getElementById('contenedoresIncidenciaId').textContent = `#${id}`;
            document.getElementById('modalContenedores').style.display = 'flex';
            const tbody = document.getElementById('contenedoresVinculadosBody');
            const vacio = document.getElementById('contenedoresVinculadosVacio');
            tbody.innerHTML = '';
            vacio.style.display = 'none';
            try {
                const res = await fetch(`${API_BASE}/incidencias/getContenedoresVinculados/${id}`);
                const json = await res.json();
                let contenedores;
                if (json.status === 'success') {
                    contenedores = json.data;
                } else {
                    contenedores = [];
                }
                if (!contenedores.length) {
                    vacio.style.display = 'flex';
                    return;
                }
                tbody.innerHTML = contenedores.map(c => {
                    let celdaLlenado;
                    if (c.nivel_llenado != null) {
                        celdaLlenado = c.nivel_llenado + '%';
                    } else {
                        celdaLlenado = '—';
                    }
                    return `
                    <tr>
                        <td class="td-id">${escapeHtml(c.id_contenedor)}</td>
                        <td>${escapeHtml(c.calle)}</td>
                        <td>${escapeHtml(c.num_calle)}</td>
                        <td><span class="badge badge-neutral">${escapeHtml(c.estado)}</span></td>
                        <td>${celdaLlenado}</td>
                    </tr>
                `;
                }).join('');
            } catch {
                mostrarToast('No se pudieron cargar los contenedores vinculados', 'error');
            }
        }

        function cerrarModalContenedores() {
            document.getElementById('modalContenedores').style.display = 'none';
        }

        function abrirModalFoto(inc) {
            document.getElementById('fotoIncidenciaId').textContent = `#${inc.id_incidencia}`;
            document.getElementById('fotoIncidenciaImg').src = inc.url_imagen;
            document.getElementById('modalFoto').style.display = 'flex';
        }

        function cerrarModalFoto() {
            document.getElementById('modalFoto').style.display = 'none';
            document.getElementById('fotoIncidenciaImg').src = '';
        }

        function ocultarControles() {
            document.getElementById('filterBar').style.display = 'none';
            document.getElementById('searchBar').style.display = 'none';
            document.getElementById('asignarBar').style.display = 'none';
        }

        function toggleFiltros() {
            const fb = document.getElementById('filterBar');
            const abierta = fb.style.display === 'none';
            ocultarControles();
            if (abierta) {
                fb.style.display = 'block';
            } else {
                fb.style.display = 'none';
            }
        }

        function toggleBusquedaId() {
            const sb = document.getElementById('searchBar');
            const abierta = sb.style.display === 'none';
            ocultarControles();
            if (abierta) {
                sb.style.display = 'block';
            } else {
                sb.style.display = 'none';
            }
        }

        function toggleAsignar() {
            const ab = document.getElementById('asignarBar');
            const abierta = ab.style.display === 'none';
            ocultarControles();
            if (abierta) {
                ab.style.display = 'block';
            } else {
                ab.style.display = 'none';
            }
        }

        async function asignarCuadrillaDesdeBarra() {
            const id = document.getElementById('asignarBarraIncidenciaId').value.trim();
            const idCuadrilla = parseInt(document.getElementById('asignarBarraIdCuadrilla').value, 10);
            if (!id) { mostrarToast('Ingresá el ID de la incidencia', 'warning'); return; }

            const regla = reglas.idCuadrilla;
            if (!idCuadrilla || (regla && regla.min !== undefined && idCuadrilla < regla.min)) {
                mostrarToast('Ingresá un ID de cuadrilla válido', 'warning');
                return;
            }

            try {
                const res = await fetch(`${API_BASE}/incidencias/asignarCuadrilla/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ idCuadrilla })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    mostrarToast('Cuadrilla asignada', 'success');
                    ocultarControles();
                    refrescarListado();
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast('Error al asignar la cuadrilla', 'error');
            }
        }

        function onOverlayClick(e, modalId) {
            if (e.target.id === modalId) {
                document.getElementById(modalId).style.display = 'none';
            }
        }

        function setTableTitle(title) {
            document.getElementById('tableTitle').textContent = title;
        }

        function setLoading(show) {
            if (show) {
                document.getElementById('loadingState').style.display = 'flex';
                document.getElementById('incidenciasBody').style.opacity = '0.3';
            } else {
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('incidenciasBody').style.opacity = '1';
            }
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
