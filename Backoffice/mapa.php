<?php
require_once __DIR__ . '/verificacion_sesion.php';
require_once __DIR__ . '/../i18n/i18n.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($RECOLIX_IDIOMA) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('backoffice.mapa_title') ?></title>
    <link rel="stylesheet" href="styleBackoffice.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
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
                <a href="mapa.php" class="nav-active"><?= t('backoffice.nav_mapa') ?></a>
                <a href="#"><?= t('backoffice.nav_reportes') ?></a>
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
        <a href="mapa.php" class="nav-active"><?= t('backoffice.nav_mapa') ?></a>
        <a href="#"><?= t('backoffice.nav_reportes') ?></a>
        <a href="../API_Gestion/Vista/incidencias.php"><?= t('backoffice.nav_incidencias') ?></a>
        <a href="../API_Usuarios/Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
    </nav>
    <div class="nav-hr"></div>
</header>


<div class="mapa-tabs" id="mapaTabs">
    <button class="mapa-tab is-active" data-tipo="contenedores"><?= t('mapa.tab_contenedores') ?></button>
    <button class="mapa-tab" data-tipo="centros"><?= t('mapa.tab_centros') ?></button>
    <button class="mapa-tab" data-tipo="camiones"><?= t('mapa.tab_camiones') ?></button>
</div>

<section class="hero">
    <h1 class="hero-title"><?= t('backoffice.mapa_titulo') ?></h1>
    <p class="hero-sub"><?= t('backoffice.mapa_sub') ?></p>
</section>


<section class="mapa-layout">

    <aside class="mapa-sidebar">
        <div class="mapa-panel">
            <h2><?= t('mapa.indicadores') ?></h2>
            <ul class="mapa-info-list" id="indicadores-contenedores">
                <li><span><?= t('mapa.contenedores_activos') ?></span><strong id="countActivos">0</strong></li>
                <li><span><?= t('mapa.inactivos') ?></span><strong id="countInactivos">0</strong></li>
                <li><span><?= t('mapa.desbordados') ?></span><strong id="countDesbordados">0</strong></li>
            </ul>
            <ul class="mapa-info-list" id="indicadores-centros" style="display:none">
                <li><span><?= t('mapa.operativos') ?></span><strong id="countOperativos">0</strong></li>
                <li><span><?= t('mapa.saturados') ?></span><strong id="countSaturados">0</strong></li>
                <li><span><?= t('mapa.cerrados') ?></span><strong id="countCerrados">0</strong></li>
            </ul>
            <ul class="mapa-info-list" id="indicadores-camiones" style="display:none">
                <li><span><?= t('mapa.camiones_en_operativa') ?></span><strong id="countCamionesTotal">0</strong></li>
                <li><span><?= t('mapa.con_tiempo_estimado') ?></span><strong id="countCamionesConEta">0</strong></li>
                <li><span><?= t('mapa.sin_proxima_parada') ?></span><strong id="countCamionesSinEta">0</strong></li>
            </ul>
        </div>

        <div class="mapa-panel">
            <h2><?= t('mapa.referencia_visual') ?></h2>
            <ul class="mapa-leyenda" id="leyenda-contenedores">
                <li class="leyenda-funcional"><?= t('mapa.js_estado_funcional') ?></li>
                <li class="leyenda-inactivo"><?= t('mapa.js_estado_inactivo') ?></li>
                <li class="leyenda-desbordado"><?= t('mapa.js_estado_desbordado') ?></li>
                <li class="leyenda-roto"><?= t('mapa.js_estado_roto') ?></li>
            </ul>
            <ul class="mapa-leyenda" id="leyenda-centros" style="display:none">
                <li class="leyenda-operativo"><?= t('mapa.js_estado_operativo') ?></li>
                <li class="leyenda-saturado"><?= t('mapa.js_estado_saturado') ?></li>
                <li class="leyenda-cerrado"><?= t('mapa.js_estado_cerrado') ?></li>
            </ul>
            <ul class="mapa-leyenda" id="leyenda-camiones" style="display:none">
                <li class="leyenda-en-ruta"><?= t('mapa.camion_en_ruta') ?></li>
            </ul>
        </div>

        <div class="mapa-panel">
            <p class="mapa-update-label"><?= t('mapa.ultima_actualizacion') ?></p>
            <p class="mapa-update-value" id="mapSummaryValue">--</p>
        </div>
    </aside>

    <div class="mapa-shell">
        <div id="mapa-principal" class="mapa-principal"></div>
    </div>

</section>


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

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const I18N = <?= json_encode(t_array([
        'tipoEcocentro' => 'mapa.js_tipo_ecocentro',
        'tipoPlantaClasificacion' => 'mapa.js_tipo_planta_clasificacion',
        'tipoRellenoSanitario' => 'mapa.js_tipo_relleno_sanitario',
        'tipoVertedero' => 'mapa.js_tipo_vertedero',
        'estadoFuncional' => 'mapa.js_estado_funcional',
        'estadoInactivo' => 'mapa.js_estado_inactivo',
        'estadoDesbordado' => 'mapa.js_estado_desbordado',
        'estadoRoto' => 'mapa.js_estado_roto',
        'estadoEnDeposito' => 'mapa.js_estado_en_deposito',
        'estadoOperativo' => 'mapa.js_estado_operativo',
        'estadoSaturado' => 'mapa.js_estado_saturado',
        'estadoCerrado' => 'mapa.js_estado_cerrado',
        'popupTipo' => 'mapa.js_popup_tipo',
        'popupEstado' => 'mapa.js_popup_estado',
        'popupDireccion' => 'mapa.js_popup_direccion',
        'popupMunicipio' => 'mapa.js_popup_municipio',
        'popupCapacidad' => 'mapa.js_popup_capacidad',
        'popupNivelLlenado' => 'mapa.js_popup_nivel_llenado',
        'popupCamion' => 'mapa.js_popup_camion',
        'popupModelo' => 'mapa.js_popup_modelo',
        'popupUltimaPosicion' => 'mapa.js_popup_ultima_posicion',
        'popupProximaParada' => 'mapa.js_popup_proxima_parada',
        'popupTiempoProximo' => 'mapa.js_popup_tiempo_proximo',
        'sinDireccion' => 'mapa.js_sin_direccion',
        'sinPendientes' => 'mapa.js_sin_pendientes',
        'unidadCentros' => 'mapa.js_unidad_centros',
        'unidadCamiones' => 'mapa.js_unidad_camiones',
        'unidadPuntos' => 'mapa.js_unidad_puntos',
        'respuestaInesperada' => 'mapa.js_respuesta_inesperada',
    ])) ?>;
    const I18N_LOCALE = <?= json_encode(t_locale()) ?>;
</script>
<script src="../LandingPage/mapa.js"></script>
<script>
function toggleMobileMenu() {
    document.getElementById('navMobile').classList.toggle('nav-mobile-open');
}
</script>

</body>
</html>
