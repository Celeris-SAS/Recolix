<?php
session_start();
require_once __DIR__ . '/../i18n/i18n.php';

if (!isset($_SESSION['ci_usuario'])) {
    header("Location: ../LandingPage/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($RECOLIX_IDIOMA) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('perfil.title') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="Vista/styles_vecino.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>
    <header class="site-header" id="siteHeader">
        <div class="container header-inner">
            <a href="vecino.php" class="logo">
                <img src="Vista/Logo.png" alt="Logo Recolix" class="logo-image" />
                <div class="logo-text">Recolix</div>
            </a>
            <nav class="main-nav" id="mainNav">
                <ul>
                    <li><a href="../LandingPage/index_LandingPage.html"><?= t('common.nav_inicio') ?></a></li>
                    <li><a href="../LandingPage/mapa.html"><?= t('common.nav_mapa') ?></a></li>
                </ul>
            </nav>
            <div class="nav-right">
                <?php include __DIR__ . '/../i18n/switcher.php'; ?>
                <a href="vecino.php" class="btn-user" title="<?= htmlspecialchars(t('backoffice.nav_perfil_usuario')) ?>">
                    <img src="Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
                </a>
                
            </div>
        </div>
    </header>
     <main class="container dash-main">

        <a href="../LandingPage/index_LandingPage.html" class="btn btn-outline btn-sm btn-volver-inicio"><?= t('vecino.btn_volver_inicio') ?></a>

        <section class="dash-section" id="seccionPerfil">
            <div class="section-header">
                <div>
                    <p class="section-eyebrow"><?= t('perfil.eyebrow') ?></p>
                    <h2><?= t('perfil.titulo') ?></h2>
                </div>
                <button class="btn btn-outline btn-sm" id="editarPerfilBtn"><?= t('perfil.btn_editar') ?></button>
            </div>

            <div class="panel-card" id="perfilVista">
                <div class="perfil-grid">
                    <div class="perfil-campo">
                        <p class="campo-label"><?= t('perfil.label_nombre_completo') ?></p>
                        <p class="campo-valor" id="perfilNombre">—</p>
                    </div>
                    <div class="perfil-campo">
                        <p class="campo-label"><?= t('perfil.label_cedula') ?></p>
                        <p class="campo-valor" id="perfilCI">—</p>
                    </div>
                    <div class="perfil-campo">
                        <p class="campo-label"><?= t('perfil.label_email') ?></p>
                        <p class="campo-valor" id="perfilEmail">—</p>
                    </div>
                    <div class="perfil-campo">
                        <p class="campo-label"><?= t('perfil.label_telefono') ?></p>
                        <p class="campo-valor" id="perfilTel">—</p>
                    </div>
                    <div class="perfil-campo">
                        <p class="campo-label"><?= t('vecino.label_municipio') ?></p>
                        <p class="campo-valor" id="perfilMunicipio">—</p>
                    </div>
                </div>
                <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap;">
                    <button class="btn btn-outline btn-sm" id="cambiarContraseniaBtn"><?= t('perfil.btn_cambiar_contrasenia') ?></button>
                    <button class="btn btn-primary btn-sm" id="asignarMunicipioBtn"><?= t('vecino.btn_agregar_municipio') ?></button>
                </div>
            </div>

            <div class="panel-card" id="municipioForm" hidden>
                <h3 id="municipioFormTitle"><?= t('vecino.btn_agregar_municipio') ?></h3>
                <p class="form-nota" id="municipioFormNota"><?= t('vecino.municipio_form_nota_agregar') ?></p>
                <div class="form-row">
                    <label for="municipioSelect"><?= t('vecino.label_municipio') ?></label>
                    <select id="municipioSelect">
                        <option value=""><?= t('vecino.opt_seleccionar_municipio') ?></option>
                        <option value="Municipio A">Municipio A</option>
                        <option value="Municipio B">Municipio B</option>
                        <option value="Municipio C">Municipio C</option>
                        <option value="Municipio CH">Municipio CH</option>
                        <option value="Municipio D">Municipio D</option>
                        <option value="Municipio E">Municipio E</option>
                        <option value="Municipio F">Municipio F</option>
                        <option value="Municipio G">Municipio G</option>
                    </select>
                    <p class="error-msg" id="municipioSelectError"></p>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" id="guardarMunicipioBtn"><?= t('vecino.btn_guardar_municipio') ?></button>
                    <button class="btn btn-outline btn-sm" id="cancelarMunicipioBtn"><?= t('perfil.btn_cancelar') ?></button>
                </div>
                <p class="form-feedback" id="municipioFeedback"></p>
            </div>

            <div class="panel-card" id="perfilForm" hidden>
                <h3><?= t('perfil.titulo_editar') ?></h3>
                <p class="form-nota"><?= t('perfil.nota_editar') ?></p>
                <div class="form-row">
                    <label for="editEmail"><?= t('perfil.field_email') ?></label>
                    <input type="email" id="editEmail" name="email" placeholder="<?= htmlspecialchars(t('perfil.field_email_ph')) ?>">
                    <p class="error-msg" id="editEmailError"></p>
                </div>
                <div class="form-row">
                    <label for="editTel"><?= t('perfil.field_tel') ?></label>
                    <input type="tel" id="editTel" name="tel" placeholder="<?= htmlspecialchars(t('perfil.field_tel_ph')) ?>">
                    <p class="error-msg" id="editTelError"></p>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" id="guardarPerfilBtn"><?= t('perfil.btn_guardar_cambios') ?></button>
                    <button class="btn btn-outline btn-sm" id="cancelarPerfilBtn"><?= t('perfil.btn_cancelar') ?></button>
                </div>
                <p class="form-feedback" id="perfilFeedback"></p>
            </div>

            <div class="panel-card" id="contraseniaForm" hidden>
                <h3><?= t('perfil.titulo_cambiar_contrasenia') ?></h3>
                <div class="form-row">
                    <label for="contraActual"><?= t('perfil.field_contra_actual') ?></label>
                    <input type="password" id="contraActual" placeholder="<?= htmlspecialchars(t('perfil.field_contra_actual_ph')) ?>">
                    <p class="error-msg" id="contraActualError"></p>
                </div>
                <div class="form-row">
                    <label for="contraNueva"><?= t('perfil.field_contra_nueva') ?></label>
                    <input type="password" id="contraNueva" placeholder="<?= htmlspecialchars(t('perfil.field_contra_nueva_ph')) ?>">
                    <p class="error-msg" id="contraNuevaError"></p>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" id="guardarContraBtn"><?= t('perfil.btn_cambiar_contrasenia') ?></button>
                    <button class="btn btn-outline btn-sm" id="cancelarContraBtn"><?= t('perfil.btn_cancelar') ?></button>
                </div>
                <p class="form-feedback" id="contraFeedback"></p>
            </div>
        </section>

        <section class="dash-section" id="seccionIncidencias">
            <div class="section-header">
                <div>
                    <p class="section-eyebrow"><?= t('vecino.eyebrow_incidencias') ?></p>
                    <h2><?= t('vecino.titulo_incidencias') ?></h2>
                </div>
                <button class="btn btn-primary btn-sm" id="nuevaIncidenciaBtn"><?= t('vecino.btn_nueva_incidencia') ?></button>
            </div>

            <div class="panel-card" id="nuevaIncidenciaForm" hidden>
                <h3><?= t('vecino.nueva_incidencia_titulo') ?></h3>
                <div class="form-row">
                    <label for="incTipo"><?= t('vecino.nueva_incidencia_tipo') ?></label>
                    <select id="incTipo">
                        <option value="rotura"><?= t('vecino.nueva_incidencia_tipo_rotura') ?></option>
                        <option value="desborde"><?= t('vecino.nueva_incidencia_tipo_desborde') ?></option>
                        <option value="falta_recoleccion"><?= t('vecino.nueva_incidencia_tipo_falta_recoleccion') ?></option>
                        <option value="vandalismo"><?= t('vecino.nueva_incidencia_tipo_vandalismo') ?></option>
                        <option value="otro"><?= t('vecino.nueva_incidencia_tipo_otro') ?></option>
                    </select>
                </div>
                <div class="form-row">
                    <label for="incContenedor"><?= t('vecino.nueva_incidencia_contenedor') ?></label>
                    <p class="form-nota" id="incUbicacionStatus"><?= t('vecino.nueva_incidencia_detectando_ubicacion') ?></p>
                    <div id="incMapaContenedor" class="modal-map"></div>
                    <p class="form-nota" id="incContenedorSeleccionado" style="display:none"></p>
                    <input type="hidden" id="incContenedor">
                    <p class="form-nota"><?= t('vecino.nueva_incidencia_contenedor_ayuda') ?></p>
                    <p class="error-msg" id="incContenedorError"></p>
                </div>
                <div class="form-row">
                    <label for="incDescripcion"><?= t('vecino.nueva_incidencia_descripcion') ?></label>
                    <textarea id="incDescripcion" rows="3" maxlength="500" placeholder="<?= htmlspecialchars(t('vecino.nueva_incidencia_descripcion_placeholder')) ?>"></textarea>
                </div>
                <div class="form-row">
                    <label for="incFoto"><?= t('vecino.nueva_incidencia_foto') ?></label>
                    <input type="file" id="incFoto" accept="image/jpeg,image/png">
                    <p class="form-nota"><?= t('vecino.nueva_incidencia_foto_ayuda') ?></p>
                    <p class="error-msg" id="incFotoError"></p>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" id="guardarIncidenciaBtn"><?= t('vecino.btn_reportar_incidencia') ?></button>
                    <button class="btn btn-outline btn-sm" id="cancelarIncidenciaBtn"><?= t('perfil.btn_cancelar') ?></button>
                </div>
                <p class="form-feedback" id="incidenciaFeedback"></p>
            </div>

            <div id="incidenciasLista">
                <p class="estado-carga"><?= t('vecino.cargando_incidencias') ?></p>
            </div>
        </section>

        <section class="dash-section" id="seccionNotificaciones">
            <div class="section-header">
                <div>
                    <p class="section-eyebrow"><?= t('vecino.eyebrow_notificaciones') ?></p>
                    <h2><?= t('vecino.titulo_notificaciones') ?></h2>
                </div>
            </div>
            <div id="notificacionesLista">
                <p class="estado-carga"><?= t('vecino.cargando_notificaciones') ?></p>
            </div>
            <div class="logout">
                <button class="btn btn-outline btn-sm" id="logoutBtn"><?= t('perfil.btn_cerrar_sesion') ?></button>

            </div>
        </section>

    </main>
    <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
                <div class="footer-brand">
          <a href="vecino.php" class="logo">
            <img src="../LandingPage/img/LogoBlanco.png" alt="Logo Recolix" class="logo-imageBlanco" />
            <div class="logo-text">Recolix</div>
          </a>
          <p><?= t('common.footer_desc') ?></p>
        </div>
        <div class="footer-col">
          <h3><?= t('common.footer_navegacion') ?></h3>
          <ul>
            <li><a href="../LandingPage/index_LandingPage.html"><?= t('common.footer_inicio') ?></a></li>
            <li><a href="../LandingPage/mapa.html"><?= t('common.footer_mapa') ?></a></li>
            <li><a href="../Backoffice/login_backoffice.php"><?= t('common.footer_portal_empleados') ?></a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h3><?= t('common.footer_canales') ?></h3>
          <ul>
            <li><?= t('common.footer_linea') ?></li>
            <li><?= t('common.footer_whatsapp') ?></li>
          </ul>
        </div>
      </div>
    </div>
  </footer>

    <script>
        const CI = <?= json_encode($_SESSION['ci_usuario']) ?>;
        const I18N = <?= json_encode(t_array([
            'msgEmailInvalido' => 'perfil.msg_email_invalido',
            'msgTelInvalido' => 'perfil.msg_tel_invalido',
            'msgNoPudoGuardar' => 'perfil.msg_no_pudo_guardar',
            'msgDatosActualizados' => 'perfil.msg_datos_actualizados',
            'msgContraActualRequerida' => 'perfil.msg_contra_actual_requerida',
            'msgContraNuevaMin' => 'perfil.msg_contra_nueva_min',
            'msgNoPudoCambiarContra' => 'perfil.msg_no_pudo_cambiar_contra',
            'msgContraActualizada' => 'perfil.msg_contra_actualizada',
            'sinAsignar' => 'vecino.sin_asignar',
            'btnAgregarMunicipio' => 'vecino.btn_agregar_municipio',
            'btnCambiarMunicipio' => 'vecino.btn_cambiar_municipio',
            'municipioFormNotaAgregar' => 'vecino.municipio_form_nota_agregar',
            'municipioFormNotaCambiar' => 'vecino.municipio_form_nota_cambiar',
            'msgSeleccionaMunicipioPunto' => 'vecino.msg_selecciona_municipio_punto',
            'msgNoPudoGuardarMunicipio' => 'vecino.msg_no_pudo_guardar_municipio',
            'msgMunicipioActualizado' => 'vecino.msg_municipio_actualizado',
            'msgMunicipioAsignado' => 'vecino.msg_municipio_asignado',
            'sinIncidencias' => 'vecino.sin_incidencias',
            'msgContenedorInvalido' => 'vecino.msg_contenedor_invalido',
            'msgIncidenciaReportada' => 'vecino.msg_incidencia_reportada',
            'msgErrorReportarIncidencia' => 'vecino.msg_error_reportar_incidencia',
            'detectandoUbicacion' => 'vecino.nueva_incidencia_detectando_ubicacion',
            'ubicacionDetectada' => 'vecino.nueva_incidencia_ubicacion_detectada',
            'ubicacionDenegada' => 'vecino.nueva_incidencia_ubicacion_denegada',
            'ubicacionNoDisponible' => 'vecino.nueva_incidencia_ubicacion_no_disponible',
            'contenedorSeleccionado' => 'vecino.nueva_incidencia_contenedor_seleccionado',
            'tuUbicacion' => 'vecino.nueva_incidencia_tu_ubicacion',
            'sinNotificaciones' => 'vecino.sin_notificaciones',
            'btnMarcarLeida' => 'vecino.btn_marcar_leida',
            'msgFotoTipoInvalido' => 'vecino.msg_foto_tipo_invalido',
            'msgFotoTamanoInvalido' => 'vecino.msg_foto_tamano_invalido',
        ])) ?>;
        const LOCALE = <?= json_encode(t_locale()) ?>;
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Mismo helper que usa la Landing Page: garantiza que ambos formularios de "reportar
         incidencia" (y el mapa público) muestren siempre los mismos contenedores. -->
    <script src="../LandingPage/mapaContenedoresBase.js"></script>
    <script src="Vista/vecino.js"></script>
</body>

</html>
