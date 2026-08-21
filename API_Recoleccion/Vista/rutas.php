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
    <title><?= t('rutas.title') ?></title>
    <link rel="stylesheet" href="styleRecoleccion.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
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
        .resumen-item-acciones { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
        .btn-sm { padding: 4px 10px; font-size: 0.76rem; border-radius: 6px; }

        .modal-lg { max-width: 860px; }
        .levante-layout { display: grid; grid-template-columns: 1.2fr 1fr; gap: 18px; margin: 16px 0; }
        .levante-map { border: 1px solid var(--border); border-radius: var(--radius); min-height: 340px; overflow: hidden; }
        .levante-map #mapaLevante { height: 340px; width: 100%; }
        .levante-form { display: flex; flex-direction: column; gap: 12px; }
        .levante-form label { font-size: 0.78rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .levante-form label:not(:first-child) { margin-top: 4px; }
        .levante-historial { border-top: 1px solid var(--border); padding-top: 14px; }
        .levante-historial h4 { margin: 0 0 10px; font-size: 0.9rem; color: var(--text); }
        .levante-historial .resumen-item { font-size: 0.82rem; }
        .levante-resumen-lev { display: inline-flex; gap: 8px; align-items: center; font-size: 0.8rem; color: var(--muted); }
        .mapa-orden-badge {
            display: inline-block; min-width: 20px; height: 20px; line-height: 20px; text-align: center;
            border-radius: 50%; background: var(--moss-dark); color: #fff; font-size: 0.7rem; font-weight: 700;
            margin-right: 6px;
        }
        .mapa-popup { font-size: 0.82rem; }
        .mapa-popup small { color: var(--muted); }

        @media (max-width: 1024px) { .resumen-section { padding: 16px 20px 4px; } }
        @media (max-width: 768px)  { .resumen-section { grid-template-columns: 1fr; padding: 16px 16px 4px; gap: 14px; }
                                     .levante-layout { grid-template-columns: 1fr; } }
    </style>
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
                <a href="camiones.php"><?= t('backoffice.nav_camiones') ?></a>
                <a href="circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
                <a href="#" class="nav-active"><?= t('backoffice.nav_rutas') ?></a>
                <a href="#"><?= t('backoffice.nav_cuadrillas') ?></a>
                <a href="../../API_Gestion/Vista/contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
                <a href="../../API_Gestion/Vista/centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
                <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
                <a href="#"><?= t('backoffice.nav_reportes') ?></a>
                <a href="../../API_Gestion/Vista/incidencias.php"><?= t('backoffice.nav_incidencias') ?></a>
                <a href="../../API_Usuarios/Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
            </nav>
        </div>
        <div class="nav-right">
            <?php include __DIR__ . '/../../i18n/switcher.php'; ?>
            <a href="/recolix/API_Usuarios/perfil.php" class="btn-user" title="<?= htmlspecialchars(t('backoffice.nav_perfil_usuario')) ?>">
                <img src="img/user.svg" alt="Usuario" class="nav-user-icon">
            </a>
            <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="<?= htmlspecialchars(t('backoffice.nav_menu')) ?>">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
    <nav class="nav-mobile" id="navMobile">
        <a href="../../Backoffice/indexBackoffice.php"><?= t('backoffice.nav_inicio') ?></a>
        <a href="camiones.php"><?= t('backoffice.nav_camiones') ?></a>
        <a href="circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
        <a href="#" class="nav-active"><?= t('backoffice.nav_rutas') ?></a>
        <a href="#"><?= t('backoffice.nav_cuadrillas') ?></a>
        <a href="../../API_Gestion/Vista/contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
        <a href="../../API_Gestion/Vista/centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
        <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
        <a href="#"><?= t('backoffice.nav_reportes') ?></a>
        <a href="../../API_Gestion/Vista/incidencias.php"><?= t('backoffice.nav_incidencias') ?></a>
        <a href="../../API_Usuarios/Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
    </nav>
    <div class="nav-hr"></div>
</header>

<section class="hero">
    <h1 class="hero-title"><?= t('rutas.hero_title') ?></h1>
    <p class="hero-sub"><?= t('rutas.hero_sub') ?></p>
</section>

<section class="resumen-section">
    <div class="resumen-card">
        <div class="resumen-card-head"><span class="resumen-dot activas"></span><h3><?= t('rutas.resumen_activas') ?></h3></div>
        <div class="resumen-count" id="countActivas">—</div>
        <ul class="resumen-list" id="listaActivas"></ul>
    </div>
    <div class="resumen-card">
        <div class="resumen-card-head"><span class="resumen-dot pendientes"></span><h3><?= t('rutas.resumen_pendientes') ?></h3></div>
        <div class="resumen-count" id="countPendientes">—</div>
        <ul class="resumen-list" id="listaPendientes"></ul>
    </div>
    <div class="resumen-card">
        <div class="resumen-card-head"><span class="resumen-dot prioritarias"></span><h3><?= t('rutas.resumen_prioritarias') ?></h3></div>
        <p class="resumen-hint"><?= t('rutas.resumen_prioritarias_hint') ?></p>
        <ul class="resumen-list" id="listaPrioritarias"></ul>
    </div>
</section>

<section class="cards-section">
    <div class="cards-grid">

        <div class="card-operar" onclick="toggleFiltros()">
            <div class="card-content">
                <div class="card-icon"><img src="img/loupe.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('crud.btn_filtrar') ?></h3>
                <p><?= t('rutas.card_filtrar_desc') ?></p>
                <button class="card-btn"><?= t('crud.btn_filtrar') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleBusquedaId()">
            <div class="card-content">
                <div class="card-icon"><img src="img/question.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('camiones.card_buscarid_titulo') ?></h3>
                <p><?= t('rutas.card_buscarid_desc') ?></p>
                <button class="card-btn"><?= t('crud.btn_buscar') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleDarDeBaja()">
            <div class="card-content">
                <div class="card-icon"><img src="img/minus.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('rutas.card_eliminar_titulo') ?></h3>
                <p><?= t('rutas.card_eliminar_desc') ?></p>
                <button class="card-btn"><?= t('crud.btn_eliminar') ?></button>
            </div>
        </div>

    </div>
</section>

<section class="control-bar" id="filterBar" style="display:none">
    <div class="control-inner">
        <select id="filtroTipo" class="ctrl-select" onchange="actualizarFiltroValor()">
            <option value=""><?= t('crud.filtrar_por') ?></option>
            <option value="circuitoId"><?= t('rutas.filtro_opt_circuito') ?></option>
            <option value="actividad"><?= t('rutas.filtro_opt_actividad') ?></option>
            <option value="nombre"><?= t('rutas.filtro_opt_nombre') ?></option>
            <option value="estadoLevante"><?= t('rutas.filtro_opt_estado_levante') ?></option>
        </select>
        <input type="text" id="filtroValor" class="ctrl-input" placeholder="<?= htmlspecialchars(t('crud.valor_filtro_placeholder')) ?>" style="display:none">
        <select id="filtroValorSelect" class="ctrl-select" style="display:none"></select>
        <button class="btn-primary" onclick="aplicarFiltro()"><?= t('crud.btn_aplicar') ?></button>
        <button class="btn-secondary" onclick="listarRutas()"><?= t('crud.btn_limpiar') ?></button>
    </div>
</section>

<section class="control-bar" id="searchBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="buscarId" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('rutas.searchid_ph')) ?>" min="1" style="max-width:200px">
        <button class="btn-primary" onclick="buscarPorId()"><?= t('crud.btn_buscar') ?></button>
        <button class="btn-secondary" onclick="listarRutas()"><?= t('crud.btn_vertodos') ?></button>
    </div>
</section>

<section class="control-bar" id="deleteBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="deleteId" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('rutas.deleteid_ph')) ?>" min="1" style="max-width:240px">
        <button class="btn-danger" onclick="darDeBajaRuta()"><?= t('crud.btn_eliminar') ?></button>
        <button class="btn-secondary" onclick="ocultarControles()"><?= t('crud.btn_cancelar') ?></button>
    </div>
</section>

<section class="table-section">
    <div class="table-header">
        <h2 class="table-title" id="tableTitle"><?= t('rutas.hero_title') ?></h2>
        <span class="table-count" id="tableCount"></span>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th><?= t('crud.th_id') ?></th>
                    <th><?= t('rutas.th_circuito') ?></th>
                    <th><?= t('rutas.th_nombre') ?></th>
                    <th><?= t('rutas.th_actividad') ?></th>
                    <th><?= t('rutas.th_turno') ?></th>
                    <th><?= t('rutas.th_levante') ?></th>
                    <th><?= t('rutas.th_llenado_prom') ?></th>
                    <th><?= t('crud.th_acciones') ?></th>
                </tr>
            </thead>
            <tbody id="rutasBody"></tbody>
        </table>
        <div id="noResultados" class="empty-state" style="display:none">
            <?= t('crud.empty_state') ?>
        </div>
        <div id="loadingState" class="loading-state">
            <?= t('crud.loading_state') ?>
        </div>
    </div>
</section>

<div class="modal-overlay" id="modalLevante" style="display:none"
     onclick="onOverlayClick(event,'modalLevante')">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3><?= t('rutas.modal_levante_titulo') ?> <span id="levanteRutaTitulo"></span></h3>
            <button class="modal-close" onclick="cerrarModalLevante()">✕</button>
        </div>
        <div class="levante-layout">
            <div class="levante-map">
                <div id="mapaLevante"></div>
            </div>
            <div class="levante-form" id="levanteFormWrap">
                <label><?= t('rutas.field_camion_disp') ?></label>
                <select id="levanteCamion" class="ctrl-select"></select>
                <label><?= t('rutas.field_cuadrilla') ?></label>
                <select id="levanteCuadrilla" class="ctrl-select"></select>
                <label><?= t('rutas.field_hora_salida') ?></label>
                <input type="datetime-local" id="levanteHoraSalida" class="ctrl-input" style="min-width:0">
                <label><?= t('rutas.field_fecha_levante') ?></label>
                <input type="date" id="levanteFecha" class="ctrl-input" style="min-width:0">
                <button class="btn-primary" onclick="planificarLevante()" style="margin-top:4px"><?= t('rutas.btn_planificar_levante') ?></button>
            </div>
            <p class="resumen-empty" id="levanteFormBloqueado" style="display:none">
                <?= t('rutas.levante_bloqueado_msg') ?>
            </p>
        </div>
        <div class="levante-historial">
            <h4><?= t('rutas.levantes_de_ruta') ?></h4>
            <ul class="resumen-list" id="levantesRuta"></ul>
        </div>
    </div>
</div>

<div class="modal-overlay" id="modalDelete" style="display:none"
     onclick="onOverlayClick(event,'modalDelete')">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3><?= t('rutas.modal_delete_title') ?></h3>
            <button class="modal-close" onclick="cerrarModalDelete()">✕</button>
        </div>
        <p class="delete-msg">
            <?= t('rutas.delete_confirm_before') ?> <strong id="deleteNombre"></strong><?= t('rutas.delete_confirm_after') ?>
        </p>
        <div class="modal-actions">
            <button class="btn-secondary" onclick="cerrarModalDelete()"><?= t('crud.btn_cancelar') ?></button>
            <button class="btn-danger" onclick="confirmarDelete()"><?= t('crud.btn_eliminar') ?></button>
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

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const API_BASE = '/recolix/API_Recoleccion';
const I18N = <?= json_encode(t_array([
    'msgIngresaId' => 'crud.msg_ingresa_id',
    'msgErrorConectar' => 'crud.msg_error_conectar',
    'msgSeleccionaFiltro' => 'crud.msg_selecciona_filtro',
    'msgIngresaValorFiltro' => 'crud.msg_ingresa_valor_filtro',
    'msgErrorEliminar' => 'crud.msg_error_eliminar',
    'resultadoSingular' => 'crud.resultado_singular',
    'resultadoPlural' => 'crud.resultado_plural',
    'filtroPrefijo' => 'crud.filtro_prefijo',
    'thId' => 'crud.th_id',
    'thAcciones' => 'crud.th_acciones',
    'thCircuito' => 'rutas.th_circuito',
    'thNombre' => 'rutas.th_nombre',
    'thActividad' => 'rutas.th_actividad',
    'thTurno' => 'rutas.th_turno',
    'thLevante' => 'rutas.th_levante',
    'thLlenadoProm' => 'rutas.th_llenado_prom',
    'resumenSinRutas' => 'rutas.resumen_sin_rutas',
    'resumenCircuitoPrefijo' => 'rutas.resumen_circuito_prefijo',
    'resumenContenedorSingular' => 'rutas.resumen_contenedor_singular',
    'resumenContenedorPlural' => 'rutas.resumen_contenedor_plural',
    'filtroPhCircuito' => 'rutas.filtro_ph_circuito',
    'filtroPhActividad' => 'rutas.filtro_ph_actividad',
    'filtroPhNombre' => 'rutas.filtro_ph_nombre',
    'filtroValorPh' => 'crud.valor_filtro_placeholder',
    'filtroOptCircuito' => 'rutas.filtro_opt_circuito',
    'filtroOptActividad' => 'rutas.filtro_opt_actividad',
    'filtroOptNombre' => 'rutas.filtro_opt_nombre',
    'filtroEstadoLevantePrefijo' => 'rutas.filtro_estado_levante_prefijo',
    'tableTitleTodas' => 'rutas.table_title_todas',
    'tableTitleRutaId' => 'rutas.table_title_ruta_id',
    'msgRutaNoEncontrada' => 'rutas.msg_ruta_no_encontrada',
    'estadoSinLevante' => 'rutas.estado_sin_levante',
    'estadoPendiente' => 'rutas.estado_pendiente',
    'estadoEnCurso' => 'rutas.estado_en_curso',
    'estadoCompletado' => 'rutas.estado_completado',
    'estadoCancelado' => 'rutas.estado_cancelado',
    'sinCamionesDisp' => 'rutas.sin_camiones_disp',
    'sinCuadrillasActivas' => 'rutas.sin_cuadrillas_activas',
    'cuadrillaPrefijo' => 'rutas.cuadrilla_prefijo',
    'sinContenedoresAsignados' => 'rutas.sin_contenedores_asignados',
    'popupCapacidad' => 'rutas.popup_capacidad',
    'sinLevantesPlanificados' => 'rutas.sin_levantes_planificados',
    'camionPrefijo' => 'rutas.camion_prefijo',
    'salidaPrefijo' => 'rutas.salida_prefijo',
    'btnCancelarLevante' => 'rutas.btn_cancelar_levante',
    'msgLevanteCancelado' => 'rutas.msg_levante_cancelado',
    'msgErrorCancelarLevante' => 'rutas.msg_error_cancelar_levante',
    'msgFaltanCamionCuadrilla' => 'rutas.msg_faltan_camion_cuadrilla',
    'msgIngresaFechaLevante' => 'rutas.msg_ingresa_fecha_levante',
    'msgLevantePlanificado' => 'rutas.msg_levante_planificado',
    'msgRutaEliminada' => 'rutas.msg_ruta_eliminada',
    'badgeSinAsignar' => 'rutas.badge_sin_asignar',
    'badgeAsignado' => 'rutas.badge_asignado',
    'titlePlanificarLevante' => 'rutas.title_planificar_levante',
    'btnEliminar' => 'crud.btn_eliminar',
    'turnoManana' => 'circuitos.opt_turno_manana',
    'turnoTarde' => 'circuitos.opt_turno_tarde',
    'turnoNoche' => 'circuitos.opt_turno_noche',
])) ?>;
let rutaIdParaEliminar = null;
let toastTimeout;
let reglas = {};
let mapaLevante = null;
let levanteRutaActual = null;
let marcadoresLevante = [];

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
        ul.innerHTML = `<li class="resumen-empty">${I18N.resumenSinRutas}</li>`;
        return;
    }
    ul.innerHTML = rutas.map(r => `
        <li class="resumen-item">
            <span><strong>#${r.id_ruta}</strong> ${r.nombre}<br><small>${I18N.resumenCircuitoPrefijo} ${r.id_circuito} · ${r.contenedores_count} ${r.contenedores_count !== 1 ? I18N.resumenContenedorPlural : I18N.resumenContenedorSingular}</small></span>
            <span class="resumen-llenado">${r.llenado_promedio !== null ? r.llenado_promedio + '%' : '—'}</span>
        </li>
    `).join('');
}

const ORDEN_ESTADO_LEVANTE_DEFAULT = { sin_levante: 0, pendiente: 1, en_curso: 2 };

async function listarRutas() {
    ocultarControles();
    setLoading(true);
    try {
        const rutas = await cargarResumen();
        const visibles = (rutas || [])
            .filter(r => r.estado_levante in ORDEN_ESTADO_LEVANTE_DEFAULT)
            .sort((a, b) => ORDEN_ESTADO_LEVANTE_DEFAULT[a.estado_levante] - ORDEN_ESTADO_LEVANTE_DEFAULT[b.estado_levante]);
        renderTabla(visibles);
        setTableTitle(I18N.tableTitleTodas);
    } catch {
        mostrarToast(I18N.msgErrorConectar, 'error');
    } finally {
        setLoading(false);
    }
}

async function buscarPorId() {
    const id = document.getElementById('buscarId').value.trim();
    if (!id) { mostrarToast(I18N.msgIngresaId, 'warning'); return; }
    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/rutas/${id}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            renderTabla([json.data]);
            setTableTitle(`${I18N.tableTitleRutaId}${id}`);
        } else {
            renderTabla([]);
            mostrarToast(json.message || I18N.msgRutaNoEncontrada, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorConectar, 'error');
    } finally {
        setLoading(false);
    }
}

const ESTADOS_LEVANTE = {
    sin_levante: I18N.estadoSinLevante,
    pendiente:   I18N.estadoPendiente,
    en_curso:    I18N.estadoEnCurso,
    completado:  I18N.estadoCompletado,
    cancelado:   I18N.estadoCancelado
};

function actualizarFiltroValor() {
    const tipo   = document.getElementById('filtroTipo').value;
    const input  = document.getElementById('filtroValor');
    const select = document.getElementById('filtroValorSelect');

    input.value  = '';
    select.value = '';
    input.style.display  = 'none';
    select.style.display = 'none';

    if (!tipo) return;

    if (tipo === 'estadoLevante') {
        select.innerHTML = '<option value="">— Seleccionar —</option>' +
            Object.entries(ESTADOS_LEVANTE).map(([v, label]) => `<option value="${v}">${label}</option>`).join('');
        select.style.display = '';
        return;
    }

    const placeholders = {
        circuitoId: I18N.filtroPhCircuito,
        actividad:  I18N.filtroPhActividad,
        nombre:     I18N.filtroPhNombre
    };
    input.placeholder = placeholders[tipo] || I18N.filtroValorPh;
    input.style.display = '';
}

async function aplicarFiltro() {
    const tipo   = document.getElementById('filtroTipo').value;
    const input  = document.getElementById('filtroValor');
    const select = document.getElementById('filtroValorSelect');
    if (!tipo) { mostrarToast(I18N.msgSeleccionaFiltro, 'warning'); return; }

    const valor = (tipo === 'estadoLevante' ? select : input).value.trim();
    if (!valor) {
        mostrarToast(I18N.msgIngresaValorFiltro, 'warning');
        return;
    }

    setLoading(true);
    try {
        if (tipo === 'estadoLevante') {
            const rutas = await cargarResumen();
            renderTabla((rutas || []).filter(r => r.estado_levante === valor));
            setTableTitle(`${I18N.filtroEstadoLevantePrefijo} ${ESTADOS_LEVANTE[valor]}`);
            return;
        }

        const res  = await fetch(`${API_BASE}/rutas?${tipo}=${encodeURIComponent(valor)}`);
        const json = await res.json();
        if (json.status === 'success') {
            renderTabla(json.data);
            const labels = {
                circuitoId: `${I18N.filtroOptCircuito}: ${valor}`, actividad: `${I18N.filtroOptActividad}: ${valor}`, nombre: `${I18N.filtroOptNombre}: ${valor}`
            };
            setTableTitle(`${I18N.filtroPrefijo} ${labels[tipo] || tipo}`);
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorConectar, 'error');
    } finally {
        setLoading(false);
    }
}

async function abrirPlanificarLevante(rutaId) {
    levanteRutaActual = rutaId;
    document.getElementById('levanteRutaTitulo').textContent = `${I18N.tableTitleRutaId}${rutaId}`;
    document.getElementById('modalLevante').style.display = 'flex';
    await Promise.all([cargarSelectsLevante(), cargarContenedoresLevante(rutaId), cargarLevantesRuta(rutaId)]);
}

async function cargarSelectsLevante() {
    const [camRes, cuaRes] = await Promise.all([
        fetch(`${API_BASE}/camiones/filtrarDisponibilidad`),
        fetch(`${API_BASE}/levantes/obtenerCuadrillas`)
    ]);
    const camiones = (await camRes.json()).data || [];
    const cuadrillas = (await cuaRes.json()).data || [];

    const selCam = document.getElementById('levanteCamion');
    selCam.innerHTML = camiones.length
        ? camiones.map(c => `<option value="${c.id_camion}">${c.matricula}${c.modelo ? ' · ' + c.modelo : ''}</option>`).join('')
        : `<option value="">${I18N.sinCamionesDisp}</option>`;

    const selCua = document.getElementById('levanteCuadrilla');
    selCua.innerHTML = cuadrillas.length
        ? cuadrillas.map(c => `<option value="${c.id_cuadrilla}">${I18N.cuadrillaPrefijo}${c.id_cuadrilla}${c.municipio ? ' · ' + c.municipio : ''}</option>`).join('')
        : `<option value="">${I18N.sinCuadrillasActivas}</option>`;

    const ahora = new Date();
    document.getElementById('levanteHoraSalida').value = ahora.toISOString().slice(0, 16);
    document.getElementById('levanteFecha').value = ahora.toISOString().slice(0, 10);
}

async function cargarContenedoresLevante(rutaId) {
    const res = await fetch(`${API_BASE}/rutas/${rutaId}/obtenerContenedores`);
    const json = await res.json();
    const contenedores = (json.status === 'success' && json.data) ? json.data : [];

    if (mapaLevante) {
        mapaLevante.remove();
        mapaLevante = null;
    }
    marcadoresLevante = [];

    mapaLevante = L.map('mapaLevante').setView([-34.9, -56.2], 13);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(mapaLevante);

    if (!contenedores.length) {
        L.marker([-34.9, -56.2]).addTo(mapaLevante).bindPopup(I18N.sinContenedoresAsignados);
        return;
    }

    const puntos = [];
    contenedores.forEach(c => {
        if (c.latitud === null || c.longitud === null) return;
        puntos.push([parseFloat(c.latitud), parseFloat(c.longitud)]);
        const marker = L.marker(puntos[puntos.length - 1]).addTo(mapaLevante);
        marker.bindPopup(`
            <div class="mapa-popup">
                <span class="mapa-orden-badge">${c.orden_visita}</span>
                <strong>#${c.id_contenedor}</strong> ${c.calle ?? ''} ${c.num_calle ?? ''}<br>
                <small>${I18N.popupCapacidad} ${c.capacidad ?? '—'} · ${c.estado ?? ''}</small>
            </div>`);
        marcadoresLevante.push(marker);
    });

    if (puntos.length > 1) {
        L.polyline(puntos, { color: '#3f5a33', weight: 2, dashArray: '6 6' }).addTo(mapaLevante);
    }
    if (puntos.length) {
        mapaLevante.fitBounds(L.latLngBounds(puntos).pad(0.15));
    }
}

async function cargarLevantesRuta(rutaId) {
    const res = await fetch(`${API_BASE}/levantes/obtenerPorRuta/${rutaId}`);
    const json = await res.json();
    const levantes = (json.status === 'success' && json.data) ? json.data : [];
    const ul = document.getElementById('levantesRuta');

    const hayActivo = levantes.some(l => l.estado === 'pendiente' || l.estado === 'en_curso');
    document.getElementById('levanteFormWrap').style.display = hayActivo ? 'none' : '';
    document.getElementById('levanteFormBloqueado').style.display = hayActivo ? '' : 'none';

    if (!levantes.length) {
        ul.innerHTML = `<li class="resumen-empty">${I18N.sinLevantesPlanificados}</li>`;
        return;
    }

    const estados = { pendiente: 'warning', en_curso: 'info', completado: 'success', cancelado: 'neutral' };
    ul.innerHTML = levantes.map(l => `
        <li class="resumen-item">
            <span>
                <strong>#${l.id_levante}</strong> ${l.hora_levante ?? ''}<br>
                <small>${I18N.camionPrefijo} ${l.matricula ?? '—'} · ${I18N.salidaPrefijo} ${l.hora_salida ? l.hora_salida.slice(11, 16) : '—'}</small>
            </span>
            <span class="resumen-item-acciones">
                <span class="badge badge-${estados[l.estado] ?? 'neutral'}">${ESTADOS_LEVANTE[l.estado] ?? l.estado}</span>
                ${l.estado === 'pendiente' ? `<button type="button" class="btn-danger btn-sm" onclick="cancelarLevanteItem(${l.id_levante})">${I18N.btnCancelarLevante}</button>` : ''}
            </span>
        </li>
    `).join('');
}

async function cancelarLevanteItem(idLevante) {
    try {
        const res = await fetch(`${API_BASE}/levantes/cancelar/${idLevante}`, { method: 'POST' });
        const json = await res.json();
        if (json.status === 'success') {
            mostrarToast(I18N.msgLevanteCancelado, 'success');
            await Promise.all([cargarLevantesRuta(levanteRutaActual), cargarSelectsLevante()]);
            listarRutas();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorCancelarLevante, 'error');
    }
}

async function planificarLevante() {
    const camionId = document.getElementById('levanteCamion').value;
    const cuadrillaId = document.getElementById('levanteCuadrilla').value;
    if (!camionId || !cuadrillaId) {
        mostrarToast(I18N.msgFaltanCamionCuadrilla, 'warning');
        return;
    }
    const horaSalida = document.getElementById('levanteHoraSalida').value.replace('T', ' ');
    const fecha = document.getElementById('levanteFecha').value;
    if (!fecha) { mostrarToast(I18N.msgIngresaFechaLevante, 'warning'); return; }

    try {
        const res = await fetch(`${API_BASE}/levantes/planificar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ rutaId: levanteRutaActual, camionId: parseInt(camionId), cuadrillaId: parseInt(cuadrillaId), horaSalida, horaLevante: fecha })
        });
        const json = await res.json();
        if (json.status === 'success') {
            mostrarToast(I18N.msgLevantePlanificado, 'success');
            cerrarModalLevante();
            listarRutas();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorConectar, 'error');
    }
}

function cerrarModalLevante() {
    document.getElementById('modalLevante').style.display = 'none';
    if (mapaLevante) { mapaLevante.remove(); mapaLevante = null; }
    levanteRutaActual = null;
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
            mostrarToast(I18N.msgRutaEliminada, 'success');
            cerrarModalDelete();
            listarRutas();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorEliminar, 'error');
    }
}

async function darDeBajaRuta() {
    const id = document.getElementById('deleteId').value.trim();
    if (!id) { mostrarToast(I18N.msgIngresaId, 'warning'); return; }
    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/rutas/${id}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            confirmarBorrar(json.data.id_ruta, json.data.nombre);
            ocultarControles();
        } else {
            mostrarToast(json.message || I18N.msgRutaNoEncontrada, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorConectar, 'error');
    } finally {
        setLoading(false);
    }
}

function badgeClassTurno(turno) {
    const map = { 'mañana': 'info', 'tarde': 'warning', 'noche': 'neutral' };
    return map[turno] ?? 'neutral';
}

function turnoFormateado(turno) {
    const map = { 'mañana': I18N.turnoManana, 'tarde': I18N.turnoTarde, 'noche': I18N.turnoNoche };
    return map[turno] ?? turno;
}

const BADGE_ESTADO_LEVANTE = {
    sin_levante: { clase: 'neutral', label: I18N.badgeSinAsignar },
    pendiente:   { clase: 'warning', label: I18N.estadoPendiente },
    en_curso:    { clase: 'info',    label: I18N.estadoEnCurso },
    completado:  { clase: 'success', label: I18N.estadoCompletado },
    cancelado:   { clase: 'danger',  label: I18N.estadoCancelado }
};

function levanteBadgeHtml(r) {
    const info = BADGE_ESTADO_LEVANTE[r.estado_levante];
    if (info) return `<span class="badge badge-${info.clase}">${info.label}</span>`;
    const tieneLevante = (r.levantes_count ?? 0) > 0;
    return `<span class="badge badge-${tieneLevante ? 'info' : 'neutral'}">${tieneLevante ? I18N.badgeAsignado : I18N.badgeSinAsignar}</span>`;
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
    count.textContent   = `${rutas.length} ${rutas.length !== 1 ? I18N.resultadoPlural : I18N.resultadoSingular}`;

    rutas.forEach(r => {
        const tr = document.createElement('tr');
        const levanteBadge = levanteBadgeHtml(r);
        tr.innerHTML = `
            <td class="td-id" data-label="${I18N.thId}">${r.id_ruta}</td>
            <td data-label="${I18N.thCircuito}">${r.id_circuito}</td>
            <td data-label="${I18N.thNombre}"><strong>${r.nombre}</strong></td>
            <td data-label="${I18N.thActividad}">${r.actividad ?? ''}</td>
            <td data-label="${I18N.thTurno}">${r.turno ? `<span class="badge badge-${badgeClassTurno(r.turno)}">${turnoFormateado(r.turno)}</span>` : '—'}</td>
            <td data-label="${I18N.thLevante}">${levanteBadge}</td>
            <td data-label="${I18N.thLlenadoProm}">${r.llenado_promedio !== undefined && r.llenado_promedio !== null ? r.llenado_promedio + '%' : '—'}</td>
            <td class="td-actions" data-label="${I18N.thAcciones}">
                <button class="btn-action" onclick="abrirPlanificarLevante(${r.id_ruta})" title="${I18N.titlePlanificarLevante}">🗓️</button>
                <button class="btn-action" onclick="confirmarBorrar(${r.id_ruta},'${r.nombre}')" title="${I18N.btnEliminar}">🗑️</button>
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
        if (modalId === 'modalLevante') {
            cerrarModalLevante();
        } else {
            cerrarModalDelete();
        }
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
