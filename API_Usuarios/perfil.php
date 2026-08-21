<?php
session_start();
require_once __DIR__ . '/../i18n/i18n.php';

$ci  = $_SESSION['usuario']['ci_usuario'] ?? null;
$rol = $_SESSION['usuario']['rol'] ?? null;

if (!$ci) {
    header("Location: ../Backoffice/login_backoffice.php");
    exit();
}

$rolesPermitidos = ['administrador', 'cuadrilla', 'operario'];
if (!in_array($rol, $rolesPermitidos)) {
    header("Location: ../Backoffice/login_backoffice.php");
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
    <link rel="stylesheet" href="../Backoffice/styleBackoffice.css">
    <link rel="stylesheet" href="Vista/styles_vecino.css">
</head>

<body>

    <header class="navbar">
        <div class="nav-inner">
            <div class="nav-left">
                <a href="../Backoffice/indexBackoffice.php" class="brand">
                    <img src="../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="brand-logo">
                    <span class="brand-name">Recolix</span>
                </a>
                <nav class="nav-links">
                    <a href="../Backoffice/indexBackoffice.php"><?= t('backoffice.nav_inicio') ?></a>
                    <a href="../API_Recoleccion/Vista/camiones.php"><?= t('backoffice.nav_camiones') ?></a>
                    <a href="../API_Recoleccion/Vista/circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
                    <a href="../API_Recoleccion/Vista/rutas.php"><?= t('backoffice.nav_rutas') ?></a>
                    <a href="#"><?= t('backoffice.nav_cuadrillas') ?></a>
                    <a href="../API_Gestion/Vista/contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
                    <a href="../API_Gestion/Vista/centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
                    <a href="../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
                    <a href="#"><?= t('backoffice.nav_reportes') ?></a>
                    <a href="#"><?= t('backoffice.nav_incidencias') ?></a>
                    <a href="Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
                </nav>
            </div>
            <div class="nav-right">
                <?php include __DIR__ . '/../i18n/switcher.php'; ?>
                <a href="perfil.php" class="btn-user" title="<?= htmlspecialchars(t('backoffice.nav_perfil_usuario')) ?>">
                    <img src="../API_Recoleccion/Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
                </a>
                <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="<?= htmlspecialchars(t('backoffice.nav_menu')) ?>">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
        <nav class="nav-mobile" id="navMobile">
            <a href="../Backoffice/indexBackoffice.php"><?= t('backoffice.nav_inicio') ?></a>
            <a href="../API_Recoleccion/Vista/camiones.php"><?= t('backoffice.nav_camiones') ?></a>
            <a href="../API_Recoleccion/Vista/circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
            <a href="../API_Recoleccion/Vista/rutas.php"><?= t('backoffice.nav_rutas') ?></a>
            <a href="#"><?= t('backoffice.nav_cuadrillas') ?></a>
            <a href="../API_Gestion/Vista/contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
            <a href="../API_Gestion/Vista/centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
            <a href="../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
            <a href="#"><?= t('backoffice.nav_reportes') ?></a>
            <a href="#"><?= t('backoffice.nav_incidencias') ?></a>
            <a href="Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
        </nav>
        <div class="nav-hr"></div>
    </header>

    <main class="container dash-main">
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
                </div>
                <button class="btn btn-outline btn-sm" id="cambiarContraseniaBtn" style="margin-top:20px;"><?= t('perfil.btn_cambiar_contrasenia') ?></button>
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

            <div class="logout">
                <button class="btn btn-outline btn-sm" id="logoutBtn"><?= t('perfil.btn_cerrar_sesion') ?></button>
            </div>
        </section>
    </main>
    <footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <div class="footer-logo">
                <img src="../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="footer-logo-img">
                <span class="footer-logo-name">Recolix</span>
            </div>
            <p><?= t('common.footer_desc') ?></p>
        </div>
        <div class="footer-col">
            <h3><?= t('common.footer_navegacion') ?></h3>
            <ul>
                <li><a href="../LandingPage/index_LandingPage.html"><?= t('backoffice.footer_pagina_principal') ?></a></li>
                <li><a href="../LandingPage/mapa.html"><?= t('backoffice.footer_mapa_publico') ?></a></li>
            </ul>
        </div>
    </div>
    <div class="footer-hr"></div>
    <p class="footer-copy"><?= t('backoffice.footer_copyright') ?></p>
</footer>

    <script>
        const CI = <?= json_encode($ci) ?>;
        const I18N = <?= json_encode(t_array([
            'msgEmailInvalido' => 'perfil.msg_email_invalido',
            'msgTelInvalido' => 'perfil.msg_tel_invalido',
            'msgNoPudoGuardar' => 'perfil.msg_no_pudo_guardar',
            'msgDatosActualizados' => 'perfil.msg_datos_actualizados',
            'msgContraActualRequerida' => 'perfil.msg_contra_actual_requerida',
            'msgContraNuevaMin' => 'perfil.msg_contra_nueva_min',
            'msgNoPudoCambiarContra' => 'perfil.msg_no_pudo_cambiar_contra',
            'msgContraActualizada' => 'perfil.msg_contra_actualizada',
        ])) ?>;
    </script>
    <script src="perfil.js"></script>
    <script>
function toggleMobileMenu() {
    document.getElementById('navMobile').classList.toggle('nav-mobile-open');
}
</script>
</body>
</html>
