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
    <title><?= t('contenedores.title') ?></title>
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
                    <a href="contenedores.php" class="nav-active"><?= t('backoffice.nav_contenedores') ?></a>
                    <a href="centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
                    <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
                    <a href="reportes.php"><?= t('backoffice.nav_reportes') ?></a>
                    <a href="incidencias.php"><?= t('backoffice.nav_incidencias') ?></a>
                    <a href="../../API_Usuarios/Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
                </nav>
            </div>
            <div class="nav-right">
                <?php include __DIR__ . '/../../i18n/switcher.php'; ?>
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/recolix/Comun/campana.php'; ?>
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
            <a href="contenedores.php" class="nav-active"><?= t('backoffice.nav_contenedores') ?></a>
            <a href="centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
            <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
            <a href="reportes.php"><?= t('backoffice.nav_reportes') ?></a>
            <a href="incidencias.php"><?= t('backoffice.nav_incidencias') ?></a>
            <a href="../../API_Usuarios/Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
        </nav>
        <div class="nav-hr"></div>
    </header>

    <section class="hero">
        <h1 class="hero-title"><?= t('contenedores.hero_title') ?></h1>
        <p class="hero-sub"><?= t('contenedores.hero_sub') ?></p>
    </section>

    <section class="cards-section">
        <div class="cards-grid">

            <div class="card-operar" onclick="abrirModalAgregar()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/plus.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('contenedores.card_agregar_titulo') ?></h3>
                    <p><?= t('contenedores.card_agregar_desc') ?></p>
                    <button class="card-btn"><?= t('crud.btn_agregar') ?></button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleFiltros()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/loupe.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('crud.btn_filtrar') ?></h3>
                    <p><?= t('contenedores.card_filtrar_desc') ?></p>
                    <button class="card-btn"><?= t('crud.btn_filtrar') ?></button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleBusquedaId()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/question.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('camiones.card_buscarid_titulo') ?></h3>
                    <p><?= t('contenedores.card_buscarid_desc') ?></p>
                    <button class="card-btn"><?= t('crud.btn_buscar') ?></button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleModificar()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/refresh.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('contenedores.card_modificar_titulo') ?></h3>
                    <p><?= t('contenedores.card_modificar_desc') ?></p>
                    <button class="card-btn"><?= t('crud.btn_modificar') ?></button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleDarDeBaja()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/minus.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('camiones.card_baja_titulo') ?></h3>
                    <p><?= t('contenedores.card_baja_desc') ?></p>
                    <button class="card-btn"><?= t('camiones.card_baja_btn') ?></button>
                </div>
            </div>

        </div>
    </section>

    <section class="control-bar" id="filterBar" style="display:none">
        <div class="control-inner">
            <select id="filtroTipo" class="ctrl-select" onchange="actualizarFiltroValor()">
                <option value=""><?= t('crud.filtrar_por') ?></option>
                <option value="municipio"><?= t('contenedores.filtro_opt_municipio') ?></option>
                <option value="tipoResiduo"><?= t('contenedores.filtro_opt_tiporesiduo') ?></option>
                <option value="estado"><?= t('contenedores.filtro_opt_estado') ?></option>
            </select>
            <input type="text" id="filtroValor" class="ctrl-input" placeholder="<?= htmlspecialchars(t('crud.valor_filtro_placeholder')) ?>"
                style="display:none">
            <select id="filtroValorSelect" class="ctrl-select" style="display:none"></select>
            <button class="btn-primary" onclick="aplicarFiltro()"><?= t('crud.btn_aplicar') ?></button>
            <button class="btn-secondary" onclick="listarContenedores()"><?= t('crud.btn_limpiar') ?></button>
        </div>
    </section>

    <section class="control-bar" id="searchBar" style="display:none">
        <div class="control-inner">
            <input type="number" id="buscarId" class="ctrl-input" placeholder="<?= htmlspecialchars(t('contenedores.searchid_ph')) ?>" min="1"
                style="max-width:200px">
            <button class="btn-primary" onclick="buscarPorId()"><?= t('crud.btn_buscar') ?></button>
            <button class="btn-secondary" onclick="listarContenedores()"><?= t('crud.btn_vertodos') ?></button>
        </div>
    </section>

    <section class="control-bar" id="modificarBar" style="display:none">
        <div class="control-inner">
            <input type="number" id="modificarId" class="ctrl-input" placeholder="<?= htmlspecialchars(t('contenedores.modificarid_ph')) ?>" min="1"
                style="max-width:140px">
            <select id="modificarCampo" class="ctrl-select" onchange="actualizarPlaceholderModificar()">
                <option value=""><?= t('crud.campo') ?></option>
                <option value="calle"><?= t('contenedores.campo_opt_calle') ?></option>
                <option value="numCalle"><?= t('contenedores.campo_opt_numcalle') ?></option>
                <option value="capacidad"><?= t('contenedores.campo_opt_capacidad') ?></option>
                <option value="estado"><?= t('contenedores.campo_opt_estado') ?></option>
                <option value="idTipo"><?= t('contenedores.campo_opt_idtipo') ?></option>
                <option value="idCircuito"><?= t('contenedores.campo_opt_idcircuito') ?></option>
            </select>
            <input type="text" id="modificarValor" class="ctrl-input" style="display:none"
                placeholder="<?= htmlspecialchars(t('crud.seleccionar_campo_placeholder')) ?>">
            <select id="modificarValorSelect" class="ctrl-select" style="display:none"></select>
            <button class="btn-primary" onclick="modificarContenedor()"><?= t('crud.btn_modificar') ?></button>
            <button class="btn-secondary" onclick="ocultarControles()"><?= t('crud.btn_cancelar') ?></button>
        </div>
    </section>

    <section class="control-bar" id="deleteBar" style="display:none">
        <div class="control-inner">
            <input type="number" id="deleteId" class="ctrl-input" placeholder="<?= htmlspecialchars(t('contenedores.deleteid_ph')) ?>"
                min="1" style="max-width:280px">
            <button class="btn-danger" onclick="darDeBajaContenedor()"><?= t('camiones.card_baja_btn') ?></button>
            <button class="btn-secondary" onclick="ocultarControles()"><?= t('crud.btn_cancelar') ?></button>
        </div>
    </section>

    <section class="table-section">
        <div class="table-header">
            <h2 class="table-title" id="tableTitle"><?= t('contenedores.hero_title') ?></h2>
            <span class="table-count" id="tableCount"></span>
            <button class="btn-secondary" id="btnMostrarTodos" style="display:none" onclick="listarContenedores()"><?= t('contenedores.btn_mostrar_todos') ?></button>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><?= t('crud.th_id') ?></th>
                        <th><?= t('contenedores.th_calle') ?></th>
                        <th><?= t('contenedores.th_numpuerta') ?></th>
                        <th><?= t('contenedores.th_capacidad') ?></th>
                        <th><?= t('contenedores.th_tiporesiduo') ?></th>
                        <th><?= t('contenedores.th_circuito') ?></th>
                        <th><?= t('contenedores.th_estado') ?></th>
                        <th><?= t('crud.th_acciones') ?></th>
                    </tr>
                </thead>
                <tbody id="contenedoresBody"></tbody>
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
                <h3 id="modalTitle"><?= t('contenedores.modal_add_title') ?></h3>
                <button class="modal-close" onclick="cerrarModal()">✕</button>
            </div>
            <form id="contenedorForm" onsubmit="guardarContenedor(event)">
                <input type="hidden" id="contenedorId">
                <input type="hidden" id="contenedorEstadoForm" value="funcional">
                <div class="form-row">
                    <div class="form-group">
                        <label for="calle"><?= t('contenedores.field_calle') ?></label>
                        <input type="text" id="calle" class="form-input" placeholder="<?= htmlspecialchars(t('contenedores.field_calle_ph')) ?>" maxlength="100"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="numCalle"><?= t('contenedores.field_numcalle') ?></label>
                        <input type="text" id="numCalle" class="form-input" placeholder="<?= htmlspecialchars(t('contenedores.field_numcalle_ph')) ?>" maxlength="20" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="latitud"><?= t('contenedores.field_latitud') ?></label>
                        <input type="number" id="latitud" class="form-input" placeholder="-34.9011" step="any" required>
                    </div>
                    <div class="form-group">
                        <label for="longitud"><?= t('contenedores.field_longitud') ?></label>
                        <input type="number" id="longitud" class="form-input" placeholder="-56.1645" step="any"
                            required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="capacidad"><?= t('contenedores.field_capacidad_litros') ?></label>
                        <input type="number" id="capacidad" class="form-input" placeholder="<?= htmlspecialchars(t('contenedores.field_capacidad_ph')) ?>" min="1" step="any"
                            required>
                    </div>
                    <div class="form-group" id="estadoGroup">
                        <label for="estado"><?= t('contenedores.field_estado') ?></label>
                        <select id="estado" class="form-input" onchange="onEstadoFormChange()">
                            <option value="funcional"><?= t('contenedores.opt_funcional') ?></option>
                            <option value="roto"><?= t('contenedores.opt_roto') ?></option>
                            <option value="desbordado"><?= t('contenedores.opt_desbordado') ?></option>
                            <option value="inactivo"><?= t('contenedores.opt_inactivo') ?></option>
                            <option value="en_deposito"><?= t('contenedores.opt_en_deposito') ?></option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="idTipo"><?= t('contenedores.field_tiporesiduo') ?></label>
                        <select id="idTipo" class="form-input" required>
                            <option value=""><?= t('crud.seleccionar') ?></option>
                        </select>
                    </div>
                    <div class="form-group" id="idCircuitoGroup">
                        <label for="idCircuito"><?= t('contenedores.field_idcircuito') ?></label>
                        <input type="number" id="idCircuito" class="form-input" placeholder="1" min="1" required>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="cerrarModal()"><?= t('crud.btn_cancelar') ?></button>
                    <button type="submit" class="btn-primary"><?= t('crud.btn_guardar') ?></button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalReparar" style="display:none">
        <div class="modal modal-sm">
            <div class="modal-header">
                <h3><?= t('contenedores.reparacion_titulo') ?></h3>
                <button class="modal-close" onclick="cerrarModalReparar()">✕</button>
            </div>
            <p class="delete-msg" id="reparacionMensaje"></p>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="cerrarModalReparar()"><?= t('crud.btn_cancelar') ?></button>
                <button class="btn-primary" onclick="confirmarReparar()"><?= t('contenedores.reparacion_confirmar') ?></button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalHistorial" style="display:none">
        <div class="modal">
            <div class="modal-header">
                <h3><?= t('contenedores.historial_titulo') ?> <span id="historialTitulo"></span></h3>
                <button class="modal-close" onclick="cerrarModalHistorial()">✕</button>
            </div>
            <div id="historialLista" class="historial-lista"></div>
        </div>
    </div>

    <div class="modal-overlay" id="modalDelete" style="display:none" onclick="onOverlayClick(event,'modalDelete')">
        <div class="modal modal-sm">
            <div class="modal-header">
                <h3><?= t('contenedores.modal_delete_title') ?></h3>
                <button class="modal-close" onclick="cerrarModalDelete()">✕</button>
            </div>
            <p class="delete-msg">
                <?= t('contenedores.delete_confirm_before') ?> <strong id="deleteNombre"></strong><?= t('contenedores.delete_confirm_after') ?>
            </p>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="cerrarModalDelete()"><?= t('crud.btn_cancelar') ?></button>
                <button class="btn-danger" onclick="confirmarDelete()"><?= t('camiones.card_baja_btn') ?></button>
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
        const I18N = <?= json_encode(t_array([
            'msgIngresaId' => 'crud.msg_ingresa_id',
            'msgErrorConectar' => 'crud.msg_error_conectar',
            'msgSeleccionaFiltro' => 'crud.msg_selecciona_filtro',
            'msgSeleccionaValorFiltro' => 'crud.msg_selecciona_valor_filtro',
            'msgSeleccionaCampo' => 'crud.msg_selecciona_campo',
            'msgIngresaValor' => 'crud.msg_ingresa_valor',
            'msgErrorGuardar' => 'crud.msg_error_guardar',
            'msgErrorModificar' => 'crud.msg_error_modificar',
            'resultadoSingular' => 'crud.resultado_singular',
            'resultadoPlural' => 'crud.resultado_plural',
            'filtroPrefijo' => 'crud.filtro_prefijo',
            'thId' => 'crud.th_id',
            'thAcciones' => 'crud.th_acciones',
            'thCalle' => 'contenedores.th_calle',
            'thNumpuerta' => 'contenedores.th_numpuerta',
            'thCapacidad' => 'contenedores.th_capacidad',
            'thTiporesiduo' => 'contenedores.th_tiporesiduo',
            'thCircuito' => 'contenedores.th_circuito',
            'thEstado' => 'contenedores.th_estado',
            'modalAddTitle' => 'contenedores.modal_add_title',
            'modalEditTitle' => 'contenedores.modal_edit_title',
            'filtroOptCritico' => 'contenedores.filtro_opt_critico',
            'filtroOptMunicipio' => 'contenedores.filtro_opt_municipio',
            'filtroOptTiporesiduo' => 'contenedores.filtro_opt_tiporesiduo',
            'filtroOptEstado' => 'contenedores.filtro_opt_estado',
            'filtroPhCalle' => 'contenedores.filtro_ph_calle',
            'filtroPhNumcalle' => 'contenedores.filtro_ph_numcalle',
            'filtroPhCapacidad' => 'contenedores.filtro_ph_capacidad',
            'phIdTiporesiduo' => 'contenedores.ph_id_tiporesiduo',
            'phIdCircuito' => 'contenedores.ph_id_circuito',
            'phNuevoValor' => 'contenedores.ph_nuevo_valor',
            'msgContenedorNoEncontrado' => 'contenedores.msg_contenedor_no_encontrado',
            'msgErrorActualizarEstado' => 'contenedores.msg_error_actualizar_estado',
            'msgEstadoActualizado' => 'contenedores.msg_estado_actualizado',
            'msgContenedorDadoBaja' => 'contenedores.msg_contenedor_dado_baja',
            'msgErrorDarBaja' => 'contenedores.msg_error_dar_baja',
            'tableTitleTodos' => 'contenedores.table_title_todos',
            'tableTitleCriticos' => 'contenedores.table_title_criticos',
            'tableTitleContenedorId' => 'contenedores.table_title_contenedor_id',
            'optFuncional' => 'contenedores.opt_funcional',
            'optRoto' => 'contenedores.opt_roto',
            'optDesbordado' => 'contenedores.opt_desbordado',
            'optInactivo' => 'contenedores.opt_inactivo',
            'optEnDeposito' => 'contenedores.opt_en_deposito',
            'seleccionarCampoPh' => 'crud.seleccionar_campo_placeholder',
            'crudTitleEditar' => 'crud.title_editar',
            'modalDeleteTitle' => 'contenedores.modal_delete_title',
            'repararTitle' => 'contenedores.reparar_btn_title',
            'sinDireccion' => 'contenedores.sin_direccion',
            'historialTitle' => 'contenedores.historial_btn_title',
            'reparacionMensaje' => 'contenedores.reparacion_mensaje',
            'msgReparacionOk' => 'contenedores.msg_reparacion_ok',
            'historialVacio' => 'contenedores.historial_vacio',
            'eventoIncidencia' => 'contenedores.evento_incidencia',
            'eventoLevante' => 'contenedores.evento_levante',
            'eventoReparacion' => 'contenedores.evento_reparacion',
        ])) ?>;
        let contenedorIdParaEliminar = null;
        let toastTimeout;
        let reglas = {};
        let contenedoresActuales = [];
        let vistaActual = 'todos';
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
        const TIPOS_RESIDUO = [
            { id: 1, descripcion: 'Residuos mezclados', color: 'Gris' },
            { id: 2, descripcion: 'Materiales reciclables', color: 'Verde' },
            { id: 3, descripcion: 'Residuos compostables', color: 'Marrón' },
            { id: 4, descripcion: 'Plástico', color: 'Amarillo' },
            { id: 5, descripcion: 'Papel y cartón', color: 'Azul' },
            { id: 6, descripcion: 'Metales', color: 'Negro' },
            { id: 7, descripcion: 'Vidrio', color: 'Blanco' }
        ];

        let tiposResiduo = [];

        async function cargarTiposResiduo() {
            try {
                const res = await fetch(`${API_BASE}/tipos-residuo`);
                const json = await res.json();
                if (json.status === 'success') {
                    tiposResiduo = json.data;
                }
            } catch {
                tiposResiduo = [];
            }
        }

        function fuenteTipos() {
            if (tiposResiduo.length) {
                return tiposResiduo;
            }
            return TIPOS_RESIDUO;
        }

        function poblarSelectTipoResiduo() {
            const select = document.getElementById('idTipo');
            select.innerHTML = '<option value="">— Seleccionar —</option>' +
                fuenteTipos().map(t =>
                    `<option value="${t.id_tipo ?? t.id}">${t.descripcion} (${t.color})</option>`
                ).join('');
        }

        document.addEventListener('DOMContentLoaded', async () => {
            await cargarReglas();
            await cargarTiposResiduo();
            poblarSelectTipoResiduo();
            document.getElementById('contenedoresBody').addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const c = contenedoresActuales.find(x => x.id_contenedor == btn.dataset.id);
                if (!c) return;
                if (btn.dataset.action === 'editar') abrirModalEditar(c);
                if (btn.dataset.action === 'baja') confirmarBorrar(c.id_contenedor, direccionContenedor(c));
                if (btn.dataset.action === 'reparar') confirmarReparacion(c);
                if (btn.dataset.action === 'historial') abrirHistorial(c);
            });
            const params = new URLSearchParams(window.location.search);
            if (params.get('vista') === 'criticos') {
                listarCriticos();
            } else {
                listarContenedores();
            }
        });

        function reglasFallback() {
            return {
                calle: { tipo: 'string', max: 100, label: 'Calle' },
                numCalle: { tipo: 'string', max: 20, label: 'Número de puerta' },
                latitud: { tipo: 'decimal', label: 'Latitud' },
                longitud: { tipo: 'decimal', label: 'Longitud' },
                capacidad: { tipo: 'decimal', min: 1, label: 'Capacidad' },
                estado: { valores: ['funcional', 'roto', 'desbordado', 'inactivo', 'en_deposito'], label: 'Estado' },
                idTipo: { tipo: 'entero', min: 1, label: 'Tipo de Residuo' },
                idCircuito: { tipo: 'entero', min: 1, label: 'Circuito' }
            };
        }

        async function cargarReglas() {
            try {
                const res = await fetch(`${API_BASE}/contenedores/reglasValidacion`);
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

        async function listarContenedores() {
            vistaActual = 'todos';
            document.getElementById('btnMostrarTodos').style.display = 'none';
            ocultarControles();
            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/contenedores`);
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
        async function listarCriticos() {
            vistaActual = 'criticos';
            document.getElementById('btnMostrarTodos').style.display = '';
            ocultarControles();
            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/contenedores/filtrarCriticos`);
                const json = await res.json();
                if (json.status === 'success') {
                    renderTabla(json.data);
                    setTableTitle(I18N.tableTitleCriticos);
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorConectar, 'error');
            } finally {
                setLoading(false);
            }
        }

        function refrescarListado() {
            if (vistaActual === 'criticos') {
                listarCriticos();
            } else {
                listarContenedores();
            }
        }

        async function buscarPorId() {
            const id = document.getElementById('buscarId').value.trim();
            if (!id) { mostrarToast(I18N.msgIngresaId, 'warning'); return; }
            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/contenedores/${id}`);
                const json = await res.json();
                if (json.status === 'success' && json.data) {
                    renderTabla([json.data]);
                    setTableTitle(`${I18N.tableTitleContenedorId}${id}`);
                } else {
                    renderTabla([]);
                    mostrarToast(json.message || I18N.msgContenedorNoEncontrado, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorConectar, 'error');
            } finally {
                setLoading(false);
            }
        }

        function actualizarFiltroValor() {
            const tipo = document.getElementById('filtroTipo').value;
            const input = document.getElementById('filtroValor');
            const select = document.getElementById('filtroValorSelect');

            input.value = '';
            select.value = '';
            input.style.display = 'none';
            select.style.display = 'none';

            if (!tipo) return;

            if (tipo === 'municipio') {
                select.innerHTML = '<option value="">— Seleccionar —</option>' +
                    MUNICIPIOS.map(m =>
                        `<option value="${m.id}">${m.nombre}</option>`
                    ).join('');
                select.style.display = '';
            } else if (tipo === 'tipoResiduo') {
                select.innerHTML = '<option value="">— Seleccionar —</option>' +
                    fuenteTipos().map(t =>
                        `<option value="${t.id_tipo ?? t.id}">${t.descripcion} (${t.color})</option>`
                    ).join('');
                select.style.display = '';
            } else if (tipo === 'estado') {
                select.innerHTML = '<option value="">— Seleccionar —</option>' +
                    ['funcional', 'roto', 'desbordado', 'inactivo', 'en_deposito']
                        .map(v => `<option value="${v}">${formatearEstado(v)}</option>`).join('') +
                    `<option value="critico">${I18N.filtroOptCritico}</option>`;
                select.style.display = '';
            }
        }

        async function aplicarFiltro() {
            const tipo = document.getElementById('filtroTipo').value;
            const input = document.getElementById('filtroValor');
            const select = document.getElementById('filtroValorSelect');
            if (!tipo) { mostrarToast(I18N.msgSeleccionaFiltro, 'warning'); return; }

            let valorCrudo;
            if (select.style.display !== 'none') {
                valorCrudo = select.value;
            } else {
                valorCrudo = input.value;
            }
            const valor = valorCrudo.trim();
            if (!valor) { mostrarToast(I18N.msgSeleccionaValorFiltro, 'warning'); return; }

            setLoading(true);
            try {
                let url;
                if (tipo === 'estado' && valor === 'critico') {
                    url = `${API_BASE}/contenedores/filtrarCriticos`;
                } else {
                    url = `${API_BASE}/contenedores?${tipo}=${encodeURIComponent(valor)}`;
                }
                const res = await fetch(url);
                const json = await res.json();
                if (json.status === 'success') {
                    renderTabla(json.data);
                    let labelEstado;
                    if (valor === 'critico') {
                        labelEstado = I18N.filtroOptCritico;
                    } else {
                        labelEstado = `${I18N.filtroOptEstado}: ${valor}`;
                    }
                    const labels = {
                        municipio: `${I18N.filtroOptMunicipio}: ${valor}`,
                        tipoResiduo: `${I18N.filtroOptTiporesiduo}: ${valor}`,
                        estado: labelEstado
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

        function aplicarRequeridoDireccion(esDeposito) {
            ['calle', 'numCalle', 'latitud', 'longitud'].forEach(campo => {
                document.getElementById(campo).required = !esDeposito;
            });
            // RNE 26: un contenedor en depósito no pertenece a ningún circuito.
            document.getElementById('idCircuito').required = !esDeposito;
            document.getElementById('idCircuito').disabled = esDeposito;
            if (esDeposito) document.getElementById('idCircuito').value = '';
            document.getElementById('idCircuitoGroup').style.display = esDeposito ? 'none' : '';
        }

        function onEstadoFormChange() {
            const estado = document.getElementById('estado').value;
            document.getElementById('contenedorEstadoForm').value = estado;
            aplicarRequeridoDireccion(estado === 'en_deposito');
        }

        function abrirModalAgregar() {
            document.getElementById('modalTitle').textContent = I18N.modalAddTitle;
            document.getElementById('contenedorId').value = '';
            document.getElementById('contenedorForm').reset();
            poblarSelectTipoResiduo();
            document.getElementById('estadoGroup').style.display = '';
            document.getElementById('contenedorEstadoForm').value = document.getElementById('estado').value;
            aplicarRequeridoDireccion(document.getElementById('estado').value === 'en_deposito');
            document.getElementById('modalForm').style.display = 'flex';
        }
        function abrirModalEditar(contenedor) {
            document.getElementById('modalTitle').textContent = I18N.modalEditTitle;
            document.getElementById('contenedorId').value = contenedor.id_contenedor;
            document.getElementById('calle').value = contenedor.calle;
            document.getElementById('numCalle').value = contenedor.num_calle;
            document.getElementById('latitud').value = contenedor.latitud;
            document.getElementById('longitud').value = contenedor.longitud;
            document.getElementById('capacidad').value = contenedor.capacidad;
            document.getElementById('idTipo').value = contenedor.id_tipo;
            document.getElementById('idCircuito').value = contenedor.id_circuito;
            document.getElementById('estadoGroup').style.display = '';
            document.getElementById('estado').value = contenedor.estado;
            document.getElementById('contenedorEstadoForm').value = contenedor.estado;
            aplicarRequeridoDireccion(contenedor.estado === 'en_deposito');
            document.getElementById('modalForm').style.display = 'flex';
        }

        async function guardarContenedor(e) {
            e.preventDefault();
            const id = document.getElementById('contenedorId').value;
            const esDeposito = document.getElementById('contenedorEstadoForm').value === 'en_deposito';

            const body = esDeposito
                ? {
                    idCircuito: null,
                    idTipo: parseInt(document.getElementById('idTipo').value),
                    calle: null,
                    numCalle: null,
                    latitud: null,
                    longitud: null,
                    capacidad: parseInt(document.getElementById('capacidad').value)
                }
                : {
                    idCircuito: parseInt(document.getElementById('idCircuito').value),
                    idTipo: parseInt(document.getElementById('idTipo').value),
                    calle: document.getElementById('calle').value.trim(),
                    numCalle: parseInt(document.getElementById('numCalle').value),
                    latitud: parseFloat(document.getElementById('latitud').value),
                    longitud: parseFloat(document.getElementById('longitud').value),
                    capacidad: parseInt(document.getElementById('capacidad').value)
                };
            if (!id) {
                body.estado = document.getElementById('estado').value;
            }

            // Al editar, el estado no viaja en este PUT (updateContenedor no lo acepta como
            // parámetro) — se cambia aparte con cambiarEstado, y tiene que ir ANTES del PUT de
            // datos: updateContenedor decide si nulear o exigir dirección leyendo el estado
            // actual del contenedor en la base, así que primero hay que dejar el estado nuevo
            // asentado para que esa regla evalúe correctamente (RF27).
            if (id) {
                try {
                    const estadoRes = await fetch(`${API_BASE}/contenedores/cambiarEstado/${id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ nuevoEstado: document.getElementById('estado').value })
                    });
                    const estadoJson = await estadoRes.json();
                    if (estadoJson.status !== 'success') {
                        mostrarToast(estadoJson.message, 'error');
                        return;
                    }
                } catch {
                    mostrarToast(I18N.msgErrorActualizarEstado, 'error');
                    return;
                }
            }

            const camposDireccion = ['calle', 'numCalle', 'latitud', 'longitud', 'idCircuito'];
            for (const [campo, valor] of Object.entries(body)) {
                if (esDeposito && camposDireccion.includes(campo)) continue;
                const regla = reglas[campo];
                if (!regla) continue;
                if (regla.valores && !regla.valores.includes(valor)) {
                    mostrarToast(`${regla.label} no válido`, 'warning');
                    return;
                }
                if (regla.tipo === 'entero' || regla.tipo === 'decimal') {
                    if (isNaN(valor)) {
                        mostrarToast(`${regla.label} debe ser un número`, 'warning');
                        return;
                    }
                    if (regla.min !== undefined && valor < regla.min) {
                        mostrarToast(`${regla.label} no puede ser menor que ${regla.min}`, 'warning');
                        return;
                    }
                }
                if (regla.tipo === 'string' && (!valor || valor === '')) {
                    mostrarToast(`${regla.label} es requerido`, 'warning');
                    return;
                }
            }

            let url, method;
            if (id) {
                url = `${API_BASE}/contenedores/${id}`;
                method = 'PUT';
            } else {
                url = `${API_BASE}/contenedores`;
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
                    mostrarToast(json.message, 'success');
                    cerrarModal();
                    refrescarListado();
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorGuardar, 'error');
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

            if (campo === 'estado') {
                select.innerHTML = ['funcional', 'roto', 'desbordado', 'inactivo', 'en_deposito']
                    .map((v, i) => {
                        let selected = '';
                        if (i === 0) {
                            selected = ' selected';
                        }
                        return `<option value="${v}"${selected}>${formatearEstado(v)}</option>`;
                    })
                    .join('');
                select.style.display = '';
            } else {
                const placeholders = {
                    calle: I18N.filtroPhCalle,
                    numCalle: I18N.filtroPhNumcalle,
                    capacidad: I18N.filtroPhCapacidad,
                    idTipo: I18N.phIdTiporesiduo,
                    idCircuito: I18N.phIdCircuito
                };
                input.placeholder = placeholders[campo] || I18N.phNuevoValor;
                input.style.display = '';
            }
        }

        async function modificarContenedor() {
            const id = document.getElementById('modificarId').value.trim();
            if (!id) { mostrarToast(I18N.msgIngresaId, 'warning'); return; }
            const campo = document.getElementById('modificarCampo').value;
            if (!campo) { mostrarToast(I18N.msgSeleccionaCampo, 'warning'); return; }

            const input = document.getElementById('modificarValor');
            const select = document.getElementById('modificarValorSelect');
            let campoValor;
            if (select.style.display !== 'none') {
                campoValor = select;
            } else {
                campoValor = input;
            }
            const valor = campoValor.value.trim();
            if (!valor) { mostrarToast(I18N.msgIngresaValor, 'warning'); return; }

            if (campo === 'estado') {
                setLoading(true);
                try {
                    const res = await fetch(`${API_BASE}/contenedores/cambiarEstado/${id}`, {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ nuevoEstado: valor })
                    });
                    const json = await res.json();
                    if (json.status === 'success') {
                        mostrarToast(I18N.msgEstadoActualizado, 'success');
                        ocultarControles();
                        refrescarListado();
                    } else {
                        mostrarToast(json.message, 'error');
                    }
                } catch {
                    mostrarToast(I18N.msgErrorModificar, 'error');
                } finally {
                    setLoading(false);
                }
                return;
            }

            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/contenedores/${id}`);
                const json = await res.json();
                if (json.status !== 'success' || !json.data) {
                    mostrarToast(json.message || I18N.msgContenedorNoEncontrado, 'error');
                    return;
                }
                const c = json.data;

                const campoMap = {
                    calle: 'calle',
                    numCalle: 'num_calle',
                    capacidad: 'capacidad',
                    estado: 'estado',
                    idTipo: 'id_tipo',
                    idCircuito: 'id_circuito'
                };
                c[campoMap[campo]] = valor;

                const putRes = await fetch(`${API_BASE}/contenedores/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        idCircuito: parseInt(c.id_circuito),
                        idTipo: parseInt(c.id_tipo),
                        calle: c.calle,
                        numCalle: parseInt(c.num_calle),
                        latitud: parseFloat(c.latitud),
                        longitud: parseFloat(c.longitud),
                        capacidad: parseInt(c.capacidad)
                    })
                });
                const putJson = await putRes.json();
                if (putJson.status === 'success') {
                    mostrarToast(putJson.message, 'success');
                    ocultarControles();
                    refrescarListado();
                } else {
                    mostrarToast(putJson.message, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorModificar, 'error');
            } finally {
                setLoading(false);
            }
        }

        async function darDeBajaContenedor() {
            const id = document.getElementById('deleteId').value.trim();
            if (!id) { mostrarToast(I18N.msgIngresaId, 'warning'); return; }
            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/contenedores/${id}`);
                const json = await res.json();
                if (json.status === 'success' && json.data) {
                    confirmarBorrar(json.data.id_contenedor, direccionContenedor(json.data));
                    ocultarControles();
                } else {
                    mostrarToast(json.message || I18N.msgContenedorNoEncontrado, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorConectar, 'error');
            } finally {
                setLoading(false);
            }
        }

        function confirmarBorrar(id, nombre) {
            contenedorIdParaEliminar = id;
            document.getElementById('deleteNombre').textContent = nombre;
            document.getElementById('modalDelete').style.display = 'flex';
        }

        async function confirmarDelete() {
            try {
                const res = await fetch(`${API_BASE}/contenedores/${contenedorIdParaEliminar}`, { method: 'DELETE' });
                const json = await res.json();
                if (json.status === 'success') {
                    mostrarToast(I18N.msgContenedorDadoBaja, 'success');
                    cerrarModalDelete();
                    refrescarListado();
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorDarBaja, 'error');
            }
        }

        // Dirección para mostrar en títulos. Un contenedor en depósito no tiene calle ni número.
        function direccionContenedor(c) {
            const partes = [c.calle, c.num_calle].filter(p => p !== null && p !== undefined && String(p).trim() !== '');
            return partes.length ? partes.join(' ') : I18N.sinDireccion;
        }

        function escapeHtml(str) {
            return String(str ?? '').replace(/[&<>"']/g, s => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[s]));
        }

        function renderTabla(contenedores) {
            const tbody = document.getElementById('contenedoresBody');
            const noRes = document.getElementById('noResultados');
            const count = document.getElementById('tableCount');

            contenedoresActuales = contenedores || [];
            tbody.innerHTML = '';

            if (contenedoresActuales.length === 0) {
                noRes.style.display = 'flex';
                count.textContent = '';
                return;
            }

            noRes.style.display = 'none';
            let etiquetaResultado;
            if (contenedoresActuales.length !== 1) {
                etiquetaResultado = I18N.resultadoPlural;
            } else {
                etiquetaResultado = I18N.resultadoSingular;
            }
            count.textContent = `${contenedoresActuales.length} ${etiquetaResultado}`;

            contenedoresActuales.forEach(c => {
                const tipo = fuenteTipos().find(t => (t.id_tipo ?? t.id) === parseInt(c.id_tipo));
                const tipoNombre = tipo ? `${tipo.descripcion} (${tipo.color})` : c.id_tipo;
                const tr = document.createElement('tr');
                tr.innerHTML = `
    <td class="td-id" data-label="${I18N.thId}">${escapeHtml(c.id_contenedor)}</td>
    <td data-label="${I18N.thCalle}"><strong>${escapeHtml(c.calle)}</strong></td>
    <td data-label="${I18N.thNumpuerta}">${escapeHtml(c.num_calle)}</td>
    <td data-label="${I18N.thCapacidad}">${Number(c.capacidad).toLocaleString()} L</td>
    <td data-label="${I18N.thTiporesiduo}">${escapeHtml(tipoNombre)}</td>
    <td data-label="${I18N.thCircuito}">${escapeHtml(c.id_circuito)}</td>
    <td data-label="${I18N.thEstado}"><span class="badge badge-${badgeClass(c.estado)}">${escapeHtml(formatearEstado(c.estado))}</span></td>
    <td class="td-actions" data-label="${I18N.thAcciones}">
        ${c.estado === 'roto' ? `<button class="btn-action" data-action="reparar" data-id="${escapeHtml(c.id_contenedor)}" title="${I18N.repararTitle}">🔧</button>` : ''}
        <button class="btn-action" data-action="historial" data-id="${escapeHtml(c.id_contenedor)}" title="${I18N.historialTitle}">📋</button>
        <button class="btn-action" data-action="editar" data-id="${escapeHtml(c.id_contenedor)}" title="${I18N.crudTitleEditar}">✏️</button>
        <button class="btn-action" data-action="baja" data-id="${escapeHtml(c.id_contenedor)}" title="${I18N.modalDeleteTitle}">🗑️</button>
    </td>
`;
                tbody.appendChild(tr);
            });
        }
        const VALORES_ESTADO_TRADUCIDOS = {
            funcional: 'optFuncional', roto: 'optRoto', desbordado: 'optDesbordado',
            inactivo: 'optInactivo', en_deposito: 'optEnDeposito'
        };
        function formatearEstado(estado) {
            const clave = VALORES_ESTADO_TRADUCIDOS[estado];
            if (clave) return I18N[clave];
            const texto = estado.replace(/_/g, ' ');
            return texto.charAt(0).toUpperCase() + texto.slice(1);
        }

        function badgeClass(estado) {
            const map = {
                'funcional': 'success',
                'roto': 'danger',
                'desbordado': 'warning',
                'inactivo': 'neutral',
                'en_deposito': 'info'
            };
            return map[estado] ?? 'neutral';
        }

        function toggleFiltros() {
            const fb = document.getElementById('filterBar');
            const sb = document.getElementById('searchBar');
            const mb = document.getElementById('modificarBar');
            const db = document.getElementById('deleteBar');
            sb.style.display = 'none';
            mb.style.display = 'none';
            db.style.display = 'none';
            if (fb.style.display === 'none') {
                fb.style.display = 'block';
            } else {
                fb.style.display = 'none';
            }
        }

        function toggleBusquedaId() {
            const sb = document.getElementById('searchBar');
            const fb = document.getElementById('filterBar');
            const mb = document.getElementById('modificarBar');
            const db = document.getElementById('deleteBar');
            fb.style.display = 'none';
            mb.style.display = 'none';
            db.style.display = 'none';
            if (sb.style.display === 'none') {
                sb.style.display = 'block';
            } else {
                sb.style.display = 'none';
            }
        }

        function toggleModificar() {
            const mb = document.getElementById('modificarBar');
            const fb = document.getElementById('filterBar');
            const sb = document.getElementById('searchBar');
            const db = document.getElementById('deleteBar');
            fb.style.display = 'none';
            sb.style.display = 'none';
            db.style.display = 'none';
            if (mb.style.display === 'none') {
                mb.style.display = 'block';
            } else {
                mb.style.display = 'none';
            }
        }

        function toggleDarDeBaja() {
            const db = document.getElementById('deleteBar');
            const fb = document.getElementById('filterBar');
            const sb = document.getElementById('searchBar');
            const mb = document.getElementById('modificarBar');
            fb.style.display = 'none';
            sb.style.display = 'none';
            mb.style.display = 'none';
            if (db.style.display === 'none') {
                db.style.display = 'block';
            } else {
                db.style.display = 'none';
            }
        }

        function ocultarControles() {
            document.getElementById('filterBar').style.display = 'none';
            document.getElementById('searchBar').style.display = 'none';
            document.getElementById('modificarBar').style.display = 'none';
            document.getElementById('deleteBar').style.display = 'none';
        }

        function cerrarModal() {
            document.getElementById('modalForm').style.display = 'none';
        }

        let contenedorReparandoId = null;

        function formatearTexto(texto) {
            const limpio = String(texto ?? '').replace(/_/g, ' ');
            return limpio.charAt(0).toUpperCase() + limpio.slice(1);
        }

        // RF27: solo un contenedor roto se puede marcar como reparado.
        function confirmarReparacion(c) {
            contenedorReparandoId = c.id_contenedor;
            document.getElementById('reparacionMensaje').textContent = I18N.reparacionMensaje;
            document.getElementById('modalReparar').style.display = 'flex';
        }

        function cerrarModalReparar() {
            document.getElementById('modalReparar').style.display = 'none';
            contenedorReparandoId = null;
        }

        async function confirmarReparar() {
            try {
                const res = await fetch(`${API_BASE}/contenedores/registrarReparacion/${contenedorReparandoId}`, { method: 'POST' });
                const json = await res.json();
                if (json.status === 'success') {
                    mostrarToast(I18N.msgReparacionOk, 'success');
                    cerrarModalReparar();
                    refrescarListado();
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorConectar, 'error');
            }
        }

        async function abrirHistorial(c) {
            document.getElementById('historialTitulo').textContent = direccionContenedor(c);
            document.getElementById('historialLista').innerHTML = '';
            document.getElementById('modalHistorial').style.display = 'flex';
            try {
                const res = await fetch(`${API_BASE}/contenedores/getHistorial/${c.id_contenedor}`);
                const json = await res.json();
                renderHistorial(json.data || []);
            } catch {
                mostrarToast(I18N.msgErrorConectar, 'error');
            }
        }

        function renderHistorial(eventos) {
            const lista = document.getElementById('historialLista');
            if (eventos.length === 0) {
                lista.innerHTML = `<p class="estado-vacio">${escapeHtml(I18N.historialVacio)}</p>`;
                return;
            }
            const etiquetas = {
                incidencia: I18N.eventoIncidencia,
                levante: I18N.eventoLevante,
                reparacion: I18N.eventoReparacion
            };
            lista.innerHTML = eventos.map(ev => `
                <div class="historial-item">
                    <strong>${escapeHtml(etiquetas[ev.tipo_evento] ?? ev.tipo_evento)}</strong>
                    · ${escapeHtml(formatearTexto(ev.descripcion))}
                    <small>${escapeHtml(ev.fecha ?? '')}</small>
                </div>`).join('');
        }

        function cerrarModalHistorial() {
            document.getElementById('modalHistorial').style.display = 'none';
        }

        function cerrarModalDelete() {
            document.getElementById('modalDelete').style.display = 'none';
            contenedorIdParaEliminar = null;
        }

        function onOverlayClick(e, modalId) {
            if (e.target.id === modalId) {
                if (modalId === 'modalForm') {
                    cerrarModal();
                } else {
                    cerrarModalDelete();
                }
            }
        }

        function setTableTitle(title) {
            document.getElementById('tableTitle').textContent = title;
        }

        function setLoading(show) {
            if (show) {
                document.getElementById('loadingState').style.display = 'flex';
                document.getElementById('contenedoresBody').style.opacity = '0.3';
            } else {
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('contenedoresBody').style.opacity = '1';
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