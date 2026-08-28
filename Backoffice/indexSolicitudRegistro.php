<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    header('Location: login_backoffice.php');
    exit;
}
require_once __DIR__ . '/../i18n/i18n.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($RECOLIX_IDIOMA) ?>">

<head>
  <meta charset="UTF-8">
    <meta name="google" content="notranslate">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= t('backoffice.solicitudes_title') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styleBackoffice.css">
  <link rel="stylesheet" href="estilos_solicitudes.css">
</head>

<body>

  <header class="navbar">
    <div class="nav-inner">
        <div class="nav-left">
            <a href="indexBackoffice.php" class="brand">
                <img src="../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="brand-logo">
                <span class="brand-name">Recolix</span>
            </a>
            <nav class="nav-links">
                <a href="indexBackoffice.php"><?= t('backoffice.nav_inicio') ?></a>
                <a href="../API_Recoleccion/Vista/camiones.php"><?= t('backoffice.nav_camiones') ?></a>
                <a href="../API_Recoleccion/Vista/circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
                <a href="../API_Recoleccion/Vista/rutas.php"><?= t('backoffice.nav_rutas') ?></a>
                <a href="../API_Usuarios/Vista/cuadrillas.php"><?= t('backoffice.nav_cuadrillas') ?></a>
                <a href="../API_Gestion/Vista/contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
                <a href="../API_Gestion/Vista/centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
                <a href="mapa.php"><?= t('backoffice.nav_mapa') ?></a>
                <a href="../API_Gestion/Vista/reportes.php"><?= t('backoffice.nav_reportes') ?></a>
                <a href="../API_Gestion/Vista/incidencias.php"><?= t('backoffice.nav_incidencias') ?></a>
                <a href="../API_Usuarios/Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
            </nav>
        </div>
        <div class="nav-right">
            <?php include __DIR__ . '/../i18n/switcher.php'; ?>
            <a href="/recolix/API_Usuarios/perfil.php" class="btn-user" title="<?= htmlspecialchars(t('backoffice.nav_perfil_usuario')) ?>">
                <img src="../API_Recoleccion/Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
            </a>
            <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="<?= htmlspecialchars(t('backoffice.nav_menu')) ?>">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
    <nav class="nav-mobile" id="navMobile">
        <a href="indexBackoffice.php"><?= t('backoffice.nav_inicio') ?></a>
        <a href="../API_Recoleccion/Vista/camiones.php"><?= t('backoffice.nav_camiones') ?></a>
        <a href="../API_Recoleccion/Vista/circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
        <a href="../API_Recoleccion/Vista/rutas.php"><?= t('backoffice.nav_rutas') ?></a>
        <a href="../API_Usuarios/Vista/cuadrillas.php"><?= t('backoffice.nav_cuadrillas') ?></a>
        <a href="../API_Gestion/Vista/contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
        <a href="../API_Gestion/Vista/centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
        <a href="mapa.php"><?= t('backoffice.nav_mapa') ?></a>
        <a href="../API_Gestion/Vista/reportes.php"><?= t('backoffice.nav_reportes') ?></a>
        <a href="../API_Gestion/Vista/incidencias.php"><?= t('backoffice.nav_incidencias') ?></a>
        <a href="../API_Usuarios/Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
    </nav>
    <div class="nav-hr"></div>
</header>

  <main class="container dash-main">

    <div class="page-header">
      <div>
        <p class="section-eyebrow"><?= t('backoffice.solicitudes_eyebrow') ?></p>
        <h1><?= t('backoffice.solicitudes_titulo') ?></h1>
        <p class="page-desc"><?= t('backoffice.solicitudes_desc') ?></p>
      </div>
      <a href="indexBackoffice.php" class="btn btn-outline btn-sm"><?= t('backoffice.solicitudes_volver') ?></a>
    </div>


    <p class="form-feedback" id="feedbackGlobal"></p>


    <div class="panel-card">
      <div id="solicitudesContenedor">
        <p class="estado-carga"><?= t('backoffice.solicitudes_cargando') ?></p>
      </div>
    </div>

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
    const I18N = <?= json_encode(t_array([
      'errorCargar' => 'backoffice.solicitudes_js_error_cargar',
      'noHay' => 'backoffice.solicitudes_js_no_hay',
      'noHayDesc' => 'backoffice.solicitudes_js_no_hay_desc',
      'colCedula' => 'backoffice.solicitudes_js_col_cedula',
      'colNombre' => 'backoffice.solicitudes_js_col_nombre',
      'colApellido' => 'backoffice.solicitudes_js_col_apellido',
      'colEmail' => 'backoffice.solicitudes_js_col_email',
      'colTelefono' => 'backoffice.solicitudes_js_col_telefono',
      'colAcciones' => 'backoffice.solicitudes_js_col_acciones',
      'aprobar' => 'backoffice.solicitudes_js_aprobar',
      'rechazar' => 'backoffice.solicitudes_js_rechazar',
      'errorConexion' => 'backoffice.solicitudes_js_error_conexion',
      'confirmarAprobar' => 'backoffice.solicitudes_js_confirmar_aprobar',
      'confirmarRechazar' => 'backoffice.solicitudes_js_confirmar_rechazar',
      'errorAprobar' => 'backoffice.solicitudes_js_error_aprobar',
      'aprobadaOk' => 'backoffice.solicitudes_js_aprobada_ok',
      'errorRechazar' => 'backoffice.solicitudes_js_error_rechazar',
      'rechazadaOk' => 'backoffice.solicitudes_js_rechazada_ok',
    ])) ?>;
  </script>
  <script src="logica_solicitudes.js"></script>
  <script>
function toggleMobileMenu() {
    document.getElementById('navMobile').classList.toggle('nav-mobile-open');
}
</script>
</body>

</html>