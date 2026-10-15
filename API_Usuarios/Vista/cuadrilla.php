<?php
$rolesPermitidos = ['cuadrilla'];
require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php';

// La cuadrilla se vuelve a leer de la base en cada carga (no solo de la sesión):
// si un admin asigna o cambia la cuadrilla del usuario mientras ya tenía la sesión
// abierta, el dato en $_SESSION queda viejo y mostraba "sin cuadrilla" por error.
require_once __DIR__ . '/../../conexion.php';
$conn = new conexion();
$stmt = mysqli_prepare($conn->getConexion(), "SELECT id_cuadrilla FROM USUARIO WHERE ci_usuario = ?");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['usuario']['ci_usuario']);
mysqli_stmt_execute($stmt);
$filaUsuario = mysqli_stmt_get_result($stmt)->fetch_assoc();
mysqli_stmt_close($stmt);

$idCuadrilla = $filaUsuario['id_cuadrilla'] ?? null;
$_SESSION['usuario']['id_cuadrilla'] = $idCuadrilla;
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($RECOLIX_IDIOMA) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('cuadrilla.title') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../../Backoffice/styleBackoffice.css">
    <link rel="stylesheet" href="styles_vecino.css">
    <link rel="stylesheet" href="navExtras.css">
    <link rel="stylesheet" href="cuadrilla.css">
</head>

<body>

    <header class="navbar">
        <div class="nav-inner">
            <div class="nav-left">
                <a href="cuadrilla.php" class="brand">
                    <img src="../../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="brand-logo">
                    <span class="brand-name">Recolix</span>
                </a>
                    <nav class="nav-links">
                    <a href="cuadrilla.php" class="nav-active"><?= t('cuadrilla.nav_inicio') ?></a>
                    <a href="../../Backoffice/mapaCuadrilla.php"><?= t('cuadrilla.nav_mapa') ?></a>
                </nav>
            </div>
            <div class="nav-right">
                <?php include __DIR__ . '/../../extra/i18n/switcher.php'; ?>
                <?php include $_SERVER['DOCUMENT_ROOT'] . '/recolix/extra/Comun/campana.php'; ?>
                <a href="../Vista/perfilCuadrilla.php" class="btn-user" title="<?= htmlspecialchars(t('backoffice.nav_perfil_usuario')) ?>">
                    <img src="../../API_Recoleccion/Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
                </a>
                <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="<?= htmlspecialchars(t('backoffice.nav_menu')) ?>">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
            <nav class="nav-mobile" id="navMobile">
            <a href="cuadrilla.php" class="nav-active"><?= t('cuadrilla.nav_inicio') ?></a>
            <a href="../../Backoffice/mapaCuadrilla.php"><?= t('cuadrilla.nav_mapa') ?></a>
        </nav>
        <div class="nav-hr"></div>
    </header>

    <main class="container dash-main">

            <div class="page-title">
            <h1><?= t('cuadrilla.page_titulo') ?></h1>
            <p><?= t('cuadrilla.page_sub') ?></p>
        </div>

        <!-- Usuario sin id_cuadrilla asignado (USUARIO.id_cuadrilla) -->
        <div class="alert-banner alert-banner--info" id="sin-cuadrilla-banner" hidden>
            <span class="alert-banner-icon">ℹ</span>
            <span><?= t('cuadrilla.msg_sin_cuadrillas') ?></span>
        </div>

        <!-- RF52: alerta generada por el sistema cuando un levante queda pendiente/en curso pasado el turno -->
        <div class="alert-banner" id="alerta-levante" hidden>
            <span class="alert-banner-icon">⚠</span>
            <span id="alerta-levante-texto"></span>
        </div>

        <!-- Incidencias asignadas por el administrador (RF10, RF13) -->
        <section class="dash-section" id="seccion-incidencias">
            <div class="panel-card">
                <h2><?= t('cuadrilla.inc_titulo') ?></h2>
                <p id="incidencias-vacio" hidden><?= t('cuadrilla.inc_vacio') ?></p>
                <div id="incidencias-lista" data-paginar-lista></div>
            </div>
        </section>

        <!-- Registrar levante -->
        <section class="dash-section" id="seccion-levante">
            <div class="panel-card">
                <div class="card-header card-header--accion">
                    <div class="card-header-info">
                        <span class="card-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 17h4V5H2v12h3" />
                                <path d="M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5" />
                                <circle cx="7.5" cy="17.5" r="2.5" />
                                <circle cx="17.5" cy="17.5" r="2.5" />
                            </svg>
                        </span>
                        <div>
                            <h2><?= t('cuadrilla.levante_titulo') ?></h2>
                            <p><?= t('cuadrilla.levante_sub') ?></p>
                        </div>
                    </div>
                    <div class="card-header-actions">
                        <button type="button" class="btn btn-primary" id="btn-iniciar-levante" hidden>
                            <?= t('cuadrilla.btn_iniciar_levante') ?>
                        </button>
                    </div>
                </div>

                <p class="empty-state" id="sin-levante-msg" hidden><?= t('cuadrilla.msg_sin_levante_activo') ?></p>
                <p class="empty-state" id="levante-pendiente-msg" hidden><?= t('cuadrilla.msg_levante_pendiente_iniciar') ?></p>

                <form id="form-levante" class="form-grid">
                    <div class="form-group">
                        <label for="contenedor-id"><?= t('cuadrilla.levante_id_label') ?></label>
                        <input type="text" id="contenedor-id" name="contenedor-id" list="lista-contenedores"
                            placeholder="<?= htmlspecialchars(t('cuadrilla.levante_id_ph')) ?>" autocomplete="off">
                        <datalist id="lista-contenedores"></datalist>
                    </div>

                        <div class="form-group">
                        <label for="contenedor-info"><?= t('cuadrilla.levante_contenedor_label') ?></label>
                        <div id="contenedor-info" class="info-box">
                            <span class="info-icon">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                            </span>
                            <span><?= t('cuadrilla.levante_seleccionar_placeholder') ?></span>
                        </div>
                    </div>

                    <!-- RF71: registro de motivo de no recolección -->
                                       <div class="form-group form-group-full">
                        <label><?= t('cuadrilla.levante_pregunta_recolectado') ?></label>
                        <div class="radio-toggle">
                            <label class="radio-option">
                                <input type="radio" name="recolectado" value="si" checked>
                                <?= t('cuadrilla.levante_opt_si') ?>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="recolectado" value="no">
                                <?= t('cuadrilla.levante_opt_no') ?>
                            </label>
                        </div>
                    </div>

                    <div class="form-group form-group-full" id="grupo-no-recolectado" hidden>
                        <div class="form-grid-inner">
                            <div class="form-group">
                                <label for="estado-encontrado"><?= t('cuadrilla.levante_estado_encontrado_label') ?></label>
                                <select id="estado-encontrado" name="estado-encontrado">
                                    <option value=""><?= t('cuadrilla.levante_estado_seleccionar') ?></option>
                                    <option value="sobrepeso"><?= t('cuadrilla.estado_sobrepeso') ?></option>
                                    <option value="riesgo_de_incendio"><?= t('cuadrilla.estado_riesgo_incendio') ?></option>
                                    <option value="acceso_bloqueado"><?= t('cuadrilla.estado_acceso_bloqueado') ?></option>
                                    <option value="contenedor_ausente"><?= t('cuadrilla.estado_contenedor_ausente') ?></option>
                                    <option value="otro"><?= t('cuadrilla.estado_otro') ?></option>
                                </select>
                                <p class="error-msg" id="estado-encontrado-error"></p>
                            </div>
                            
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="ingreso-peso"><?= t('cuadrilla.levante_peso_label') ?></label>
                        <input type="number" id="ingreso-peso" name="peso" min="1" placeholder="<?= htmlspecialchars(t('cuadrilla.levante_peso_ph')) ?>">
                    </div>

                    <div class="form-group">
                        <label for="observaciones"><?= t('cuadrilla.levante_observaciones_label') ?></label>
                        <textarea id="observaciones" name="observaciones"
                            placeholder="<?= htmlspecialchars(t('cuadrilla.levante_observaciones_ph')) ?>"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-full">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 17h4V5H2v12h3" />
                                <path d="M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5" />
                                <circle cx="7.5" cy="17.5" r="2.5" />
                                <circle cx="17.5" cy="17.5" r="2.5" />
                            </svg>
                            <?= t('cuadrilla.btn_registrar_levante') ?>
                        </button>
                    </div>
                </form>

                <!-- Lista de contenedores de la ruta del levante activo: solo estos se pueden registrar -->
                <div class="contenedores-ruta" id="contenedores-ruta-wrap" hidden>
                    <h3 class="contenedores-ruta-titulo"><?= t('cuadrilla.contenedores_ruta_titulo') ?></h3>
                    <ul class="contenedores-ruta-list" id="contenedores-ruta-list" data-paginar-lista></ul>
                </div>
            </div>
        </section>

        <!-- Rutas asignadas + camión (RF35, RF36) -->
        <section class="dash-section">
            <div class="panel-card">
                <div class="card-header">
                    <span class="card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="6" cy="19" r="2" />
                            <circle cx="18" cy="5" r="2" />
                            <path d="M12 19H8.5a2.5 2.5 0 0 1 0-5h7a2.5 2.5 0 0 0 0-5H6" />
                        </svg>
                    </span>
                        <div>
                        <h2><?= t('cuadrilla.rutas_titulo') ?></h2>
                        <p><?= t('cuadrilla.rutas_sub') ?></p>
                    </div>
                </div>

                <div class="routes-list" id="routes-list" data-paginar-lista>
                    <p class="empty-state" id="routes-empty"><?= t('cuadrilla.sin_rutas_asignadas') ?></p>
                </div>

                <a class="btn btn-outline btn-full" href="../../Backoffice/mapaCuadrilla.php">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6v15l6-3 6 3 6-3V3l-6 3-6-3-6 3Z" />
                        <path d="M9 3v15" />    
                        <path d="M15 6v15" />
                    </svg>
                    <?= t('cuadrilla.btn_ver_mapa') ?>
                </a>
            </div>
        </section>

    </main>

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

    <!-- RF36: Modal de reporte de desperfecto mecánico -->
    <div class="modal-overlay" id="modal-desperfecto">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-desperfecto-titulo">
            <h3 id="modal-desperfecto-titulo"><?= t('cuadrilla.modal_desperfecto_titulo') ?></h3>
            <p><?= t('cuadrilla.modal_desperfecto_camion') ?> <strong id="desperfecto-matricula"></strong></p>
            <div class="form-row">
                <label for="desperfecto-descripcion"><?= t('cuadrilla.modal_desperfecto_desc_label') ?></label>
                <textarea id="desperfecto-descripcion" rows="3"
                    placeholder="<?= htmlspecialchars(t('cuadrilla.modal_desperfecto_desc_ph')) ?>"></textarea>
                <p class="error-msg" id="desperfecto-error"></p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" id="desperfecto-cancelar"><?= t('crud.btn_cancelar') ?></button>
                <button type="button" class="btn btn-primary" id="desperfecto-enviar"><?= t('cuadrilla.btn_enviar_notificacion') ?></button>
            </div>
        </div>
    </div>

    <!-- Toast de confirmación -->
    <div class="toast" id="toast"></div>

    <script>

        const ID_CUADRILLA = <?= json_encode($idCuadrilla) ?>;
        const API_RECOLECCION = '/recolix/API_Recoleccion';
        const API_GESTION = '/recolix/API_Gestion';

        const I18N = <?= json_encode(t_array([
            'placeholderVacio' => 'cuadrilla.levante_seleccionar_placeholder',
            'sinDatos' => 'cuadrilla.contenedor_sin_datos',
            'ingresaId' => 'cuadrilla.msg_ingresa_id',
            'contenedorFueraDeRuta' => 'cuadrilla.msg_contenedor_fuera_de_ruta',
            'contenedoresRutaTitulo' => 'cuadrilla.contenedores_ruta_titulo',
            'badgeContenedorPendiente' => 'cuadrilla.badge_contenedor_pendiente',
            'badgeContenedorRecolectado' => 'cuadrilla.badge_contenedor_recolectado',
            'badgeContenedorNoRecolectado' => 'cuadrilla.badge_contenedor_no_recolectado',
            'seleccionaEstado' => 'cuadrilla.msg_selecciona_estado',
            'ingresaPeso' => 'cuadrilla.msg_ingresa_peso',
            'noRecolectado' => 'cuadrilla.msg_no_recolectado',
            'incIniciar' => 'cuadrilla.inc_btn_iniciar',
            'incCerrar' => 'cuadrilla.inc_btn_cerrar',
            'incIniciada' => 'cuadrilla.inc_msg_iniciada',
            'incCerrada' => 'cuadrilla.inc_msg_cerrada',
            'incEstadoAsignada' => 'cuadrilla.inc_estado_asignada',
            'incEstadoEnCurso' => 'cuadrilla.inc_estado_en_curso',
            'incContenedor' => 'cuadrilla.inc_contenedor',
            'incLlenado' => 'cuadrilla.inc_llenado',
            'incContFuncional' => 'cuadrilla.inc_cont_funcional',
            'incContRoto' => 'cuadrilla.inc_cont_roto',
            'incContDesbordado' => 'cuadrilla.inc_cont_desbordado',
            'incContInactivo' => 'cuadrilla.inc_cont_inactivo',
            'incContEnDeposito' => 'cuadrilla.inc_cont_en_deposito',
            'motivoRegistrado' => 'cuadrilla.msg_motivo_registrado',
            'levanteRegistrado' => 'cuadrilla.msg_levante_registrado',
            'debeIniciarRuta' => 'cuadrilla.msg_levante_pendiente_iniciar',
            'camionActualizado' => 'cuadrilla.msg_camion_actualizado',
            'describiDesperfecto' => 'cuadrilla.msg_describi_desperfecto',
            'desperfectoNotificado' => 'cuadrilla.msg_desperfecto_notificado',
            'levanteSigue' => 'cuadrilla.alerta_levante_sigue',
            'turnoFinalizo' => 'cuadrilla.alerta_turno_finalizo',
            'estadoPendiente' => 'cuadrilla.estado_pendiente_texto',
            'estadoEnCurso' => 'cuadrilla.estado_en_curso_texto',
            'levanteIniciado' => 'cuadrilla.msg_levante_iniciado',
            'errorCargar' => 'cuadrilla.msg_error_cargar',
            'sinCuadrillas' => 'cuadrilla.msg_sin_cuadrillas',
            'sinRutas' => 'cuadrilla.sin_rutas_asignadas',
            'rutaLabel' => 'cuadrilla.ruta_asignada',
            'turnoLabel' => 'cuadrilla.turno',
            'inicioLabel' => 'cuadrilla.inicio',
            'badgeCompletado' => 'cuadrilla.badge_levante_completado',
            'badgeEnCurso' => 'cuadrilla.badge_levante_en_curso',
            'badgePendiente' => 'cuadrilla.badge_levante_pendiente',
            'badgeSinLevante' => 'cuadrilla.badge_sin_levante',
            'camionDisponible' => 'cuadrilla.camion_disponible',
            'camionEnRuta' => 'cuadrilla.camion_en_ruta',
            'camionEnMantenimiento' => 'cuadrilla.camion_en_mantenimiento',
            'camionDeBaja' => 'cuadrilla.camion_de_baja',
            'camionEnDeposito' => 'cuadrilla.camion_en_deposito',
            'btnReportarDesperfecto' => 'cuadrilla.btn_reportar_desperfecto',
        ])) ?>;

        const ICONO_PIN = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>';
        const ICONO_CAMION = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17h4V5H2v12h3"/><path d="M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>';
        const ICONO_RUTA = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="19" r="2"/><circle cx="18" cy="5" r="2"/><path d="M12 19H8.5a2.5 2.5 0 0 1 0-5h7a2.5 2.5 0 0 0 0-5H6"/></svg>';

        const inputContenedor = document.getElementById('contenedor-id');
        const listaContenedores = document.getElementById('lista-contenedores');
        const infoBox = document.getElementById('contenedor-info');
        const formLevante = document.getElementById('form-levante');
        const btnIniciar = document.getElementById('btn-iniciar-levante');
        const routesList = document.getElementById('routes-list');
        const routesEmpty = document.getElementById('routes-empty');
        const seccionLevante = document.getElementById('seccion-levante');
        const alertaLevante = document.getElementById('alerta-levante');
        const alertaLevanteTexto = document.getElementById('alerta-levante-texto');
        const sinCuadrillaBanner = document.getElementById('sin-cuadrilla-banner');
        const sinLevanteMsg = document.getElementById('sin-levante-msg');
        const levantePendienteMsg = document.getElementById('levante-pendiente-msg');
        const contenedoresRutaWrap = document.getElementById('contenedores-ruta-wrap');
        const contenedoresRutaList = document.getElementById('contenedores-ruta-list');

        // Estado en memoria: lo que hoy hay en la base, no datos simulados.
        let cuadrilla = null;
        let levanteActivo = null;
        let CONTENEDORES = {}; // id_contenedor -> datos, se llena al cargar el levante activo

        function escapeHtml(valor) {
            const div = document.createElement('div');
            div.textContent = valor ?? '';
            return div.innerHTML;
        }

        // Toast
        const toast = document.getElementById('toast');
        let toastTimeout;

        function mostrarToast(mensaje, tipo) {
            toast.textContent = mensaje;
            toast.className = 'toast is-visible' + (tipo === 'warning' ? ' toast-warning' : '');
            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(function () {
                toast.classList.remove('is-visible');
            }, 3500);
        }

        async function apiFetch(url, options) {
            const res = await fetch(url, options);
            const json = await res.json();
            if (json.status !== 'success') {
                throw new Error(json.message || I18N.errorCargar);
            }
            return json.data;
        }

        function actualizarInfoContenedor(id) {
            if (!id) {
                infoBox.classList.remove('info-box--activo');
                infoBox.innerHTML = '<span class="info-icon">' + ICONO_PIN + '</span><span>' + I18N.placeholderVacio + '</span>';
                return;
            }
            const datos = CONTENEDORES[id];
            infoBox.classList.add('info-box--activo');
            if (datos) {
                infoBox.innerHTML = '<span class="info-icon">' + ICONO_PIN + '</span><span><strong>#' + id + '</strong> — ' + escapeHtml(datos.ubicacion) + '</span>';
            } else {
                infoBox.innerHTML = '<span class="info-icon">' + ICONO_PIN + '</span><span><strong>#' + id + '</strong> — ' + I18N.sinDatos + '</span>';
            }
        }

        // Se puede tipear cualquier ID a mano; el datalist solo sugiere los del levante activo
        inputContenedor.addEventListener('input', function () {
            actualizarInfoContenedor(inputContenedor.value.trim());
        });

        function habilitarFormLevante(habilitado) {
            Array.from(formLevante.elements).forEach(function (el) { el.disabled = !habilitado; });
        }

        // ---- Carga inicial ----
        // ID_CUADRILLA viene de sesión (USUARIO.id_cuadrilla, FK real). Si el
        // usuario no tiene cuadrilla asignada, se avisa y no se carga nada.
        // Incidencias asignadas por el administrador (RF10, RF13)
        const listaIncidencias = document.getElementById('incidencias-lista');
        const incidenciasVacio = document.getElementById('incidencias-vacio');

        async function cargarIncidencias() {
            try {
                const incidencias = await apiFetch(`${API_GESTION}/incidencias/obtenerPorCuadrilla/${cuadrilla.id_cuadrilla}`);
                renderIncidencias(incidencias);
            } catch (e) {
                listaIncidencias.innerHTML = '';
                incidenciasVacio.hidden = false;
                mostrarToast(e.message, 'warning');
            }
        }

        const estadoIncidencia = {
            asignada: I18N.incEstadoAsignada,
            en_curso: I18N.incEstadoEnCurso
        };

        const estadoContenedor = {
            funcional: I18N.incContFuncional,
            roto: I18N.incContRoto,
            desbordado: I18N.incContDesbordado,
            inactivo: I18N.incContInactivo,
            en_deposito: I18N.incContEnDeposito
        };

        function capitalizar(texto) {
            if (!texto) return '';
            return texto.charAt(0).toUpperCase() + texto.slice(1);
        }

        function renderIncidencias(incidencias) {
            listaIncidencias.innerHTML = '';
            incidenciasVacio.hidden = incidencias.length > 0;
            incidencias.forEach(function (inc) {
                const enCurso = inc.estado === 'en_curso';
                const fila = document.createElement('div');
                fila.className = 'route-row';
                const contenedores = (inc.contenedores || []).map(function (c) {
                    const direccion = [c.calle, c.num_calle].filter(Boolean).join(' ') || I18N.sinDatos;
                    const estado = estadoContenedor[c.estado] || c.estado;
                    return `<li>
                        <strong>${I18N.incContenedor} ${escapeHtml(String(c.id_contenedor))}</strong>
                        · ${escapeHtml(direccion)}
                        · ${escapeHtml(estado)}
                        · ${I18N.incLlenado} ${escapeHtml(String(c.nivel_llenado))}%
                        ${c.tipo_residuo ? '· ' + escapeHtml(c.tipo_residuo) : ''}
                    </li>`;
                }).join('');
                fila.innerHTML = `
                    <div>
                        <strong>${escapeHtml(capitalizar(inc.tipo))}</strong>
                        <div>${escapeHtml(inc.descripcion || '')}</div>
                        <small>${escapeHtml(estadoIncidencia[inc.estado] || inc.estado)}</small>
                        <ul class="incidencia-contenedores">${contenedores}</ul>
                    </div>
                    <button type="button" class="btn ${enCurso ? 'btn-primary' : 'btn-outline'}"
                        data-accion="${enCurso ? 'cerrar' : 'iniciar'}" data-id="${inc.id_incidencia}">
                        ${enCurso ? I18N.incCerrar : I18N.incIniciar}
                    </button>`;
                listaIncidencias.appendChild(fila);
            });
        }

        async function cerrarIncidencia(id) {
            await apiFetch(`${API_GESTION}/incidencias/cerrarPorCuadrilla/${id}`, { method: 'POST' });
        }

        listaIncidencias.addEventListener('click', async function (e) {
            const boton = e.target.closest('button[data-accion]');
            if (!boton) return;

            try {
                if (boton.dataset.accion === 'cerrar') {
                    await cerrarIncidencia(boton.dataset.id);
                    mostrarToast(I18N.incCerrada, 'success');
                } else {
                    await apiFetch(`${API_GESTION}/incidencias/iniciarAtencion/${boton.dataset.id}`, { method: 'POST' });
                    mostrarToast(I18N.incIniciada, 'success');
                }
                await cargarIncidencias();
            } catch (err) {
                mostrarToast(err.message, 'warning');
            }
        });

        async function init() {
            if (!ID_CUADRILLA) {
                sinCuadrillaBanner.hidden = false;
                seccionLevante.hidden = true;
                routesEmpty.hidden = false;
                return;
            }

            cuadrilla = { id_cuadrilla: ID_CUADRILLA };
            await cargarRutas();
            await cargarIncidencias();
        }

        // Registrar levante solo tiene sentido si la cuadrilla tiene al menos una
        // ruta asignada por un admin: sin ruta no hay nada para iniciar.
        async function cargarRutas() {
            let rutas = [];
            try {
                rutas = await apiFetch(`${API_RECOLECCION}/rutas/obtenerPorCuadrilla/${cuadrilla.id_cuadrilla}`);
            } catch (e) {
                mostrarToast(I18N.errorCargar, 'warning');
            }
            renderRutas(rutas);

            const tieneRutaAsignada = rutas.length > 0;
            seccionLevante.hidden = !tieneRutaAsignada;
            if (!tieneRutaAsignada) return;

            await actualizarLevanteActivo();
            await cargarAlertaLevante();
        }

        function badgeLevante(estado) {
            if (estado === 'completado') return { clase: 'badge-ok', texto: I18N.badgeCompletado };
            if (estado === 'en_curso') return { clase: 'badge-info', texto: I18N.badgeEnCurso };
            if (estado === 'pendiente') return { clase: 'badge-warning', texto: I18N.badgePendiente };
            return { clase: 'badge-neutral', texto: I18N.badgeSinLevante };
        }

        function renderRutas(rutas) {
            routesEmpty.hidden = rutas.length > 0;
            routesList.querySelectorAll('.route-row').forEach(function (el) { el.remove(); });

            rutas.forEach(function (r) {
                const badge = badgeLevante(r.estado_levante);
                const row = document.createElement('div');
                row.className = 'route-row';
                row.innerHTML = `
                    <div class="route-info">
                        <span class="route-item">${ICONO_RUTA} ${I18N.rutaLabel} <strong>${escapeHtml(r.nombre)}</strong></span>
                        ${r.turno ? `<span class="route-item">${I18N.turnoLabel} <strong>${escapeHtml(r.turno)}</strong></span>` : ''}
                        ${r.hora_salida ? `<span class="route-item">${I18N.inicioLabel} <strong>${r.hora_salida.slice(11, 16)}</strong></span>` : ''}
                        <span class="badge ${badge.clase}">${badge.texto}</span>
                    </div>
                    ${r.id_camion ? `
                    <div class="camion-info">
                        <span class="camion-matricula">${ICONO_CAMION} ${escapeHtml(r.matricula)}</span>
                        <select class="camion-estado" data-camion-id="${r.id_camion}">
                            <option value="disponible">${I18N.camionDisponible}</option>
                            <option value="en_ruta">${I18N.camionEnRuta}</option>
                            <option value="en_mantenimiento">${I18N.camionEnMantenimiento}</option>
                            <option value="de_baja">${I18N.camionDeBaja}</option>
                            <option value="en_deposito">${I18N.camionEnDeposito}</option>
                        </select>
                        <button type="button" class="btn btn-sm btn-outline btn-desperfecto" data-camion-id="${r.id_camion}" data-matricula="${escapeHtml(r.matricula)}">${I18N.btnReportarDesperfecto}</button>
                    </div>` : ''}
                `;
                routesList.appendChild(row);
                if (r.id_camion) {
                    const select = row.querySelector('.camion-estado');
                    select.value = r.estado_camion;
                    select.addEventListener('change', onCambioEstadoCamion);
                    row.querySelector('.btn-desperfecto').addEventListener('click', onAbrirDesperfecto);
                }
            });
        }

        // El levante activo de la cuadrilla, por LEVANTE.id_cuadrilla directo
        // (no se deriva de la lista de rutas: ruta_cuadrilla es una relación
        // aparte y puede no coincidir con quién ejecuta el levante).
        async function actualizarLevanteActivo() {
            let idLevante = null;
            try {
                idLevante = await apiFetch(`${API_RECOLECCION}/levantes/obtenerActivoPorCuadrilla/${cuadrilla.id_cuadrilla}`);
            } catch (e) {
                mostrarToast(I18N.errorCargar, 'warning');
            }

            if (idLevante) {
                sinLevanteMsg.hidden = true;
                formLevante.hidden = false;
                contenedoresRutaWrap.hidden = false;
                await cargarLevante(idLevante);
            } else {
                levanteActivo = null;
                CONTENEDORES = {};
                listaContenedores.innerHTML = '';
                btnIniciar.hidden = true;
                habilitarFormLevante(false);
                sinLevanteMsg.hidden = false;
                levantePendienteMsg.hidden = true;
                formLevante.hidden = true;
                contenedoresRutaWrap.hidden = true;
                contenedoresRutaList.innerHTML = '';
            }
        }

        // Lista visible de los contenedores que la ruta de este levante cubre. Es también
        // la única fuente de "qué se puede registrar": los botones acá son la forma normal
        // de elegir un contenedor, y CONTENEDORES (usado para validar el ID tipeado a mano)
        // sale de este mismo array.
        function renderContenedoresRuta(contenedores) {
            contenedoresRutaList.innerHTML = '';
            contenedores.forEach(function (c) {
                const ubicacion = [c.calle, c.num_calle].filter(Boolean).join(' ') || I18N.sinDatos;
                let badgeClase = 'badge-warning';
                let badgeTexto = I18N.badgeContenedorPendiente;
                if (c.hora_vaciado) {
                    badgeClase = 'badge-ok';
                    badgeTexto = I18N.badgeContenedorRecolectado;
                } else if (c.motivo) {
                    badgeClase = 'badge-danger';
                    badgeTexto = I18N.badgeContenedorNoRecolectado;
                }

                const li = document.createElement('li');
                li.className = 'contenedor-ruta-item';
                li.innerHTML = `
                    <span class="contenedor-ruta-info">
                        ${ICONO_PIN}
                        <strong>#${c.id_contenedor}</strong>
                        <span class="contenedor-ruta-ubicacion">${escapeHtml(ubicacion)}</span>
                    </span>
                    <span class="badge ${badgeClase}">${badgeTexto}</span>
                `;
                li.addEventListener('click', function () {
                    inputContenedor.value = c.id_contenedor;
                    actualizarInfoContenedor(String(c.id_contenedor));
                    inputContenedor.focus();
                });
                contenedoresRutaList.appendChild(li);
            });
        }

        async function cargarLevante(idLevante) {
            try {
                levanteActivo = await apiFetch(`${API_RECOLECCION}/levantes/${idLevante}`);
            } catch (e) {
                mostrarToast(I18N.errorCargar, 'warning');
                return;
            }

            CONTENEDORES = {};
            listaContenedores.innerHTML = '';
            (levanteActivo.contenedores || []).forEach(function (c) {
                const ubicacion = [c.calle, c.num_calle].filter(Boolean).join(' ') || I18N.sinDatos;
                CONTENEDORES[c.id_contenedor] = { ubicacion: ubicacion };
                const opt = document.createElement('option');
                opt.value = c.id_contenedor;
                listaContenedores.appendChild(opt);
            });
            renderContenedoresRuta(levanteActivo.contenedores || []);

            const esPendiente = levanteActivo.estado === 'pendiente';
            btnIniciar.hidden = !esPendiente;
            levantePendienteMsg.hidden = !esPendiente;
            // Se puede completar el formulario (tipear, elegir contenedor) aunque la ruta
            // todavía no haya arrancado; solo el envío final queda bloqueado hasta iniciarla
            // (ver validación en el submit de form-levante).
            habilitarFormLevante(true);
        }

        btnIniciar.addEventListener('click', async function () {
            if (!levanteActivo) return;
            try {
                await apiFetch(`${API_RECOLECCION}/levantes/iniciar/${levanteActivo.id_levante}`, { method: 'POST' });
                mostrarToast(I18N.levanteIniciado);
                await cargarRutas();
            } catch (e) {
                mostrarToast(e.message, 'warning');
            }
        });

        // RF71 — Registro de motivo de no recolección
        const grupoNoRecolectado = document.getElementById('grupo-no-recolectado');
        const estadoEncontrado = document.getElementById('estado-encontrado');
        const motivoInput = document.getElementById('observaciones');
        const pesoInput = document.getElementById('ingreso-peso');
        const estadoEncontradoError = document.getElementById('estado-encontrado-error');

        document.querySelectorAll('input[name="recolectado"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                const noRecolectado = document.querySelector('input[name="recolectado"]:checked').value === 'no';
                grupoNoRecolectado.hidden = !noRecolectado;
                if (!noRecolectado) {
                    estadoEncontrado.value = '';
                    estadoEncontradoError.textContent = '';
                }
            });
        });

        // Registrar levante (formulario principal) — contra el levante en curso real
        formLevante.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (!levanteActivo) {
                mostrarToast(I18N.errorCargar, 'warning');
                return;
            }
            if (levanteActivo.estado !== 'en_curso') {
                mostrarToast(I18N.debeIniciarRuta, 'warning');
                return;
            }

            const id = inputContenedor.value.trim();
            if (!id) {
                mostrarToast(I18N.ingresaId, 'warning');
                return;
            }
            if (!CONTENEDORES[id]) {
                mostrarToast(I18N.contenedorFueraDeRuta, 'warning');
                return;
            }

            const fueRecolectado = document.querySelector('input[name="recolectado"]:checked').value === 'si';
            let estadoEncontradoVal = 'todo_ok';
            let motivoVal = null;
            let pesoVal = null;

            if (!fueRecolectado) {
                estadoEncontradoError.textContent = '';
                let valido = true;
                if (!estadoEncontrado.value) {
                    estadoEncontradoError.textContent = I18N.seleccionaEstado;
                    valido = false;
                }
                if (!valido) return;
                estadoEncontradoVal = estadoEncontrado.value;
                // El motivo es una nota libre y opcional (ver placeholder del campo): null si no se cargó.
                motivoVal = motivoInput.value.trim() || null;
            } else {
                if (!pesoInput.value) {
                    mostrarToast(I18N.ingresaPeso, 'warning');
                    return;
                }
                pesoVal = parseInt(pesoInput.value, 10);
            }

            try {
                await apiFetch(`${API_RECOLECCION}/levantes/registrarContenedor`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        idLevante: levanteActivo.id_levante,
                        idContenedor: parseInt(id, 10),
                        estadoEncontrado: estadoEncontradoVal,
                        peso: pesoVal,
                        motivo: motivoVal
                    })
                });

                // Si la recolección resolvió alguna incidencia, sale de la lista.
                await cargarIncidencias();

                let mensaje;
                let tipoToast;
                if (fueRecolectado) {
                    mensaje = I18N.levanteRegistrado + ' #' + id + '.';
                } else {
                    const estadoTexto = estadoEncontrado.options[estadoEncontrado.selectedIndex].text;
                    mensaje = I18N.noRecolectado + ' #' + id + ' (' + estadoTexto + ').';
                    if (motivoVal) {
                        mensaje += ' ' + I18N.motivoRegistrado;
                    }
                    tipoToast = 'warning';
                }

                formLevante.reset();
                actualizarInfoContenedor('');
                grupoNoRecolectado.hidden = true;
                await cargarLevante(levanteActivo.id_levante);

                // Al registrar el último contenedor pendiente el backend cierra el levante
                // solo: se vuelve a cargar el panel para que la ruta deje de aparecer.
                if (levanteActivo && levanteActivo.estado === 'completado') {
                    mensaje += ' ' + I18N.badgeCompletado + '.';
                    await cargarRutas();
                }
                mostrarToast(mensaje, tipoToast);
            } catch (e) {
                mostrarToast(e.message, 'warning');
            }
        });

        // RF35 — Actualización del estado del camión asignado contra la API real
        async function onCambioEstadoCamion(e) {
            const select = e.target;
            const idCamion = select.dataset.camionId;
            const nuevoEstado = select.value;
            try {
                const camion = await apiFetch(`${API_RECOLECCION}/camiones/${idCamion}`);
                await apiFetch(`${API_RECOLECCION}/camiones/${idCamion}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        matricula: camion.matricula,
                        tipoCarga: camion.tipo_carga,
                        capacidad: camion.capacidad,
                        anio: camion.anio,
                        modelo: camion.modelo,
                        estado: nuevoEstado
                    })
                });
                mostrarToast(I18N.camionActualizado + ' ' + camion.matricula + '.');
            } catch (err) {
                mostrarToast(err.message, 'warning');
            }
        }

        // RF36 — Reporte de desperfecto mecánico, contra mantenimientos reales
        const modalDesperfecto = document.getElementById('modal-desperfecto');
        const desperfectoMatricula = document.getElementById('desperfecto-matricula');
        const desperfectoDescripcion = document.getElementById('desperfecto-descripcion');
        const desperfectoError = document.getElementById('desperfecto-error');
        const desperfectoCancelar = document.getElementById('desperfecto-cancelar');
        const desperfectoEnviar = document.getElementById('desperfecto-enviar');
        let camionDesperfectoId = null;
        let camionDesperfectoMatricula = null;

        function onAbrirDesperfecto(e) {
            camionDesperfectoId = e.currentTarget.dataset.camionId;
            camionDesperfectoMatricula = e.currentTarget.dataset.matricula;
            desperfectoMatricula.textContent = camionDesperfectoMatricula;
            desperfectoDescripcion.value = '';
            desperfectoError.textContent = '';
            modalDesperfecto.classList.add('is-open');
        }

        desperfectoCancelar.addEventListener('click', function () {
            modalDesperfecto.classList.remove('is-open');
        });

        modalDesperfecto.addEventListener('click', function (e) {
            if (e.target === modalDesperfecto) {
                modalDesperfecto.classList.remove('is-open');
            }
        });

        desperfectoEnviar.addEventListener('click', async function () {
            if (!desperfectoDescripcion.value.trim()) {
                desperfectoError.textContent = I18N.describiDesperfecto;
                return;
            }
            const ahora = new Date().toISOString().slice(0, 19);
            try {
                await apiFetch(`${API_RECOLECCION}/mantenimientos`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        camionId: parseInt(camionDesperfectoId, 10),
                        tipo: 'correctivo',
                        descripcion: desperfectoDescripcion.value.trim(),
                        fechaInicio: ahora,
                        fechaFin: null,
                        costo: 0
                    })
                });
                // RF37: además del mantenimiento, se notifica a los administradores.
                await apiFetch(`${API_RECOLECCION}/mantenimientos/notificarDesperfecto`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        camionId: parseInt(camionDesperfectoId, 10),
                        descripcion: desperfectoDescripcion.value.trim()
                    })
                });
                modalDesperfecto.classList.remove('is-open');
                mostrarToast(I18N.desperfectoNotificado + ' ' + camionDesperfectoMatricula + '.', 'warning');
                await cargarRutas();
            } catch (err) {
                desperfectoError.textContent = err.message;
            }
        });

        // RF52 — Alerta generada por el sistema: levante propio pendiente/en curso con el turno vencido
        async function cargarAlertaLevante() {
            let vencidos = [];
            try {
                vencidos = await apiFetch(`${API_RECOLECCION}/levantes/obtenerPendientesVencidos`);
            } catch (e) {
                return;
            }
            const propio = vencidos.find(function (l) { return l.id_cuadrilla == cuadrilla.id_cuadrilla; });
            if (propio) {
                const estadoTexto = propio.estado === 'en_curso' ? I18N.estadoEnCurso : I18N.estadoPendiente;
                alertaLevanteTexto.textContent = I18N.levanteSigue + ' ' + propio.ruta_nombre + ' ' + estadoTexto + ' ' + I18N.turnoFinalizo;
                alertaLevante.hidden = false;
            } else {
                alertaLevante.hidden = true;
            }
        }

        function toggleMobileMenu() {
            document.getElementById('navMobile').classList.toggle('nav-mobile-open');
        }

        init();
    </script>

    <script src="../../Backoffice/js/paginacion.js"></script>
</body>

</html>