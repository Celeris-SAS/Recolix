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
    <title><?= t('centrosAcopio.title') ?></title>
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
                    <a href="centrosAcopio.php" class="nav-active"><?= t('backoffice.nav_centros_acopio') ?></a>
                    <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
                    <a href="reportes.php"><?= t('backoffice.nav_reportes') ?></a>
                    <a href="incidencias.php"><?= t('backoffice.nav_incidencias') ?></a>
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
            <a href="centrosAcopio.php" class="nav-active"><?= t('backoffice.nav_centros_acopio') ?></a>
            <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
            <a href="reportes.php"><?= t('backoffice.nav_reportes') ?></a>
            <a href="incidencias.php"><?= t('backoffice.nav_incidencias') ?></a>
            <a href="../../API_Usuarios/Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
        </nav>
        <div class="nav-hr"></div>
    </header>


    <section class="hero">
        <h1 class="hero-title"><?= t('centrosAcopio.hero_title') ?></h1>
        <p class="hero-sub"><?= t('centrosAcopio.hero_sub') ?></p>
    </section>

    
    <section class="cards-section">
        <div class="cards-grid">

            <div class="card-operar" onclick="abrirModalAgregar()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/plus.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('centrosAcopio.card_agregar_titulo') ?></h3>
                    <p><?= t('centrosAcopio.card_agregar_desc') ?></p>
                    <button class="card-btn"><?= t('crud.btn_agregar') ?></button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleFiltros()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/loupe.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('crud.btn_filtrar') ?></h3>
                    <p><?= t('centrosAcopio.card_filtrar_desc') ?></p>
                    <button class="card-btn"><?= t('crud.btn_filtrar') ?></button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleBusquedaId()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/question.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('camiones.card_buscarid_titulo') ?></h3>
                    <p><?= t('centrosAcopio.card_buscarid_desc') ?></p>
                    <button class="card-btn"><?= t('crud.btn_buscar') ?></button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleDarDeBaja()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/minus.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('camiones.card_baja_titulo') ?></h3>
                    <p><?= t('centrosAcopio.card_baja_desc') ?></p>
                    <button class="card-btn"><?= t('camiones.card_baja_btn') ?></button>
                </div>
            </div>

        </div>
    </section>


    <section class="control-bar" id="filterBar" style="display:none">
        <div class="control-inner">
            <select id="filtroCampo" class="ctrl-select" onchange="actualizarFiltroValor()">
                <option value=""><?= t('crud.filtrar_por') ?></option>
                <option value="municipio"><?= t('centrosAcopio.filtro_opt_municipio') ?></option>
                <option value="tipo"><?= t('centrosAcopio.filtro_opt_tipo') ?></option>
                <option value="estado"><?= t('centrosAcopio.filtro_opt_estado') ?></option>
            </select>
            <select id="filtroValorSelect" class="ctrl-select" style="display:none"></select>
            <button class="btn-primary" onclick="aplicarFiltro()"><?= t('crud.btn_aplicar') ?></button>
            <button class="btn-secondary" onclick="listarCentros()"><?= t('crud.btn_limpiar') ?></button>
        </div>
    </section>


    <section class="control-bar" id="searchBar" style="display:none">
        <div class="control-inner">
            <input type="number" id="buscarId" class="ctrl-input" placeholder="<?= htmlspecialchars(t('centrosAcopio.searchid_ph')) ?>" min="1"
                style="max-width:200px">
            <button class="btn-primary" onclick="buscarPorId()"><?= t('crud.btn_buscar') ?></button>
            <button class="btn-secondary" onclick="listarCentros()"><?= t('crud.btn_vertodos') ?></button>
        </div>
    </section>


    <section class="control-bar" id="deleteBar" style="display:none">
        <div class="control-inner">
            <input type="number" id="deleteId" class="ctrl-input" placeholder="<?= htmlspecialchars(t('centrosAcopio.deleteid_ph')) ?>"
                min="1" style="max-width:280px">
            <button class="btn-danger" onclick="darDeBajaCentro()"><?= t('camiones.card_baja_btn') ?></button>
            <button class="btn-secondary" onclick="ocultarControles()"><?= t('crud.btn_cancelar') ?></button>
        </div>
    </section>

    
    <section class="table-section">
        <div class="table-header">
            <h2 class="table-title" id="tableTitle"><?= t('centrosAcopio.hero_title') ?></h2>
            <span class="table-count" id="tableCount"></span>
            <button class="btn-secondary" id="btnMostrarTodos" style="display:none" onclick="listarCentros()"><?= t('centrosAcopio.btn_mostrar_todos') ?></button>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><?= t('crud.th_id') ?></th>
                        <th><?= t('centrosAcopio.th_nombre') ?></th>
                        <th><?= t('centrosAcopio.th_tipo') ?></th>
                        <th><?= t('centrosAcopio.th_municipio') ?></th>
                        <th><?= t('centrosAcopio.th_ocupacion') ?></th>
                        <th><?= t('centrosAcopio.th_estado') ?></th>
                        <th><?= t('crud.th_acciones') ?></th>
                    </tr>
                </thead>
                <tbody id="centrosBody"></tbody>
            </table>
            <div id="noResultados" class="empty-state" style="display:none">
                <?= t('crud.empty_state') ?>
            </div>
            <div id="loadingState" class="loading-state">
                <?= t('crud.loading_state') ?>
            </div>
        </div>
    </section>


    <div class="modal-overlay" id="modalForm" style="display:none" onclick="onOverlayClick(event,'modalForm')">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalTitle"><?= t('centrosAcopio.modal_add_title') ?></h3>
                <button class="modal-close" onclick="cerrarModal()">✕</button>
            </div>
            <form id="centroForm" onsubmit="guardarCentro(event)">
                <input type="hidden" id="centroId">
                <div class="form-row">
                    <div class="form-group">
                        <label for="idMunicipio"><?= t('centrosAcopio.field_municipio') ?></label>
                        <select id="idMunicipio" class="form-input" required></select>
                    </div>
                    <div class="form-group">
                        <label for="nombre"><?= t('centrosAcopio.field_nombre') ?></label>
                        <input type="text" id="nombre" class="form-input" placeholder="<?= htmlspecialchars(t('centrosAcopio.field_nombre_ph')) ?>" maxlength="100" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="tipo"><?= t('centrosAcopio.field_tipo') ?></label>
                        <select id="tipo" class="form-input" required>
                            <option value="ecocentro"><?= t('centrosAcopio.opt_ecocentro') ?></option>
                            <option value="planta_clasificacion"><?= t('centrosAcopio.opt_planta_clasificacion') ?></option>
                            <option value="relleno_sanitario"><?= t('centrosAcopio.opt_relleno_sanitario') ?></option>
                            <option value="vertedero"><?= t('centrosAcopio.opt_vertedero') ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="estado"><?= t('centrosAcopio.field_estado') ?></label>
                        <select id="estado" class="form-input" required>
                            <option value="operativo"><?= t('centrosAcopio.opt_operativo') ?></option>
                            <option value="saturado"><?= t('centrosAcopio.opt_saturado') ?></option>
                            <option value="cerrado"><?= t('centrosAcopio.opt_cerrado') ?></option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="calle"><?= t('centrosAcopio.field_calle') ?></label>
                        <input type="text" id="calle" class="form-input" placeholder="<?= htmlspecialchars(t('centrosAcopio.field_calle_ph')) ?>" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label for="numCalle"><?= t('centrosAcopio.field_numcalle') ?></label>
                        <input type="number" id="numCalle" class="form-input" placeholder="<?= htmlspecialchars(t('centrosAcopio.field_numcalle_ph')) ?>" min="1" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="latitud"><?= t('centrosAcopio.field_latitud') ?></label>
                        <input type="number" id="latitud" class="form-input" placeholder="-34.9011" step="any" required>
                    </div>
                    <div class="form-group">
                        <label for="longitud"><?= t('centrosAcopio.field_longitud') ?></label>
                        <input type="number" id="longitud" class="form-input" placeholder="-56.1645" step="any" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="capacidadTotal"><?= t('centrosAcopio.field_capacidad_total') ?></label>
                        <input type="number" id="capacidadTotal" class="form-input" placeholder="<?= htmlspecialchars(t('centrosAcopio.field_capacidad_total_ph')) ?>" min="1" required>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="cerrarModal()"><?= t('crud.btn_cancelar') ?></button>
                    <button type="submit" class="btn-primary"><?= t('crud.btn_guardar') ?></button>
                </div>
            </form>
        </div>
    </div>


    <div class="modal-overlay" id="modalDelete" style="display:none" onclick="onOverlayClick(event,'modalDelete')">
        <div class="modal modal-sm">
            <div class="modal-header">
                <h3><?= t('centrosAcopio.modal_delete_title') ?></h3>
                <button class="modal-close" onclick="cerrarModalDelete()">✕</button>
            </div>
            <p class="delete-msg">
                <?= t('centrosAcopio.delete_confirm_before') ?> <strong id="deleteNombre"></strong><?= t('centrosAcopio.delete_confirm_after') ?>
            </p>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="cerrarModalDelete()"><?= t('crud.btn_cancelar') ?></button>
                <button class="btn-danger" onclick="confirmarDelete()"><?= t('camiones.card_baja_btn') ?></button>
            </div>
        </div>
    </div>

    <!-- ── MODAL MAQUINARIA DEL CENTRO ───────────────────────────────────── -->
    <div class="modal-overlay" id="modalMaquinaria" style="display:none" onclick="onOverlayClick(event,'modalMaquinaria')">
        <div class="modal" style="max-width:820px">
            <div class="modal-header">
                <h3><?= t('centrosAcopio.maquinaria_titulo_prefijo') ?> <span id="maquinariaCentroNombre"></span></h3>
                <button class="modal-close" onclick="cerrarModalMaquinaria()">✕</button>
            </div>
            <div class="table-wrapper" style="margin-bottom:20px">
                <table>
                    <thead>
                        <tr>
                            <th><?= t('centrosAcopio.maq_th_nombre') ?></th><th><?= t('centrosAcopio.maq_th_tipo') ?></th><th><?= t('centrosAcopio.maq_th_adquisicion') ?></th><th><?= t('centrosAcopio.maq_th_estado') ?></th><th></th>
                        </tr>
                    </thead>
                    <tbody id="maquinariaBody"></tbody>
                </table>
                <div id="maquinariaVacio" class="empty-state" style="display:none"><?= t('centrosAcopio.maquinaria_vacio') ?></div>
            </div>
            <form id="maquinariaForm" onsubmit="guardarMaquinaria(event)">
                <input type="hidden" id="maquinariaIdCentro">
                <input type="hidden" id="maquinariaId">
                <div class="form-row">
                    <div class="form-group">
                        <label for="maqNombre"><?= t('centrosAcopio.maq_field_nombre') ?></label>
                        <input type="text" id="maqNombre" class="form-input" placeholder="<?= htmlspecialchars(t('centrosAcopio.maq_field_nombre_ph')) ?>" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label for="maqTipo"><?= t('centrosAcopio.maq_field_tipo') ?></label>
                        <select id="maqTipo" class="form-input" required>
                            <option value="compactadora"><?= t('centrosAcopio.opt_compactadora') ?></option>
                            <option value="cinta"><?= t('centrosAcopio.opt_cinta') ?></option>
                            <option value="pala"><?= t('centrosAcopio.opt_pala') ?></option>
                            <option value="bascula"><?= t('centrosAcopio.opt_bascula') ?></option>
                            <option value="herramienta"><?= t('centrosAcopio.opt_herramienta') ?></option>
                            <option value="otro"><?= t('centrosAcopio.opt_otro') ?></option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="maqFecha"><?= t('centrosAcopio.maq_field_fecha') ?></label>
                        <input type="date" id="maqFecha" class="form-input" required>
                    </div>
                    <div class="form-group" id="maqEstadoGroup">
                        <label for="maqEstado"><?= t('centrosAcopio.maq_field_estado') ?></label>
                        <select id="maqEstado" class="form-input" required>
                            <option value="operativa"><?= t('centrosAcopio.opt_maq_operativa') ?></option>
                            <option value="en_mantenimiento"><?= t('centrosAcopio.opt_maq_en_mantenimiento') ?></option>
                            <option value="fuera_servicio"><?= t('centrosAcopio.opt_maq_fuera_servicio') ?></option>
                        </select>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" id="maquinariaCancelarBtn" style="display:none" onclick="cancelarEdicionMaquinaria()"><?= t('centrosAcopio.btn_cancelar_edicion') ?></button>
                    <button type="submit" class="btn-primary" id="maquinariaSubmitBtn"><?= t('centrosAcopio.btn_agregar_maquinaria') ?></button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── MODAL OPERARIOS DEL CENTRO ────────────────────────────────────── -->
    <div class="modal-overlay" id="modalOperarios" style="display:none" onclick="onOverlayClick(event,'modalOperarios')">
        <div class="modal">
            <div class="modal-header">
                <h3><span id="operariosTituloPrefijo"></span> <span id="operariosCentroNombre"></span></h3>
                <button class="modal-close" onclick="cerrarModalOperarios()">✕</button>
            </div>
            <ul class="miembros-list" id="operariosList"></ul>
            <p class="empty-state" id="operariosVacio" style="display:none"></p>
            <div class="miembros-asignar">
                <select id="operarioSelect" class="form-input"></select>
                <button type="button" class="btn-secondary" onclick="asignarOperario()"><?= t('crud.btn_asignar') ?></button>
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

    <script>
        const API_BASE = '/recolix/API_Gestion';
        const API_USUARIOS = '/recolix/API_Usuarios/api.php';
        const I18N = <?= json_encode(t_array([
            'titleVerOperarios' => 'centrosAcopio.title_ver_operarios',
            'operariosTituloPrefijo' => 'centrosAcopio.operarios_titulo_prefijo',
            'operariosVacio' => 'centrosAcopio.operarios_vacio',
            'msgOperarioAsignado' => 'centrosAcopio.msg_operario_asignado',
            'msgOperarioQuitado' => 'centrosAcopio.msg_operario_quitado',
            'msgErrorAsignarOperario' => 'centrosAcopio.msg_error_asignar_operario',
            'msgErrorQuitarOperario' => 'centrosAcopio.msg_error_quitar_operario',
            'msgNoPudoCargarOperarios' => 'centrosAcopio.msg_no_pudo_cargar_operarios',
            'msgNoConectarApi' => 'centrosAcopio.msg_no_conectar_api',
            'msgErrorBuscarCentro' => 'centrosAcopio.msg_error_buscar_centro',
            'msgErrorFiltrar' => 'centrosAcopio.msg_error_filtrar',
            'msgCentroModificado' => 'centrosAcopio.msg_centro_modificado',
            'msgCentroAgregado' => 'centrosAcopio.msg_centro_agregado',
            'msgErrorGuardar' => 'centrosAcopio.msg_error_guardar',
            'msgErrorConexionApi' => 'centrosAcopio.msg_error_conexion_api',
            'msgCentroDadoBaja' => 'centrosAcopio.msg_centro_dado_baja',
            'msgErrorDarBaja' => 'centrosAcopio.msg_error_dar_baja',
            'msgNoPudoCargarMaquinaria' => 'centrosAcopio.msg_no_pudo_cargar_maquinaria',
            'msgMaquinariaModificada' => 'centrosAcopio.msg_maquinaria_modificada',
            'msgMaquinariaAgregada' => 'centrosAcopio.msg_maquinaria_agregada',
            'msgErrorGuardarMaquinaria' => 'centrosAcopio.msg_error_guardar_maquinaria',
            'sinDatos' => 'centrosAcopio.sin_datos',
            'municipioPrefijo' => 'centrosAcopio.municipio_prefijo',
            'modalAddTitle' => 'centrosAcopio.modal_add_title',
            'modalEditTitle' => 'centrosAcopio.modal_edit_title',
            'titleVerMaquinaria' => 'centrosAcopio.title_ver_maquinaria',
            'crudTitleEditar' => 'crud.title_editar',
            'modalDeleteTitle' => 'centrosAcopio.modal_delete_title',
            'btnGuardarCambios' => 'centrosAcopio.btn_guardar_cambios',
            'btnAgregarMaquinaria' => 'centrosAcopio.btn_agregar_maquinaria',
        ])) ?>;
        const LABELS_TIPO_CENTRO_I18N = <?= json_encode(t_array([
            'ecocentro' => 'centrosAcopio.opt_ecocentro',
            'planta_clasificacion' => 'centrosAcopio.opt_planta_clasificacion',
            'relleno_sanitario' => 'centrosAcopio.opt_relleno_sanitario',
            'vertedero' => 'centrosAcopio.opt_vertedero',
        ])) ?>;
        const LABELS_ESTADO_CENTRO_I18N = <?= json_encode(t_array([
            'operativo' => 'centrosAcopio.opt_operativo',
            'saturado' => 'centrosAcopio.opt_saturado',
            'cerrado' => 'centrosAcopio.opt_cerrado',
        ])) ?>;
        const LABELS_TIPO_MAQUINARIA_I18N = <?= json_encode(t_array([
            'compactadora' => 'centrosAcopio.opt_compactadora',
            'cinta' => 'centrosAcopio.opt_cinta',
            'pala' => 'centrosAcopio.opt_pala',
            'bascula' => 'centrosAcopio.opt_bascula',
            'herramienta' => 'centrosAcopio.opt_herramienta',
            'otro' => 'centrosAcopio.opt_otro',
        ])) ?>;
        const LABELS_ESTADO_MAQUINARIA_I18N = <?= json_encode(t_array([
            'operativa' => 'centrosAcopio.opt_maq_operativa',
            'en_mantenimiento' => 'centrosAcopio.opt_maq_en_mantenimiento',
            'fuera_servicio' => 'centrosAcopio.opt_maq_fuera_servicio',
        ])) ?>;
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

        const LABELS_TIPO_CENTRO = LABELS_TIPO_CENTRO_I18N;
        const LABELS_ESTADO_CENTRO = LABELS_ESTADO_CENTRO_I18N;

        function poblarSelectMunicipio() {
            const select = document.getElementById('idMunicipio');
            select.innerHTML = MUNICIPIOS.map(m => `<option value="${m.id}">${m.nombre}</option>`).join('');
        }

        function nombreMunicipio(id) {
            const m = MUNICIPIOS.find(x => x.id == id);
            if (m) {
                return m.nombre;
            }
            return `${I18N.municipioPrefijo} ${id}`;
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
                if (btn.dataset.action === 'operarios') abrirModalOperarios(c);
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
                if (json.status === 'success') {
                    reglasCentro = json.data;
                } else {
                    reglasCentro = reglasCentroFallback();
                }
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
                if (json.status === 'success') {
                    centrosActuales = json.data;
                } else {
                    centrosActuales = [];
                }
                renderCentros(centrosActuales);
                document.getElementById('btnMostrarTodos').style.display = 'none';
            } catch {
                mostrarToast(I18N.msgNoConectarApi, 'error');
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
            if (rows.length) {
                document.getElementById('noResultados').style.display = 'none';
            } else {
                document.getElementById('noResultados').style.display = 'flex';
            }

            body.innerHTML = rows.map(c => `
                <tr data-id="${c.id_centro}">
                    <td class="td-id">${c.id_centro}</td>
                    <td>${c.nombre}</td>
                    <td>${LABELS_TIPO_CENTRO[c.tipo] ?? c.tipo}</td>
                    <td>${nombreMunicipio(c.id_municipio)}</td>
                    <td id="ocupacion-${c.id_centro}">—</td>
                    <td><span class="badge ${badgeClaseEstado(c.estado)}">${LABELS_ESTADO_CENTRO[c.estado] ?? c.estado}</span></td>
                    <td class="td-actions">
                        <button class="btn-action" data-action="maquinaria" data-id="${c.id_centro}" title="${I18N.titleVerMaquinaria}">🔧</button>
                        <button class="btn-action" data-action="operarios" data-id="${c.id_centro}" title="${I18N.titleVerOperarios}">👷</button>
                        <button class="btn-action" data-action="editar" data-id="${c.id_centro}" title="${I18N.crudTitleEditar}">✎</button>
                        <button class="btn-action" data-action="baja" data-id="${c.id_centro}" title="${I18N.modalDeleteTitle}">🗑</button>
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
                    celda.textContent = I18N.sinDatos;
                }
            } catch {
                celda.textContent = I18N.sinDatos;
            }
        }

        function mostrarCargando(mostrar) {
            if (mostrar) {
                document.getElementById('loadingState').style.display = 'flex';
            } else {
                document.getElementById('loadingState').style.display = 'none';
            }
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
                mostrarToast(I18N.msgErrorBuscarCentro, 'error');
            }
            mostrarCargando(false);
        }

        

        function actualizarFiltroValor() {
            const campo = document.getElementById('filtroCampo').value;
            const select = document.getElementById('filtroValorSelect');
            if (campo) {
                select.style.display = 'inline-block';
            } else {
                select.style.display = 'none';
            }
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
                if (json.status === 'success') {
                    centrosActuales = json.data;
                } else {
                    centrosActuales = [];
                }
                renderCentros(centrosActuales);
                document.getElementById('btnMostrarTodos').style.display = 'inline-block';
            } catch {
                mostrarToast(I18N.msgErrorFiltrar, 'error');
            }
            mostrarCargando(false);
        }



        function abrirModalAgregar() {
            document.getElementById('modalTitle').textContent = I18N.modalAddTitle;
            document.getElementById('centroForm').reset();
            document.getElementById('centroId').value = '';
            document.getElementById('modalForm').style.display = 'flex';
        }

        function abrirModalEditar(c) {
            document.getElementById('modalTitle').textContent = I18N.modalEditTitle;
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
            let url, method;
            if (id) {
                url = `${API_BASE}/centros-acopio/${id}`;
                method = 'PUT';
            } else {
                url = `${API_BASE}/centros-acopio`;
                method = 'POST';
            }
            try {
                const res = await fetch(
                    url,
                    {
                        method: method,
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(body)
                    }
                );
                const json = await res.json();
                if (json.status === 'success') {
                    let mensaje;
                    if (id) {
                        mensaje = I18N.msgCentroModificado;
                    } else {
                        mensaje = I18N.msgCentroAgregado;
                    }
                    mostrarToast(mensaje);
                    cerrarModal();
                    listarCentros();
                } else {
                    mostrarToast(json.message || I18N.msgErrorGuardar, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorConexionApi, 'error');
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
                    mostrarToast(I18N.msgCentroDadoBaja);
                    listarCentros();
                } else {
                    mostrarToast(json.message || I18N.msgErrorDarBaja, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorConexionApi, 'error');
            }
            cerrarModalDelete();
        }

        async function darDeBajaCentro() {
            const id = document.getElementById('deleteId').value;
            if (!id) return;
            const c = centrosActuales.find(x => x.id_centro == id);
            let nombreCentro;
            if (c) {
                nombreCentro = c.nombre;
            } else {
                nombreCentro = `#${id}`;
            }
            confirmarBorrar(id, nombreCentro);
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
                if (json.status === 'success') {
                    maquinariaActual = json.data;
                } else {
                    maquinariaActual = [];
                }
                if (maquinariaActual.length) {
                    vacio.style.display = 'none';
                } else {
                    vacio.style.display = 'flex';
                }
                body.innerHTML = maquinariaActual.map(m => `
                    <tr>
                        <td>${m.nombre}</td>
                        <td>${LABELS_TIPO_MAQUINARIA_I18N[m.tipo] ?? m.tipo}</td>
                        <td>${m.fecha_adquisicion}</td>
                        <td><span class="badge ${badgeClaseEstado(m.estado)}">${LABELS_ESTADO_MAQUINARIA_I18N[m.estado] ?? m.estado}</span></td>
                        <td class="td-actions">
                            <button type="button" class="btn-action" onclick="abrirEdicionMaquinaria(${m.id_maquinaria})" title="${I18N.crudTitleEditar}">✎</button>
                        </td>
                    </tr>
                `).join('');
            } catch {
                mostrarToast(I18N.msgNoPudoCargarMaquinaria, 'error');
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
            document.getElementById('maqEstadoGroup').style.display = 'none';
            document.getElementById('maquinariaSubmitBtn').textContent = I18N.btnGuardarCambios;
            document.getElementById('maquinariaCancelarBtn').style.display = 'inline-block';
        }

        function cancelarEdicionMaquinaria() {
            document.getElementById('maquinariaForm').reset();
            document.getElementById('maquinariaId').value = '';
            document.getElementById('maqEstadoGroup').style.display = '';
            document.getElementById('maquinariaSubmitBtn').textContent = I18N.btnAgregarMaquinaria;
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
            let body, url, method;
            if (editando) {
                body = datosComunes;
                url = `${API_BASE}/maquinaria/${idMaquinaria}`;
                method = 'PUT';
            } else {
                body = { idCentro: parseInt(idCentro, 10), ...datosComunes };
                url = `${API_BASE}/maquinaria`;
                method = 'POST';
            }
            try {
                const res = await fetch(
                    url,
                    {
                        method: method,
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(body)
                    }
                );
                const json = await res.json();
                if (json.status === 'success') {
                    let mensaje;
                    if (editando) {
                        mensaje = I18N.msgMaquinariaModificada;
                    } else {
                        mensaje = I18N.msgMaquinariaAgregada;
                    }
                    mostrarToast(mensaje);
                    cancelarEdicionMaquinaria();
                    cargarMaquinariaDeCentro(idCentro);
                } else {
                    mostrarToast(json.message || I18N.msgErrorGuardarMaquinaria, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorConexionApi, 'error');
            }
        }

        // ── OPERARIOS DEL CENTRO (USUARIO.id_centro) ─────────────────────────
        // Solo usuarios con rol 'operario' pueden pertenecer a un centro de acopio.
        // Se gestiona contra API_Usuarios porque id_centro vive en la tabla USUARIO.
        let operariosCentroActual = null;

        async function abrirModalOperarios(centro) {
            operariosCentroActual = centro.id_centro;
            document.getElementById('operariosTituloPrefijo').textContent = I18N.operariosTituloPrefijo;
            document.getElementById('operariosCentroNombre').textContent = centro.nombre;
            document.getElementById('modalOperarios').style.display = 'flex';
            await cargarOperariosDeCentro(centro.id_centro);
        }

        function cerrarModalOperarios() {
            document.getElementById('modalOperarios').style.display = 'none';
        }

        async function cargarOperariosDeCentro(idCentro) {
            const lista = document.getElementById('operariosList');
            const vacio = document.getElementById('operariosVacio');
            const select = document.getElementById('operarioSelect');
            lista.innerHTML = '';
            vacio.textContent = I18N.operariosVacio;
            select.innerHTML = `<option value="">${I18N.msgNoPudoCargarOperarios}</option>`;

            try {
                const [resOperarios, resCandidatos] = await Promise.all([
                    fetch(`${API_USUARIOS}/usuarios/obtenerPorCentro/${idCentro}`),
                    fetch(`${API_USUARIOS}/usuarios/filtrarRol?rol=operario`)
                ]);
                const jsonOperarios = await resOperarios.json();
                const jsonCandidatos = await resCandidatos.json();

                const operarios = jsonOperarios.status === 'success' ? jsonOperarios.data : [];
                const candidatos = jsonCandidatos.status === 'success' ? jsonCandidatos.data : [];

                vacio.style.display = operarios.length ? 'none' : 'block';
                lista.innerHTML = operarios.map(o => `
                    <li class="miembro-item">
                        <span><span class="miembro-nombre">${o.primer_nombre} ${o.p_apellido}</span>
                        <span class="miembro-ci"> — CI ${o.ci_usuario}</span></span>
                        <button type="button" class="btn-action" title="${I18N.msgOperarioQuitado}" onclick="quitarOperario(${o.ci_usuario})">🗑️</button>
                    </li>
                `).join('');

                const disponibles = candidatos.filter(c => String(c.id_centro) !== String(idCentro));
                if (!disponibles.length) {
                    select.innerHTML = `<option value="">${I18N.operariosVacio}</option>`;
                } else {
                    select.innerHTML = disponibles.map(c => {
                        const etiqueta = c.id_centro
                            ? `${c.primer_nombre} ${c.p_apellido} (CI ${c.ci_usuario}) — #${c.id_centro}`
                            : `${c.primer_nombre} ${c.p_apellido} (CI ${c.ci_usuario})`;
                        return `<option value="${c.ci_usuario}">${etiqueta}</option>`;
                    }).join('');
                }
            } catch {
                mostrarToast(I18N.msgNoPudoCargarOperarios, 'error');
            }
        }

        async function asignarOperario() {
            const ci = document.getElementById('operarioSelect').value;
            if (!ci || !operariosCentroActual) return;
            try {
                const res = await fetch(`${API_USUARIOS}/usuarios/asignarCentro`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ci: parseInt(ci, 10), idCentro: parseInt(operariosCentroActual, 10) })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    mostrarToast(I18N.msgOperarioAsignado);
                    cargarOperariosDeCentro(operariosCentroActual);
                } else {
                    mostrarToast(json.message || I18N.msgErrorAsignarOperario, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorAsignarOperario, 'error');
            }
        }

        async function quitarOperario(ci) {
            if (!operariosCentroActual) return;
            try {
                const res = await fetch(`${API_USUARIOS}/usuarios/quitarCentro/${ci}`, { method: 'POST' });
                const json = await res.json();
                if (json.status === 'success') {
                    mostrarToast(I18N.msgOperarioQuitado);
                    cargarOperariosDeCentro(operariosCentroActual);
                } else {
                    mostrarToast(json.message || I18N.msgErrorQuitarOperario, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorQuitarOperario, 'error');
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
            if (bar.style.display === 'none') {
                bar.style.display = 'block';
            } else {
                bar.style.display = 'none';
            }
        }

        function toggleBusquedaId() {
            const bar = document.getElementById('searchBar');
            ocultarControles();
            if (bar.style.display === 'none') {
                bar.style.display = 'block';
            } else {
                bar.style.display = 'none';
            }
        }

        function toggleDarDeBaja() {
            const bar = document.getElementById('deleteBar');
            ocultarControles();
            if (bar.style.display === 'none') {
                bar.style.display = 'block';
            } else {
                bar.style.display = 'none';
            }
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
