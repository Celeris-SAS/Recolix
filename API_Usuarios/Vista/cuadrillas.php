<?php
require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php';
require_once __DIR__ . '/../../extra/i18n/i18n.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($RECOLIX_IDIOMA) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('cuadrillas.title') ?></title>
    <link rel="icon" type="image/png" href="/recolix/LandingPage/img/Logo.png">
    <link rel="stylesheet" href="../../Backoffice/styleBackoffice.css">
    <link rel="stylesheet" href="styleCuadrillas.css">
</head>
<body>

<header class="navbar">
    <div class="nav-inner">
        <div class="nav-left">
            <a href="../../Backoffice/indexBackoffice.php" class="brand">
                <img src="../../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="brand-logo">
                <span class="brand-name">Recolix</span>
            </a>
            <nav class="nav-links">
                <a href="../../Backoffice/indexBackoffice.php"><?= t('backoffice.nav_inicio') ?></a>
                <a href="../../API_Recoleccion/Vista/camiones.php"><?= t('backoffice.nav_camiones') ?></a>
                <a href="../../API_Recoleccion/Vista/circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
                <a href="../../API_Recoleccion/Vista/rutas.php"><?= t('backoffice.nav_rutas') ?></a>
                <a href="cuadrillas.php" class="nav-active"><?= t('backoffice.nav_cuadrillas') ?></a>
                <a href="../../API_Gestion/Vista/contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
                <a href="../../API_Gestion/Vista/centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
                <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
                <a href="#"><?= t('backoffice.nav_reportes') ?></a>
                <a href="../../API_Gestion/Vista/incidencias.php"><?= t('backoffice.nav_incidencias') ?></a>
                <a href="usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
            </nav>
        </div>
        <div class="nav-right">
            <?php include __DIR__ . '/../../extra/i18n/switcher.php'; ?>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/recolix/extra/Comun/campana.php'; ?>
            <a href="../perfil.php" class="btn-user" title="<?= htmlspecialchars(t('backoffice.nav_perfil_usuario')) ?>">
                <img src="../../API_Recoleccion/Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
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
        <a href="cuadrillas.php" class="nav-active"><?= t('backoffice.nav_cuadrillas') ?></a>
        <a href="../../API_Gestion/Vista/contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
        <a href="../../API_Gestion/Vista/centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
        <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
        <a href="#"><?= t('backoffice.nav_reportes') ?></a>
        <a href="../../API_Gestion/Vista/incidencias.php"><?= t('backoffice.nav_incidencias') ?></a>
        <a href="usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
    </nav>
    <div class="nav-hr"></div>
</header>

<section class="hero">
    <h1 class="hero-title"><?= t('cuadrillas.hero_title') ?></h1>
    <p class="hero-sub"><?= t('cuadrillas.hero_sub') ?></p>
</section>

<section class="cards-section">
    <div class="cards-grid">

        <div class="card-operar" onclick="abrirModalAgregar()">
            <div class="card-content">
                <div class="card-icon"><img src="img/plus.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('cuadrillas.card_agregar_titulo') ?></h3>
                <p><?= t('cuadrillas.card_agregar_desc') ?></p>
                <button class="card-btn"><?= t('cuadrillas.btn_agregar') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleFiltros()">
            <div class="card-content">
                <div class="card-icon"><img src="img/loupe.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('cuadrillas.card_filtrar_titulo') ?></h3>
                <p><?= t('cuadrillas.card_filtrar_desc') ?></p>
                <button class="card-btn"><?= t('cuadrillas.card_filtrar_titulo') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleBusquedaId()">
            <div class="card-content">
                <div class="card-icon"><img src="img/question.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('cuadrillas.card_buscar_titulo') ?></h3>
                <p><?= t('cuadrillas.card_buscar_desc') ?></p>
                <button class="card-btn"><?= t('cuadrillas.btn_buscar') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleModificar()">
            <div class="card-content">
                <div class="card-icon"><img src="img/refresh.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('cuadrillas.card_modificar_titulo') ?></h3>
                <p><?= t('cuadrillas.card_modificar_desc') ?></p>
                <button class="card-btn"><?= t('cuadrillas.btn_modificar') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleDarDeBaja()">
            <div class="card-content">
                <div class="card-icon"><img src="img/minus.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('cuadrillas.card_baja_titulo') ?></h3>
                <p><?= t('cuadrillas.card_baja_desc') ?></p>
                <button class="card-btn"><?= t('cuadrillas.card_baja_titulo') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleAsignarUsuario()">
            <div class="card-content">
                <div class="card-icon"><img src="img/user.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('cuadrillas.card_asignar_usuario_titulo') ?></h3>
                <p><?= t('cuadrillas.card_asignar_usuario_desc') ?></p>
                <button class="card-btn"><?= t('cuadrillas.btn_asignar') ?></button>
            </div>
        </div>

    </div>
</section>

<section class="control-bar" id="filterBar" style="display:none">
    <div class="control-inner">
        <select id="filtroTipo" class="ctrl-select" onchange="actualizarFiltroValor()">
            <option value=""><?= t('cuadrillas.filtrar_por') ?></option>
            <option value="estado"><?= t('cuadrillas.th_estado') ?></option>
            <option value="municipio"><?= t('cuadrillas.th_municipio') ?></option>
            <option value="disponibilidad"><?= t('cuadrillas.opt_activas') ?></option>
        </select>
        <input type="text" id="filtroValor" class="ctrl-input" placeholder="<?= htmlspecialchars(t('cuadrillas.placeholder_valor_filtro')) ?>" style="display:none">
        <select id="filtroValorSelect" class="ctrl-select" style="display:none"></select>
        <button class="btn-primary" onclick="aplicarFiltro()"><?= t('cuadrillas.btn_aplicar') ?></button>
        <button class="btn-secondary" onclick="listarCuadrillas()"><?= t('cuadrillas.btn_limpiar') ?></button>
    </div>
</section>

<section class="control-bar" id="searchBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="buscarId" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('cuadrillas.placeholder_id_cuadrilla')) ?>" min="1" style="max-width:200px">
        <button class="btn-primary" onclick="buscarPorId()"><?= t('cuadrillas.btn_buscar') ?></button>
        <button class="btn-secondary" onclick="listarCuadrillas()"><?= t('cuadrillas.btn_ver_todas') ?></button>
    </div>
</section>

<section class="control-bar" id="modificarBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="modificarId" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('cuadrillas.placeholder_id_cuadrilla')) ?>" min="1" style="max-width:140px">
        <select id="modificarCampo" class="ctrl-select" onchange="actualizarPlaceholderModificar()">
            <option value=""><?= t('cuadrillas.opt_campo') ?></option>
            <option value="municipio"><?= t('cuadrillas.th_municipio') ?></option>
            <option value="estado"><?= t('cuadrillas.th_estado') ?></option>
        </select>
        <input type="text" id="modificarValor" class="ctrl-input" style="display:none" placeholder="<?= htmlspecialchars(t('cuadrillas.placeholder_selecciona_campo')) ?>">
        <select id="modificarValorSelect" class="ctrl-select" style="display:none"></select>
        <button class="btn-primary" onclick="modificarCuadrilla()"><?= t('cuadrillas.btn_modificar') ?></button>
        <button class="btn-secondary" onclick="ocultarControles()"><?= t('cuadrillas.btn_cancelar') ?></button>
    </div>
</section>

<section class="control-bar" id="deleteBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="deleteId" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('cuadrillas.placeholder_id_baja')) ?>" min="1" style="max-width:260px">
        <button class="btn-danger" onclick="darDeBajaCuadrilla()"><?= t('cuadrillas.card_baja_titulo') ?></button>
        <button class="btn-secondary" onclick="ocultarControles()"><?= t('cuadrillas.btn_cancelar') ?></button>
    </div>
</section>

<section class="control-bar" id="asignarUsuarioBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="asignarCi" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('cuadrillas.placeholder_ci_usuario')) ?>" min="1" style="max-width:220px">
        <input type="number" id="asignarIdCuadrilla" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('cuadrillas.placeholder_id_cuadrilla')) ?>" min="1" style="max-width:200px">
        <button class="btn-primary" onclick="asignarUsuarioPorCi()"><?= t('cuadrillas.btn_asignar') ?></button>
        <button class="btn-secondary" onclick="ocultarControles()"><?= t('cuadrillas.btn_cancelar') ?></button>
    </div>
</section>

<section class="table-section">
    <div class="table-header">
        <h2 class="table-title" id="tableTitle"><?= t('cuadrillas.hero_title') ?></h2>
        <span class="table-count" id="tableCount"></span>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th><?= t('cuadrillas.th_id') ?></th>
                    <th><?= t('cuadrillas.th_municipio') ?></th>
                    <th><?= t('cuadrillas.th_estado') ?></th>
                    <th><?= t('cuadrillas.th_acciones') ?></th>
                </tr>
            </thead>
            <tbody id="cuadrillasBody"></tbody>
        </table>
        <div id="noResultados" class="empty-state" style="display:none">
            <?= t('cuadrillas.sin_resultados') ?>
        </div>
        <div id="loadingState" class="loading-state">
            <?= t('cuadrillas.cargando') ?>
        </div>
    </div>
</section>

<div class="modal-overlay" id="modalForm" style="display:none"
     onclick="onOverlayClick(event,'modalForm')">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle"><?= t('cuadrillas.card_agregar_titulo') ?></h3>
            <button class="modal-close" onclick="cerrarModal()">✕</button>
        </div>
        <form id="cuadrillaForm" onsubmit="guardarCuadrilla(event)">
            <input type="hidden" id="cuadrillaId">
            <div class="form-row">
                <div class="form-group">
                    <label for="municipio"><?= t('cuadrillas.th_municipio') ?></label>
                    <select id="municipio" class="form-input" required></select>
                </div>
                <div class="form-group">
                    <label for="estado"><?= t('cuadrillas.th_estado') ?></label>
                    <select id="estado" class="form-input">
                        <option value="activa" selected><?= t('cuadrillas.estado_activa') ?></option>
                        <option value="inactiva"><?= t('cuadrillas.estado_inactiva') ?></option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="cerrarModal()"><?= t('cuadrillas.btn_cancelar') ?></button>
                <button type="submit" class="btn-primary"><?= t('cuadrillas.btn_guardar') ?></button>
            </div>
        </form>

        <div class="miembros-section" id="miembrosSection" style="display:none">
            <h4 class="miembros-title"><?= t('cuadrillas.miembros_titulo') ?></h4>
            <ul class="miembros-list" id="miembrosList"></ul>
            <p class="empty-state miembros-empty" id="miembrosEmpty" style="display:none">
                <?= t('cuadrillas.miembros_vacio') ?>
            </p>
            <div class="miembros-asignar">
                <select id="miembroSelect" class="form-input"></select>
                <button type="button" class="btn-secondary" onclick="asignarMiembro()"><?= t('cuadrillas.btn_asignar') ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalDelete" style="display:none"
     onclick="onOverlayClick(event,'modalDelete')">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3><?= t('cuadrillas.modal_baja_titulo') ?></h3>
            <button class="modal-close" onclick="cerrarModalDelete()">✕</button>
        </div>
        <p class="delete-msg">
            <?= t('cuadrillas.confirmar_baja_pre') ?> <strong id="deleteNombre"></strong>?
        </p>
        <div class="modal-actions">
            <button class="btn-secondary" onclick="cerrarModalDelete()"><?= t('cuadrillas.btn_cancelar') ?></button>
            <button class="btn-danger" onclick="confirmarDelete()"><?= t('cuadrillas.btn_eliminar') ?></button>
        </div>
    </div>
</div>

<div id="toast" class="toast"></div>

<footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <div class="footer-logo">
                <img src="../../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="footer-logo-img">
                <span class="footer-logo-name">Recolix</span>
            </div>
            <p><?= t('common.footer_desc') ?></p>
        </div>
        <div class="footer-col">
            <h3><?= t('common.footer_navegacion') ?></h3>
            <ul>
                <li><a href="../../LandingPage/index_LandingPage.html"><?= t('backoffice.footer_pagina_principal') ?></a></li>
                <li><a href="../../LandingPage/mapa.html"><?= t('backoffice.footer_mapa_publico') ?></a></li>
            </ul>
        </div>
    </div>
    <div class="footer-hr"></div>
    <p class="footer-copy"><?= t('backoffice.footer_copyright') ?></p>
</footer>

<?php include __DIR__ . '/../../extra/i18n/validacion_js.php'; ?>
<script>
<?php
$I18N_KEYS = ['hero_title', 'resultado', 'resultados', 'th_acciones', 'th_municipio', 'th_estado', 'opt_activas', 'seleccionar', 'titulo_todas', 'label_estado', 'label_municipio', 'municipio_num', 'quitar_cuadrilla', 'sin_usuarios_disponibles', 'ya_en_cuadrilla', 'titulo_editar_btn', 'btn_eliminar', 'modal_editar', 'card_agregar_titulo', 'cargando', 'placeholder_selecciona_campo', 'estado_activa', 'estado_inactiva', 'api_error', 'ingresa_id', 'no_encontrada', 'selecciona_filtro', 'selecciona_valor', 'selecciona_municipio', 'selecciona_usuario', 'usuario_asignado', 'usuario_quitado', 'error_cargar_usuarios', 'error_asignar_usuario', 'error_quitar_usuario', 'error_guardar', 'error_eliminar', 'error_modificar', 'baja_ok', 'selecciona_campo_modificar', 'ingresa_nuevo_valor', 'ingresa_ci'];
?>
const I18N = <?= json_encode(array_combine($I18N_KEYS, array_map(function ($k) { return t('cuadrillas.' . $k); }, $I18N_KEYS)), JSON_UNESCAPED_UNICODE) ?>;

const API_BASE       = '/recolix/API_Usuarios/api.php';
const MUNICIPIOS_API = '/recolix/API_Gestion/api.php';

let cuadrillaIdParaEliminar = null;
let toastTimeout;
let reglas = {};
let municipios = [];

document.addEventListener('DOMContentLoaded', async () => {
    await cargarMunicipios();
    await cargarReglas();
    listarCuadrillas();
});

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
    return m ? m.nombre : `${I18N.municipio_num}${id}`;
}

function escaparTexto(texto) {
    const div = document.createElement('div');
    div.textContent = texto ?? '';
    return div.innerHTML;
}

async function listarCuadrillas() {
    ocultarControles();
    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/cuadrillas`);
        const json = await res.json();
        if (json.status === 'success') {
            renderTabla(json.data);
            setTableTitle(I18N.titulo_todas);
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.api_error, 'error');
    } finally {
        setLoading(false);
    }
}

async function buscarPorId() {
    const id = document.getElementById('buscarId').value.trim();
    if (!id) { mostrarToast(I18N.ingresa_id, 'warning'); return; }
    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/cuadrillas/${id}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            renderTabla([json.data]);
            setTableTitle(`Cuadrilla #${id}`);
        } else {
            renderTabla([]);
            mostrarToast(json.message || I18N.no_encontrada, 'error');
        }
    } catch {
        mostrarToast(I18N.api_error, 'error');
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

    const sinValor = ['disponibilidad'];
    if (sinValor.includes(tipo) || !tipo) return;

    if (tipo === 'estado') {
        select.innerHTML = '<option value="">' + I18N.seleccionar + '</option>' +
            ['activa', 'inactiva'].map(v => `<option value="${v}">${formatearValor(v)}</option>`).join('');
        select.style.display = '';
    } else if (tipo === 'municipio') {
        select.innerHTML = '<option value="">' + I18N.seleccionar + '</option>' +
            municipios.map(m => `<option value="${m.id_municipio}">${m.nombre}</option>`).join('');
        select.style.display = '';
    }
}

async function aplicarFiltro() {
    const tipo   = document.getElementById('filtroTipo').value;
    const input  = document.getElementById('filtroValor');
    const select = document.getElementById('filtroValorSelect');
    if (!tipo) { mostrarToast(I18N.selecciona_filtro, 'warning'); return; }

    const sinValor  = ['disponibilidad'];
    const accionMap = { estado: 'filtrarEstado', municipio: 'filtrarMunicipio', disponibilidad: 'filtrarDisponibilidad' };
    const paramMap  = { estado: 'estado', municipio: 'idMunicipio' };

    const valor = sinValor.includes(tipo) ? '' :
        (select.style.display !== 'none' ? select.value : input.value).trim();

    if (!sinValor.includes(tipo) && !valor) {
        mostrarToast(I18N.selecciona_valor, 'warning');
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
                estado: `${I18N.label_estado}: ${formatearValor(valor)}`,
                municipio: `${I18N.label_municipio}: ${nombreMunicipio(valor)}`,
                disponibilidad: I18N.opt_activas
            };
            setTableTitle(`Filtro — ${labels[tipo] || tipo}`);
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.api_error, 'error');
    } finally {
        setLoading(false);
    }
}

function abrirModalAgregar() {
    document.getElementById('modalTitle').textContent = I18N.card_agregar_titulo;
    document.getElementById('cuadrillaId').value = '';
    document.getElementById('cuadrillaForm').reset();
    document.getElementById('miembrosSection').style.display = 'none';
    document.getElementById('modalForm').style.display = 'flex';
}

function abrirModalEditar(cuadrilla) {
    document.getElementById('modalTitle').textContent = I18N.modal_editar;
    document.getElementById('cuadrillaId').value = cuadrilla.id_cuadrilla;

    const municipioEl = document.getElementById('municipio');
    municipioEl.value = cuadrilla.id_municipio;

    const estadoEl = document.getElementById('estado');
    estadoEl.value = cuadrilla.estado;
    if (!estadoEl.value) estadoEl.selectedIndex = 0;

    document.getElementById('miembrosSection').style.display = 'block';
    cargarMiembros(cuadrilla.id_cuadrilla);

    document.getElementById('modalForm').style.display = 'flex';
}

async function cargarMiembros(idCuadrilla) {
    const lista  = document.getElementById('miembrosList');
    const vacio  = document.getElementById('miembrosEmpty');
    const select = document.getElementById('miembroSelect');
    lista.innerHTML = '';
    select.innerHTML = '<option value="">' + I18N.cargando + '</option>';

    try {
        const [resMiembros, resCandidatos] = await Promise.all([
            fetch(`${API_BASE}/usuarios/obtenerPorCuadrilla/${idCuadrilla}`),
            fetch(`${API_BASE}/usuarios/filtrarRol?rol=cuadrilla`)
        ]);
        const jsonMiembros   = await resMiembros.json();
        const jsonCandidatos = await resCandidatos.json();

        const miembros   = jsonMiembros.status === 'success' ? jsonMiembros.data : [];
        const candidatos  = jsonCandidatos.status === 'success' ? jsonCandidatos.data : [];

        vacio.style.display = miembros.length ? 'none' : 'block';
        miembros.forEach(m => {
            const li = document.createElement('li');
            li.className = 'miembro-item';
            li.innerHTML = `
                <span><span class="miembro-nombre">${m.primer_nombre} ${m.p_apellido}</span>
                <span class="miembro-ci"> — CI ${m.ci_usuario}</span></span>
                <button type="button" class="btn-action" title="${I18N.quitar_cuadrilla}" onclick="quitarMiembro(${m.ci_usuario}, ${idCuadrilla})">🗑️</button>
            `;
            lista.appendChild(li);
        });

        const disponibles = candidatos.filter(c => String(c.id_cuadrilla) !== String(idCuadrilla));
        if (!disponibles.length) {
            select.innerHTML = '<option value="">' + I18N.sin_usuarios_disponibles + '</option>';
        } else {
            select.innerHTML = disponibles.map(c => {
                const etiqueta = c.id_cuadrilla
                    ? `${c.primer_nombre} ${c.p_apellido} (CI ${c.ci_usuario}) — ${I18N.ya_en_cuadrilla} #${c.id_cuadrilla}`
                    : `${c.primer_nombre} ${c.p_apellido} (CI ${c.ci_usuario})`;
                return `<option value="${c.ci_usuario}">${etiqueta}</option>`;
            }).join('');
        }
    } catch {
        mostrarToast(I18N.error_cargar_usuarios, 'error');
    }
}

async function asignarMiembro() {
    const idCuadrilla = document.getElementById('cuadrillaId').value;
    const select = document.getElementById('miembroSelect');
    const ci = select.value;
    if (!ci) { mostrarToast(I18N.selecciona_usuario, 'warning'); return; }

    try {
        const res = await fetch(`${API_BASE}/usuarios/asignarCuadrilla`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ci: parseInt(ci), idCuadrilla: parseInt(idCuadrilla) })
        });
        const json = await res.json();
        if (json.status === 'success') {
            mostrarToast(I18N.usuario_asignado, 'success');
            cargarMiembros(idCuadrilla);
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.error_asignar_usuario, 'error');
    }
}

async function quitarMiembro(ci, idCuadrilla) {
    try {
        const res = await fetch(`${API_BASE}/usuarios/quitarCuadrilla/${ci}`, { method: 'POST' });
        const json = await res.json();
        if (json.status === 'success') {
            mostrarToast(I18N.usuario_quitado, 'success');
            cargarMiembros(idCuadrilla);
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.error_quitar_usuario, 'error');
    }
}

async function guardarCuadrilla(e) {
    e.preventDefault();
    const id = document.getElementById('cuadrillaId').value;

    const idMunicipio = document.getElementById('municipio').value;
    const estado      = document.getElementById('estado').value;

    if (!idMunicipio) {
        mostrarToast(I18N.selecciona_municipio, 'warning');
        return;
    }

    const campos = { idMunicipio, estado };
    for (const [campo, valor] of Object.entries(campos)) {
        const regla = reglas[campo];
        if (!regla) continue;
        if (regla.valores && !regla.valores.includes(valor)) {
            mostrarToast(msgValidacion('noValido', campo, regla), 'warning');
            return;
        }
        if (regla.tipo === 'entero') {
            const num = parseInt(valor);
            if (isNaN(num)) {
                mostrarToast(msgValidacion('entero', campo, regla), 'warning');
                return;
            }
            if (regla.min !== undefined && num < regla.min) {
                mostrarToast(msgValidacion('menor', campo, regla), 'warning');
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
        mostrarToast(I18N.error_guardar, 'error');
    }
}

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
            mostrarToast(I18N.baja_ok, 'success');
            cerrarModalDelete();
            listarCuadrillas();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.error_eliminar, 'error');
    }
}

async function darDeBajaCuadrilla() {
    const id = document.getElementById('deleteId').value.trim();
    if (!id) { mostrarToast(I18N.ingresa_id, 'warning'); return; }
    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/cuadrillas/${id}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            confirmarBorrar(json.data.id_cuadrilla, nombreMunicipio(json.data.id_municipio));
            ocultarControles();
        } else {
            mostrarToast(json.message || I18N.no_encontrada, 'error');
        }
    } catch {
        mostrarToast(I18N.api_error, 'error');
    } finally {
        setLoading(false);
    }
}

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
        input.placeholder = I18N.placeholder_selecciona_campo;
        input.style.display = '';
    }
}

async function modificarCuadrilla() {
    const id = document.getElementById('modificarId').value.trim();
    if (!id) { mostrarToast(I18N.ingresa_id, 'warning'); return; }
    const campo = document.getElementById('modificarCampo').value;
    if (!campo) { mostrarToast(I18N.selecciona_campo_modificar, 'warning'); return; }

    const input  = document.getElementById('modificarValor');
    const select = document.getElementById('modificarValorSelect');
    const valor  = (select.style.display !== 'none' ? select : input).value.trim();
    if (!valor) { mostrarToast(I18N.ingresa_nuevo_valor, 'warning'); return; }

    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/cuadrillas/${id}`);
        const json = await res.json();
        if (json.status !== 'success' || !json.data) {
            mostrarToast(json.message || I18N.no_encontrada, 'error');
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
        mostrarToast(I18N.error_modificar, 'error');
    } finally {
        setLoading(false);
    }
}

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
    count.textContent   = `${cuadrillas.length} ${cuadrillas.length !== 1 ? I18N.resultados : I18N.resultado}`;

    cuadrillas.forEach(c => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="td-id" data-label="ID">${c.id_cuadrilla}</td>
            <td data-label="${I18N.th_municipio}"><strong>${nombreMunicipio(c.id_municipio)}</strong></td>
            <td data-label="${I18N.th_estado}"><span class="badge badge-${badgeClass(c.estado)}">${formatearValor(c.estado)}</span></td>
            <td class="td-actions" data-label="${I18N.th_acciones}">
                <button class="btn-action" onclick='abrirModalEditar(${JSON.stringify(c)})' title="${I18N.titulo_editar_btn}">✏️</button>
                <button class="btn-action" onclick="confirmarBorrar(${c.id_cuadrilla},'${nombreMunicipio(c.id_municipio)}')" title="${I18N.btn_eliminar}">🗑️</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function formatearValor(v) {
    const estados = { activa: I18N.estado_activa, inactiva: I18N.estado_inactiva };
    return estados[v] ?? String(v).replace(/_/g, ' ');
}

function badgeClass(estado) {
    const map = { 'activa': 'success', 'inactiva': 'danger' };
    return map[estado] ?? 'neutral';
}

function toggleFiltros() {
    const fb = document.getElementById('filterBar');
    ocultarControles();
    fb.style.display = fb.style.display === 'none' ? 'block' : 'none';
}

function toggleBusquedaId() {
    const sb = document.getElementById('searchBar');
    ocultarControles();
    sb.style.display = sb.style.display === 'none' ? 'block' : 'none';
}

function ocultarControles() {
    document.getElementById('filterBar').style.display = 'none';
    document.getElementById('searchBar').style.display = 'none';
    document.getElementById('modificarBar').style.display = 'none';
    document.getElementById('deleteBar').style.display = 'none';
    document.getElementById('asignarUsuarioBar').style.display = 'none';
}

function toggleModificar() {
    const mb = document.getElementById('modificarBar');
    ocultarControles();
    mb.style.display = mb.style.display === 'none' ? 'block' : 'none';
}

function toggleDarDeBaja() {
    const db = document.getElementById('deleteBar');
    ocultarControles();
    db.style.display = db.style.display === 'none' ? 'block' : 'none';
}

function toggleAsignarUsuario() {
    const ab = document.getElementById('asignarUsuarioBar');
    ocultarControles();
    ab.style.display = ab.style.display === 'none' ? 'block' : 'none';
}

async function asignarUsuarioPorCi() {
    const ci = document.getElementById('asignarCi').value.trim();
    const idCuadrilla = document.getElementById('asignarIdCuadrilla').value.trim();

    if (!ci) { mostrarToast(I18N.ingresa_ci, 'warning'); return; }
    if (!idCuadrilla) { mostrarToast(I18N.ingresa_id, 'warning'); return; }

    try {
        const res = await fetch(`${API_BASE}/usuarios/asignarCuadrilla`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ci: parseInt(ci), idCuadrilla: parseInt(idCuadrilla) })
        });
        const json = await res.json();
        if (json.status === 'success') {
            mostrarToast(I18N.usuario_asignado, 'success');
            document.getElementById('asignarCi').value = '';
            document.getElementById('asignarIdCuadrilla').value = '';
            ocultarControles();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.error_asignar_usuario, 'error');
    }
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

    <script src="../../Backoffice/js/paginacion.js"></script>
</body>
</html>