<?php
require_once __DIR__ . '/verificacion_sesion.php';
require_once __DIR__ . '/../i18n/i18n.php';

$usuario        = $_SESSION['usuario'];
$primerNombre   = htmlspecialchars($usuario['primer_nombre'] ?? '');
$apellido       = htmlspecialchars($usuario['p_apellido'] ?? '');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($RECOLIX_IDIOMA) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('backoffice.index_title') ?></title>
    <link rel="stylesheet" href="styleBackoffice.css">
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
                <a href="#" class="nav-active"><?= t('backoffice.nav_inicio') ?></a>
                <a href="../API_Recoleccion/Vista/camiones.php"><?= t('backoffice.nav_camiones') ?></a>
                <a href="../API_Recoleccion/Vista/circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
                <a href="../API_Recoleccion/Vista/rutas.php"><?= t('backoffice.nav_rutas') ?></a>
                <a href="#"><?= t('backoffice.nav_cuadrillas') ?></a>
                <a href="../API_Gestion/Vista/contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
                <a href="../API_Gestion/Vista/centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
                <a href="mapa.php"><?= t('backoffice.nav_mapa') ?></a>
                <a href="#"><?= t('backoffice.nav_reportes') ?></a>
                <a href="#"><?= t('backoffice.nav_incidencias') ?></a>
                <a href="../API_Usuarios/Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
            </nav>
        </div>
        <div class="nav-right">
            <?php include __DIR__ . '/../i18n/switcher.php'; ?>
            <a href="../API_Usuarios/perfil.php" class="btn-user" title="<?= htmlspecialchars(t('backoffice.nav_perfil_usuario')) ?>">
                <img src="../API_Recoleccion/Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
            </a>
            <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="<?= htmlspecialchars(t('backoffice.nav_menu')) ?>">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
    <nav class="nav-mobile" id="navMobile">
        <a href="#" class="nav-active"><?= t('backoffice.nav_inicio') ?></a>
        <a href="../API_Recoleccion/Vista/camiones.php"><?= t('backoffice.nav_camiones') ?></a>
        <a href="../API_Recoleccion/Vista/circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
        <a href="../API_Recoleccion/Vista/rutas.php"><?= t('backoffice.nav_rutas') ?></a>
        <a href="#"><?= t('backoffice.nav_cuadrillas') ?></a>
        <a href="../API_Gestion/Vista/contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
        <a href="../API_Gestion/Vista/centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
        <a href="mapa.php"><?= t('backoffice.nav_mapa') ?></a>
        <a href="#"><?= t('backoffice.nav_reportes') ?></a>
        <a href="#"><?= t('backoffice.nav_incidencias') ?></a>
        <a href="../API_Usuarios/Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
    </nav>
    <div class="nav-hr"></div>
</header>


<section class="hero">
    <h1 class="hero-title"><?= t('backoffice.index_bienvenido') ?> <?= $primerNombre ?></h1>
    <p class="hero-sub"><?= t('backoffice.index_panel') ?></p>
</section>


<section class="novedades">
    <div class="novedades-header">
        <h2 class="novedades-title"><?= t('backoffice.index_novedades') ?></h2>
        <span class="novedades-count" id="novedadesCount"></span>
    </div>
    <div class="novedades-grid" id="novedadesGrid">
        <a href="#" class="novedad-card">
            <div class="novedad-icon novedad-icon-incidencias">
                <img src="img/exclamacion.png" alt="" class="card-svg-icon">
            </div>
            <div class="novedad-body">
                <h3><?= t('backoffice.index_incidencias_nuevas') ?></h3>
                <p class="novedad-resumen"><?= t('backoffice.index_incidencias_resumen') ?></p>
                <span class="novedad-badge" id="badgeIncidencias">0</span>
            </div>
        </a>
        <a href="indexSolicitudRegistro.php" class="novedad-card">
            <div class="novedad-icon novedad-icon-usuarios">
                <img src="../API_Recoleccion/Vista/img/user.svg" alt="" class="card-svg-icon">
            </div>
            <div class="novedad-body">
                <h3><?= t('backoffice.index_usuarios_nuevos') ?></h3>
                <p class="novedad-resumen"><?= t('backoffice.index_usuarios_resumen') ?></p>
                <span class="novedad-badge" id="badgeUsuarios">0</span>
            </div>
        </a>
        <a href="../API_Gestion/Vista/contenedores.php?vista=criticos" class="novedad-card">
            <div class="novedad-icon novedad-icon-contenedores">
                <img src="img/contenedor.png" alt="" class="card-svg-icon">
            </div>
            <div class="novedad-body">
                <h3><?= t('backoffice.index_contenedores_criticos') ?></h3>
                <p class="novedad-resumen"><?= t('backoffice.index_contenedores_resumen') ?></p>
                <span class="novedad-badge" id="badgeContenedores">0</span>
            </div>
        </a>
    </div>
</section>


<section class="cards-section">
    <div class="cards-grid">

        <a href="../API_Recoleccion/Vista/camiones.php" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="../API_Recoleccion/Vista/img/truck-svgrepo-com.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('backoffice.index_card_camiones_titulo') ?></h3>
                <p><?= t('backoffice.index_card_camiones_desc') ?></p>
            </div>
        </a>

        <a href="../API_Recoleccion/Vista/circuitos.php" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="img/circuito.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('backoffice.index_card_circuitos_titulo') ?></h3>
                <p><?= t('backoffice.index_card_circuitos_desc') ?></p>
            </div>
        </a>

        <a href="../API_Recoleccion/Vista/rutas.php" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="img/ruta.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('backoffice.index_card_rutas_titulo') ?></h3>
                <p><?= t('backoffice.index_card_rutas_desc') ?></p>
            </div>
        </a>

        <a href="../API_Gestion/Vista/contenedores.php" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="img/contenedor.png" alt="" class="card-svg-icon"></div>
                <h3><?= t('backoffice.index_card_contenedores_titulo') ?></h3>
                <p><?= t('backoffice.index_card_contenedores_desc') ?></p>
            </div>
        </a>

        <a href="../API_Gestion/Vista/reportes.html" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="img/reportes.png" alt="" class="card-svg-icon"></div>
                <h3><?= t('backoffice.index_card_reportes_titulo') ?></h3>
                <p><?= t('backoffice.index_card_reportes_desc') ?></p>
            </div>
        </a>

        <a href="../API_Gestion/Vista/incidencias.html" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="img/exclamacion.png" alt="" class="card-svg-icon"></div>
                <h3><?= t('backoffice.index_card_incidencias_titulo') ?></h3>
                <p><?= t('backoffice.index_card_incidencias_desc') ?></p>
            </div>
        </a>

        <a href="#" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="img/cuadrilla.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('backoffice.index_card_cuadrillas_titulo') ?></h3>
                <p><?= t('backoffice.index_card_cuadrillas_desc') ?></p>
            </div>
        </a>

        <a href="mapa.php" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="img/mapa.png" alt="" class="card-svg-icon"></div>
                <h3><?= t('backoffice.index_card_mapa_titulo') ?></h3>
                <p><?= t('backoffice.index_card_mapa_desc') ?></p>
            </div>
        </a>

        <a href="../API_Gestion/Vista/centrosAcopio.php" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="img/centroAcopio.png" alt="" class="card-svg-icon"></div>
                <h3><?= t('backoffice.index_card_centros_titulo') ?></h3>
                <p><?= t('backoffice.index_card_centros_desc') ?></p>
            </div>
        </a>
        <a href="../API_Usuarios/Vista/usuarios.php" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="../API_Usuarios/Vista/img/user.svg" alt="" class="card-svg-icon"></div>
                <h3><?= t('backoffice.index_card_usuarios_titulo') ?></h3>
                <p><?= t('backoffice.index_card_usuarios_desc') ?></p>
            </div>
        </a>

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

<script>
const API_GESTION      = '/recolix/API_Gestion';
const API_RECOLECCION  = '/recolix/API_Recoleccion';
const API_USUARIOS     = '/recolix/API_Usuarios';
const I18N = <?= json_encode(t_array([
    'pendienteSingular' => 'backoffice.index_js_pendiente_singular',
    'pendientePlural' => 'backoffice.index_js_pendiente_plural',
])) ?>;

async function cargarNovedades() {
    const badges = {
        incidencias:  document.getElementById('badgeIncidencias'),
        usuarios:     document.getElementById('badgeUsuarios'),
        contenedores: document.getElementById('badgeContenedores')
    };
    const countEl = document.getElementById('novedadesCount');
    let total = 0;

    try {
        const res  = await fetch(`${API_GESTION}/incidencias?estado=Abierta`);
        const json = await res.json();
        if (json.status === 'success' && Array.isArray(json.data)) {
            badges.incidencias.textContent = json.data.length;
            total += json.data.length;
        }
    } catch {
        badges.incidencias.textContent = '0';
    }

    try {
        const res  = await fetch(`${API_USUARIOS}/solicitudes/getSolicitudesPendientes`);
        const json = await res.json();
        if (json.status === 'success' && Array.isArray(json.data)) {
            badges.usuarios.textContent = json.data.length;
            total += json.data.length;
        }
    } catch {
        badges.usuarios.textContent = '0';
    }

    let criticos = 0;
    try {
        const res  = await fetch(`${API_GESTION}/contenedores?estado=desbordado`);
        const json = await res.json();
        if (json.status === 'success' && Array.isArray(json.data)) criticos += json.data.length;
    } catch {}
    try {
        const res  = await fetch(`${API_GESTION}/contenedores?estado=roto`);
        const json = await res.json();
        if (json.status === 'success' && Array.isArray(json.data)) criticos += json.data.length;
    } catch {}
    badges.contenedores.textContent = criticos;
    total += criticos;

    if (countEl) {
        countEl.textContent = total > 0 ? `${total} ${total !== 1 ? I18N.pendientePlural : I18N.pendienteSingular}` : '';
    }
}

document.addEventListener('DOMContentLoaded', cargarNovedades);

function toggleMobileMenu() {
    document.getElementById('navMobile').classList.toggle('nav-mobile-open');
}
</script>

</body>
</html>
