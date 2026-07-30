<?php require_once __DIR__ . '/verificacion_sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa — Recolix</title>
    <link rel="stylesheet" href="styleBackoffice.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>
<body>


<header class="navbar">
    <div class="nav-inner">
        <div class="nav-left">
            <div class="brand">
                <img src="../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="brand-logo">
                <span class="brand-name">Recolix</span>
            </div>
            <nav class="nav-links">
                <a href="indexBackoffice.php">Inicio</a>
                <a href="../API_Recoleccion/Vista/camiones.php">Camiones</a>
                <a href="../API_Recoleccion/Vista/circuitos.php">Circuitos</a>
                <a href="../API_Recoleccion/Vista/rutas.php">Rutas</a>
                <a href="#">Cuadrillas</a>
                <a href="../API_Gestion/Vista/contenedores.php">Contenedores</a>
                <a href="#">Centros de Acopio</a>
                <a href="mapa.php" class="nav-active">Mapa</a>
                <a href="#">Reportes</a>
                <a href="#">Incidencias</a>
            </nav>
        </div>
        <div class="nav-right">
            <a href="/recolix/API_Usuarios/perfil.php" class="btn-user" title="Perfil de usuario">
                <img src="../API_Recoleccion/Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
            </a>
            <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="Menú">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
    <nav class="nav-mobile" id="navMobile">
        <a href="indexBackoffice.php">Inicio</a>
        <a href="../API_Recoleccion/Vista/camiones.php">Camiones</a>
        <a href="../API_Recoleccion/Vista/circuitos.php">Circuitos</a>
        <a href="../API_Recoleccion/Vista/rutas.php">Rutas</a>
        <a href="#">Cuadrillas</a>
        <a href="../API_Gestion/Vista/contenedores.php">Contenedores</a>
        <a href="#">Centros de Acopio</a>
        <a href="mapa.php" class="nav-active">Mapa</a>
        <a href="#">Reportes</a>
        <a href="#">Incidencias</a>
    </nav>
    <div class="nav-hr"></div>
</header>


<section class="hero">
    <h1 class="hero-title">Mapa operativo</h1>
    <p class="hero-sub">Visualizá el estado de los contenedores en Montevideo.</p>
</section>


<section class="mapa-layout">

    <aside class="mapa-sidebar">
        <div class="mapa-panel">
            <h2>Indicadores</h2>
            <ul class="mapa-info-list">
                <li><span>Contenedores activos</span><strong id="countActivos">0</strong></li>
                <li><span>Inactivos</span><strong id="countInactivos">0</strong></li>
                <li><span>Desbordados</span><strong id="countDesbordados">0</strong></li>
            </ul>
        </div>

        <div class="mapa-panel">
            <h2>Referencia visual</h2>
            <ul class="mapa-leyenda">
                <li class="leyenda-funcional">Funcional</li>
                <li class="leyenda-inactivo">Inactivo</li>
                <li class="leyenda-desbordado">Desbordado</li>
                <li class="leyenda-roto">Roto</li>
            </ul>
        </div>

        <div class="mapa-panel">
            <p class="mapa-update-label">Última actualización</p>
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
            <p>Plataforma de gestión de residuos urbanos para Montevideo.</p>
        </div>
        <div class="footer-col">
            <h3>Navegación</h3>
            <ul>
                <li><a href="../LandingPage/index_LandingPage.html">Página principal</a></li>
                <li><a href="../LandingPage/mapa.html">Mapa público</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-hr"></div>
    <p class="footer-copy">&copy; 2026 Recolix. Todos los derechos reservados.</p>
</footer>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="../LandingPage/mapaContenedores.js"></script>
<script>
function toggleMobileMenu() {
    document.getElementById('navMobile').classList.toggle('nav-mobile-open');
}
</script>

</body>
</html>
