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
    <title><?= t('usuarios.title') ?></title>
    <link rel="stylesheet" href="styleUsuarios.css">
</head>

<body>

    <header class="navbar">
        <div class="nav-inner">
            <div class="nav-left">
                <a href="../../Backoffice/indexBackoffice.php" class="brand">
                    <img src="Logo.png" alt="Recolix" class="brand-logo">
                    <span class="brand-name">Recolix</span>
                </a>
                <nav class="nav-links">
                    <a href="../../Backoffice/indexBackoffice.php"><?= t('backoffice.nav_inicio') ?></a>
                    <a href="../../API_Recoleccion/Vista/camiones.php"><?= t('backoffice.nav_camiones') ?></a>
                    <a href="../../API_Recoleccion/Vista/circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
                    <a href="../../API_Recoleccion/Vista/rutas.php"><?= t('backoffice.nav_rutas') ?></a>
                    <a href="cuadrillas.php"><?= t('backoffice.nav_cuadrillas') ?></a>
                    <a href="../../API_Gestion/Vista/contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
                    <a href="../../API_Gestion/Vista/centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
                    <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
                    <a href="../../API_Gestion/Vista/reportes.php"><?= t('backoffice.nav_reportes') ?></a>
                    <a href="../../API_Gestion/Vista/incidencias.php"><?= t('backoffice.nav_incidencias') ?></a>
                    <a href="usuarios.php" class="nav-active"><?= t('backoffice.nav_usuarios') ?></a>
                </nav>
            </div>
            <div class="nav-right">
                <?php include __DIR__ . '/../../i18n/switcher.php'; ?>
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/recolix/Comun/campana.php'; ?>
                <a href="../perfil.php" class="btn-user" title="<?= htmlspecialchars(t('backoffice.nav_perfil_usuario')) ?>">
                    <img src="img/user.svg" alt="Usuario" class="nav-user-icon">
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
            <a href="cuadrillas.php"><?= t('backoffice.nav_cuadrillas') ?></a>
            <a href="../../API_Gestion/Vista/contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
            <a href="../../API_Gestion/Vista/centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
            <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
            <a href="../../API_Gestion/Vista/reportes.php"><?= t('backoffice.nav_reportes') ?></a>
            <a href="../../API_Gestion/Vista/incidencias.php"><?= t('backoffice.nav_incidencias') ?></a>
            <a href="usuarios.php" class="nav-active"><?= t('backoffice.nav_usuarios') ?></a>
        </nav>
        <div class="nav-hr"></div>
    </header>

    <section class="hero">
        <h1 class="hero-title"><?= t('usuarios.hero_title') ?></h1>
        <p class="hero-sub"><?= t('usuarios.hero_sub') ?></p>
    </section>

    <section class="cards-section">
        <div class="cards-grid">

            <div class="card-operar" onclick="abrirModalAgregar()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/plus.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('usuarios.card_agregar_titulo') ?></h3>
                    <p><?= t('usuarios.card_agregar_desc') ?></p>
                    <button class="card-btn"><?= t('crud.btn_agregar') ?></button>
                </div>
            </div>

            <a href="../../Backoffice/indexSolicitudRegistro.php" class="card-operar">
                <div class="card-content">
                    <div class="card-icon"><img src="img/user.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('usuarios.card_solicitudes_titulo') ?></h3>
                    <p><?= t('usuarios.card_solicitudes_desc') ?></p>
                </div>
            </a>

            <div class="card-operar" onclick="toggleFiltros()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/loupe.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('crud.btn_filtrar') ?></h3>
                    <p><?= t('usuarios.card_filtrar_desc') ?></p>
                    <button class="card-btn"><?= t('crud.btn_filtrar') ?></button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleBusquedaId()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/question.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('usuarios.card_buscarid_titulo') ?></h3>
                    <p><?= t('usuarios.card_buscarid_desc') ?></p>
                    <button class="card-btn"><?= t('crud.btn_buscar') ?></button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleModificar()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/refresh.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('usuarios.card_modificar_titulo') ?></h3>
                    <p><?= t('usuarios.card_modificar_desc') ?></p>
                    <button class="card-btn"><?= t('crud.btn_modificar') ?></button>
                </div>
            </div>

            <div class="card-operar" onclick="toggleCambiarEstado()">
                <div class="card-content">
                    <div class="card-icon"><img src="img/minus.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('usuarios.card_estado_titulo') ?></h3>
                    <p><?= t('usuarios.card_estado_desc') ?></p>
                    <button class="card-btn"><?= t('usuarios.btn_cambiar_estado') ?></button>
                </div>
            </div>

        </div>
    </section>

    <section class="control-bar" id="filterBar" style="display:none">
        <div class="control-inner">
            <select id="filtroTipo" class="ctrl-select" onchange="actualizarFiltroValor()">
                <option value=""><?= t('crud.filtrar_por') ?></option>
                <option value="rol"><?= t('usuarios.filtro_opt_rol') ?></option>
                <option value="acceso"><?= t('usuarios.filtro_opt_acceso') ?></option>
            </select>
            <select id="filtroValorSelect" class="ctrl-select" style="display:none"></select>
            <button class="btn-primary" onclick="aplicarFiltro()"><?= t('crud.btn_aplicar') ?></button>
            <button class="btn-secondary" onclick="listarUsuarios()"><?= t('crud.btn_limpiar') ?></button>
        </div>
    </section>

    <section class="control-bar" id="searchBar" style="display:none">
        <div class="control-inner">
            <input type="number" id="buscarCi" class="ctrl-input" placeholder="<?= htmlspecialchars(t('usuarios.searchid_ph')) ?>" min="1"
                style="max-width:200px">
            <button class="btn-primary" onclick="buscarPorCi()"><?= t('crud.btn_buscar') ?></button>
            <button class="btn-secondary" onclick="listarUsuarios()"><?= t('crud.btn_vertodos') ?></button>
        </div>
    </section>

    <section class="control-bar" id="modificarBar" style="display:none">
        <div class="control-inner">
            <input type="number" id="modificarCi" class="ctrl-input" placeholder="<?= htmlspecialchars(t('usuarios.modificarid_ph')) ?>" min="1"
                style="max-width:160px">
            <select id="modificarCampo" class="ctrl-select" onchange="actualizarPlaceholderModificar()">
                <option value=""><?= t('crud.campo') ?></option>
                <option value="rol"><?= t('usuarios.campo_opt_rol') ?></option>
                <option value="email"><?= t('usuarios.campo_opt_email') ?></option>
                <option value="tel"><?= t('usuarios.campo_opt_tel') ?></option>
            </select>
            <input type="text" id="modificarValor" class="ctrl-input" style="display:none"
                placeholder="<?= htmlspecialchars(t('crud.seleccionar_campo_placeholder')) ?>">
            <select id="modificarValorSelect" class="ctrl-select" style="display:none"></select>
            <button class="btn-primary" onclick="modificarUsuario()"><?= t('crud.btn_modificar') ?></button>
            <button class="btn-secondary" onclick="ocultarControles()"><?= t('crud.btn_cancelar') ?></button>
        </div>
    </section>

    <section class="control-bar" id="estadoBar" style="display:none">
        <div class="control-inner">
            <input type="number" id="estadoCi" class="ctrl-input" placeholder="<?= htmlspecialchars(t('usuarios.estadoid_ph')) ?>" min="1"
                style="max-width:200px">
            <button class="btn-danger" onclick="buscarParaCambiarEstado()"><?= t('usuarios.btn_continuar') ?></button>
            <button class="btn-secondary" onclick="ocultarControles()"><?= t('crud.btn_cancelar') ?></button>
        </div>
    </section>

    <section class="table-section">
        <div class="table-header">
            <h2 class="table-title" id="tableTitle"><?= t('usuarios.hero_title') ?></h2>
            <span class="table-count" id="tableCount"></span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><?= t('usuarios.th_cedula') ?></th>
                        <th><?= t('usuarios.th_nombre') ?></th>
                        <th><?= t('usuarios.th_apellido') ?></th>
                        <th><?= t('usuarios.th_email') ?></th>
                        <th><?= t('usuarios.th_telefono') ?></th>
                        <th><?= t('usuarios.th_rol') ?></th>
                        <th><?= t('usuarios.th_acceso') ?></th>
                        <th><?= t('usuarios.th_acciones') ?></th>
                    </tr>
                </thead>
                <tbody id="usuariosBody"></tbody>
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
                <h3 id="modalTitle"><?= t('usuarios.modal_add_title') ?></h3>
                <button class="modal-close" onclick="cerrarModal()">✕</button>
            </div>
            <form id="usuarioForm" onsubmit="guardarUsuario(event)">
                <div class="form-row" id="identidadRow">
                    <div class="form-group">
                        <label for="usuarioCi"><?= t('usuarios.field_cedula') ?></label>
                        <input type="number" id="usuarioCi" class="form-input" placeholder="12345678" min="1000000"
                            required>
                    </div>
                    <div class="form-group" id="municipioGroup">
                        <label for="idMunicipio"><?= t('usuarios.field_municipio') ?></label>
                        <select id="idMunicipio" class="form-input">
                            <option value=""><?= t('crud.seleccionar') ?></option>
                        </select>
                    </div>
                </div>
                <div class="form-row" id="nombreRow">
                    <div class="form-group">
                        <label for="primerNombre"><?= t('usuarios.field_nombre') ?></label>
                        <input type="text" id="primerNombre" class="form-input" placeholder="<?= htmlspecialchars(t('usuarios.field_nombre_ph')) ?>" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="pApellido"><?= t('usuarios.field_apellido') ?></label>
                        <input type="text" id="pApellido" class="form-input" placeholder="<?= htmlspecialchars(t('usuarios.field_apellido_ph')) ?>" maxlength="50">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email"><?= t('usuarios.field_email') ?></label>
                        <input type="email" id="email" class="form-input" placeholder="<?= htmlspecialchars(t('usuarios.field_email_ph')) ?>"
                            maxlength="120" required>
                    </div>
                    <div class="form-group">
                        <label for="tel"><?= t('usuarios.field_telefono') ?></label>
                        <input type="text" id="tel" class="form-input" placeholder="<?= htmlspecialchars(t('usuarios.field_telefono_ph')) ?>" maxlength="9"
                            required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="rol"><?= t('usuarios.field_rol') ?></label>
                        <select id="rol" class="form-input" required>
                            <option value="vecino"><?= t('usuarios.opt_vecino') ?></option>
                            <option value="cuadrilla"><?= t('usuarios.opt_cuadrilla') ?></option>
                            <option value="operario"><?= t('usuarios.opt_operario') ?></option>
                            <option value="administrador"><?= t('usuarios.opt_administrador') ?></option>
                        </select>
                    </div>
                    <div class="form-group" id="contraseniaGroup">
                        <label for="contrasenia"><?= t('usuarios.field_contrasenia') ?></label>
                        <input type="password" id="contrasenia" class="form-input" placeholder="<?= htmlspecialchars(t('usuarios.field_contrasenia_ph')) ?>"
                            minlength="8">
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
                <h3><?= t('usuarios.modal_estado_title') ?></h3>
                <button class="modal-close" onclick="cerrarModalDelete()">✕</button>
            </div>
            <p class="delete-msg" id="deleteMsg"></p>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="cerrarModalDelete()"><?= t('crud.btn_cancelar') ?></button>
                <button class="btn-danger" onclick="confirmarDelete()"><?= t('usuarios.btn_confirmar') ?></button>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-logo">
                    <img src="Logo.png" alt="Recolix" class="footer-logo-img">
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
            const API_BASE = '/recolix/API_Usuarios/api.php';
            const I18N = <?= json_encode(t_array([
            'msgIngresaCedula' => 'usuarios.msg_ingresa_cedula',
            'msgUsuarioNoEncontrado' => 'usuarios.msg_usuario_no_encontrado',
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
            'modalAddTitle' => 'usuarios.modal_add_title',
            'modalEditTitle' => 'usuarios.modal_edit_title',
            'msgUsuarioActualizado' => 'usuarios.msg_usuario_actualizado',
            'msgUsuarioCreado' => 'usuarios.msg_usuario_creado',
            'msgIngresaLaCedula' => 'usuarios.msg_ingresa_la_cedula',
            'msgSeleccionaMunicipio' => 'usuarios.msg_selecciona_municipio',
            'msgIngresaNombreApellido' => 'usuarios.msg_ingresa_nombre_apellido',
            'msgContraseniaMin' => 'usuarios.msg_contrasenia_min',
            'msgRolActualizado' => 'usuarios.msg_rol_actualizado',
            'msgErrorCambiarEstado' => 'usuarios.msg_error_cambiar_estado',
            'confirmEstadoBefore' => 'usuarios.confirm_estado_before',
            'confirmEstadoHabilitar' => 'usuarios.confirm_estado_habilitar',
            'confirmEstadoDeshabilitar' => 'usuarios.confirm_estado_deshabilitar',
            'confirmEstadoMiddle' => 'usuarios.confirm_estado_middle',
            'confirmEstadoAfter' => 'usuarios.confirm_estado_after',
            'usuarioHabilitadoOk' => 'usuarios.usuario_habilitado_ok',
            'usuarioDeshabilitadoOk' => 'usuarios.usuario_deshabilitado_ok',
            'titleDeshabilitar' => 'usuarios.title_deshabilitar',
            'titleHabilitar' => 'usuarios.title_habilitar',
            'crudTitleEditar' => 'crud.title_editar',
            'filtroPhEmail' => 'usuarios.filtro_ph_email',
            'filtroPhTel' => 'usuarios.filtro_ph_tel',
            'phNuevoValor' => 'usuarios.ph_nuevo_valor',
            'tableTitleTodos' => 'usuarios.table_title_todos',
            'tableTitleUsuarioId' => 'usuarios.table_title_usuario_id',
            'th_cedula' => 'usuarios.th_cedula',
            'th_nombre' => 'usuarios.th_nombre',
            'th_apellido' => 'usuarios.th_apellido',
            'th_email' => 'usuarios.th_email',
            'th_telefono' => 'usuarios.th_telefono',
            'th_rol' => 'usuarios.th_rol',
            'th_acceso' => 'usuarios.th_acceso',
            'th_acciones' => 'usuarios.th_acciones',
        ])) ?>;
        const VALORES_TRADUCIDOS = <?= json_encode(t_array([
            'habilitado' => 'usuarios.val_habilitado',
            'pendiente' => 'usuarios.val_pendiente',
            'deshabilitado' => 'usuarios.val_deshabilitado',
            'vecino' => 'usuarios.val_vecino',
            'cuadrilla' => 'usuarios.val_cuadrilla',
            'operario' => 'usuarios.val_operario',
            'administrador' => 'usuarios.val_administrador',
        ])) ?>;
        let usuarioCiParaEstado = null;
        let estadoDestino = null;
        let toastTimeout;
        let reglas = {};
        let usuariosActuales = [];
        let modoEdicion = false;
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
            const select = document.getElementById('idMunicipio');
            select.innerHTML = '<option value="">— Seleccionar —</option>' +
                MUNICIPIOS.map(m => `<option value="${m.id}">${m.nombre}</option>`).join('');
        }

        function reglasFallback() {
            return {
                email: { tipo: 'email', max: 120, label: 'Correo electrónico' },
                tel: { tipo: 'string', min: 9, max: 9, label: 'Teléfono' },
                rol: { valores: ['administrador', 'cuadrilla', 'operario', 'vecino'], label: 'Rol' },
                acceso: { valores: ['pendiente', 'habilitado', 'deshabilitado'], label: 'Acceso' }
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
                    const destino = u.acceso === 'habilitado' ? 'deshabilitado' : 'habilitado';
                    confirmarCambioEstado(u.ci_usuario, `${u.primer_nombre} ${u.p_apellido}`, destino);
                }
            });
            listarUsuarios();
        });

        async function listarUsuarios() {
            ocultarControles();
            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/usuarios`);
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

        async function buscarPorCi() {
            const ci = document.getElementById('buscarCi').value.trim();
            if (!ci) { mostrarToast(I18N.msgIngresaCedula, 'warning'); return; }
            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/usuarios/${ci}`);
                const json = await res.json();
                if (json.status === 'success' && json.data) {
                    renderTabla([json.data]);
                    setTableTitle(`${I18N.tableTitleUsuarioId}${ci}`);
                } else {
                    renderTabla([]);
                    mostrarToast(json.message || I18N.msgUsuarioNoEncontrado, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorConectar, 'error');
            } finally {
                setLoading(false);
            }
        }

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
            if (!tipo) { mostrarToast(I18N.msgSeleccionaFiltro, 'warning'); return; }
            const valor = document.getElementById('filtroValorSelect').value;
            if (!valor) { mostrarToast(I18N.msgSeleccionaValorFiltro, 'warning'); return; }

            const endpointMap = {
                rol: 'filtrarRol',
                acceso: 'filtrarAcceso'
            };
            const labelMap = { rol: I18N.th_rol, acceso: I18N.th_acceso };

            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/usuarios/${endpointMap[tipo]}?${tipo}=${encodeURIComponent(valor)}`);
                const json = await res.json();
                if (json.status === 'success') {
                    renderTabla(json.data);
                    setTableTitle(`${I18N.filtroPrefijo} ${labelMap[tipo] || tipo}: ${formatearValor(valor)}`);
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
            modoEdicion = false;
            document.getElementById('modalTitle').textContent = I18N.modalAddTitle;
            document.getElementById('usuarioForm').reset();
            document.getElementById('usuarioCi').disabled = false;
            poblarSelectMunicipio();

            document.getElementById('identidadRow').style.display = '';
            document.getElementById('nombreRow').style.display = '';
            document.getElementById('contraseniaGroup').style.display = '';
            document.getElementById('idMunicipio').required = true;
            document.getElementById('primerNombre').required = true;
            document.getElementById('pApellido').required = true;
            document.getElementById('contrasenia').required = true;

            document.getElementById('modalForm').style.display = 'flex';
        }

        function abrirModalEditar(usuario) {
            modoEdicion = true;
            document.getElementById('modalTitle').textContent = I18N.modalEditTitle;
            document.getElementById('usuarioForm').reset();
            document.getElementById('usuarioCi').value = usuario.ci_usuario;
            document.getElementById('usuarioCi').disabled = true;
            document.getElementById('email').value = usuario.email;
            document.getElementById('tel').value = usuario.tel;
            document.getElementById('rol').value = usuario.rol;

            document.getElementById('identidadRow').style.display = 'none';
            document.getElementById('nombreRow').style.display = 'none';
            document.getElementById('contraseniaGroup').style.display = 'none';
            document.getElementById('idMunicipio').required = false;
            document.getElementById('primerNombre').required = false;
            document.getElementById('pApellido').required = false;
            document.getElementById('contrasenia').required = false;

            document.getElementById('modalForm').style.display = 'flex';
        }

        async function guardarUsuario(e) {
            e.preventDefault();
            const email = document.getElementById('email').value.trim();
            const tel   = document.getElementById('tel').value.trim();
            const rol   = document.getElementById('rol').value;

            if (!validarCampo('email', email) || !validarCampo('tel', tel)) return;

            if (modoEdicion) {
                const ci = document.getElementById('usuarioCi').value;
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

                    mostrarToast(I18N.msgUsuarioActualizado, 'success');
                    cerrarModal();
                    listarUsuarios();
                } catch {
                    mostrarToast(I18N.msgErrorGuardar, 'error');
                }
                return;
            }

            const ci            = document.getElementById('usuarioCi').value.trim();
            const idMunicipio    = document.getElementById('idMunicipio').value;
            const primerNombre   = document.getElementById('primerNombre').value.trim();
            const pApellido      = document.getElementById('pApellido').value.trim();
            const contrasenia    = document.getElementById('contrasenia').value;

            if (!ci) { mostrarToast(I18N.msgIngresaLaCedula, 'warning'); return; }
            if (!idMunicipio) { mostrarToast(I18N.msgSeleccionaMunicipio, 'warning'); return; }
            if (!primerNombre || !pApellido) { mostrarToast(I18N.msgIngresaNombreApellido, 'warning'); return; }
            if (contrasenia.length < 8) { mostrarToast(I18N.msgContraseniaMin, 'warning'); return; }

            try {
                const res = await fetch(`${API_BASE}/usuarios`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        ci: parseInt(ci),
                        primerNombre,
                        pApellido,
                        email,
                        tel,
                        contrasenia,
                        rol,
                        idMunicipio: parseInt(idMunicipio)
                    })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    mostrarToast(I18N.msgUsuarioCreado, 'success');
                    cerrarModal();
                    listarUsuarios();
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorGuardar, 'error');
            }
        }

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
                    email: I18N.filtroPhEmail,
                    tel: I18N.filtroPhTel
                };
                input.placeholder = placeholders[campo] || I18N.phNuevoValor;
                input.style.display = '';
            }
        }

        async function modificarUsuario() {
            const ci = document.getElementById('modificarCi').value.trim();
            if (!ci) { mostrarToast(I18N.msgIngresaCedula, 'warning'); return; }
            const campo = document.getElementById('modificarCampo').value;
            if (!campo) { mostrarToast(I18N.msgSeleccionaCampo, 'warning'); return; }

            const input  = document.getElementById('modificarValor');
            const select = document.getElementById('modificarValorSelect');
            const valor  = (campo === 'rol' ? select : input).value.trim();
            if (!valor) { mostrarToast(I18N.msgIngresaValor, 'warning'); return; }

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
                        mostrarToast(I18N.msgRolActualizado, 'success');
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
                    mostrarToast(json.message || I18N.msgUsuarioNoEncontrado, 'error');
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
                mostrarToast(I18N.msgErrorModificar, 'error');
            } finally {
                setLoading(false);
            }
        }

        function validarCampo(campo, valor) {
            const regla = reglas[campo];
            if (!regla) return true;
            if (regla.valores && !regla.valores.includes(valor)) {
                mostrarToast(msgValidacion('noValido', campo, regla), 'warning');
                return false;
            }
            if (campo === 'email' && (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor) || valor.length > (regla.max || 120))) {
                mostrarToast(msgValidacion('formato', campo, regla), 'warning');
                return false;
            }
            if (campo === 'tel' && !/^\d{9}$/.test(valor)) {
                mostrarToast(msgValidacion('nueveDigitos', campo, regla), 'warning');
                return false;
            }
            return true;
        }

        async function buscarParaCambiarEstado() {
            const ci = document.getElementById('estadoCi').value.trim();
            if (!ci) { mostrarToast(I18N.msgIngresaCedula, 'warning'); return; }
            setLoading(true);
            try {
                const res = await fetch(`${API_BASE}/usuarios/${ci}`);
                const json = await res.json();
                if (json.status === 'success' && json.data) {
                    const destino = json.data.acceso === 'habilitado' ? 'deshabilitado' : 'habilitado';
                    confirmarCambioEstado(json.data.ci_usuario, `${json.data.primer_nombre} ${json.data.p_apellido}`, destino);
                    ocultarControles();
                } else {
                    mostrarToast(json.message || I18N.msgUsuarioNoEncontrado, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorConectar, 'error');
            } finally {
                setLoading(false);
            }
        }

        function confirmarCambioEstado(ci, nombre, destino) {
            usuarioCiParaEstado = ci;
            estadoDestino = destino;
            const verbo = destino === 'habilitado' ? I18N.confirmEstadoHabilitar : I18N.confirmEstadoDeshabilitar;
            document.getElementById('deleteMsg').innerHTML =
                `${I18N.confirmEstadoBefore} <strong>${verbo}</strong> ${I18N.confirmEstadoMiddle} <strong>${nombre}</strong>${I18N.confirmEstadoAfter}`;
            document.getElementById('modalDelete').style.display = 'flex';
        }

        async function confirmarDelete() {
            try {
                const res = await fetch(`${API_BASE}/usuarios/cambiarAcceso/${usuarioCiParaEstado}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nuevoAcceso: estadoDestino })
                });
                const json = await res.json();
                if (json.status === 'success') {
                    mostrarToast(estadoDestino === 'habilitado' ? I18N.usuarioHabilitadoOk : I18N.usuarioDeshabilitadoOk, 'success');
                    cerrarModalDelete();
                    listarUsuarios();
                } else {
                    mostrarToast(json.message, 'error');
                }
            } catch {
                mostrarToast(I18N.msgErrorCambiarEstado, 'error');
            }
        }

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
            count.textContent = `${usuariosActuales.length} ${usuariosActuales.length !== 1 ? I18N.resultadoPlural : I18N.resultadoSingular}`;

            usuariosActuales.forEach(u => {
                const tr = document.createElement('tr');
                const esHabilitado = u.acceso === 'habilitado';
                tr.innerHTML = `
    <td class="td-id" data-label="${I18N.th_cedula}">${escapeHtml(u.ci_usuario)}</td>
    <td data-label="${I18N.th_nombre}"><strong>${escapeHtml(u.primer_nombre)}</strong></td>
    <td data-label="${I18N.th_apellido}">${escapeHtml(u.p_apellido)}</td>
    <td data-label="${I18N.th_email}">${escapeHtml(u.email)}</td>
    <td data-label="${I18N.th_telefono}">${escapeHtml(u.tel)}</td>
    <td data-label="${I18N.th_rol}"><span class="badge badge-neutral">${formatearValor(u.rol)}</span></td>
    <td data-label="${I18N.th_acceso}"><span class="badge badge-${badgeClaseAcceso(u.acceso)}">${formatearValor(u.acceso)}</span></td>
    <td class="td-actions" data-label="${I18N.th_acciones}">
        <button class="btn-action" data-action="editar" data-id="${escapeHtml(u.ci_usuario)}" title="${I18N.crudTitleEditar}">✏️</button>
        <button class="btn-action" data-action="estado" data-id="${escapeHtml(u.ci_usuario)}" title="${esHabilitado ? I18N.titleDeshabilitar : I18N.titleHabilitar}">${esHabilitado ? '🔒' : '🔓'}</button>
    </td>
`;
                tbody.appendChild(tr);
            });
        }

        function formatearValor(valor) {
            const clave = String(valor ?? '');
            if (VALORES_TRADUCIDOS[clave]) return VALORES_TRADUCIDOS[clave];
            const texto = clave.replace(/_/g, ' ');
            return texto.charAt(0).toUpperCase() + texto.slice(1);
        }

        function badgeClaseAcceso(acceso) {
            const map = { habilitado: 'success', pendiente: 'warning', deshabilitado: 'danger' };
            return map[acceso] ?? 'neutral';
        }

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

    <?php include __DIR__ . '/../../i18n/validacion_js.php'; ?>
    <script src="../../Backoffice/js/paginacion.js"></script>
</body>

</html>
