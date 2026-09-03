<?php require_once __DIR__ . '/../../i18n/i18n.php'; ?>
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
                <?php include __DIR__ . '/../../i18n/switcher.php'; ?>
                <a href="../Vista/perfilCuadrilla.html" class="btn-user" title="<?= htmlspecialchars(t('backoffice.nav_perfil_usuario')) ?>">
                    <img src="../../API_Recoleccion/Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
                </a>
                <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="Menú">
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

        <!-- RF52: alerta generada por el sistema cuando un levante queda pendiente/en curso pasado el turno -->
        <div class="alert-banner" id="alerta-levante" hidden>
            <span class="alert-banner-icon">⚠</span>
            <span id="alerta-levante-texto"></span>
        </div>

        <!-- Registrar levante -->
        <section class="dash-section" id="seccion-levante">
            <div class="panel-card">
                <div class="card-header">
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

                <form id="form-levante" class="form-grid">
                    <div class="form-group">
                        <label for="contenedor-id"><?= t('cuadrilla.levante_id_label') ?></label>
                        <input type="text" id="contenedor-id" name="contenedor-id" list="lista-contenedores"
                            placeholder="<?= htmlspecialchars(t('cuadrilla.levante_id_ph')) ?>" autocomplete="off">
                        <datalist id="lista-contenedores">
                            <option value="CT-4587">
                            <option value="CT-6123">
                            <option value="CT-7790">
                        </datalist>
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
                            <div class="form-group">
                                <label for="motivo"><?= t('cuadrilla.levante_motivo_label') ?></label>
                                <input type="text" id="motivo" name="motivo" maxlength="150"
                                    placeholder="<?= htmlspecialchars(t('cuadrilla.levante_motivo_ph')) ?>">
                                <p class="error-msg" id="motivo-error"></p>
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

                <div class="routes-list" id="routes-list">
                    <div class="route-row">
                        <div class="route-info">
                            <span class="route-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="6" cy="19" r="2" />
                                    <circle cx="18" cy="5" r="2" />
                                    <path d="M12 19H8.5a2.5 2.5 0 0 1 0-5h7a2.5 2.5 0 0 0 0-5H6" />
                                </svg>
                            <?= t('cuadrilla.ruta_asignada') ?> <strong>Circuito 24</strong>
                            </span>
                            <span class="route-item"><?= t('cuadrilla.turno') ?> <strong><?= t('cuadrilla.turno_matutino') ?></strong></span>
                            <span class="route-item"><?= t('cuadrilla.inicio') ?> <strong>08:00</strong></span>
                            <span class="badge badge-ok"><?= t('cuadrilla.badge_levante_completado') ?></span>
                        </div>
                        <div class="camion-info">
                            <span class="camion-matricula">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10 17h4V5H2v12h3" />
                                    <path d="M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5" />
                                    <circle cx="7.5" cy="17.5" r="2.5" />
                                    <circle cx="17.5" cy="17.5" r="2.5" />
                                </svg>
                                SBC 1234
                            </span>
                                <select class="camion-estado" data-camion="SBC 1234">
                                <option value="disponible"><?= t('cuadrilla.camion_disponible') ?></option>
                                <option value="en_ruta" selected><?= t('cuadrilla.camion_en_ruta') ?></option>
                                <option value="en_mantenimiento"><?= t('cuadrilla.camion_en_mantenimiento') ?></option>
                                <option value="de_baja"><?= t('cuadrilla.camion_de_baja') ?></option>
                            </select>
                            <button type="button" class="btn btn-sm btn-outline btn-desperfecto"
                                data-camion="SBC 1234"><?= t('cuadrilla.btn_reportar_desperfecto') ?></button>
                        </div>
                    </div>

                    <div class="route-row">
                        <div class="route-info">
                            <span class="route-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="6" cy="19" r="2" />
                                    <circle cx="18" cy="5" r="2" />
                                    <path d="M12 19H8.5a2.5 2.5 0 0 1 0-5h7a2.5 2.5 0 0 0 0-5H6" />
                                </svg>
                            <?= t('cuadrilla.ruta_asignada') ?> <strong>Circuito 31</strong>
                            </span>
                            <span class="route-item"><?= t('cuadrilla.turno') ?> <strong><?= t('cuadrilla.turno_matutino') ?></strong></span>
                            <span class="route-item"><?= t('cuadrilla.inicio') ?> <strong>13:30</strong></span>
                            <span class="badge badge-info"><?= t('cuadrilla.badge_levante_en_curso') ?></span>
                        </div>
                        <div class="camion-info">
                            <span class="camion-matricula">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10 17h4V5H2v12h3" />
                                    <path d="M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5" />
                                    <circle cx="7.5" cy="17.5" r="2.5" />
                                    <circle cx="17.5" cy="17.5" r="2.5" />
                                </svg>
                                STY 5678
                            </span>
                                <select class="camion-estado" data-camion="STY 5678">
                                <option value="disponible"><?= t('cuadrilla.camion_disponible') ?></option>
                                <option value="en_ruta" selected><?= t('cuadrilla.camion_en_ruta') ?></option>
                                <option value="en_mantenimiento"><?= t('cuadrilla.camion_en_mantenimiento') ?></option>
                                <option value="de_baja"><?= t('cuadrilla.camion_de_baja') ?></option>
                            </select>
                            <button type="button" class="btn btn-sm btn-outline btn-desperfecto"
                                data-camion="STY 5678"><?= t('cuadrilla.btn_reportar_desperfecto') ?></button>
                        </div>
                    </div>  
                </div>

                <a class="btn btn-outline btn-full" href="#">
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

        <!-- Lista de urgentes -->
        <section class="dash-section">
            <div class="panel-card">
                <div class="card-header">
                    <span class="card-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                        </svg>
                    </span>
                                       <div>
                        <h2><?= t('cuadrilla.urgentes_titulo') ?></h2>
                        <p><?= t('cuadrilla.urgentes_sub') ?></p>
                    </div>
                </div>

                <div class="table-wrapper">
                    <table class="table" id="tabla-urgentes">
                                                <thead>
                            <tr>
                                <th><?= t('cuadrilla.th_contenedor') ?></th>
                                <th><?= t('cuadrilla.th_ubicacion') ?></th>
                                <th><?= t('cuadrilla.th_tipo') ?></th>
                                <th><?= t('cuadrilla.th_dias_sin_recoleccion') ?></th>
                                <th><?= t('cuadrilla.th_nivel') ?></th>
                                <th><?= t('cuadrilla.th_accion') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr data-id="CT-4587">
                                <td><span class="contenedor-id"><span class="alert-icon alert-critical">!</span>
                                        CT-4587</span></td>
                                <td>Av. Italia 1234</td>
                                <td>Orgánico</td>
                                <td><span class="badge badge-danger">3 <?= t('cuadrilla.dias') ?></span></td>
                                <td><span class="badge badge-danger">100%</span></td>
                                <td><button type="button" class="btn btn-sm btn-outline btn-registrar"><?= t('cuadrilla.btn_registrar') ?></button></td>
                            </tr>
                                                        <tr data-id="CT-6123">
                                <td><span class="contenedor-id"><span class="alert-icon alert-critical">!</span>
                                        CT-6123</span></td>
                                <td>Bvr. Artigas 789</td>
                                <td>Plástico</td>
                                <td><span class="badge badge-warning">2 <?= t('cuadrilla.dias') ?></span></td>
                                <td><span class="badge badge-warning">90%</span></td>
                                <td><button type="button"
                                        class="btn btn-sm btn-outline btn-registrar"><?= t('cuadrilla.btn_registrar') ?></button></td>
                            </tr>
                            <tr data-id="CT-7790">
                                <td><span class="contenedor-id"><span class="alert-icon alert-warning">!</span>
                                        CT-7790</span></td>
                                <td>Cno. Maldonado 456</td>
                                <td>Mixto</td>
                                <td><span class="badge badge-warning">2 <?= t('cuadrilla.dias') ?></span></td>
                                <td><span class="badge badge-warning">85%</span></td>
                                <td><button type="button"
                                        class="btn btn-sm btn-outline btn-registrar"><?= t('cuadrilla.btn_registrar') ?></button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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

        const I18N = <?= json_encode(t_array([
            'placeholderVacio' => 'cuadrilla.levante_seleccionar_placeholder',
            'sinDatos' => 'cuadrilla.contenedor_sin_datos',
            'ingresaId' => 'cuadrilla.msg_ingresa_id',
            'seleccionaEstado' => 'cuadrilla.msg_selecciona_estado',
            'ingresaMotivo' => 'cuadrilla.msg_ingresa_motivo',
            'noRecolectado' => 'cuadrilla.msg_no_recolectado',
            'motivoRegistrado' => 'cuadrilla.msg_motivo_registrado',
            'levanteRegistrado' => 'cuadrilla.msg_levante_registrado',
            'sinContenedoresUrgentes' => 'cuadrilla.sin_contenedores_urgentes',
            'camionActualizado' => 'cuadrilla.msg_camion_actualizado',
            'describiDesperfecto' => 'cuadrilla.msg_describi_desperfecto',
            'desperfectoNotificado' => 'cuadrilla.msg_desperfecto_notificado',
            'levanteSigue' => 'cuadrilla.alerta_levante_sigue',
            'turnoFinalizo' => 'cuadrilla.alerta_turno_finalizo',
            'estadoPendiente' => 'cuadrilla.estado_pendiente_texto',
            'estadoEnCurso' => 'cuadrilla.estado_en_curso_texto',
        ])) ?>;

               const CONTENEDORES = {
            'CT-4587': { ubicacion: 'Av. Italia 1234', tipo: 'Orgánico' },
            'CT-6123': { ubicacion: 'Bvr. Artigas 789', tipo: 'Plástico' },
            'CT-7790': { ubicacion: 'Cno. Maldonado 456', tipo: 'Mixto' }
        };

        const inputContenedor = document.getElementById('contenedor-id');
        const listaContenedores = document.getElementById('lista-contenedores');
        const infoBox = document.getElementById('contenedor-info');
        const formLevante = document.getElementById('form-levante');

        const ICONO_PIN = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>';

            function actualizarInfoContenedor(id) {
            if (!id) {
                infoBox.classList.remove('info-box--activo');
                infoBox.innerHTML = '<span class="info-icon">' + ICONO_PIN + '</span><span>' + I18N.placeholderVacio + '</span>';
                return;
            }
            const datos = CONTENEDORES[id];
            infoBox.classList.add('info-box--activo');
            if (datos) {
                infoBox.innerHTML = '<span class="info-icon">' + ICONO_PIN + '</span><span><strong>' + id + '</strong> — ' + datos.ubicacion + ' · ' + datos.tipo + '</span>';
            } else {
                infoBox.innerHTML = '<span class="info-icon">' + ICONO_PIN + '</span><span><strong>' + id + '</strong> — ' + I18N.sinDatos + '</span>';
            }
        }

        // Se puede tipear cualquier ID a mano; el datalist solo sugiere los conocidos
        inputContenedor.addEventListener('input', function () {
            const id = inputContenedor.value.trim().toUpperCase();
            actualizarInfoContenedor(id);
        });

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

        // RF71 — Registro de motivo de no recolección
        const grupoNoRecolectado = document.getElementById('grupo-no-recolectado');
        const estadoEncontrado = document.getElementById('estado-encontrado');
        const motivoInput = document.getElementById('motivo');
        const estadoEncontradoError = document.getElementById('estado-encontrado-error');
        const motivoError = document.getElementById('motivo-error');

        document.querySelectorAll('input[name="recolectado"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                const noRecolectado = document.querySelector('input[name="recolectado"]:checked').value === 'no';
                grupoNoRecolectado.hidden = !noRecolectado;
                if (!noRecolectado) {
                    estadoEncontrado.value = '';
                    motivoInput.value = '';
                    estadoEncontradoError.textContent = '';
                    motivoError.textContent = '';
                }
            });
        });

        // Registrar levante (formulario principal)
        formLevante.addEventListener('submit', function (e) {
            e.preventDefault();
            const id = inputContenedor.value.trim().toUpperCase();
            if (!id) {
                mostrarToast(I18N.ingresaId, 'warning');
                return;
            }

            const fueRecolectado = document.querySelector('input[name="recolectado"]:checked').value === 'si';
            let mensajeToast;
            let tipoToast;

            if (!fueRecolectado) {
                estadoEncontradoError.textContent = '';
                motivoError.textContent = '';
                let valido = true;
                   if (!estadoEncontrado.value) {
                    estadoEncontradoError.textContent = I18N.seleccionaEstado;
                    valido = false;
                }
                if (!motivoInput.value.trim()) {
                    motivoError.textContent = I18N.ingresaMotivo;
                    valido = false;
                }
                if (!valido) return;

                const estadoTexto = estadoEncontrado.options[estadoEncontrado.selectedIndex].text;
                mensajeToast = I18N.noRecolectado + ' ' + id + ' (' + estadoTexto + '). ' + I18N.motivoRegistrado;
                tipoToast = 'warning';
            } else {
                mensajeToast = I18N.levanteRegistrado + ' ' + id + '.';
            }

            eliminarContenedorPendiente(id);
            formLevante.reset();
            actualizarInfoContenedor('');
            grupoNoRecolectado.hidden = true;
            mostrarToast(mensajeToast, tipoToast);
        });

                // Lista de Urgentes — "Registrar" lleva al formulario de arriba con el ID precargado
        const tablaUrgentes = document.getElementById('tabla-urgentes');
        const seccionLevante = document.getElementById('seccion-levante');

        function verificarTablaVacia() {
            const tbody = tablaUrgentes.querySelector('tbody');
            if (!tbody.querySelector('tr')) {
                const fila = document.createElement('tr');
                fila.innerHTML = '<td colspan="6" class="empty-state">' + I18N.sinContenedoresUrgentes + '</td>';
                tbody.appendChild(fila);
            }
        }

        // Saca un contenedor ya registrado tanto del input de "ID del contenedor"
        // (y su sugerencia en el datalist) como de la Lista de Urgentes,
        // sin importar desde dónde se registró el levante
        function eliminarContenedorPendiente(id) {
            delete CONTENEDORES[id];

            const opcion = listaContenedores.querySelector('option[value="' + id + '"]');
            if (opcion) opcion.remove();

            if (inputContenedor.value.trim().toUpperCase() === id) {
                inputContenedor.value = '';
            }

            const fila = tablaUrgentes.querySelector('tr[data-id="' + id + '"]');
            if (fila) fila.remove();

            verificarTablaVacia();
        }

        tablaUrgentes.querySelectorAll('.btn-registrar').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const id = btn.closest('tr').getAttribute('data-id');
                inputContenedor.value = id;
                actualizarInfoContenedor(id);
                seccionLevante.scrollIntoView({ behavior: 'smooth', block: 'start' });
                inputContenedor.focus();
            });
        });

        // RF35 — Actualización del estado del camión asignado
        document.querySelectorAll('.camion-estado').forEach(function (select) {
            select.addEventListener('change', function () {
                const matricula = select.getAttribute('data-camion');
                const estadoTexto = select.options[select.selectedIndex].text;
                mostrarToast(I18N.camionActualizado + ' ' + matricula + ': "' + estadoTexto + '".');
            });
        });

        // RF36 — Reporte de desperfecto mecánico
        const modalDesperfecto = document.getElementById('modal-desperfecto');
        const desperfectoMatricula = document.getElementById('desperfecto-matricula');
        const desperfectoDescripcion = document.getElementById('desperfecto-descripcion');
        const desperfectoError = document.getElementById('desperfecto-error');
        const desperfectoCancelar = document.getElementById('desperfecto-cancelar');
        const desperfectoEnviar = document.getElementById('desperfecto-enviar');
        let camionDesperfecto = null;

        document.querySelectorAll('.btn-desperfecto').forEach(function (btn) {
            btn.addEventListener('click', function () {
                camionDesperfecto = btn.getAttribute('data-camion');
                desperfectoMatricula.textContent = camionDesperfecto;
                desperfectoDescripcion.value = '';
                desperfectoError.textContent = '';
                modalDesperfecto.classList.add('is-open');
            });
        });

        desperfectoCancelar.addEventListener('click', function () {
            modalDesperfecto.classList.remove('is-open');
        });

        modalDesperfecto.addEventListener('click', function (e) {
            if (e.target === modalDesperfecto) {
                modalDesperfecto.classList.remove('is-open');
            }
        });

               desperfectoEnviar.addEventListener('click', function () {
            if (!desperfectoDescripcion.value.trim()) {
                desperfectoError.textContent = I18N.describiDesperfecto;
                return;
            }
            modalDesperfecto.classList.remove('is-open');
            mostrarToast(I18N.desperfectoNotificado + ' ' + camionDesperfecto + '.', 'warning');
        });

        // RF52 — Alerta generada por el sistema: levante pendiente/en curso con el turno ya finalizado
        const RUTAS_INFO = [
            { circuito: 'Circuito 24', turnoFinalizado: true, estadoLevante: 'completado' },
            { circuito: 'Circuito 31', turnoFinalizado: true, estadoLevante: 'en_curso' }
        ];

        const alertaLevante = document.getElementById('alerta-levante');
        const alertaLevanteTexto = document.getElementById('alerta-levante-texto');

        const rutaConProblema = RUTAS_INFO.find(function (ruta) {
            return ruta.turnoFinalizado && (ruta.estadoLevante === 'pendiente' || ruta.estadoLevante === 'en_curso');
        });

            if (rutaConProblema) {
            const estadoTexto = rutaConProblema.estadoLevante === 'en_curso' ? I18N.estadoEnCurso : I18N.estadoPendiente;
            alertaLevanteTexto.textContent = I18N.levanteSigue + ' ' + rutaConProblema.circuito + ' ' + estadoTexto + ' ' + I18N.turnoFinalizo;
            alertaLevante.hidden = false;
        }

        function toggleMobileMenu() {
            document.getElementById('navMobile').classList.toggle('nav-mobile-open');
        }
    </script>

</body>

</html>