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
    <title><?= t('circuitos.title') ?></title>
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

        .mapa-circuitos-section { padding: 28px 32px; }
        .mapa-circuitos-box { width: 100%; height: 440px; border-radius: var(--radius); z-index: 0; margin-top: 10px; }
        .mapa-panel { background: var(--card-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 20px; margin-top: 16px; }
        .mapa-panel-empty { color: var(--muted); font-size: 0.9rem; text-align: center; padding: 20px 10px; }
        @media (max-width: 1024px) {
            .mapa-circuitos-section { padding-left: 20px; padding-right: 20px; }
            .mapa-circuitos-box { height: 320px; }
        }

        .generar-rutas-resultado { width: 100%; margin-top: 14px; }
        .generar-rutas-resumen { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        .generar-rutas-resumen span { color: var(--muted); font-size: 0.875rem; }
        .generar-rutas-lista { list-style: none; margin: 12px 0 0; padding: 0; display: flex; flex-direction: column; gap: 6px; max-height: 220px; overflow-y: auto; }
        .generar-rutas-lista li { background: var(--card-bg); border: 1px solid var(--border); border-radius: 8px; padding: 8px 12px; font-size: 0.85rem; }
        .generar-rutas-log { margin-top: 12px; display: flex; flex-direction: column; gap: 6px; }
        .generar-rutas-log .log-ok { color: var(--green); font-size: 0.85rem; }
        .generar-rutas-log .log-error { color: var(--red); font-size: 0.85rem; }
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
                <a href="circuitos.php" class="nav-active"><?= t('backoffice.nav_circuitos') ?></a>
                <a href="rutas.php"><?= t('backoffice.nav_rutas') ?></a>
                <a href="../../API_Usuarios/Vista/cuadrillas.php"><?= t('backoffice.nav_cuadrillas') ?></a>
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
        <a href="circuitos.php" class="nav-active"><?= t('backoffice.nav_circuitos') ?></a>
        <a href="rutas.php"><?= t('backoffice.nav_rutas') ?></a>
        <a href="../../API_Usuarios/Vista/cuadrillas.php"><?= t('backoffice.nav_cuadrillas') ?></a>
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
    <h1 class="hero-title"><?= t('circuitos.hero_title') ?></h1>
    <p class="hero-sub"><?= t('circuitos.hero_sub') ?></p>
</section>

<section class="mapa-circuitos-section" id="mapaCircuitosSection">
    <div class="table-header">
        <h2 class="table-title"><?= t('circuitos.mapa_titulo') ?></h2>
    </div>
    <p class="mapa-hint"><?= t('circuitos.mapa_hint') ?></p>
    <div id="mapaCircuitos" class="mapa-circuitos-box"></div>
    <aside class="mapa-panel" id="mapaPanel" style="display:none">
        <h3 id="mapaPanelTitulo"></h3>
        <div class="mapa-modal-actions">
            <button type="button" class="btn-secondary" onclick="cargarContenedoresEnMapa()"><?= t('circuitos.btn_cargar_contenedores') ?></button>
        </div>
        <div class="mapa-ruta-form">
            <div class="form-group">
                <label for="rutaNombre"><?= t('circuitos.field_ruta_nombre') ?></label>
                <input type="text" id="rutaNombre" class="form-input" placeholder="<?= htmlspecialchars(t('circuitos.field_ruta_nombre_ph')) ?>" maxlength="100">
            </div>
            <div class="form-group">
                <label for="rutaActividad"><?= t('circuitos.field_ruta_actividad') ?></label>
                <input type="text" id="rutaActividad" class="form-input" placeholder="<?= htmlspecialchars(t('circuitos.field_ruta_actividad_ph')) ?>" maxlength="60">
            </div>
            <button type="button" class="btn-primary" onclick="generarRutaOptima()"><?= t('circuitos.btn_generar_ruta_optima') ?></button>
        </div>
        <div id="mapaResultado" class="mapa-resultado"></div>
    </aside>
</section>

<section class="cards-section">
    <div class="cards-grid">

        <div class="card-operar" onclick="abrirModalAgregar()">
            <div class="card-content">
                <div class="card-icon"><img src="img/plus.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('circuitos.card_agregar_titulo') ?></h3>
                <p><?= t('circuitos.card_agregar_desc') ?></p>
                <button class="card-btn"><?= t('crud.btn_agregar') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleFiltros()">
            <div class="card-content">
                <div class="card-icon"><img src="img/loupe.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('crud.btn_filtrar') ?></h3>
                <p><?= t('circuitos.card_filtrar_desc') ?></p>
                <button class="card-btn"><?= t('crud.btn_filtrar') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleBusquedaId()">
            <div class="card-content">
                <div class="card-icon"><img src="img/question.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('camiones.card_buscarid_titulo') ?></h3>
                <p><?= t('circuitos.card_buscarid_desc') ?></p>
                <button class="card-btn"><?= t('crud.btn_buscar') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleModificar()">
            <div class="card-content">
                <div class="card-icon"><img src="img/refresh.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('circuitos.card_modificar_titulo') ?></h3>
                <p><?= t('circuitos.card_modificar_desc') ?></p>
                <button class="card-btn"><?= t('crud.btn_modificar') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleDarDeBaja()">
            <div class="card-content">
                <div class="card-icon"><img src="img/minus.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('circuitos.card_eliminar_titulo') ?></h3>
                <p><?= t('circuitos.card_eliminar_desc') ?></p>
                <button class="card-btn"><?= t('crud.btn_eliminar') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleGenerarRutas()">
            <div class="card-content">
                <div class="card-icon"><img src="../../Backoffice/img/ruta.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('circuitos.card_genrutas_titulo') ?></h3>
                <p><?= t('circuitos.card_genrutas_desc') ?></p>
                <button class="card-btn"><?= t('circuitos.card_genrutas_btn') ?></button>
            </div>
        </div>

    </div>
</section>

<section class="control-bar" id="filterBar" style="display:none">
    <div class="control-inner">
        <select id="filtroTipo" class="ctrl-select" onchange="actualizarFiltroValor()">
            <option value=""><?= t('crud.filtrar_por') ?></option>
            <option value="municipioId"><?= t('circuitos.filtro_opt_municipio') ?></option>
            <option value="turno"><?= t('circuitos.filtro_opt_turno') ?></option>
            <option value="actividad"><?= t('circuitos.filtro_opt_actividad') ?></option>
            <option value="frecuencia"><?= t('circuitos.filtro_opt_frecuencia') ?></option>
            <option value="dia"><?= t('circuitos.filtro_opt_dia') ?></option>
        </select>
        <input type="text" id="filtroValor" class="ctrl-input" placeholder="<?= htmlspecialchars(t('crud.valor_filtro_placeholder')) ?>" style="display:none">
        <select id="filtroValorSelect" class="ctrl-select" style="display:none"></select>
        <button class="btn-primary" onclick="aplicarFiltro()"><?= t('crud.btn_aplicar') ?></button>
        <button class="btn-secondary" onclick="listarCircuitos()"><?= t('crud.btn_limpiar') ?></button>
    </div>
</section>

<section class="control-bar" id="searchBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="buscarId" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('circuitos.searchid_ph')) ?>" min="1" style="max-width:200px">
        <button class="btn-primary" onclick="buscarPorId()"><?= t('crud.btn_buscar') ?></button>
        <button class="btn-secondary" onclick="listarCircuitos()"><?= t('crud.btn_vertodos') ?></button>
    </div>
</section>

<section class="control-bar" id="modificarBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="modificarId" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('circuitos.modificarid_ph')) ?>" min="1" style="max-width:120px">
        <select id="modificarCampo" class="ctrl-select" onchange="actualizarPlaceholderModificar()">
            <option value=""><?= t('crud.campo') ?></option>
            <option value="municipioId"><?= t('circuitos.campo_opt_municipio') ?></option>
            <option value="nombre"><?= t('circuitos.campo_opt_nombre') ?></option>
            <option value="codigo"><?= t('circuitos.campo_opt_codigo') ?></option>
            <option value="actividad"><?= t('circuitos.campo_opt_actividad') ?></option>
            <option value="turno"><?= t('circuitos.campo_opt_turno') ?></option>
            <option value="frecuencia"><?= t('circuitos.campo_opt_frecuencia') ?></option>
        </select>
        <input type="text" id="modificarValor" class="ctrl-input" style="display:none" placeholder="<?= htmlspecialchars(t('crud.seleccionar_campo_placeholder')) ?>">
        <select id="modificarValorSelect" class="ctrl-select" style="display:none"></select>
        <button class="btn-primary" onclick="modificarCircuito()"><?= t('crud.btn_modificar') ?></button>
        <button class="btn-secondary" onclick="ocultarControles()"><?= t('crud.btn_cancelar') ?></button>
    </div>
</section>

<section class="control-bar" id="deleteBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="deleteId" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('circuitos.deleteid_ph')) ?>" min="1" style="max-width:240px">
        <button class="btn-danger" onclick="darDeBajaCircuito()"><?= t('crud.btn_eliminar') ?></button>
        <button class="btn-secondary" onclick="ocultarControles()"><?= t('crud.btn_cancelar') ?></button>
    </div>
</section>

<section class="control-bar" id="generarRutasBar" style="display:none">
    <div class="control-inner">
        <select id="generarRutasDia" class="ctrl-select">
            <option value=""><?= t('circuitos.genrutas_dia') ?></option>
            <option value="domingo"><?= t('circuitos.dia_domingo') ?></option>
            <option value="lunes"><?= t('circuitos.dia_lunes') ?></option>
            <option value="martes"><?= t('circuitos.dia_martes') ?></option>
            <option value="miércoles"><?= t('circuitos.dia_miercoles') ?></option>
            <option value="jueves"><?= t('circuitos.dia_jueves') ?></option>
            <option value="viernes"><?= t('circuitos.dia_viernes') ?></option>
            <option value="sábado"><?= t('circuitos.dia_sabado') ?></option>
        </select>
        <select id="generarRutasTurno" class="ctrl-select">
            <option value=""><?= t('circuitos.genrutas_turno') ?></option>
            <option value="mañana"><?= t('circuitos.turno_manana') ?></option>
            <option value="tarde"><?= t('circuitos.turno_tarde') ?></option>
            <option value="noche"><?= t('circuitos.turno_noche') ?></option>
        </select>
        <select id="generarRutasFrecuencia" class="ctrl-select">
            <option value=""><?= t('circuitos.genrutas_frecuencia') ?></option>
        </select>
        <button class="btn-primary" onclick="buscarCircuitosParaRutas()"><?= t('circuitos.btn_buscar_circuitos') ?></button>
        <button class="btn-secondary" onclick="ocultarControles()"><?= t('crud.btn_cancelar') ?></button>
    </div>
    <div id="generarRutasResultado" class="generar-rutas-resultado" style="display:none">
        <div class="generar-rutas-resumen">
            <span id="generarRutasResumenTexto"></span>
            <button type="button" class="btn-primary" id="btnGenerarRutasMasivo" onclick="generarRutasParaFiltrados()"><?= t('circuitos.btn_generar_rutas_todos') ?></button>
        </div>
        <ul id="generarRutasLista" class="generar-rutas-lista"></ul>
        <div id="generarRutasLog" class="generar-rutas-log"></div>
    </div>
</section>

<section class="table-section">
    <div class="table-header">
        <h2 class="table-title" id="tableTitle"><?= t('circuitos.hero_title') ?></h2>
        <span class="table-count" id="tableCount"></span>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th><?= t('crud.th_id') ?></th>
                    <th><?= t('circuitos.th_municipio') ?></th>
                    <th><?= t('circuitos.th_nombre') ?></th>
                    <th><?= t('circuitos.th_codigo') ?></th>
                    <th><?= t('circuitos.th_actividad') ?></th>
                    <th><?= t('circuitos.th_turno') ?></th>
                    <th><?= t('circuitos.th_frecuencia') ?></th>
                    <th><?= t('crud.th_acciones') ?></th>
                </tr>
            </thead>
            <tbody id="circuitosBody"></tbody>
        </table>
        <div id="noResultados" class="empty-state" style="display:none">
            <?= t('crud.empty_state') ?>
        </div>
        <div id="loadingState" class="loading-state">
            <?= t('crud.loading_state') ?>
        </div>
    </div>
</section>

<div class="modal-overlay" id="modalForm" style="display:none"
     onclick="onOverlayClick(event,'modalForm')">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle"><?= t('circuitos.modal_add_title') ?></h3>
            <button class="modal-close" onclick="cerrarModal()">✕</button>
        </div>
        <form id="circuitoForm" onsubmit="guardarCircuito(event)">
            <input type="hidden" id="circuitoId">
            <div class="form-row">
                <div class="form-group">
                    <label for="municipioId"><?= t('circuitos.field_municipio') ?></label>
                    <select id="municipioId" class="form-input" required>
                        <option value=""><?= t('crud.seleccionar') ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="nombre"><?= t('circuitos.field_nombre') ?></label>
                    <input type="text" id="nombre" class="form-input"
                           placeholder="<?= htmlspecialchars(t('circuitos.field_nombre_ph')) ?>" maxlength="100" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="codigo"><?= t('circuitos.field_codigo') ?></label>
                    <input type="text" id="codigo" class="form-input"
                           placeholder="<?= htmlspecialchars(t('circuitos.field_codigo_ph')) ?>" maxlength="20" required>
                </div>
                <div class="form-group">
                    <label for="actividad"><?= t('circuitos.field_actividad') ?></label>
                    <input type="text" id="actividad" class="form-input"
                           placeholder="<?= htmlspecialchars(t('circuitos.field_actividad_ph')) ?>" maxlength="60" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="turno"><?= t('circuitos.field_turno') ?></label>
                    <select id="turno" class="form-input">
                        <option value="mañana" selected><?= t('circuitos.opt_turno_manana') ?></option>
                        <option value="tarde"><?= t('circuitos.opt_turno_tarde') ?></option>
                        <option value="noche"><?= t('circuitos.opt_turno_noche') ?></option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="frecuencia"><?= t('circuitos.field_frecuencia') ?></label>
                    <input type="text" id="frecuencia" class="form-input"
                           placeholder="<?= htmlspecialchars(t('circuitos.field_frecuencia_ph')) ?>" maxlength="40" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label><?= t('circuitos.field_area_label') ?></label>
                    <div id="formMapa" class="mapa-box"></div>
                    <p class="mapa-hint"><?= t('circuitos.area_hint') ?></p>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="cerrarModal()"><?= t('crud.btn_cancelar') ?></button>
                <button type="submit" class="btn-primary"><?= t('crud.btn_guardar') ?></button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalDelete" style="display:none"
     onclick="onOverlayClick(event,'modalDelete')">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3><?= t('circuitos.modal_delete_title') ?></h3>
            <button class="modal-close" onclick="cerrarModalDelete()">✕</button>
        </div>
        <p class="delete-msg">
            <?= t('circuitos.delete_confirm_before') ?> <strong id="deleteNombre"></strong><?= t('circuitos.delete_confirm_after') ?>
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
<script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
<script>
const API_BASE = '/recolix/API_Recoleccion';
const I18N = <?= json_encode(t_array([
    'msgIngresaId' => 'crud.msg_ingresa_id',
    'msgErrorConectar' => 'crud.msg_error_conectar',
    'msgSeleccionaFiltro' => 'crud.msg_selecciona_filtro',
    'msgIngresaValorFiltro' => 'crud.msg_ingresa_valor_filtro',
    'msgSeleccionaCampo' => 'crud.msg_selecciona_campo',
    'msgIngresaValor' => 'crud.msg_ingresa_valor',
    'msgErrorGuardar' => 'crud.msg_error_guardar',
    'msgErrorModificar' => 'crud.msg_error_modificar',
    'msgErrorEliminar' => 'crud.msg_error_eliminar',
    'resultadoSingular' => 'crud.resultado_singular',
    'resultadoPlural' => 'crud.resultado_plural',
    'filtroPrefijo' => 'crud.filtro_prefijo',
    'titleEditar' => 'crud.title_editar',
    'thId' => 'crud.th_id',
    'thAcciones' => 'crud.th_acciones',
    'btnEliminar' => 'crud.btn_eliminar',
    'thMunicipio' => 'circuitos.th_municipio',
    'thNombre' => 'circuitos.th_nombre',
    'thCodigo' => 'circuitos.th_codigo',
    'thActividad' => 'circuitos.th_actividad',
    'thTurno' => 'circuitos.th_turno',
    'thFrecuencia' => 'circuitos.th_frecuencia',
    'modalAddTitle' => 'circuitos.modal_add_title',
    'modalEditTitle' => 'circuitos.modal_edit_title',
    'filtroPhActividad' => 'circuitos.filtro_ph_actividad',
    'filtroPhFrecuencia' => 'circuitos.filtro_ph_frecuencia',
    'seleccionarCampoPh' => 'crud.seleccionar_campo_placeholder',
    'fieldNombrePh' => 'circuitos.field_nombre_ph',
    'fieldCodigoPh' => 'circuitos.field_codigo_ph',
    'fieldActividadPh' => 'circuitos.field_actividad_ph',
    'fieldFrecuenciaPh' => 'circuitos.field_frecuencia_ph',
    'turnoManana' => 'circuitos.opt_turno_manana',
    'turnoTarde' => 'circuitos.opt_turno_tarde',
    'turnoNoche' => 'circuitos.opt_turno_noche',
    'msgCircuitoNoEncontrado' => 'circuitos.msg_circuito_no_encontrado',
    'msgCircuitoEliminado' => 'circuitos.msg_circuito_eliminado',
    'msgSeleccionarDiaTurnoFrec' => 'circuitos.msg_seleccionar_dia_turno_frec',
    'msgErrorBuscarCircuitos' => 'circuitos.msg_error_buscar_circuitos',
    'msgNoPudieronCargarContenedores' => 'circuitos.msg_no_pudieron_cargar_contenedores',
    'msgErrorCargarContenedores' => 'circuitos.msg_error_cargar_contenedores',
    'msgContenedoresCargados' => 'circuitos.msg_contenedores_cargados',
    'msgCompletaNombreActividad' => 'circuitos.msg_completa_nombre_actividad',
    'msgNoPudoGenerarRuta' => 'circuitos.msg_no_pudo_generar_ruta',
    'msgRutaGeneradaResultado' => 'circuitos.msg_ruta_generada_resultado',
    'msgRutaOptimaGenerada' => 'circuitos.msg_ruta_optima_generada',
    'msgErrorGenerarRuta' => 'circuitos.msg_error_generar_ruta',
    'sinFrecuencia' => 'circuitos.sin_frecuencia',
    'generandoRutaPara' => 'circuitos.generando_ruta_para',
    'errorDesconocido' => 'circuitos.error_desconocido',
    'errorConectarApi' => 'circuitos.error_conectar_api',
    'circuitoSingular' => 'circuitos.circuito_singular',
    'circuitoPlural' => 'circuitos.circuito_plural',
    'encontradoSingular' => 'circuitos.encontrado_singular',
    'encontradoPlural' => 'circuitos.encontrado_plural',
    'rutaSingular' => 'circuitos.ruta_singular',
    'rutaPlural' => 'circuitos.ruta_plural',
    'generadaSingular' => 'circuitos.generada_singular',
    'generadaPlural' => 'circuitos.generada_plural',
    'conErrorSufijo' => 'circuitos.con_error_sufijo',
    'tituloPanelPrefijo' => 'circuitos.titulo_panel_prefijo',
    'tituloPanelSinArea' => 'circuitos.titulo_panel_sin_area',
    'popupContenedores' => 'circuitos.popup_contenedores',
    'popupLlenado' => 'circuitos.popup_llenado',
    'popupEstado' => 'circuitos.popup_estado',
    'rutaPrefijo' => 'circuitos.ruta_prefijo',
    'kmAprox' => 'circuitos.km_aprox',
    'tableTitleTodos' => 'circuitos.table_title_todos',
    'tableTitleCircuitoId' => 'circuitos.table_title_circuito_id',
    'filtroValorPh' => 'crud.valor_filtro_placeholder',
    'filtroOptDia' => 'circuitos.filtro_opt_dia',
    'diaDomingo' => 'circuitos.dia_domingo',
    'diaLunes' => 'circuitos.dia_lunes',
    'diaMartes' => 'circuitos.dia_martes',
    'diaMiercoles' => 'circuitos.dia_miercoles',
    'diaJueves' => 'circuitos.dia_jueves',
    'diaViernes' => 'circuitos.dia_viernes',
    'diaSabado' => 'circuitos.dia_sabado',
    'titleMapaRuta' => 'circuitos.title_mapa_ruta',
    'fieldRutaNombrePh' => 'circuitos.field_ruta_nombre_ph',
])) ?>;
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
    initMapaCircuitos();
    listarCircuitos();
});

window.addEventListener('resize', () => {
    if (mapaCircuitos) mapaCircuitos.invalidateSize();
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
            setTableTitle(I18N.tableTitleTodos);
        } else {
            mostrarToast(json.message, 'error');
        }
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
        const res  = await fetch(`${API_BASE}/circuitos/${id}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            renderTabla([json.data]);
            setTableTitle(`${I18N.tableTitleCircuitoId}${id}`);
        } else {
            renderTabla([]);
            mostrarToast(json.message || I18N.msgCircuitoNoEncontrado, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorConectar, 'error');
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
        turno: ['mañana', 'tarde', 'noche'],
        dia:   ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado']
    };

    if (listas[tipo]) {
        select.innerHTML = '<option value="">— Seleccionar —</option>' +
            listas[tipo].map(v => `<option value="${v}">${valorFormateado(v)}</option>`).join('');
        select.style.display = '';
    } else {
        const placeholders = {
            actividad:  I18N.filtroPhActividad,
            frecuencia: I18N.filtroPhFrecuencia
        };
        input.placeholder = placeholders[tipo] || I18N.filtroValorPh;
        input.style.display = '';
    }
}

async function aplicarFiltro() {
    const tipo   = document.getElementById('filtroTipo').value;
    const input  = document.getElementById('filtroValor');
    const select = document.getElementById('filtroValorSelect');
    if (!tipo) { mostrarToast(I18N.msgSeleccionaFiltro, 'warning'); return; }

    const valor = (select.style.display !== 'none' ? select.value : input.value).trim();
    if (!valor) {
        mostrarToast(I18N.msgIngresaValorFiltro, 'warning');
        return;
    }

    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/circuitos?${tipo}=${encodeURIComponent(valor)}`);
        const json = await res.json();
        if (json.status === 'success') {
            renderTabla(json.data);
            const labels = {
                municipioId: `${I18N.thMunicipio}: ${nombreMunicipio(valor)}`, turno: `${I18N.thTurno}: ${valorFormateado(valor)}`,
                actividad: `${I18N.thActividad}: ${valor}`, frecuencia: `${I18N.thFrecuencia}: ${valor}`,
                dia: `${I18N.filtroOptDia}: ${valorFormateado(valor)}`
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
    document.getElementById('modalTitle').textContent = I18N.modalAddTitle;
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
    document.getElementById('modalTitle').textContent = I18N.modalEditTitle;
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
        mostrarToast(I18N.msgErrorGuardar, 'error');
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
            mostrarToast(I18N.msgCircuitoEliminado, 'success');
            cerrarModalDelete();
            listarCircuitos();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorEliminar, 'error');
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
            `<option value="${v}"${i === 0 ? ' selected' : ''}>${valorFormateado(v)}</option>`
        ).join('');
        select.style.display = '';
    } else {
        const placeholders = {
            nombre:     I18N.fieldNombrePh,
            codigo:     I18N.fieldCodigoPh,
            actividad:  I18N.fieldActividadPh,
            frecuencia: I18N.fieldFrecuenciaPh
        };
        const maxlengths = {
            nombre: 100, codigo: 20, actividad: 60, frecuencia: 40
        };
        input.placeholder = placeholders[campo] || I18N.seleccionarCampoPh;
        input.maxLength = maxlengths[campo] || '';
        input.style.display = '';
    }
}

async function modificarCircuito() {
    const id = document.getElementById('modificarId').value.trim();
    if (!id) { mostrarToast(I18N.msgIngresaId, 'warning'); return; }
    const campo = document.getElementById('modificarCampo').value;
    if (!campo) { mostrarToast(I18N.msgSeleccionaCampo, 'warning'); return; }

    const input = document.getElementById('modificarValor');
    const select = document.getElementById('modificarValorSelect');
    const valor = (select.style.display !== 'none' ? select : input).value.trim();
    if (!valor) { mostrarToast(I18N.msgIngresaValor, 'warning'); return; }

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
            mostrarToast(json.message || I18N.msgCircuitoNoEncontrado, 'error');
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
        mostrarToast(I18N.msgErrorModificar, 'error');
    } finally {
        setLoading(false);
    }
}

async function darDeBajaCircuito() {
    const id = document.getElementById('deleteId').value.trim();
    if (!id) { mostrarToast(I18N.msgIngresaId, 'warning'); return; }
    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/circuitos/${id}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            confirmarBorrar(json.data.id_circuito, json.data.nombre);
            ocultarControles();
        } else {
            mostrarToast(json.message || I18N.msgCircuitoNoEncontrado, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorConectar, 'error');
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
        renderPoligonosCircuitos([]);
        return;
    }

    noRes.style.display = 'none';
    count.textContent   = `${circuitos.length} ${circuitos.length !== 1 ? I18N.resultadoPlural : I18N.resultadoSingular}`;

    circuitos.forEach(c => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="td-id" data-label="${I18N.thId}">${c.id_circuito}</td>
            <td data-label="${I18N.thMunicipio}">${nombreMunicipio(c.id_municipio)}</td>
            <td data-label="${I18N.thNombre}"><strong>${c.nombre}</strong></td>
            <td data-label="${I18N.thCodigo}">${c.codigo ?? ''}</td>
            <td data-label="${I18N.thActividad}">${c.actividad ?? ''}</td>
            <td data-label="${I18N.thTurno}"><span class="badge badge-${badgeClass(c.turno)}">${valorFormateado(c.turno ?? '')}</span></td>
            <td data-label="${I18N.thFrecuencia}">${c.frecuencia ?? ''}</td>
            <td class="td-actions" data-label="${I18N.thAcciones}">
                <button class="btn-action" onclick='abrirModalEditar(${JSON.stringify(c)})' title="${I18N.titleEditar}">✏️</button>
                <button class="btn-action" onclick='seleccionarCircuito(${JSON.stringify(c)})' title="${I18N.titleMapaRuta}">🗺️</button>
                <button class="btn-action" onclick="confirmarBorrar(${c.id_circuito},'${c.nombre}')" title="${I18N.btnEliminar}">🗑️</button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    renderPoligonosCircuitos(circuitos);
}

const VALORES_TRADUCIDOS = {
    mañana: 'turnoManana', tarde: 'turnoTarde', noche: 'turnoNoche',
    domingo: 'diaDomingo', lunes: 'diaLunes', martes: 'diaMartes', 'miércoles': 'diaMiercoles',
    jueves: 'diaJueves', viernes: 'diaViernes', 'sábado': 'diaSabado'
};

function valorFormateado(v) {
    const clave = VALORES_TRADUCIDOS[v];
    return clave ? I18N[clave] : v.replace(/_/g, ' ');
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
    const gb = document.getElementById('generarRutasBar');
    sb.style.display = 'none';
    mb.style.display = 'none';
    db.style.display = 'none';
    gb.style.display = 'none';
    fb.style.display = fb.style.display === 'none' ? 'block' : 'none';
}

function toggleBusquedaId() {
    const sb = document.getElementById('searchBar');
    const fb = document.getElementById('filterBar');
    const mb = document.getElementById('modificarBar');
    const db = document.getElementById('deleteBar');
    const gb = document.getElementById('generarRutasBar');
    fb.style.display = 'none';
    mb.style.display = 'none';
    db.style.display = 'none';
    gb.style.display = 'none';
    sb.style.display = sb.style.display === 'none' ? 'block' : 'none';
}

function ocultarControles() {
    document.getElementById('filterBar').style.display = 'none';
    document.getElementById('searchBar').style.display = 'none';
    document.getElementById('modificarBar').style.display = 'none';
    document.getElementById('deleteBar').style.display = 'none';
    document.getElementById('generarRutasBar').style.display = 'none';
}

function toggleModificar() {
    const mb = document.getElementById('modificarBar');
    const fb = document.getElementById('filterBar');
    const sb = document.getElementById('searchBar');
    const db = document.getElementById('deleteBar');
    const gb = document.getElementById('generarRutasBar');
    fb.style.display = 'none';
    sb.style.display = 'none';
    db.style.display = 'none';
    gb.style.display = 'none';
    mb.style.display = mb.style.display === 'none' ? 'block' : 'none';
}

function toggleDarDeBaja() {
    const db = document.getElementById('deleteBar');
    const fb = document.getElementById('filterBar');
    const sb = document.getElementById('searchBar');
    const mb = document.getElementById('modificarBar');
    const gb = document.getElementById('generarRutasBar');
    fb.style.display = 'none';
    sb.style.display = 'none';
    mb.style.display = 'none';
    gb.style.display = 'none';
    db.style.display = db.style.display === 'none' ? 'block' : 'none';
}

async function toggleGenerarRutas() {
    const gb = document.getElementById('generarRutasBar');
    const fb = document.getElementById('filterBar');
    const sb = document.getElementById('searchBar');
    const mb = document.getElementById('modificarBar');
    const db = document.getElementById('deleteBar');
    fb.style.display = 'none';
    sb.style.display = 'none';
    mb.style.display = 'none';
    db.style.display = 'none';

    const abrir = gb.style.display === 'none';
    gb.style.display = abrir ? 'block' : 'none';
    if (abrir) {
        document.getElementById('generarRutasResultado').style.display = 'none';
        await poblarFrecuenciasGenerarRutas();
    }
}

async function poblarFrecuenciasGenerarRutas() {
    const select = document.getElementById('generarRutasFrecuencia');
    try {
        const res  = await fetch(`${API_BASE}/circuitos`);
        const json = await res.json();
        if (json.status !== 'success') return;
        const frecuencias = [...new Set(json.data.map(c => c.frecuencia).filter(Boolean))].sort();
        select.innerHTML = '<option value="">— Frecuencia —</option>' +
            frecuencias.map(f => `<option value="${f}">${f}</option>`).join('');
    } catch {
        // si falla, se puede seguir usando solo el filtro por día
    }
}

let circuitosParaGenerarRutas = [];

async function buscarCircuitosParaRutas() {
    const dia = document.getElementById('generarRutasDia').value;
    const turno = document.getElementById('generarRutasTurno').value;
    const frecuencia = document.getElementById('generarRutasFrecuencia').value;

    if (!dia && !turno && !frecuencia) {
        mostrarToast(I18N.msgSeleccionarDiaTurnoFrec, 'warning');
        return;
    }

    const [param, valor] = dia ? ['dia', dia] : turno ? ['turno', turno] : ['frecuencia', frecuencia];

    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/circuitos?${param}=${encodeURIComponent(valor)}`);
        const json = await res.json();
        if (json.status !== 'success') {
            mostrarToast(json.message || I18N.msgErrorBuscarCircuitos, 'error');
            return;
        }

        let resultado = json.data;
        if (turno) resultado = resultado.filter(c => c.turno === turno);
        if (frecuencia) resultado = resultado.filter(c => c.frecuencia === frecuencia);
        circuitosParaGenerarRutas = resultado;

        renderResultadoGenerarRutas();
    } catch {
        mostrarToast(I18N.msgErrorConectar, 'error');
    } finally {
        setLoading(false);
    }
}

function renderResultadoGenerarRutas() {
    const resultado = document.getElementById('generarRutasResultado');
    const resumen = document.getElementById('generarRutasResumenTexto');
    const lista = document.getElementById('generarRutasLista');
    const boton = document.getElementById('btnGenerarRutasMasivo');
    const log = document.getElementById('generarRutasLog');

    log.innerHTML = '';
    resultado.style.display = 'block';
    const n = circuitosParaGenerarRutas.length;
    resumen.textContent = `${n} ${n !== 1 ? I18N.circuitoPlural : I18N.circuitoSingular} ${n !== 1 ? I18N.encontradoPlural : I18N.encontradoSingular}`;
    boton.disabled = circuitosParaGenerarRutas.length === 0;

    lista.innerHTML = circuitosParaGenerarRutas
        .map(c => `<li>#${c.id_circuito} — ${c.nombre} (${c.frecuencia ?? I18N.sinFrecuencia})</li>`)
        .join('');
}

async function generarRutasParaFiltrados() {
    if (!circuitosParaGenerarRutas.length) return;

    const boton = document.getElementById('btnGenerarRutasMasivo');
    const log = document.getElementById('generarRutasLog');
    boton.disabled = true;
    log.innerHTML = '';

    let exitosas = 0;
    let fallidas = 0;

    for (const circuito of circuitosParaGenerarRutas) {
        const linea = document.createElement('div');
        linea.textContent = `${I18N.generandoRutaPara} #${circuito.id_circuito} — ${circuito.nombre}...`;
        log.appendChild(linea);

        try {
            const res = await fetch(`${API_BASE}/rutas/generarOptima`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    circuitoId: circuito.id_circuito,
                    nombre: `${I18N.rutaPrefijo} ${circuito.nombre}`,
                    actividad: circuito.actividad || 'recolección domiciliaria'
                })
            });
            const json = await res.json();
            if (json.status === 'success') {
                exitosas++;
                linea.className = 'log-ok';
                linea.textContent = `✓ #${circuito.id_circuito} — ${circuito.nombre}: ${I18N.rutaPrefijo} #${json.data.ruta.id_ruta} ${I18N.msgRutaGeneradaResultado} (${json.data.distancia_total_km} km)`;
            } else {
                fallidas++;
                linea.className = 'log-error';
                linea.textContent = `✗ #${circuito.id_circuito} — ${circuito.nombre}: ${json.message || I18N.errorDesconocido}`;
            }
        } catch {
            fallidas++;
            linea.className = 'log-error';
            linea.textContent = `✗ #${circuito.id_circuito} — ${circuito.nombre}: ${I18N.errorConectarApi}`;
        }
    }

    boton.disabled = false;
    const rutaLabel = exitosas !== 1 ? I18N.rutaPlural : I18N.rutaSingular;
    const generadaLabel = exitosas !== 1 ? I18N.generadaPlural : I18N.generadaSingular;
    mostrarToast(`${exitosas} ${rutaLabel} ${generadaLabel}${fallidas ? `, ${fallidas} ${I18N.conErrorSufijo}` : ''}`, fallidas ? 'warning' : 'success');
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

let mapaCircuitos = null;
let mapaCircuitosMarkers = [];
let mapaCircuitosPolyline = null;
let circuitoActualMapa = null;
let poligonosPorCircuito = {};
let poligonoSeleccionadoId = null;

const COLORES_CONTENEDOR = {
    funcional: '#4CAF50',
    inactivo: '#e3b23c',
    desbordado: '#c0392b',
    roto: '#2b2b2b',
    en_deposito: '#4a5568'
};

const COLOR_POLIGONO = '#3f5a33';
const COLOR_POLIGONO_SELECCIONADO = '#8bbd3f';

function initMapaCircuitos() {
    if (mapaCircuitos) return;
    mapaCircuitos = L.map('mapaCircuitos').setView(CENTRO_MAPA, 12);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(mapaCircuitos);
}

function limpiarCapasMapa() {
    mapaCircuitosMarkers.forEach(m => mapaCircuitos.removeLayer(m));
    mapaCircuitosMarkers = [];
    if (mapaCircuitosPolyline) { mapaCircuitos.removeLayer(mapaCircuitosPolyline); mapaCircuitosPolyline = null; }
}

function tituloPanel(circuito) {
    return circuito.poligono
        ? `${I18N.tituloPanelPrefijo} ${circuito.nombre}`
        : `${I18N.tituloPanelPrefijo} ${circuito.nombre} ${I18N.tituloPanelSinArea}`;
}

function renderPoligonosCircuitos(circuitos) {
    Object.values(poligonosPorCircuito).forEach(layer => mapaCircuitos.removeLayer(layer));
    poligonosPorCircuito = {};

    const bounds = [];
    (circuitos || []).forEach(c => {
        if (!c.poligono) return;
        let puntos;
        try { puntos = JSON.parse(c.poligono); } catch { return; }
        if (!Array.isArray(puntos) || puntos.length < 3) return;

        const layer = L.polygon(puntos, { color: COLOR_POLIGONO, weight: 2, fillOpacity: 0.08 })
            .bindTooltip(c.nombre)
            .addTo(mapaCircuitos);
        layer.on('click', () => seleccionarCircuito(c));
        layer.on('mouseover', () => layer.bringToFront());

        poligonosPorCircuito[c.id_circuito] = layer;
        bounds.push(...puntos);
    });

    if (bounds.length) {
        mapaCircuitos.fitBounds(bounds, { padding: [30, 30] });
    } else {
        mapaCircuitos.setView(CENTRO_MAPA, 12);
    }

    if (poligonoSeleccionadoId !== null) {
        const circuitoSeleccionado = (circuitos || []).find(c => c.id_circuito === poligonoSeleccionadoId);
        if (circuitoSeleccionado) {
            resaltarPoligono(poligonoSeleccionadoId);
            circuitoActualMapa = circuitoSeleccionado;
            const tituloEl = document.getElementById('mapaPanelTitulo');
            if (tituloEl) tituloEl.textContent = tituloPanel(circuitoSeleccionado);
        } else {
            deseleccionarCircuito();
        }
    }
}

function resaltarPoligono(id) {
    const layer = poligonosPorCircuito[id];
    if (layer) layer.setStyle({ color: COLOR_POLIGONO_SELECCIONADO, weight: 3, fillOpacity: 0.15 });
}

function deseleccionarCircuito() {
    if (poligonoSeleccionadoId !== null && poligonosPorCircuito[poligonoSeleccionadoId]) {
        poligonosPorCircuito[poligonoSeleccionadoId].setStyle({ color: COLOR_POLIGONO, weight: 2, fillOpacity: 0.08 });
    }
    poligonoSeleccionadoId = null;
    circuitoActualMapa = null;
    limpiarCapasMapa();
    document.getElementById('mapaPanel').style.display = 'none';
}

function seleccionarCircuito(circuito) {
    if (poligonoSeleccionadoId !== null && poligonosPorCircuito[poligonoSeleccionadoId]) {
        poligonosPorCircuito[poligonoSeleccionadoId].setStyle({ color: COLOR_POLIGONO, weight: 2, fillOpacity: 0.08 });
    }

    circuitoActualMapa = circuito;
    poligonoSeleccionadoId = circuito.id_circuito;
    limpiarCapasMapa();

    const layer = poligonosPorCircuito[circuito.id_circuito];
    if (layer) {
        resaltarPoligono(circuito.id_circuito);
        mapaCircuitos.fitBounds(layer.getBounds(), { padding: [30, 30] });
    } else {
        mapaCircuitos.setView(CENTRO_MAPA, 12);
    }

    document.getElementById('mapaPanelTitulo').textContent = tituloPanel(circuito);
    document.getElementById('rutaNombre').value = `${I18N.fieldRutaNombrePh} ${circuito.nombre}`;
    document.getElementById('rutaActividad').value = circuito.actividad || '';
    document.getElementById('mapaResultado').innerHTML = '';
    const panel = document.getElementById('mapaPanel');
    panel.style.display = '';
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

async function cargarContenedoresEnMapa() {
    if (!circuitoActualMapa) return;
    try {
        const res  = await fetch(`${API_BASE}/circuitos/${circuitoActualMapa.id_circuito}/obtenerContenedores`);
        const json = await res.json();
        if (json.status !== 'success') {
            mostrarToast(json.message || I18N.msgNoPudieronCargarContenedores, 'error');
            return;
        }

        mapaCircuitosMarkers.forEach(m => mapaCircuitos.removeLayer(m));
        mapaCircuitosMarkers = [];

        const bounds = [];
        (json.data || []).forEach(c => {
            const lat = parseFloat(c.latitud);
            const lon = parseFloat(c.longitud);
            if (Number.isNaN(lat) || Number.isNaN(lon)) return;
            const color = COLORES_CONTENEDOR[c.estado] || COLORES_CONTENEDOR.funcional;
            const marker = L.circleMarker([lat, lon], {
                radius: 6, color: '#fff', weight: 1.4, opacity: 0.7,
                fillColor: color, fillOpacity: 0.9
            }).bindPopup(`<b>#${c.id_contenedor}</b> ${c.calle ?? ''} ${c.num_calle ?? ''}<br>${I18N.popupLlenado} ${c.nivel_llenado}%<br>${I18N.popupEstado} ${c.estado}`);
            marker.addTo(mapaCircuitos);
            mapaCircuitosMarkers.push(marker);
            bounds.push([lat, lon]);
        });

        if (bounds.length && !poligonosPorCircuito[circuitoActualMapa.id_circuito]) {
            mapaCircuitos.fitBounds(bounds, { padding: [30, 30] });
        }
        mostrarToast(`${mapaCircuitosMarkers.length} ${I18N.popupContenedores}`, 'success');
    } catch {
        mostrarToast(I18N.msgErrorCargarContenedores, 'error');
    }
}

async function generarRutaOptima() {
    if (!circuitoActualMapa) return;
    const nombre = document.getElementById('rutaNombre').value.trim();
    const actividad = document.getElementById('rutaActividad').value.trim();
    if (!nombre || !actividad) {
        mostrarToast(I18N.msgCompletaNombreActividad, 'warning');
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
            mostrarToast(json.message || I18N.msgNoPudoGenerarRuta, 'error');
            return;
        }

        const { ruta, contenedores, distancia_total_km } = json.data;
        if (mapaCircuitosPolyline) { mapaCircuitos.removeLayer(mapaCircuitosPolyline); mapaCircuitosPolyline = null; }

        const puntosRuta = (contenedores || [])
            .map(c => [parseFloat(c.latitud), parseFloat(c.longitud)])
            .filter(([lat, lon]) => !Number.isNaN(lat) && !Number.isNaN(lon));

        if (puntosRuta.length > 1) {
            mapaCircuitosPolyline = L.polyline(puntosRuta, { color: '#c0392b', weight: 4 }).addTo(mapaCircuitos);
            mapaCircuitos.fitBounds(mapaCircuitosPolyline.getBounds(), { padding: [30, 30] });
        }

        document.getElementById('mapaResultado').innerHTML =
            `${I18N.rutaPrefijo} <strong>#${ruta.id_ruta}</strong> ${I18N.msgRutaGeneradaResultado} — ${contenedores.length} ${I18N.popupContenedores}, ${distancia_total_km} ${I18N.kmAprox}`;
        mostrarToast(I18N.msgRutaOptimaGenerada, 'success');
    } catch {
        mostrarToast(I18N.msgErrorGenerarRuta, 'error');
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
