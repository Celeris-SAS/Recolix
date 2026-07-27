<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    header('Location: login_backoffice.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recolix — Solicitudes de registro</title>
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
            <div class="brand">
                <img src="../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="brand-logo">
                <span class="brand-name">Recolix</span>
            </div>
            <nav class="nav-links">
                <a href="indexBackoffice.php">Inicio</a>
                <a href="../API_Recoleccion/Vista/camiones.php">Camiones</a>
                <a href="#">Cuadrillas</a>
                <a href="../API_Gestion/Vista/contenedores.php">Contenedores</a>
                <a href="#">Centros de Acopio</a>
                <a href="mapa.php">Mapa</a>
                <a href="#">Reportes</a>
                <a href="#">Incidencias</a>
                <a href="../API_Usuarios/Vista/usuarios.php">Usuarios</a>
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
        <a href="#">Cuadrillas</a>
        <a href="../API_Gestion/Vista/contenedores.php">Contenedores</a>
        <a href="#">Centros de Acopio</a>
        <a href="mapa.php">Mapa</a>
        <a href="#">Reportes</a>
        <a href="#">Incidencias</a>
        <a href="../API_Usuarios/Vista/usuarios.php">Usuarios</a>
    </nav>
    <div class="nav-hr"></div>
</header>

  <main class="container dash-main">

    <div class="page-header">
      <div>
        <p class="section-eyebrow">Administración</p>
        <h1>Solicitudes de registro</h1>
        <p class="page-desc">Revisá las solicitudes pendientes y aprobá o rechazá cada una.</p>
      </div>
      <a href="indexBackoffice.php" class="btn btn-outline btn-sm">← Volver</a>
    </div>

    
    <p class="form-feedback" id="feedbackGlobal"></p>

   
    <div class="panel-card">
      <div id="solicitudesContenedor">
        <p class="estado-carga">Cargando solicitudes...</p>
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

  <script src="logica_solicitudes.js"></script>
  <script>
function toggleMobileMenu() {
    document.getElementById('navMobile').classList.toggle('nav-mobile-open');
}
</script>
</body>

</html>