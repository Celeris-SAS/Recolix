<?php
require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centros de Acopio — Recolix</title>
    <link rel="stylesheet" href="styleContenedores.css">
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
                    <a href="../../API_Recoleccion/Vista/camiones.php">Camiones</a>
                    <a href="../../API_Recoleccion/Vista/circuitos.php">Circuitos</a>
                    <a href="../../API_Recoleccion/Vista/rutas.php">Rutas</a>
                    <a href="#">Cuadrillas</a>
                    <a href="contenedores.php">Contenedores</a>
                    <a href="#" class="nav-active">Centros de Acopio</a>
                    <a href="../../Backoffice/mapa.php">Mapa</a>
                    <a href="#">Reportes</a>
                    <a href="#">Incidencias</a>
                    <a href="../../API_Usuarios/Vista/usuarios.php">Usuarios</a>
                </nav>
            </div>
            <div class="nav-right">
                <a href="/recolix/API_Usuarios/perfil.php" class="btn-user" title="Perfil de usuario">
                    <img src="user.svg" alt="Usuario" class="nav-user-icon">
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
            <a href="#">Cuadrillas</a>
            <a href="contenedores.php">Contenedores</a>
            <a href="#" class="nav-active">Centros de Acopio</a>
            <a href="../../Backoffice/mapa.php">Mapa</a>
            <a href="#">Reportes</a>
            <a href="#">Incidencias</a>
            <a href="../../API_Usuarios/Vista/usuarios.php">Usuarios</a>
        </nav>
        <div class="nav-hr"></div>
    </header>

    
    <section class="hero">
        <h1 class="hero-title">Centros de Acopio</h1>
        <p class="hero-sub">Gestioná ecocentros, plantas de clasificación, rellenos sanitarios y vertederos.</p>
    </section>

    
    <section class="cards-section">
        <div class="cards-grid">

            <div class="card-operar" onclick="abrirModalAgregar()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/plus.svg" alt="" class="card-svg-icon"></div>
                    <h3>Agregar Centro</h3>
                    <p>Registrar un nuevo centro de acopio</p>
                    <button class="card-btn">Agregar</button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleFiltros()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/loupe.svg" alt="" class="card-svg-icon"></div>
                    <h3>Filtrar</h3>
                    <p>Buscar por municipio, tipo o estado...</p>
                    <button class="card-btn">Filtrar</button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleBusquedaId()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/question.svg" alt="" class="card-svg-icon"></div>
                    <h3>Buscar por ID</h3>
                    <p>Obtener un centro específico</p>
                    <button class="card-btn">Buscar</button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleDarDeBaja()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/minus.svg" alt="" class="card-svg-icon"></div>
                    <h3>Dar de Baja</h3>
                    <p>Cerrar un centro de acopio</p>
                    <button class="card-btn">Dar de Baja</button>
                </div>
            </div>

        </div>
    </section>

    
    <section class="control-bar" id="filterBar" style="display:none">
        <div class="control-inner">
            <select id="filtroCampo" class="ctrl-select" onchange="actualizarFiltroValor()">
                <option value="">— Filtrar por —</option>
                <option value="municipio">Municipio</option>
                <option value="tipo">Tipo</option>
                <option value="estado">Estado</option>
            </select>
            <select id="filtroValorSelect" class="ctrl-select" style="display:none"></select>
            <button class="btn-primary" onclick="aplicarFiltro()">Aplicar</button>
            <button class="btn-secondary" onclick="listarCentros()">Limpiar</button>
        </div>
    </section>

    
    <section class="control-bar" id="searchBar" style="display:none">
        <div class="control-inner">
            <input type="number" id="buscarId" class="ctrl-input" placeholder="ID del centro..." min="1"
                style="max-width:200px">
            <button class="btn-primary" onclick="buscarPorId()">Buscar</button>
            <button class="btn-secondary" onclick="listarCentros()">Ver Todos</button>
        </div>
    </section>

    
    <section class="control-bar" id="deleteBar" style="display:none">
        <div class="control-inner">
            <input type="number" id="deleteId" class="ctrl-input" placeholder="ID del centro a dar de baja..."
                min="1" style="max-width:280px">
            <button class="btn-danger" onclick="darDeBajaCentro()">Dar de Baja</button>
            <button class="btn-secondary" onclick="ocultarControles()">Cancelar</button>
        </div>
    </section>

    
    <section class="table-section">
        <div class="table-header">
            <h2 class="table-title" id="tableTitle">Centros de Acopio</h2>
            <span class="table-count" id="tableCount"></span>
            <button class="btn-secondary" id="btnMostrarTodos" style="display:none" onclick="listarCentros()">Mostrar Todos</button>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Municipio</th>
                        <th>Ocupación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="centrosBody"></tbody>
            </table>
            <div id="noResultados" class="empty-state" style="display:none">
                Sin resultados para mostrar.
            </div>
            <div id="loadingState" class="loading-state">
                Cargando...
            </div>
        </div>
    </section>

    
    <div class="modal-overlay" id="modalForm" style="display:none" onclick="onOverlayClick(event,'modalForm')">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalTitle">Agregar Centro</h3>
                <button class="modal-close" onclick="cerrarModal()">✕</button>
            </div>
            <form id="centroForm" onsubmit="guardarCentro(event)">
                <input type="hidden" id="centroId">
                <div class="form-row">
                    <div class="form-group">
                        <label for="idMunicipio">Municipio</label>
                        <select id="idMunicipio" class="form-input" required></select>
                    </div>
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" class="form-input" placeholder="Ecocentro Malvín" maxlength="100" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <select id="tipo" class="form-input" required>
                            <option value="ecocentro">Ecocentro</option>
                            <option value="planta_clasificacion">Planta de clasificación</option>
                            <option value="relleno_sanitario">Relleno sanitario</option>
                            <option value="vertedero">Vertedero</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="estado">Estado</label>
                        <select id="estado" class="form-input" required>
                            <option value="operativo">Operativo</option>
                            <option value="saturado">Saturado</option>
                            <option value="cerrado">Cerrado</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="calle">Calle</label>
                        <input type="text" id="calle" class="form-input" placeholder="Av. 18 de Julio" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label for="numCalle">Número de puerta</label>
                        <input type="number" id="numCalle" class="form-input" placeholder="1234" min="1" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="latitud">Latitud</label>
                        <input type="number" id="latitud" class="form-input" placeholder="-34.9011" step="any" required>
                    </div>
                    <div class="form-group">
                        <label for="longitud">Longitud</label>
                        <input type="number" id="longitud" class="form-input" placeholder="-56.1645" step="any" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="capacidadTotal">Capacidad total</label>
                        <input type="number" id="capacidadTotal" class="form-input" placeholder="5000" min="1" required>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="modal-overlay" id="modalDelete" style="display:none" onclick="onOverlayClick(event,'modalDelete')">
        <div class="modal modal-sm">
            <div class="modal-header">
                <h3>Dar de baja</h3>
                <button class="modal-close" onclick="cerrarModalDelete()">✕</button>
            </div>
            <p class="delete-msg">
                ¿Confirmás cerrar el centro <strong id="deleteNombre"></strong>?
            </p>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="cerrarModalDelete()">Cancelar</button>
                <button class="btn-danger" onclick="confirmarDelete()">Dar de Baja</button>
            </div>
        </div>
    </div>

    <!-- ── MODAL MAQUINARIA DEL CENTRO ───────────────────────────────────── -->
    <div class="modal-overlay" id="modalMaquinaria" style="display:none" onclick="onOverlayClick(event,'modalMaquinaria')">
        <div class="modal" style="max-width:820px">
            <div class="modal-header">
                <h3>Maquinaria de <span id="maquinariaCentroNombre"></span></h3>
                <button class="modal-close" onclick="cerrarModalMaquinaria()">✕</button>
            </div>
            <div class="table-wrapper" style="margin-bottom:20px">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th><th>Tipo</th><th>Adquisición</th><th>Estado</th><th></th>
                        </tr>
                    </thead>
                    <tbody id="maquinariaBody"></tbody>
                </table>
                <div id="maquinariaVacio" class="empty-state" style="display:none">Este centro todavía no tiene maquinaria registrada.</div>
            </div>
            <form id="maquinariaForm" onsubmit="guardarMaquinaria(event)">
                <input type="hidden" id="maquinariaIdCentro">
                <input type="hidden" id="maquinariaId">
                <div class="form-row">
                    <div class="form-group">
                        <label for="maqNombre">Nombre</label>
                        <input type="text" id="maqNombre" class="form-input" placeholder="Compactadora CX-200" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label for="maqTipo">Tipo</label>
                        <select id="maqTipo" class="form-input" required>
                            <option value="compactadora">Compactadora</option>
                            <option value="cinta">Cinta</option>
                            <option value="pala">Pala</option>
                            <option value="bascula">Báscula</option>
                            <option value="herramienta">Herramienta</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="maqFecha">Fecha de adquisición</label>
                        <input type="date" id="maqFecha" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="maqEstado">Estado</label>
                        <select id="maqEstado" class="form-input" required>
                            <option value="operativa">Operativa</option>
                            <option value="en_mantenimiento">En mantenimiento</option>
                            <option value="fuera_servicio">Fuera de servicio</option>
                        </select>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="maquinariaCancelarBtn" style="display:none" onclick="cancelarEdicionMaquinaria()">Cancelar edición</button>
                    <button type="submit" class="btn-primary" id="maquinariaSubmitBtn">Agregar maquinaria</button>
                </div>
            </form>
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
        let centroIdParaEliminar = null;
        let toastTimeout;
        let reglasCentro = {};
        let reglasMaquinaria = {};
        let centrosActuales = [];

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

        const TIPOS_CENTRO = ['ecocentro', 'planta_clasificacion', 'relleno_sanitario', 'vertedero'];
        const ESTADOS_CENTRO = ['operativo', 'saturado', 'cerrado'];

        
        const LABELS_TIPO_CENTRO = {
            ecocentro: 'Ecocentro',
            planta_clasificacion: 'Planta de clasificación',
            relleno_sanitario: 'Relleno sanitario',
            vertedero: 'Vertedero'
        };
        const LABELS_ESTADO_CENTRO = {
            operativo: 'Operativo',
            saturado: 'Saturado',
            cerrado: 'Cerrado'
        };

        function poblarSelectMunicipio() {
            const select = document.getElementById('idMunicipio');
            select.innerHTML = MUNICIPIOS.map(m => `<option value="${m.id}">${m.nombre}</option>`).join('');
        }

        function nombreMunicipio(id) {
            const m = MUNICIPIOS.find(x => x.id == id);
            return m ? m.nombre : `Municipio ${id}`;
        }

        document.addEventListener('DOMContentLoaded', async () => {
            poblarSelectMunicipio();
            await cargarReglas();
            document.getElementById('centrosBody').addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const c = centrosActuales.find(x => x.id_centro == btn.dataset.id);
                if (!c) return;
                if (btn.dataset.action === 'editar') abrirModalEditar(c);
                if (btn.dataset.action === 'baja') confirmarBorrar(c.id_centro, c.nombre);
                if (btn.dataset.action === 'maquinaria') abrirModalMaquinaria(c);
            });
            listarCentros();
        });

        function reglasCentroFallback() {
            return {
                idMunicipio: { tipo: 'entero', min: 1, label: 'Municipio' },
                nombre: { max: 100, label: 'Nombre' },
                tipo: { valores: TIPOS_CENTRO, label: 'Tipo' },
                calle: { max: 100, label: 'Calle' },
                numCalle: { tipo: 'entero', min: 1, label: 'Número de puerta' },
                latitud: { tipo: 'decimal', label: 'Latitud' },
                longitud: { tipo: 'decimal', label: 'Longitud' },
                capacidadTotal: { tipo: 'entero', min: 1, label: 'Capacidad total' },
                estado: { valores: ESTADOS_CENTRO, label: 'Estado' }
            };
        }

        async function cargarReglas() {
            try {
                const res = await fetch(`${API_BASE}/centros-acopio/reglasValidacion`);
                const json = await res.json();
                reglasCentro = json.status === 'success' ? json.data : reglasCentroFallback();
            } catch {
                reglasCentro = reglasCentroFallback();
            }
            try {
                const res2 = await fetch(`${API_BASE}/maquinaria/reglasValidacion`);
                const json2 = await res2.json();
                if (json2.status === 'success') reglasMaquinaria = json2.data;
            } catch {
                reglasMaquinaria = {};
            }
        }

        

        async function listarCentros() {
            ocultarControles();
            mostrarCargando(true);
            try {
                const res = await fetch(`${API_BASE}/centros-acopio`);
                const json = await res.json();
                centrosActuales = json.status === 'success' ? json.data : [];
                renderCentros(centrosActuales);
                document.getElementById('btnMostrarTodos').style.display = 'none';
            } catch {
                mostrarToast('No se pudo conectar con la API', 'error');
                renderCentros([]);
            }
            mostrarCargando(false);
        }

        function badgeClaseEstado(estado) {
            if (estado === 'operativo' || estado === 'operativa') return 'badge-success';
            if (estado === 'saturado' || estado === 'en_mantenimiento') return 'badge-warning';
            return 'badge-danger';
        }

        function renderCentros(rows) {
            const body = document.getElementById('centrosBody');
            document.getElementById('tableCount').textContent = `(${rows.length})`;
            document.getElementById('noResultados').style.display = rows.length ? 'none' : 'flex';

            body.innerHTML = rows.map(c => `
                <tr data-id="${c.id_centro}">
                    <td class="td-id">${c.id_centro}</td>
                    <td>${c.nombre}</td>
                    <td>${c.tipo}</td>
                    <td>${nombreMunicipio(c.id_municipio)}</td>
                    <td id="ocupacion-${c.id_centro}">—</td>
                    <td><span class="badge ${badgeClaseEstado(c.estado)}">${c.estado}</span></td>
                    <td class="td-actions">
                        <button class="btn-action" data-action="maquinaria" data-id="${c.id_centro}" title="Ver maquinaria">🔧</button>
                        <button class="btn-action" data-action="editar" data-id="${c.id_centro}" title="Editar">✎</button>
                        <button class="btn-action" data-action="baja" data-id="${c.id_centro}" title="Dar de baja">🗑</button>
                    </td>
                </tr>
            `).join('');

            rows.forEach(c => cargarOcupacion(c.id_centro));
        }

        async function cargarOcupacion(idCentro) {
            const celda = document.getElementById(`ocupacion-${idCentro}`);
            if (!celda) return;
            try {
                const res = await fetch(`${API_BASE}/centros-acopio/getOcupacion/${idCentro}`);
                const json = await res.json();
                if (json.status === 'success' && json.data.porcentaje !== null) {
                    celda.textContent = `${json.data.porcentaje}%`;
                } else {
                    celda.textContent = 'Sin datos';
                }
            } catch {
                celda.textContent = 'Sin datos';
            }
        }

        function mostrarCargando(mostrar) {
            document.getElementById('loadingState').style.display = mostrar ? 'flex' : 'none';
        }

        async function buscarPorId() {
            const id = document.getElementById('buscarId').value;
            if (!id) return;
            mostrarCargando(true);
            try {
                const res = await fetch(`${API_BASE}/centros-acopio/${id}`);
                const json = await res.json();
                if (json.status === 'success' && json.data) {
                    centrosActuales = [json.data];
                    renderCentros(centrosActuales);
                    document.getElementById('btnMostrarTodos').style.display = 'inline-block';
                } else {
                    renderCentros([]);
                }
            } catch {
                mostrarToast('Error al buscar el centro', 'error');
            }
            mostrarCargando(false);
        }

        

        function actualizarFiltroValor() {
            const campo = document.getElementById('filtroCampo').value;
            const select = document.getElementById('filtroValorSelect');
            select.style.display = campo ? 'inline-block' : 'none';
            if (campo === 'municipio') {
                select.innerHTML = MUNICIPIOS.map(m => `<option value="${m.id}">${m.nombre}</option>`).join('');
            } else if (campo === 'tipo') {
                select.innerHTML = TIPOS_CENTRO.map(t => `<option value="${t}">${LABELS_TIPO_CENTRO[t]}</option>`).join('');
            } else if (campo === 'estado') {
                select.innerHTML = ESTADOS_CENTRO.map(e => `<option value="${e}">${LABELS_ESTADO_CENTRO[e]}</option>`).join('');
            }
        }

        const ACCION_POR_CAMPO = { municipio: 'filtrarMunicipio', tipo: 'filtrarTipo', estado: 'filtrarEstado' };

        async function aplicarFiltro() {
            const campo = document.getElementById('filtroCampo').value;
            const valor = document.getElementById('filtroValorSelect').value;
            if (!campo || !valor) return;
            mostrarCargando(true);
            try {
                const res = await fetch(`${API_BASE}/centros-acopio/${ACCION_POR_CAMPO[campo]}/${encodeURIComponent(valor)}`);
                const json = await res.json();
                centrosActuales = json.status === 'success' ? json.data : [];
                renderCentros(centrosActuales);
                document.getElementById('btnMostrarTodos').style.display = 'inline-block';
            } catch {
                mostrarToast('Error al filtrar', 'error');
            }
            mostrarCargando(false);
        }

        

        function abrirModalAgregar() {
            document.getElementById('modalTitle').textContent = 'Agregar Centro';
            document.getElementById('centroForm').reset();
            document.getElementById('centroId').value = '';
            document.getElementById('modalForm').style.display = 'flex';
        }

        function abrirModalEditar(c) {
            document.getElementById('modalTitle').textContent = 'Modificar Centro';
            document.getElementById('centroId').value = c.id_centro;
            document.getElementById('idMunicipio').value = c.id_municipio;
            document.getElementById('nombre').value = c.nombre;
            document.getElementById('tipo').value = c.tipo;
            document.getElementById('estado').value = c.estado;
            document.getElementById('calle').value = c.calle;
            document.getElementById('numCalle').value = c.num_calle;
            document.getElementById('latitud').value = c.latitud;
            document.getElementById('longitud').value = c.longitud;
            document.getElementById('capacidadTotal').value = c.capacidad_total;
            document.getElementById('modalForm').style.display = 'flex';
        }

        function cerrarModal() {
            document.getElementById('modalForm').style.display = 'none';
        }

        async function guardarCentro(event) {
            event.preventDefault();
            const id = document.getElementById('centroId').value;
            
            const body = {
                idMunicipio: parseInt(document.getElementById('idMunicipio').value, 10),
                nombre: document.getElementById('nombre').value,
                tipo: document.getElementById('tipo').value,
                calle: document.getElementById('calle').value,
                numCalle: parseInt(document.getElementById('numCalle').value, 10),
                latitud: parseFloat(document.getElementById('latitud').value),
                longitud: parseFloat(document.getElementById('longitud').value),
                capacidadTotal: parseInt(document.getElementById('capacidadTotal').value, 10),
                estado: document.getElementById('estado').value
            };
            try {
                const res = await fetch(
                    id ? `${API_BASE}/centros-acopio/${id}` : `${API_BASE}/centros-acopio`,
                    {
                        method: id ? 'PUT' : 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(body)
                    }
                );
                const json = await res.json();
                if (json.status === 'success') {
                    mostrarToast(id ? 'Centro modificado' : 'Centro agregado');
                    cerrarModal();
                    listarCentros();
                } else {
                    mostrarToast(json.message || 'Error al guardar', 'error');
                }
            } catch {
                mostrarToast('Error de conexión con la API', 'error');
            }
        }

        

        function confirmarBorrar(id, nombre) {
            centroIdParaEliminar = id;
            document.getElementById('deleteNombre').textContent = nombre;
            document.getElementById('modalDelete').style.display = 'flex';
        }

        function cerrarModalDelete() {
            document.getElementById('modalDelete').style.display = 'none';
            centroIdParaEliminar = null;
        }

        async function confirmarDelete() {
            if (!centroIdParaEliminar) return;
            try {
                const res = await fetch(`${API_BASE}/centros-acopio/${centroIdParaEliminar}`, { method: 'DELETE' });
                const json = await res.json();
                if (json.status === 'success') {
                    mostrarToast('Centro dado de baja');
                    listarCentros();
                } else {
                    mostrarToast(json.message || 'Error al dar de baja', 'error');
                }
            } catch {
                mostrarToast('Error de conexión con la API', 'error');
            }
            cerrarModalDelete();
        }

        async function darDeBajaCentro() {
            const id = document.getElementById('deleteId').value;
            if (!id) return;
            const c = centrosActuales.find(x => x.id_centro == id);
            confirmarBorrar(id, c ? c.nombre : `#${id}`);
        }

        // ── MAQUINARIA (anidada dentro del centro) ─────────────────────────

        let maquinariaActual = [];

        async function abrirModalMaquinaria(centro) {
            document.getElementById('maquinariaCentroNombre').textContent = centro.nombre;
            document.getElementById('maquinariaIdCentro').value = centro.id_centro;
            cancelarEdicionMaquinaria();
            document.getElementById('modalMaquinaria').style.display = 'flex';
            await cargarMaquinariaDeCentro(centro.id_centro);
        }

        function cerrarModalMaquinaria() {
            document.getElementById('modalMaquinaria').style.display = 'none';
        }

        async function cargarMaquinariaDeCentro(idCentro) {
            const body = document.getElementById('maquinariaBody');
            const vacio = document.getElementById('maquinariaVacio');
            try {
                const res = await fetch(`${API_BASE}/maquinaria/obtenerPorCentro/${idCentro}`);
                const json = await res.json();
                maquinariaActual = json.status === 'success' ? json.data : [];
                vacio.style.display = maquinariaActual.length ? 'none' : 'flex';
                body.innerHTML = maquinariaActual.map(m => `
                    <tr>
                        <td>${m.nombre}</td>
                        <td>${m.tipo}</td>
                        <td>${m.fecha_adquisicion}</td>
                        <td><span class="badge ${badgeClaseEstado(m.estado)}">${m.estado}</span></td>
                        <td class="td-actions" style="flex-wrap:wrap">
                            <select class="ctrl-select" style="font-size:0.78rem;padding:4px 6px;max-width:130px"
                                onchange="cambiarEstadoMaquinaria(${m.id_maquinaria}, this.value, ${idCentro})">
                                <option value="">Cambiar estado...</option>
                                <option value="operativa">Operativa</option>
                                <option value="en_mantenimiento">En mantenimiento</option>
                                <option value="fuera_servicio">Fuera de servicio</option>
                            </select>
                            <button type="button" class="btn-action" onclick="abrirEdicionMaquinaria(${m.id_maquinaria})" title="Editar">✎</button>
                        </td>
                    </tr>
                `).join('');
            } catch {
                mostrarToast('No se pudo cargar la maquinaria', 'error');
            }
        }

        function abrirEdicionMaquinaria(idMaquinaria) {
            const m = maquinariaActual.find(x => x.id_maquinaria == idMaquinaria);
            if (!m) return;
            document.getElementById('maquinariaId').value = m.id_maquinaria;
            document.getElementById('maqNombre').value = m.nombre;
            document.getElementById('maqTipo').value = m.tipo;
            document.getElementById('maqFecha').value = m.fecha_adquisicion;
            document.getElementById('maqEstado').value = m.estado;
            document.getElementById('maquinariaSubmitBtn').textContent = 'Guardar cambios';
            document.getElementById('maquinariaCancelarBtn').style.display = 'inline-block';
        }

        function cancelarEdicionMaquinaria() {
            document.getElementById('maquinariaForm').reset();
            document.getElementById('maquinariaId').value = '';
            document.getElementById('maquinariaSubmitBtn').textContent = 'Agregar maquinaria';
            document.getElementById('maquinariaCancelarBtn').style.display = 'none';
        }

        async function guardarMaquinaria(event) {
            event.preventDefault();
            const idCentro = document.getElementById('maquinariaIdCentro').value;
            const idMaquinaria = document.getElementById('maquinariaId').value;
            // Mismo orden que los parámetros del controller: insertMaquinaria($idCentro, $nombre, $tipo, $fechaAdquisicion, $estado)
            // y updateMaquinaria($id, $nombre, $tipo, $fechaAdquisicion, $estado) — el update no lleva idCentro.
            const datosComunes = {
                nombre: document.getElementById('maqNombre').value,
                tipo: document.getElementById('maqTipo').value,
                fechaAdquisicion: document.getElementById('maqFecha').value,
                estado: document.getElementById('maqEstado').value
            };
            const editando = !!idMaquinaria;
            const body = editando ? datosComunes : { idCentro: parseInt(idCentro, 10), ...datosComunes };
            try {
                const res = await fetch(
                    editando ? `${API_BASE}/maquinaria/${idMaquinaria}` : `${API_BASE}/maquinaria`,
                    {
                        method: editando ? 'PUT' : 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(body)
                    }
                );
                const json = await res.json();
                if (json.status === 'success') {
                    mostrarToast(editando ? 'Maquinaria modificada' : 'Maquinaria agregada');
                    cancelarEdicionMaquinaria();
                    cargarMaquinariaDeCentro(idCentro);
                } else {
                    mostrarToast(json.message || 'Error al guardar la maquinaria', 'error');
                }
            } catch {
                mostrarToast('Error de conexión con la API', 'error');
            }
        }

        async function cambiarEstadoMaquinaria(idMaquinaria, nuevoEstado, idCentro) {
            if (!nuevoEstado) return;
            try {
                const res = await fetch(`${API_BASE}/maquinaria/cambiarEstado/${idMaquinaria}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nuevoEstado })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    mostrarToast('Estado de maquinaria actualizado');
                    cargarMaquinariaDeCentro(idCentro);
                } else {
                    mostrarToast(json.message || 'Error al cambiar estado', 'error');
                }
            } catch {
                mostrarToast('Error de conexión con la API', 'error');
            }
        }


        function ocultarControles() {
            ['filterBar', 'searchBar', 'deleteBar'].forEach(id => {
                document.getElementById(id).style.display = 'none';
            });
        }

        function toggleFiltros() {
            const bar = document.getElementById('filterBar');
            ocultarControles();
            bar.style.display = bar.style.display === 'none' ? 'block' : 'none';
        }

        function toggleBusquedaId() {
            const bar = document.getElementById('searchBar');
            ocultarControles();
            bar.style.display = bar.style.display === 'none' ? 'block' : 'none';
        }

        function toggleDarDeBaja() {
            const bar = document.getElementById('deleteBar');
            ocultarControles();
            bar.style.display = bar.style.display === 'none' ? 'block' : 'none';
        }

        function onOverlayClick(event, modalId) {
            if (event.target.id === modalId) {
                document.getElementById(modalId).style.display = 'none';
            }
        }

        function mostrarToast(mensaje, tipo = 'success') {
            const toast = document.getElementById('toast');
            clearTimeout(toastTimeout);
            toast.textContent = mensaje;
            toast.className = `toast toast-${tipo} toast-show`;
            toastTimeout = setTimeout(() => toast.classList.remove('toast-show'), 3500);
        }

        function toggleMobileMenu() {
            document.getElementById('navMobile').classList.toggle('nav-mobile-open');
        }
    </script>

</body>

</html>
