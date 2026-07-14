<?php require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios — Recolix</title>
    <link rel="stylesheet" href="styleUsuarios.css">
</head>

<body>

    <!-- ── BARRA DE NAVEGACIÓN ─────────────────────────────────────────── -->
    <header class="navbar">
        <div class="nav-inner">
            <div class="nav-left">
                <div class="brand">
                    <img src="Logo.png" alt="Recolix" class="brand-logo">
                    <span class="brand-name">Recolix</span>
                </div>
                <nav class="nav-links">
                    <a href="../../Backoffice/indexBackoffice.php">Inicio</a>
                    <a href="../../API_Recoleccion/Vista/camiones.php">Camiones</a>
                    <a href="#">Cuadrillas</a>
                    <a href="../../API_Gestion/Vista/contenedores.php">Contenedores</a>
                    <a href="#">Vertederos</a>
                    <a href="#">Centros de Acopio</a>
                    <a href="../../Backoffice/mapa.php">Mapa</a>
                    <a href="#">Reportes</a>
                    <a href="#">Incidencias</a>
                    <a href="#" class="nav-active">Usuarios</a>
                </nav>
            </div>
            <div class="nav-right">
                <a href="perfil.html" class="btn-user" title="Perfil de usuario">
                    <img src="img/user.svg" alt="Usuario" class="nav-user-icon">
                </a>
                <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="Menú">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
        <nav class="nav-mobile" id="navMobile">
            <a href="../../Backoffice/indexBackoffice.php">Inicio</a>
            <a href="../../API_Recoleccion/Vista/camiones.php">Camiones</a>
            <a href="#">Cuadrillas</a>
            <a href="../../API_Gestion/Vista/contenedores.php">Contenedores</a>
            <a href="#">Vertederos</a>
            <a href="#">Centros de Acopio</a>
            <a href="../../Backoffice/mapa.php">Mapa</a>
            <a href="#">Reportes</a>
            <a href="#">Incidencias</a>
            <a href="#" class="nav-active">Usuarios</a>
        </nav>
        <div class="nav-hr"></div>
    </header>

    <!-- ── HERO ───────────────────────────────────────────────────────── -->
    <section class="hero">
        <h1 class="hero-title">Usuarios</h1>
        <p class="hero-sub">Administrá las cuentas de vecinos, cuadrillas y administradores del sistema.</p>
    </section>

    <!-- ── TARJETAS ─────────────────────────────────────────────────────── -->
    <section class="cards-section">
        <div class="cards-grid">

            <a href="../../Backoffice/indexSolicitudRegistro.php" class="card-operar">
                <div class="card-content">
                    <div class="card-icon"><img src="img/plus.svg" alt="" class="card-svg-icon"></div>
                    <h3>Solicitudes Pendientes</h3>
                    <p>Aprobar o rechazar registros de vecinos</p>
                </div>
            </a>

            <div class="card-operar" onclick="toggleFiltros()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/loupe.svg" alt="" class="card-svg-icon"></div>
                    <h3>Filtrar</h3>
                    <p>Buscar por rol, acceso o estado...</p>
                    <button class="card-btn">Filtrar</button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleBusquedaId()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/question.svg" alt="" class="card-svg-icon"></div>
                    <h3>Buscar por Cédula</h3>
                    <p>Obtener un usuario específico</p>
                    <button class="card-btn">Buscar</button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleModificar()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/refresh.svg" alt="" class="card-svg-icon"></div>
                    <h3>Modificar Usuario</h3>
                    <p>Editar rol, contacto o estado de un usuario</p>
                    <button class="card-btn">Modificar</button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleCambiarEstado()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/minus.svg" alt="" class="card-svg-icon"></div>
                    <h3>Habilitar / Deshabilitar</h3>
                    <p>Bloquear o restaurar el acceso de un usuario</p>
                    <button class="card-btn">Cambiar Estado</button>
                </div>
            </div>

        </div>
    </section>

    <!-- ── BARRA DE FILTROS ─────────────────────────────────────────────── -->
    <section class="control-bar" id="filterBar" style="display:none">
        <div class="control-inner">
            <select id="filtroTipo" class="ctrl-select" onchange="actualizarFiltroValor()">
                <option value="">— Filtrar por —</option>
                <option value="rol">Rol</option>
                <option value="acceso">Acceso</option>
                <option value="estado">Estado</option>
            </select>
            <select id="filtroValorSelect" class="ctrl-select" style="display:none"></select>
            <button class="btn-primary" onclick="aplicarFiltro()">Aplicar</button>
            <button class="btn-secondary" onclick="listarUsuarios()">Limpiar</button>
        </div>
    </section>

    <!-- ── BARRA DE BÚSQUEDA POR CÉDULA ─────────────────────────────────── -->
    <section class="control-bar" id="searchBar" style="display:none">
        <div class="control-inner">
            <input type="number" id="buscarCi" class="ctrl-input" placeholder="Cédula del usuario..." min="1"
                style="max-width:200px">
            <button class="btn-primary" onclick="buscarPorCi()">Buscar</button>
            <button class="btn-secondary" onclick="listarUsuarios()">Ver Todos</button>
        </div>
    </section>

    <!-- ── BARRA DE MODIFICACIÓN ──────────────────────────────────────── -->
    <section class="control-bar" id="modificarBar" style="display:none">
        <div class="control-inner">
            <input type="number" id="modificarCi" class="ctrl-input" placeholder="Cédula del usuario..." min="1"
                style="max-width:160px">
            <select id="modificarCampo" class="ctrl-select" onchange="actualizarPlaceholderModificar()">
                <option value="">— Campo —</option>
                <option value="rol">Rol</option>
                <option value="email">Correo electrónico</option>
                <option value="tel">Teléfono</option>
            </select>
            <input type="text" id="modificarValor" class="ctrl-input" style="display:none"
                placeholder="Seleccioná un campo...">
            <select id="modificarValorSelect" class="ctrl-select" style="display:none"></select>
            <button class="btn-primary" onclick="modificarUsuario()">Modificar</button>
            <button class="btn-secondary" onclick="ocultarControles()">Cancelar</button>
        </div>
    </section>

    <!-- ── BARRA DE CAMBIO DE ESTADO ─────────────────────────────────────── -->
    <section class="control-bar" id="estadoBar" style="display:none">
        <div class="control-inner">
            <input type="number" id="estadoCi" class="ctrl-input" placeholder="Cédula del usuario..." min="1"
                style="max-width:200px">
            <button class="btn-danger" onclick="buscarParaCambiarEstado()">Continuar</button>
            <button class="btn-secondary" onclick="ocultarControles()">Cancelar</button>
        </div>
    </section>

    <!-- ── TABLA ────────────────────────────────────────────────────────── -->
    <section class="table-section">
        <div class="table-header">
            <h2 class="table-title" id="tableTitle">Usuarios</h2>
            <span class="table-count" id="tableCount"></span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Acceso</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="usuariosBody"></tbody>
            </table>
            <div id="noResultados" class="empty-state" style="display:none">
                Sin resultados para mostrar.
            </div>
            <div id="loadingState" class="loading-state">
                Cargando...
            </div>
        </div>
    </section>

    <!-- ── MODAL EDITAR ─────────────────────────────────────────────────── -->
    <div class="modal-overlay" id="modalForm" style="display:none" onclick="onOverlayClick(event,'modalForm')">
        <div class="modal">
            <div class="modal-header">
                <h3>Editar Usuario</h3>
                <button class="modal-close" onclick="cerrarModal()">✕</button>
            </div>
            <form id="usuarioForm" onsubmit="guardarUsuario(event)">
                <input type="hidden" id="usuarioCi">
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Correo electrónico</label>
                        <input type="email" id="email" class="form-input" placeholder="vecino@correo.com"
                            maxlength="120" required>
                    </div>
                    <div class="form-group">
                        <label for="tel">Teléfono</label>
                        <input type="text" id="tel" class="form-input" placeholder="099123456" maxlength="9"
                            required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="rol">Rol</label>
                        <select id="rol" class="form-input" required>
                            <option value="vecino">Vecino</option>
                            <option value="cuadrilla">Cuadrilla</option>
                            <option value="administrador">Administrador</option>
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

    <!-- ── MODAL CONFIRMAR CAMBIO DE ESTADO ─────────────────────────────── -->
    <div class="modal-overlay" id="modalDelete" style="display:none" onclick="onOverlayClick(event,'modalDelete')">
        <div class="modal modal-sm">
            <div class="modal-header">
                <h3>Cambiar estado</h3>
                <button class="modal-close" onclick="cerrarModalDelete()">✕</button>
            </div>
            <p class="delete-msg" id="deleteMsg"></p>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="cerrarModalDelete()">Cancelar</button>
                <button class="btn-danger" onclick="confirmarDelete()">Confirmar</button>
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
                    <img src="Logo.png" alt="Recolix" class="footer-logo-img">
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
        const API_BASE = '/recolix/API_Usuarios';
        let usuarioCiParaEstado = null;
        let estadoDestino = null;
        let toastTimeout;
        let reglas = {};
        let usuariosActuales = [];

        function reglasFallback() {
            return {
                email: { tipo: 'email', max: 120, label: 'Correo electrónico' },
                tel: { tipo: 'string', min: 9, max: 9, label: 'Teléfono' },
                rol: { valores: ['administrador', 'cuadrilla', 'vecino'], label: 'Rol' },
                estado: { valores: ['habilitado', 'deshabilitado'], label: 'Estado' },
                acceso: { valores: ['pendiente', 'aprobado', 'rechazado'], label: 'Acceso' }
            };
        }

        async function cargarReglas() {
            try {
                const res = await fetch(`${API_BASE}/usuarios/reglasValidacion`);
                const json = await res.json();
                reglas = json.status === 'success' ? json.data : reglasFallback();
            } catch {
                reglas = reglasFallback();
            }
        }

        document.addEventListener('DOMContentLoaded', async () => {
            await cargarReglas();
            document.getElementById('usuariosBody').addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const u = usuariosActuales.find(x => x.ci_usuario == btn.dataset.id);
                if (!u) return;
                if (btn.dataset.action === 'editar') abrirModalEditar(u);
                if (btn.dataset.action === 'estado') {
                    const destino = u.estado === 'habilitado' ? 'deshabilitado' : 'habilitado';
                    confirmarCambioEstado(u.ci_usuario, `${u.primer_nombre} ${u.p_apellido}`, destino);
                }
            });
            listarUsuarios();
        });

        // ── LISTAR TODOS ──────────────────────────────────────────────────────
        async function listarUsuarios() {
            ocultarControles();
            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/usuarios`);
                const json = await res.json();
                if (json.status === 'success') {
                    renderTabla(json.data);
                    setTableTitle('Todos los Usuarios');
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast('Error al conectar con la API', 'error');
            } finally {
                setLoading(false);
            }
        }

        // ── BUSCAR POR CÉDULA ─────────────────────────────────────────────────
        async function buscarPorCi() {
            const ci = document.getElementById('buscarCi').value.trim();
            if (!ci) { mostrarToast('Ingresá una cédula', 'warning'); return; }
            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/usuarios/${ci}`);
                const json = await res.json();
                if (json.status === 'success' && json.data) {
                    renderTabla([json.data]);
                    setTableTitle(`Usuario #${ci}`);
                } else {
                    renderTabla([]);
                    mostrarToast(json.message || 'Usuario no encontrado', 'error');
                }
            } catch {
                mostrarToast('Error al conectar con la API', 'error');
            } finally {
                setLoading(false);
            }
        }

        // ── FILTRAR ───────────────────────────────────────────────────────────
        function actualizarFiltroValor() {
            const tipo = document.getElementById('filtroTipo').value;
            const select = document.getElementById('filtroValorSelect');
            select.value = '';
            select.style.display = 'none';
            if (!tipo) return;

            const valores = (reglas[tipo] && reglas[tipo].valores) || reglasFallback()[tipo].valores;
            select.innerHTML = '<option value="">— Seleccionar —</option>' +
                valores.map(v => `<option value="${v}">${formatearValor(v)}</option>`).join('');
            select.style.display = '';
        }

        async function aplicarFiltro() {
            const tipo = document.getElementById('filtroTipo').value;
            if (!tipo) { mostrarToast('Seleccioná un filtro', 'warning'); return; }
            const valor = document.getElementById('filtroValorSelect').value;
            if (!valor) { mostrarToast('Seleccioná un valor para el filtro', 'warning'); return; }

            const endpointMap = {
                rol: 'filtrarRol',
                acceso: 'filtrarAcceso',
                estado: 'filtrarEstadoUsuario'
            };

            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/usuarios/${endpointMap[tipo]}?${tipo}=${encodeURIComponent(valor)}`);
                const json = await res.json();
                if (json.status === 'success') {
                    renderTabla(json.data);
                    setTableTitle(`Filtro — ${reglas[tipo]?.label || tipo}: ${formatearValor(valor)}`);
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast('Error al conectar con la API', 'error');
            } finally {
                setLoading(false);
            }
        }

        // ── MODAL EDITAR ──────────────────────────────────────────────────────
        function abrirModalEditar(usuario) {
            document.getElementById('usuarioCi').value = usuario.ci_usuario;
            document.getElementById('email').value = usuario.email;
            document.getElementById('tel').value = usuario.tel;
            document.getElementById('rol').value = usuario.rol;
            document.getElementById('modalForm').style.display = 'flex';
        }

        // ── GUARDAR (PUT) ──────────────────────────────────────────────────────
        async function guardarUsuario(e) {
            e.preventDefault();
            const ci    = document.getElementById('usuarioCi').value;
            const email = document.getElementById('email').value.trim();
            const tel   = document.getElementById('tel').value.trim();
            const rol   = document.getElementById('rol').value;

            if (!validarCampo('email', email) || !validarCampo('tel', tel)) return;

            try {
                const res = await fetch(`${API_BASE}/usuarios/${ci}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, tel })
                });
                const json = await res.json();
                if (json.status !== 'success') {
                    mostrarToast(json.message, 'error');
                    return;
                }

                const resRol = await fetch(`${API_BASE}/usuarios/cambiarRol/${ci}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nuevoRol: rol })
                });
                const jsonRol = await resRol.json();
                if (jsonRol.status !== 'success') {
                    mostrarToast(jsonRol.message, 'error');
                    return;
                }

                mostrarToast('Usuario actualizado', 'success');
                cerrarModal();
                listarUsuarios();
            } catch {
                mostrarToast('Error al guardar', 'error');
            }
        }

        // ── MODIFICAR CAMPO INDIVIDUAL ────────────────────────────────────────
        function actualizarPlaceholderModificar() {
            const campo  = document.getElementById('modificarCampo').value;
            const input  = document.getElementById('modificarValor');
            const select = document.getElementById('modificarValorSelect');

            input.value = '';
            select.value = '';
            input.style.display = 'none';
            select.style.display = 'none';

            if (campo === 'rol') {
                const valores = (reglas.rol && reglas.rol.valores) || reglasFallback().rol.valores;
                select.innerHTML = valores.map(v => `<option value="${v}">${formatearValor(v)}</option>`).join('');
                select.style.display = '';
            } else if (campo) {
                const placeholders = {
                    email: 'ej: vecino@correo.com',
                    tel: 'ej: 099123456'
                };
                input.placeholder = placeholders[campo] || 'Nuevo valor...';
                input.style.display = '';
            }
        }

        async function modificarUsuario() {
            const ci = document.getElementById('modificarCi').value.trim();
            if (!ci) { mostrarToast('Ingresá una cédula', 'warning'); return; }
            const campo = document.getElementById('modificarCampo').value;
            if (!campo) { mostrarToast('Seleccioná un campo a modificar', 'warning'); return; }

            const input  = document.getElementById('modificarValor');
            const select = document.getElementById('modificarValorSelect');
            const valor  = (campo === 'rol' ? select : input).value.trim();
            if (!valor) { mostrarToast('Ingresá el nuevo valor', 'warning'); return; }

            if (!validarCampo(campo, valor)) return;

            setLoading(true);
            try {
                if (campo === 'rol') {
                    const res = await fetch(`${API_BASE}/usuarios/cambiarRol/${ci}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ nuevoRol: valor })
                    });
                    const json = await res.json();
                    if (json.status === 'success') {
                        mostrarToast('Rol actualizado', 'success');
                        ocultarControles();
                        listarUsuarios();
                    } else {
                        mostrarToast(json.message, 'error');
                    }
                    return;
                }

                const res = await fetch(`${API_BASE}/usuarios/${ci}`);
                const json = await res.json();
                if (json.status !== 'success' || !json.data) {
                    mostrarToast(json.message || 'Usuario no encontrado', 'error');
                    return;
                }
                const u = { ...json.data };
                u[campo] = valor;

                const putRes = await fetch(`${API_BASE}/usuarios/${ci}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: u.email, tel: u.tel })
                });
                const putJson = await putRes.json();
                if (putJson.status === 'success') {
                    mostrarToast(putJson.message, 'success');
                    ocultarControles();
                    listarUsuarios();
                } else {
                    mostrarToast(putJson.message, 'error');
                }
            } catch {
                mostrarToast('Error al modificar', 'error');
            } finally {
                setLoading(false);
            }
        }

        function validarCampo(campo, valor) {
            const regla = reglas[campo];
            if (!regla) return true;
            if (regla.valores && !regla.valores.includes(valor)) {
                mostrarToast(`${regla.label} no válido`, 'warning');
                return false;
            }
            if (campo === 'email' && (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor) || valor.length > (regla.max || 120))) {
                mostrarToast(`${regla.label} no tiene un formato válido`, 'warning');
                return false;
            }
            if (campo === 'tel' && !/^\d{9}$/.test(valor)) {
                mostrarToast(`${regla.label} debe tener 9 dígitos`, 'warning');
                return false;
            }
            return true;
        }

        // ── CAMBIAR ESTADO (HABILITAR / DESHABILITAR) ─────────────────────────
        async function buscarParaCambiarEstado() {
            const ci = document.getElementById('estadoCi').value.trim();
            if (!ci) { mostrarToast('Ingresá una cédula', 'warning'); return; }
            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/usuarios/${ci}`);
                const json = await res.json();
                if (json.status === 'success' && json.data) {
                    const destino = json.data.estado === 'habilitado' ? 'deshabilitado' : 'habilitado';
                    confirmarCambioEstado(json.data.ci_usuario, `${json.data.primer_nombre} ${json.data.p_apellido}`, destino);
                    ocultarControles();
                } else {
                    mostrarToast(json.message || 'Usuario no encontrado', 'error');
                }
            } catch {
                mostrarToast('Error al conectar con la API', 'error');
            } finally {
                setLoading(false);
            }
        }

        function confirmarCambioEstado(ci, nombre, destino) {
            usuarioCiParaEstado = ci;
            estadoDestino = destino;
            const verbo = destino === 'habilitado' ? 'habilitar' : 'deshabilitar';
            document.getElementById('deleteMsg').innerHTML =
                `¿Confirmás que querés <strong>${verbo}</strong> al usuario <strong>${nombre}</strong>?`;
            document.getElementById('modalDelete').style.display = 'flex';
        }

        async function confirmarDelete() {
            try {
                const res = await fetch(`${API_BASE}/usuarios/cambiarEstadoUsuario/${usuarioCiParaEstado}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nuevoEstado: estadoDestino })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    mostrarToast(`Usuario ${estadoDestino === 'habilitado' ? 'habilitado' : 'deshabilitado'} correctamente`, 'success');
                    cerrarModalDelete();
                    listarUsuarios();
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast('Error al cambiar el estado', 'error');
            }
        }

        // ── RENDERIZAR TABLA ──────────────────────────────────────────────────
        function escapeHtml(str) {
            return String(str ?? '').replace(/[&<>"']/g, s => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[s]));
        }

        function renderTabla(usuarios) {
            const tbody = document.getElementById('usuariosBody');
            const noRes = document.getElementById('noResultados');
            const count = document.getElementById('tableCount');

            usuariosActuales = usuarios || [];
            tbody.innerHTML = '';

            if (usuariosActuales.length === 0) {
                noRes.style.display = 'flex';
                count.textContent = '';
                return;
            }

            noRes.style.display = 'none';
            count.textContent = `${usuariosActuales.length} resultado${usuariosActuales.length !== 1 ? 's' : ''}`;

            usuariosActuales.forEach(u => {
                const tr = document.createElement('tr');
                const esHabilitado = u.estado === 'habilitado';
                tr.innerHTML = `
    <td class="td-id" data-label="Cédula">${escapeHtml(u.ci_usuario)}</td>
    <td data-label="Nombre"><strong>${escapeHtml(u.primer_nombre)}</strong></td>
    <td data-label="Apellido">${escapeHtml(u.p_apellido)}</td>
    <td data-label="Email">${escapeHtml(u.email)}</td>
    <td data-label="Teléfono">${escapeHtml(u.tel)}</td>
    <td data-label="Rol"><span class="badge badge-neutral">${formatearValor(u.rol)}</span></td>
    <td data-label="Acceso"><span class="badge badge-${badgeClaseAcceso(u.acceso)}">${formatearValor(u.acceso)}</span></td>
    <td data-label="Estado"><span class="badge badge-${esHabilitado ? 'success' : 'danger'}">${formatearValor(u.estado)}</span></td>
    <td class="td-actions" data-label="Acciones">
        <button class="btn-action" data-action="editar" data-id="${escapeHtml(u.ci_usuario)}" title="Editar">✏️</button>
        <button class="btn-action" data-action="estado" data-id="${escapeHtml(u.ci_usuario)}" title="${esHabilitado ? 'Deshabilitar' : 'Habilitar'}">${esHabilitado ? '🔒' : '🔓'}</button>
    </td>
`;
                tbody.appendChild(tr);
            });
        }

        function formatearValor(valor) {
            const texto = String(valor ?? '').replace(/_/g, ' ');
            return texto.charAt(0).toUpperCase() + texto.slice(1);
        }

        function badgeClaseAcceso(acceso) {
            const map = { aprobado: 'success', pendiente: 'warning', rechazado: 'danger' };
            return map[acceso] ?? 'neutral';
        }

        // ── UTILIDADES ────────────────────────────────────────────────────────
        function toggleFiltros() {
            const fb = document.getElementById('filterBar');
            const sb = document.getElementById('searchBar');
            const mb = document.getElementById('modificarBar');
            const eb = document.getElementById('estadoBar');
            sb.style.display = 'none';
            mb.style.display = 'none';
            eb.style.display = 'none';
            fb.style.display = fb.style.display === 'none' ? 'block' : 'none';
        }

        function toggleBusquedaId() {
            const sb = document.getElementById('searchBar');
            const fb = document.getElementById('filterBar');
            const mb = document.getElementById('modificarBar');
            const eb = document.getElementById('estadoBar');
            fb.style.display = 'none';
            mb.style.display = 'none';
            eb.style.display = 'none';
            sb.style.display = sb.style.display === 'none' ? 'block' : 'none';
        }

        function toggleModificar() {
            const mb = document.getElementById('modificarBar');
            const fb = document.getElementById('filterBar');
            const sb = document.getElementById('searchBar');
            const eb = document.getElementById('estadoBar');
            fb.style.display = 'none';
            sb.style.display = 'none';
            eb.style.display = 'none';
            mb.style.display = mb.style.display === 'none' ? 'block' : 'none';
        }

        function toggleCambiarEstado() {
            const eb = document.getElementById('estadoBar');
            const fb = document.getElementById('filterBar');
            const sb = document.getElementById('searchBar');
            const mb = document.getElementById('modificarBar');
            fb.style.display = 'none';
            sb.style.display = 'none';
            mb.style.display = 'none';
            eb.style.display = eb.style.display === 'none' ? 'block' : 'none';
        }

        function ocultarControles() {
            document.getElementById('filterBar').style.display = 'none';
            document.getElementById('searchBar').style.display = 'none';
            document.getElementById('modificarBar').style.display = 'none';
            document.getElementById('estadoBar').style.display = 'none';
        }

        function cerrarModal() {
            document.getElementById('modalForm').style.display = 'none';
        }

        function cerrarModalDelete() {
            document.getElementById('modalDelete').style.display = 'none';
            usuarioCiParaEstado = null;
            estadoDestino = null;
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
            document.getElementById('usuariosBody').style.opacity = show ? '0.3' : '1';
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
