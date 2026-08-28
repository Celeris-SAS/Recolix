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
    <title><?= t('camiones.title') ?></title>
    <link rel="stylesheet" href="styleRecoleccion.css">
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
                <a href="camiones.php" class="nav-active"><?= t('backoffice.nav_camiones') ?></a>
                <a href="circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
                <a href="rutas.php"><?= t('backoffice.nav_rutas') ?></a>
                <a href="../../API_Usuarios/Vista/cuadrillas.php"><?= t('backoffice.nav_cuadrillas') ?></a>
                <a href="../../API_Gestion/Vista/contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
                <a href="../../API_Gestion/Vista/centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
                <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
                <a href="../../API_Gestion/Vista/reportes.php"><?= t('backoffice.nav_reportes') ?></a>
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
        <a href="camiones.php" class="nav-active"><?= t('backoffice.nav_camiones') ?></a>
        <a href="circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
        <a href="rutas.php"><?= t('backoffice.nav_rutas') ?></a>
        <a href="../../API_Usuarios/Vista/cuadrillas.php"><?= t('backoffice.nav_cuadrillas') ?></a>
        <a href="../../API_Gestion/Vista/contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
        <a href="../../API_Gestion/Vista/centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
        <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
        <a href="../../API_Gestion/Vista/reportes.php"><?= t('backoffice.nav_reportes') ?></a>
        <a href="../../API_Gestion/Vista/incidencias.php"><?= t('backoffice.nav_incidencias') ?></a>
        <a href="../../API_Usuarios/Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
    </nav>
    <div class="nav-hr"></div>
</header>

<section class="hero">
    <h1 class="hero-title"><?= t('camiones.hero_title') ?></h1>
    <p class="hero-sub"><?= t('camiones.hero_sub') ?></p>
</section>

<section class="cards-section">
    <div class="cards-grid">

        <div class="card-operar" onclick="abrirModalAgregar()">
            <div class="card-content">
                <div class="card-icon"><img src="img/plus.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('camiones.card_agregar_titulo') ?></h3>
                <p><?= t('camiones.card_agregar_desc') ?></p>
                <button class="card-btn"><?= t('camiones.card_agregar_btn') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleFiltros()">
            <div class="card-content">
                <div class="card-icon"><img src="img/loupe.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('camiones.card_filtrar_titulo') ?></h3>
                <p><?= t('camiones.card_filtrar_desc') ?></p>
                <button class="card-btn"><?= t('camiones.card_filtrar_btn') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleBusquedaId()">
            <div class="card-content">
                <div class="card-icon"><img src="img/question.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('camiones.card_buscarid_titulo') ?></h3>
                <p><?= t('camiones.card_buscarid_desc') ?></p>
                <button class="card-btn"><?= t('camiones.card_buscarid_btn') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleModificar()">
            <div class="card-content">
                <div class="card-icon"><img src="img/refresh.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('camiones.card_modificar_titulo') ?></h3>
                <p><?= t('camiones.card_modificar_desc') ?></p>
                <button class="card-btn"><?= t('camiones.card_modificar_btn') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleDarDeBaja()">
            <div class="card-content">
                <div class="card-icon"><img src="img/minus.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('camiones.card_baja_titulo') ?></h3>
                <p><?= t('camiones.card_baja_desc') ?></p>
                <button class="card-btn"><?= t('camiones.card_baja_btn') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleMantenimientoRegistrar()">
            <div class="card-content">
                <div class="card-icon"><img src="img/correctivo.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('camiones.card_regmant_titulo') ?></h3>
                <p><?= t('camiones.card_regmant_desc') ?></p>
                <button class="card-btn"><?= t('camiones.card_regmant_btn') ?></button>
            </div>
        </div>

        <div class="card-operar" onclick="toggleMantenimientoProgramar()">
            <div class="card-content">
                <div class="card-icon"><img src="img/preventivo.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('camiones.card_progmant_titulo') ?></h3>
                <p><?= t('camiones.card_progmant_desc') ?></p>
                <button class="card-btn"><?= t('camiones.card_progmant_btn') ?></button>
            </div>
        </div>

    </div>
</section>

<section class="control-bar" id="filterBar" style="display:none">
    <div class="control-inner">
        <select id="filtroTipo" class="ctrl-select" onchange="actualizarFiltroValor()">
            <option value=""><?= t('camiones.filtro_placeholder_select') ?></option>
            <option value="estado"><?= t('camiones.filtro_opt_estado') ?></option>
            <option value="tipoCarga"><?= t('camiones.filtro_opt_tipoCarga') ?></option>
            <option value="disponibilidad"><?= t('camiones.filtro_opt_disponibilidad') ?></option>
            <option value="mantenimiento"><?= t('camiones.filtro_opt_mantenimiento') ?></option>
            <option value="proximosMantenimientos"><?= t('camiones.filtro_opt_proximosMantenimientos') ?></option>
            <option value="anio"><?= t('camiones.filtro_opt_anio') ?></option>
            <option value="modelo"><?= t('camiones.filtro_opt_modelo') ?></option>
            <option value="capacidad"><?= t('camiones.filtro_opt_capacidad') ?></option>
        </select>
        <input type="text" id="filtroValor" class="ctrl-input" placeholder="<?= htmlspecialchars(t('camiones.filtro_valor_placeholder')) ?>" style="display:none">
        <select id="filtroValorSelect" class="ctrl-select" style="display:none"></select>
        <button class="btn-primary" onclick="aplicarFiltro()"><?= t('camiones.btn_aplicar') ?></button>
        <button class="btn-secondary" onclick="listarCamiones()"><?= t('camiones.btn_limpiar') ?></button>
    </div>
</section>

<section class="control-bar" id="searchBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="buscarId" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('camiones.buscarid_placeholder')) ?>" min="1" style="max-width:200px">
        <button class="btn-primary" onclick="buscarPorId()"><?= t('camiones.btn_buscar') ?></button>
        <button class="btn-secondary" onclick="listarCamiones()"><?= t('camiones.btn_vertodos') ?></button>
    </div>
</section>

<section class="control-bar" id="modificarBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="modificarId" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('camiones.modificarid_placeholder')) ?>" min="1" style="max-width:120px">
        <select id="modificarCampo" class="ctrl-select" onchange="actualizarPlaceholderModificar()">
            <option value=""><?= t('camiones.campo_placeholder_select') ?></option>
            <option value="matricula"><?= t('camiones.campo_opt_matricula') ?></option>
            <option value="tipoCarga"><?= t('camiones.campo_opt_tipoCarga') ?></option>
            <option value="capacidad"><?= t('camiones.campo_opt_capacidad') ?></option>
            <option value="anio"><?= t('camiones.campo_opt_anio') ?></option>
            <option value="modelo"><?= t('camiones.campo_opt_modelo') ?></option>
            <option value="estado"><?= t('camiones.campo_opt_estado') ?></option>
        </select>
        <input type="text" id="modificarValor" class="ctrl-input" style="display:none" placeholder="<?= htmlspecialchars(t('camiones.valor_placeholder_seleccionacampo')) ?>">
        <select id="modificarValorSelect" class="ctrl-select" style="display:none"></select>
        <button class="btn-primary" onclick="modificarCamion()"><?= t('camiones.btn_modificar') ?></button>
        <button class="btn-secondary" onclick="ocultarControles()"><?= t('camiones.btn_cancelar') ?></button>
    </div>
</section>

<section class="control-bar" id="deleteBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="deleteId" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('camiones.deleteid_placeholder')) ?>" min="1" style="max-width:240px">
        <button class="btn-danger" onclick="darDeBajaCamion()"><?= t('camiones.btn_dardebaja') ?></button>
        <button class="btn-secondary" onclick="ocultarControles()"><?= t('camiones.btn_cancelar') ?></button>
    </div>
</section>

<section class="control-bar" id="mantenimientoRegistrarBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="regMantId" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('camiones.mant_id_placeholder')) ?>" min="1" style="max-width:130px">
        <select id="regMantTipo" class="ctrl-select">
            <option value="preventivo"><?= t('camiones.mant_tipo_preventivo') ?></option>
            <option value="correctivo"><?= t('camiones.mant_tipo_correctivo') ?></option>
        </select>
        <input type="datetime-local" id="regMantFecha" class="ctrl-input" style="max-width:220px">
        <input type="datetime-local" id="regMantFechaFin" class="ctrl-input" style="max-width:220px">
        <input type="text" id="regMantDescripcion" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('camiones.mant_descripcion_placeholder')) ?>" style="max-width:240px">
        <input type="number" id="regMantCosto" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('camiones.mant_costo_placeholder')) ?>" min="0" step="0.01" style="max-width:120px">
        <button class="btn-primary" onclick="registrarMantenimiento()"><?= t('camiones.btn_registrar') ?></button>
        <button class="btn-secondary" onclick="ocultarControles()"><?= t('camiones.btn_cancelar') ?></button>
    </div>
</section>

<section class="control-bar" id="mantenimientoProgramarBar" style="display:none">
    <div class="control-inner">
        <input type="number" id="progMantId" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('camiones.mant_id_placeholder')) ?>" min="1" style="max-width:130px">
        <select id="progMantTipo" class="ctrl-select">
            <option value="preventivo"><?= t('camiones.mant_tipo_preventivo') ?></option>
            <option value="correctivo"><?= t('camiones.mant_tipo_correctivo') ?></option>
        </select>
        <input type="datetime-local" id="progMantFecha" class="ctrl-input" style="max-width:220px">
        <input type="datetime-local" id="progMantFechaFin" class="ctrl-input" style="max-width:220px">
        <input type="text" id="progMantDescripcion" class="ctrl-input"
               placeholder="<?= htmlspecialchars(t('camiones.mant_descripcion_placeholder')) ?>" style="max-width:240px">
        <button class="btn-primary" onclick="programarMantenimiento()"><?= t('camiones.btn_programar') ?></button>
        <button class="btn-secondary" onclick="ocultarControles()"><?= t('camiones.btn_cancelar') ?></button>
    </div>
</section>

<section class="table-section">
    <div class="table-header">
        <h2 class="table-title" id="tableTitle"><?= t('camiones.hero_title') ?></h2>
        <span class="table-count" id="tableCount"></span>
    </div>
    <div class="table-wrapper">
        <table id="tablaPrincipal">
            <thead>
                <tr id="tablaHeaders">
                    <th><?= t('camiones.th_id') ?></th>
                    <th><?= t('camiones.th_matricula') ?></th>
                    <th><?= t('camiones.th_tipocarga') ?></th>
                    <th><?= t('camiones.th_capacidad') ?></th>
                    <th><?= t('camiones.th_anio') ?></th>
                    <th><?= t('camiones.th_modelo') ?></th>
                    <th><?= t('camiones.th_estado') ?></th>
                    <th><?= t('camiones.th_acciones') ?></th>
                </tr>
            </thead>
            <tbody id="camionesBody"></tbody>
        </table>
        <div id="noResultados" class="empty-state" style="display:none">
            <?= t('camiones.empty_state') ?>
        </div>
        <div id="loadingState" class="loading-state">
            <?= t('camiones.loading_state') ?>
        </div>
    </div>
</section>

<div class="modal-overlay" id="modalForm" style="display:none"
     onclick="onOverlayClick(event,'modalForm')">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle"><?= t('camiones.modal_add_title') ?></h3>
            <button class="modal-close" onclick="cerrarModal()">✕</button>
        </div>
        <form id="camionForm" onsubmit="guardarCamion(event)">
            <input type="hidden" id="camionId">
            <div class="form-row">
                <div class="form-group">
                    <label for="matricula"><?= t('camiones.field_matricula') ?></label>
                    <input type="text" id="matricula" class="form-input"
                           placeholder="<?= htmlspecialchars(t('camiones.field_matricula_placeholder')) ?>" maxlength="7" required
                           oninput="this.setCustomValidity('')">
                </div>
                <div class="form-group">
                    <label for="tipoCarga"><?= t('camiones.field_tipocarga') ?></label>
                    <select id="tipoCarga" class="form-input">
                        <option value="carga_lateral" selected><?= t('camiones.opt_carga_lateral') ?></option>
                        <option value="carga_trasera"><?= t('camiones.opt_carga_trasera') ?></option>
                        <option value="puerta_a_puerta"><?= t('camiones.opt_puerta_a_puerta') ?></option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="capacidad"><?= t('camiones.field_capacidad') ?></label>
                    <input type="number" id="capacidad" class="form-input"
                           placeholder="<?= htmlspecialchars(t('camiones.field_capacidad_placeholder')) ?>" min="0" required>
                </div>
                <div class="form-group">
                    <label for="anio"><?= t('camiones.field_anio') ?></label>
                    <input type="number" id="anio" class="form-input"
                           placeholder="<?= htmlspecialchars(t('camiones.field_anio_placeholder')) ?>" min="1990" max="2030" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="modelo"><?= t('camiones.field_modelo') ?></label>
                    <input type="text" id="modelo" class="form-input"
                           placeholder="<?= htmlspecialchars(t('camiones.field_modelo_placeholder')) ?>" required>
                </div>
                <div class="form-group">
                    <label for="estado"><?= t('camiones.field_estado') ?></label>
                    <select id="estado" class="form-input">
                        <option value="disponible"><?= t('camiones.opt_estado_disponible') ?></option>
                        <option value="en_ruta"><?= t('camiones.opt_estado_en_ruta') ?></option>
                        <option value="en_mantenimiento"><?= t('camiones.opt_estado_en_mantenimiento') ?></option>
                        <option value="de_baja"><?= t('camiones.opt_estado_de_baja') ?></option>
                        <option value="en_deposito"><?= t('camiones.opt_estado_en_deposito') ?></option>
                    </select>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="cerrarModal()"><?= t('camiones.btn_cancelar') ?></button>
                <button type="submit" class="btn-primary"><?= t('camiones.btn_guardar') ?></button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="modalDelete" style="display:none"
     onclick="onOverlayClick(event,'modalDelete')">
    <div class="modal modal-sm">
        <div class="modal-header">
            <h3><?= t('camiones.modal_delete_title') ?></h3>
            <button class="modal-close" onclick="cerrarModalDelete()">✕</button>
        </div>
        <p class="delete-msg">
            <?= t('camiones.delete_confirm_before') ?> <strong id="deleteNombre"></strong><?= t('camiones.delete_confirm_after') ?>
        </p>
        <div class="modal-actions">
            <button class="btn-secondary" onclick="cerrarModalDelete()"><?= t('camiones.btn_cancelar') ?></button>
            <button class="btn-danger" onclick="confirmarDelete()"><?= t('camiones.btn_eliminar') ?></button>
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
const API_BASE = '/recolix/API_Recoleccion';
const I18N = <?= json_encode(t_array([
    'msgIngresaId' => 'camiones.msg_ingresa_id',
    'msgCamionNoEncontrado' => 'camiones.msg_camion_no_encontrado',
    'msgErrorConectar' => 'camiones.msg_error_conectar',
    'msgSeleccionaFiltro' => 'camiones.msg_selecciona_filtro',
    'msgSeleccionaValorFiltro' => 'camiones.msg_selecciona_valor_filtro',
    'msgSeleccionaCampo' => 'camiones.msg_selecciona_campo',
    'msgIngresaValor' => 'camiones.msg_ingresa_valor',
    'msgErrorGuardar' => 'camiones.msg_error_guardar',
    'msgErrorModificar' => 'camiones.msg_error_modificar',
    'msgErrorEliminar' => 'camiones.msg_error_eliminar',
    'msgErrorVerificarDisp' => 'camiones.msg_error_verificar_disp',
    'msgCamionDadoBaja' => 'camiones.msg_camion_dado_baja',
    'msgDisponibleSi' => 'camiones.msg_disponible_si',
    'msgDisponibleNo' => 'camiones.msg_disponible_no',
    'msgMatFormato' => 'camiones.msg_mat_formato',
    'msgMatLetras' => 'camiones.msg_mat_letras',
    'msgMatNumeros' => 'camiones.msg_mat_numeros',
    'msgIngresaFechaInicio' => 'camiones.msg_ingresa_fecha_inicio',
    'msgIngresaFechaFin' => 'camiones.msg_ingresa_fecha_fin',
    'msgFechaFinAnterior' => 'camiones.msg_fecha_fin_anterior',
    'msgIngresaIdCamion' => 'camiones.msg_ingresa_id_camion',
    'msgMantRegistrado' => 'camiones.msg_mant_registrado',
    'msgMantProgramado' => 'camiones.msg_mant_programado',
    'sinDescripcion' => 'camiones.sin_descripcion',
    'thId' => 'camiones.th_id',
    'thMatricula' => 'camiones.th_matricula',
    'thTipocarga' => 'camiones.th_tipocarga',
    'thCapacidad' => 'camiones.th_capacidad',
    'thAnio' => 'camiones.th_anio',
    'thModelo' => 'camiones.th_modelo',
    'thEstado' => 'camiones.th_estado',
    'thAcciones' => 'camiones.th_acciones',
    'thHasta' => 'camiones.th_hasta',
    'thCamion' => 'camiones.th_camion',
    'thTipo' => 'camiones.th_tipo',
    'thDescripcion' => 'camiones.th_descripcion',
    'thFechaprog' => 'camiones.th_fechaprog',
    'thCosto' => 'camiones.th_costo',
    'modalAddTitle' => 'camiones.modal_add_title',
    'modalEditTitle' => 'camiones.modal_edit_title',
    'valDisponible' => 'camiones.val_disponible',
    'valEnRuta' => 'camiones.val_en_ruta',
    'valEnMantenimiento' => 'camiones.val_en_mantenimiento',
    'valDeBaja' => 'camiones.val_de_baja',
    'valEnDeposito' => 'camiones.val_en_deposito',
    'valCargaLateral' => 'camiones.val_carga_lateral',
    'valCargaTrasera' => 'camiones.val_carga_trasera',
    'valPuertaAPuerta' => 'camiones.val_puerta_a_puerta',
    'valPreventivo' => 'camiones.val_preventivo',
    'valCorrectivo' => 'camiones.val_correctivo',
    'filtroTituloEstado' => 'camiones.filtro_titulo_estado',
    'filtroTituloTipo' => 'camiones.filtro_titulo_tipo',
    'filtroTituloDisponibles' => 'camiones.filtro_titulo_disponibles',
    'filtroTituloEnmantenimiento' => 'camiones.filtro_titulo_enmantenimiento',
    'filtroTituloMantprox' => 'camiones.filtro_titulo_mantprox',
    'filtroTituloAnio' => 'camiones.filtro_titulo_anio',
    'filtroTituloModelo' => 'camiones.filtro_titulo_modelo',
    'filtroTituloCapacidad' => 'camiones.filtro_titulo_capacidad',
    'filtroTituloCapacidadSuf' => 'camiones.filtro_titulo_capacidad_suf',
    'filtroTituloPrefijo' => 'camiones.filtro_titulo_prefijo',
    'tableTitleTodos' => 'camiones.table_title_todos',
    'tableTitleCamionId' => 'camiones.table_title_camion_id',
    'resultadoSingular' => 'camiones.resultado_singular',
    'resultadoPlural' => 'camiones.resultado_plural',
    'filtroPhAnio' => 'camiones.filtro_ph_anio',
    'filtroPhModelo' => 'camiones.filtro_ph_modelo',
    'filtroPhCapacidad' => 'camiones.filtro_ph_capacidad',
    'fieldMatriculaPh' => 'camiones.field_matricula_placeholder',
    'valorPhSeleccionaCampo' => 'camiones.valor_placeholder_seleccionacampo',
    'filtroValorPh' => 'camiones.filtro_valor_placeholder',
    'btnModificar' => 'camiones.btn_modificar',
    'btnEliminar' => 'camiones.btn_eliminar',
])) ?>;
const I18N_LOCALE = <?= json_encode(t_locale()) ?>;
let camionIdParaEliminar = null;
let toastTimeout;
let reglas = {};
let vistaActual = { url: '', render: 'tabla' };
let firmaTabla = '';

document.addEventListener('DOMContentLoaded', async () => {
    await cargarReglas();
    listarCamiones();
    setInterval(refrescarVistaActual, 30000);
});

function reglasFallback() {
    return {
        matricula: { pattern: '^[A-Z]{3}\\d{4}$', max: 7, label: 'Matrícula', mensaje: 'Debe tener 3 letras seguidas de 4 números (ej: ABC1234)' },
        tipoCarga: { valores: ['carga_lateral', 'carga_trasera', 'puerta_a_puerta'], max: 50, label: 'Tipo de carga' },
        capacidad: { tipo: 'entero', min: 1, max: 100000, label: 'Capacidad' },
        anio: { tipo: 'entero', min: 1990, max: 2030, label: 'Año' },
        modelo: { max: 100, label: 'Modelo' },
        estado: { valores: ['disponible', 'en_ruta', 'en_mantenimiento', 'de_baja', 'en_deposito'], max: 50, label: 'Estado' }
    };
}

async function cargarReglas() {
    try {
        const res = await fetch(`${API_BASE}/camiones/reglasValidacion`);
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

async function listarCamiones() {
    ocultarControles();
    setLoading(true);
    vistaActual = { url: `${API_BASE}/camiones`, render: 'tabla' };
    try {
        const res  = await fetch(`${API_BASE}/camiones`);
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
    vistaActual = { url: `${API_BASE}/camiones/${id}`, render: 'tabla' };
    try {
        const res  = await fetch(`${API_BASE}/camiones/${id}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            renderTabla([json.data]);
            setTableTitle(`${I18N.tableTitleCamionId}${id}`);
        } else {
            renderTabla([]);
            mostrarToast(json.message || I18N.msgCamionNoEncontrado, 'error');
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

    const sinValor = ['disponibilidad', 'mantenimiento'];
    if (sinValor.includes(tipo) || !tipo) return;

    const listas = {
        estado:    ['disponible', 'en_ruta', 'en_mantenimiento', 'de_baja', 'en_deposito'],
        tipoCarga: ['carga_lateral', 'carga_trasera', 'puerta_a_puerta'],
        proximosMantenimientos: ['preventivo', 'correctivo']
    };

    if (listas[tipo]) {
        select.innerHTML = '<option value="">— Seleccionar —</option>' +
            listas[tipo].map(v => `<option value="${v}">${valorFormateado(v)}</option>`).join('');
        select.style.display = '';
    } else {
        const placeholders = { anio: I18N.filtroPhAnio, modelo: I18N.filtroPhModelo, capacidad: I18N.filtroPhCapacidad };
        input.placeholder = placeholders[tipo] || I18N.filtroValorPh;
        input.style.display = '';
    }
}

async function aplicarFiltro() {
    const tipo   = document.getElementById('filtroTipo').value;
    const input  = document.getElementById('filtroValor');
    const select = document.getElementById('filtroValorSelect');
    if (!tipo) { mostrarToast(I18N.msgSeleccionaFiltro, 'warning'); return; }

    const sinValor = ['disponibilidad', 'mantenimiento'];
    const valor = sinValor.includes(tipo) ? '' :
        (select.style.display !== 'none' ? select.value : input.value).trim();

    if (!sinValor.includes(tipo) && !valor) {
        mostrarToast(I18N.msgSeleccionaValorFiltro, 'warning');
        return;
    }

    const accionMap = { disponibilidad: 'filtrarDisponibilidad', mantenimiento: 'filtrarMantenimiento' };
    const url = tipo === 'proximosMantenimientos'
        ? `${API_BASE}/mantenimientos/proximos?tipo=${encodeURIComponent(valor)}`
        : sinValor.includes(tipo)
            ? `${API_BASE}/camiones/${accionMap[tipo]}`
            : `${API_BASE}/camiones?${tipo}=${encodeURIComponent(valor)}`;
    vistaActual = {
        url,
        render: tipo === 'proximosMantenimientos' ? 'proximos'
            : tipo === 'mantenimiento' ? 'mantenimiento' : 'tabla'
    };
    setLoading(true);
    try {
        const res  = await fetch(url);
        const json = await res.json();
        if (json.status === 'success') {
            if (tipo === 'proximosMantenimientos') {
                renderTablaMantenimientos(json.data);
            } else if (tipo === 'mantenimiento') {
                renderTablaEnMantenimiento(json.data);
            } else {
                renderTabla(json.data);
            }
            const labels = {
                estado: `${I18N.filtroTituloEstado} ${valorFormateado(valor)}`, tipoCarga: `${I18N.filtroTituloTipo} ${valorFormateado(valor)}`,
                disponibilidad: I18N.filtroTituloDisponibles, mantenimiento: I18N.filtroTituloEnmantenimiento,
                proximosMantenimientos: `${I18N.filtroTituloMantprox} ${valorFormateado(valor)}`,
                anio: `${I18N.filtroTituloAnio} ${valor}`, modelo: `${I18N.filtroTituloModelo} ${valor}`,
                capacidad: `${I18N.filtroTituloCapacidad} ${valor} ${I18N.filtroTituloCapacidadSuf}`
            };
            setTableTitle(`${I18N.filtroTituloPrefijo} ${labels[tipo] || tipo}`);
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorConectar, 'error');
    } finally {
        setLoading(false);
    }
}

function abrirModalAgregar() {
    document.getElementById('modalTitle').textContent = I18N.modalAddTitle;
    document.getElementById('camionId').value = '';
    document.getElementById('camionForm').reset();
    document.getElementById('modalForm').style.display = 'flex';
}

function abrirModalEditar(camion) {
    document.getElementById('modalTitle').textContent = I18N.modalEditTitle;
    document.getElementById('camionId').value  = camion.id_camion;
    document.getElementById('matricula').value = camion.matricula;
    document.getElementById('capacidad').value = camion.capacidad;
    document.getElementById('anio').value      = camion.anio;
    document.getElementById('modelo').value    = camion.modelo;

    const tipoCargaEl = document.getElementById('tipoCarga');
    tipoCargaEl.value = camion.tipo_carga;
    if (!tipoCargaEl.value) tipoCargaEl.selectedIndex = 0;

    const estadoEl = document.getElementById('estado');
    estadoEl.value = camion.estado;
    if (!estadoEl.value) estadoEl.selectedIndex = 0;

    document.getElementById('modalForm').style.display = 'flex';
}

async function guardarCamion(e) {
    e.preventDefault();
    const id = document.getElementById('camionId').value;

    const matInput = document.getElementById('matricula');
    const matricula = matInput.value.toUpperCase();
    matInput.setCustomValidity('');
    if (matricula.length !== 7) {
        matInput.setCustomValidity(I18N.msgMatFormato);
        matInput.reportValidity();
        return;
    }
    for (let i = 0; i < 3; i++) {
        if (matricula[i] < 'A' || matricula[i] > 'Z') {
            matInput.setCustomValidity(I18N.msgMatLetras);
            matInput.reportValidity();
            return;
        }
    }
    for (let i = 3; i < 7; i++) {
        if (matricula[i] < '0' || matricula[i] > '9') {
            matInput.setCustomValidity(I18N.msgMatNumeros);
            matInput.reportValidity();
            return;
        }
    }

    const campos = {
        matricula,
        tipoCarga: document.getElementById('tipoCarga').value,
        capacidad: document.getElementById('capacidad').value,
        anio:      document.getElementById('anio').value,
        modelo:    document.getElementById('modelo').value,
        estado:    document.getElementById('estado').value
    };

    for (const [campo, valor] of Object.entries(campos)) {
        const regla = reglas[campo];
        if (!regla) continue;
        if (regla.pattern) {
            const re = new RegExp(regla.pattern);
            if (!re.test(valor)) {
                mostrarToast(regla.mensaje || `${regla.label} no válido`, 'warning');
                return;
            }
        }
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
            if (regla.max !== undefined && num > regla.max) {
                mostrarToast(`${regla.label} no puede ser mayor que ${regla.max}`, 'warning');
                return;
            }
        }
    }

    const body = {
        matricula: matricula.toUpperCase(),
        tipoCarga: document.getElementById('tipoCarga').value,
        capacidad: parseInt(document.getElementById('capacidad').value),
        anio:      parseInt(document.getElementById('anio').value),
        modelo:    document.getElementById('modelo').value,
        estado:    document.getElementById('estado').value
    };

    try {
        const res = await fetch(
            id ? `${API_BASE}/camiones/${id}` : `${API_BASE}/camiones`,
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
            listarCamiones();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorGuardar, 'error');
    }
}

function confirmarBorrar(id, matricula) {
    camionIdParaEliminar = id;
    document.getElementById('deleteNombre').textContent = matricula;
    document.getElementById('modalDelete').style.display = 'flex';
}

async function confirmarDelete() {
    try {
        const res  = await fetch(`${API_BASE}/camiones/${camionIdParaEliminar}`, { method: 'DELETE' });
        const json = await res.json();
        if (json.status === 'success') {
            mostrarToast(I18N.msgCamionDadoBaja, 'success');
            cerrarModalDelete();
            listarCamiones();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorEliminar, 'error');
    }
}

async function verificarDisponibilidad(id, matricula) {
    try {
        const res  = await fetch(`${API_BASE}/camiones/${id}/esDisponible`);
        const json = await res.json();
        if (json.status === 'success') {
            mostrarToast(
                (json.data ? I18N.msgDisponibleSi : I18N.msgDisponibleNo).replace('{m}', matricula),
                json.data ? 'success' : 'warning'
            );
        } else {
            mostrarToast(json.message || I18N.msgErrorVerificarDisp, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorVerificarDisp, 'error');
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

    const listas = {
        tipoCarga: ['carga_lateral', 'carga_trasera', 'puerta_a_puerta'],
        estado:    ['disponible', 'en_ruta', 'en_mantenimiento', 'de_baja', 'en_deposito']
    };

    if (listas[campo]) {
        select.innerHTML = listas[campo].map((v, i) =>
            `<option value="${v}"${i === 0 ? ' selected' : ''}>${valorFormateado(v)}</option>`
        ).join('');
        select.style.display = '';
    } else {
        const placeholders = {
            matricula: I18N.fieldMatriculaPh,
            capacidad: I18N.filtroPhCapacidad,
            anio:      I18N.filtroPhAnio,
            modelo:    I18N.filtroPhModelo
        };
        const maxlengths = {
            matricula: 7,
            capacidad: 6,
            anio:      4,
            modelo:    100
        };
        input.placeholder = placeholders[campo] || I18N.valorPhSeleccionaCampo;
        input.maxLength = maxlengths[campo] || '';
        input.style.display = '';
    }
}

async function modificarCamion() {
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
        const valorFinal = campo === 'matricula' ? valor.toUpperCase() : valor;
        if (regla.pattern) {
            const re = new RegExp(regla.pattern);
            if (!re.test(valorFinal)) {
                mostrarToast(regla.mensaje || `${regla.label} no válido`, 'warning');
                return;
            }
        }
        if (regla.valores && !regla.valores.includes(valorFinal)) {
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
            if (regla.max !== undefined && num > regla.max) {
                mostrarToast(`${regla.label} no puede ser mayor que ${regla.max}`, 'warning');
                return;
            }
        }
    }

    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/camiones/${id}`);
        const json = await res.json();
        if (json.status !== 'success' || !json.data) {
            mostrarToast(json.message || I18N.msgCamionNoEncontrado, 'error');
            return;
        }
        const camion = json.data;

        const campoMap = {
            matricula: 'matricula',
            tipoCarga: 'tipo_carga',
            capacidad: 'capacidad',
            anio:      'anio',
            modelo:    'modelo',
            estado:    'estado'
        };

        camion[campoMap[campo]] = campo === 'matricula' ? valor.toUpperCase() : valor;

        const putRes = await fetch(`${API_BASE}/camiones/${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                matricula: camion.matricula,
                tipoCarga: camion.tipo_carga,
                capacidad: parseInt(camion.capacidad),
                anio:      parseInt(camion.anio),
                modelo:    camion.modelo,
                estado:    camion.estado
            })
        });
        const putJson = await putRes.json();
        if (putJson.status === 'success') {
            mostrarToast(putJson.message, 'success');
            ocultarControles();
            listarCamiones();
        } else {
            mostrarToast(putJson.message, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorModificar, 'error');
    } finally {
        setLoading(false);
    }
}

async function darDeBajaCamion() {
    const id = document.getElementById('deleteId').value.trim();
    if (!id) { mostrarToast(I18N.msgIngresaId, 'warning'); return; }
    setLoading(true);
    try {
        const res  = await fetch(`${API_BASE}/camiones/${id}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            confirmarBorrar(json.data.id_camion, json.data.matricula);
            ocultarControles();
        } else {
            mostrarToast(json.message || I18N.msgCamionNoEncontrado, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorConectar, 'error');
    } finally {
        setLoading(false);
    }
}

function restaurarHeadersCamiones() {
    document.getElementById('tablaHeaders').innerHTML = `
        <th>${I18N.thId}</th>
        <th>${I18N.thMatricula}</th>
        <th>${I18N.thTipocarga}</th>
        <th>${I18N.thCapacidad}</th>
        <th>${I18N.thAnio}</th>
        <th>${I18N.thModelo}</th>
        <th>${I18N.thEstado}</th>
        <th>${I18N.thAcciones}</th>`;
}

function renderTabla(camiones) {
    const tbody = document.getElementById('camionesBody');
    const noRes = document.getElementById('noResultados');
    const count = document.getElementById('tableCount');

    restaurarHeadersCamiones();
    tbody.innerHTML = '';

    if (!camiones || camiones.length === 0) {
        noRes.style.display = 'flex';
        count.textContent   = '';
        return;
    }

    noRes.style.display = 'none';
    count.textContent   = `${camiones.length} ${camiones.length !== 1 ? I18N.resultadoPlural : I18N.resultadoSingular}`;

    camiones.forEach(c => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="td-id" data-label="${I18N.thId}">${c.id_camion}</td>
            <td data-label="${I18N.thMatricula}"><strong>${c.matricula}</strong></td>
            <td data-label="${I18N.thTipocarga}">${valorFormateado(c.tipo_carga)}</td>
            <td data-label="${I18N.thCapacidad}">${Number(c.capacidad).toLocaleString()}</td>
            <td data-label="${I18N.thAnio}">${c.anio}</td>
            <td data-label="${I18N.thModelo}">${c.modelo}</td>
            <td data-label="${I18N.thEstado}"><span class="badge badge-${badgeClass(c.estado)}">${valorFormateado(c.estado)}</span></td>
            <td class="td-actions" data-label="${I18N.thAcciones}">
                <button class="btn-action" onclick='abrirModalEditar(${JSON.stringify(c)})' title="${I18N.btnModificar}">✏️</button>
                <button class="btn-action" onclick="verificarDisponibilidad(${c.id_camion},'${c.matricula}')" title="${I18N.msgErrorVerificarDisp}">✅</button>
                <button class="btn-action" onclick="confirmarBorrar(${c.id_camion},'${c.matricula}')" title="${I18N.btnEliminar}">🗑️</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function formatearFecha(fechaStr) {
    if (!fechaStr) return '—';
    const d = new Date(fechaStr.replace(' ', 'T'));
    if (isNaN(d.getTime())) return fechaStr;
    return d.toLocaleString(I18N_LOCALE, { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function renderTablaEnMantenimiento(camiones) {
    const tbody = document.getElementById('camionesBody');
    const noRes = document.getElementById('noResultados');
    const count = document.getElementById('tableCount');

    document.getElementById('tablaHeaders').innerHTML = `
        <th>${I18N.thId}</th>
        <th>${I18N.thMatricula}</th>
        <th>${I18N.thTipocarga}</th>
        <th>${I18N.thCapacidad}</th>
        <th>${I18N.thAnio}</th>
        <th>${I18N.thModelo}</th>
        <th>${I18N.thHasta}</th>
        <th>${I18N.thAcciones}</th>`;

    tbody.innerHTML = '';

    if (!camiones || camiones.length === 0) {
        noRes.style.display = 'flex';
        count.textContent   = '';
        return;
    }

    noRes.style.display = 'none';
    count.textContent   = `${camiones.length} ${camiones.length !== 1 ? I18N.resultadoPlural : I18N.resultadoSingular}`;

    camiones.forEach(c => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="td-id" data-label="${I18N.thId}">${c.id_camion}</td>
            <td data-label="${I18N.thMatricula}"><strong>${c.matricula}</strong></td>
            <td data-label="${I18N.thTipocarga}">${valorFormateado(c.tipo_carga)}</td>
            <td data-label="${I18N.thCapacidad}">${Number(c.capacidad).toLocaleString()}</td>
            <td data-label="${I18N.thAnio}">${c.anio}</td>
            <td data-label="${I18N.thModelo}">${c.modelo}</td>
            <td data-label="${I18N.thHasta}"><span class="badge badge-warning">${formatearFecha(c.fecha_fin_mantenimiento)}</span></td>
            <td class="td-actions" data-label="${I18N.thAcciones}">
                <button class="btn-action" onclick='abrirModalEditar(${JSON.stringify(c)})' title="${I18N.btnModificar}">✏️</button>
                <button class="btn-action" onclick="verificarDisponibilidad(${c.id_camion},'${c.matricula}')" title="${I18N.msgErrorVerificarDisp}">✅</button>
                <button class="btn-action" onclick="confirmarBorrar(${c.id_camion},'${c.matricula}')" title="${I18N.btnEliminar}">🗑️</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function renderTablaMantenimientos(mantenimientos) {
    const tbody = document.getElementById('camionesBody');
    const noRes = document.getElementById('noResultados');
    const count = document.getElementById('tableCount');

    document.getElementById('tablaHeaders').innerHTML = `
        <th>${I18N.thId}</th>
        <th>${I18N.thCamion}</th>
        <th>${I18N.thTipo}</th>
        <th>${I18N.thDescripcion}</th>
        <th>${I18N.thFechaprog}</th>
        <th>${I18N.thCosto}</th>`;

    tbody.innerHTML = '';

    if (!mantenimientos || mantenimientos.length === 0) {
        noRes.style.display = 'flex';
        count.textContent   = '';
        return;
    }

    noRes.style.display = 'none';
    count.textContent   = `${mantenimientos.length} ${mantenimientos.length !== 1 ? I18N.resultadoPlural : I18N.resultadoSingular}`;

    mantenimientos.forEach(m => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="td-id" data-label="${I18N.thId}">${m.id_mantenimiento}</td>
            <td data-label="${I18N.thCamion}">${m.id_camion}</td>
            <td data-label="${I18N.thTipo}"><span class="badge badge-${m.tipo === 'correctivo' ? 'danger' : 'info'}">${valorFormateado(m.tipo)}</span></td>
            <td data-label="${I18N.thDescripcion}">${m.descripcion || '—'}</td>
            <td data-label="${I18N.thFechaprog}">${formatearFecha(m.fecha_inicio)}</td>
            <td data-label="${I18N.thCosto}">${m.costo != null ? '$ ' + Number(m.costo).toLocaleString() : '—'}</td>
        `;
        tbody.appendChild(tr);
    });
}

const VALORES_TRADUCIDOS = {
    disponible: 'valDisponible', en_ruta: 'valEnRuta', en_mantenimiento: 'valEnMantenimiento',
    de_baja: 'valDeBaja', en_deposito: 'valEnDeposito', carga_lateral: 'valCargaLateral',
    carga_trasera: 'valCargaTrasera', puerta_a_puerta: 'valPuertaAPuerta',
    preventivo: 'valPreventivo', correctivo: 'valCorrectivo'
};

function valorFormateado(v) {
    const clave = VALORES_TRADUCIDOS[v];
    return clave ? I18N[clave] : v.replace(/_/g, ' ');
}

function badgeClass(estado) {
    const map = { 'disponible': 'success', 'en_ruta': 'info', 'en_mantenimiento': 'warning', 'de_baja': 'danger', 'en_deposito': 'neutral' };
    return map[estado] ?? 'neutral';
}

function toggleFiltros() {
    const fb = document.getElementById('filterBar');
    const mostrar = fb.style.display === 'none';
    ocultarControles();
    if (mostrar) fb.style.display = 'block';
}

function toggleBusquedaId() {
    const sb = document.getElementById('searchBar');
    const mostrar = sb.style.display === 'none';
    ocultarControles();
    if (mostrar) sb.style.display = 'block';
}

function ocultarControles() {
    document.getElementById('filterBar').style.display = 'none';
    document.getElementById('searchBar').style.display = 'none';
    document.getElementById('modificarBar').style.display = 'none';
    document.getElementById('deleteBar').style.display = 'none';
    document.getElementById('mantenimientoRegistrarBar').style.display = 'none';
    document.getElementById('mantenimientoProgramarBar').style.display = 'none';
}

function toggleModificar() {
    const mb = document.getElementById('modificarBar');
    const mostrar = mb.style.display === 'none';
    ocultarControles();
    if (mostrar) mb.style.display = 'block';
}

function toggleDarDeBaja() {
    const db = document.getElementById('deleteBar');
    const mostrar = db.style.display === 'none';
    ocultarControles();
    if (mostrar) db.style.display = 'block';
}

function ahoraLocal() {
    const d = new Date();
    d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
    return d.toISOString().slice(0, 16);
}

function toggleMantenimientoRegistrar() {
    const bar = document.getElementById('mantenimientoRegistrarBar');
    const mostrar = bar.style.display === 'none';
    ocultarControles();
    if (mostrar) {
        document.getElementById('regMantFecha').value = ahoraLocal();
        document.getElementById('regMantFechaFin').value = '';
        document.getElementById('regMantId').value = '';
        document.getElementById('regMantDescripcion').value = '';
        document.getElementById('regMantCosto').value = '';
        document.getElementById('regMantTipo').value = 'preventivo';
        bar.style.display = 'block';
    }
}

function toggleMantenimientoProgramar() {
    const bar = document.getElementById('mantenimientoProgramarBar');
    const mostrar = bar.style.display === 'none';
    ocultarControles();
    if (mostrar) {
        document.getElementById('progMantFecha').value = '';
        document.getElementById('progMantFechaFin').value = '';
        document.getElementById('progMantId').value = '';
        document.getElementById('progMantDescripcion').value = '';
        document.getElementById('progMantTipo').value = 'preventivo';
        bar.style.display = 'block';
    }
}

async function registrarMantenimiento() {
    const id = document.getElementById('regMantId').value.trim();
    const tipo = document.getElementById('regMantTipo').value;
    const fechaInicio = document.getElementById('regMantFecha').value;
    const fechaFin = document.getElementById('regMantFechaFin').value;
    const descripcion = document.getElementById('regMantDescripcion').value.trim();
    const costo = document.getElementById('regMantCosto').value.trim();

    if (!id) { mostrarToast(I18N.msgIngresaIdCamion, 'warning'); return; }
    if (!fechaInicio) { mostrarToast(I18N.msgIngresaFechaInicio, 'warning'); return; }
    if (!fechaFin) { mostrarToast(I18N.msgIngresaFechaFin, 'warning'); return; }
    if (fechaFin < fechaInicio) { mostrarToast(I18N.msgFechaFinAnterior, 'warning'); return; }

    setLoading(true);
    try {
        const res = await fetch(`${API_BASE}/mantenimientos`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                camionId: parseInt(id),
                tipo,
                descripcion: descripcion || I18N.sinDescripcion,
                fechaInicio: fechaInicio.replace('T', ' '),
                fechaFin: fechaFin.replace('T', ' '),
                costo: costo ? parseFloat(costo) : 0
            })
        });
        const json = await res.json();
        if (json.status === 'success') {
            mostrarToast(I18N.msgMantRegistrado, 'success');
            listarCamiones();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorConectar, 'error');
    } finally {
        setLoading(false);
    }
}

async function programarMantenimiento() {
    const id = document.getElementById('progMantId').value.trim();
    const tipo = document.getElementById('progMantTipo').value;
    const fechaInicio = document.getElementById('progMantFecha').value;
    const fechaFin = document.getElementById('progMantFechaFin').value;
    const descripcion = document.getElementById('progMantDescripcion').value.trim();

    if (!id) { mostrarToast(I18N.msgIngresaIdCamion, 'warning'); return; }
    if (!fechaInicio) { mostrarToast(I18N.msgIngresaFechaInicio, 'warning'); return; }
    if (!fechaFin) { mostrarToast(I18N.msgIngresaFechaFin, 'warning'); return; }
    if (fechaFin < fechaInicio) { mostrarToast(I18N.msgFechaFinAnterior, 'warning'); return; }

    setLoading(true);
    try {
        const res = await fetch(`${API_BASE}/mantenimientos/programar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                camionId: parseInt(id),
                tipo,
                descripcion: descripcion || I18N.sinDescripcion,
                fechaInicio: fechaInicio.replace('T', ' '),
                fechaFin: fechaFin.replace('T', ' ')
            })
        });
        const json = await res.json();
        if (json.status === 'success') {
            mostrarToast(I18N.msgMantProgramado, 'success');
            listarCamiones();
        } else {
            mostrarToast(json.message, 'error');
        }
    } catch {
        mostrarToast(I18N.msgErrorConectar, 'error');
    } finally {
        setLoading(false);
    }
}

function cerrarModal() {
    document.getElementById('modalForm').style.display = 'none';
}

function cerrarModalDelete() {
    document.getElementById('modalDelete').style.display = 'none';
    camionIdParaEliminar = null;
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
    document.getElementById('camionesBody').style.opacity = show ? '0.3' : '1';
}

async function refrescarVistaActual() {
    if (document.hidden || !vistaActual.url) return;
    try {
        const res = await fetch(vistaActual.url);
        const json = await res.json();
        if (json.status !== 'success') return;
        const firma = JSON.stringify(json.data);
        if (firma === firmaTabla) return;
        firmaTabla = firma;
        if (vistaActual.render === 'tabla') {
            renderTabla(json.data);
        } else if (vistaActual.render === 'mantenimiento') {
            renderTablaEnMantenimiento(json.data);
        } else if (vistaActual.render === 'proximos') {
            renderTablaMantenimientos(json.data);
        }
    } catch {}
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
