<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    header('Location: login.php');
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
  <link rel="stylesheet" href="estilos_solicitudes.css">
</head>

<body>

 <header class="navbar">
    <div class="nav-inner">
        <div class="nav-left">
            <div class="brand">
                <img src="img/Logo.png" alt="Recolix" class="brand-logo">
                <span class="brand-name">Recolix</span>
            </div>
            <nav class="nav-links">
                <a href="../Backoffice/indexBackoffice.php">Inicio</a>
                <a href="#" class="nav-active">Camiones</a>
                <a href="#">Cuadrillas</a>
                <a href="../../API_Gestion/Vista/contenedores.html">Contenedores</a>
                <a href="#">Vertederos</a>
                <a href="#">Centros de Acopio</a>
                <a href="../../Backoffice/mapa.php">Mapa</a>
                <a href="#">Reportes</a>
                <a href="#">Incidencias</a>
            </nav>
        </div>
        <div class="nav-right">
            <a href="/recolix/API_Usuarios/Vista/perfil.html" class="btn-user" title="Perfil de usuario">
                <img src="img/user.svg" alt="Usuario" class="nav-user-icon">
            </a>
            <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="Menú">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
    <nav class="nav-mobile" id="navMobile">
        <a href="../../Backoffice/indexBackoffice.php">Inicio</a>
        <a href="#" class="nav-active">Camiones</a>
        <a href="#">Cuadrillas</a>
        <a href="../../API_Gestion/Vista/contenedores.html">Contenedores</a>
        <a href="#">Vertederos</a>
        <a href="#">Centros de Acopio</a>
        <a href="../../Backoffice/mapa.php">Mapa</a>
        <a href="#">Reportes</a>
        <a href="#">Incidencias</a>
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

    <!-- Feedback global -->
    <p class="form-feedback" id="feedbackGlobal"></p>

    <!-- Tabla de solicitudes -->
    <div class="panel-card">
      <div id="solicitudesContenedor">
        <p class="estado-carga">Cargando solicitudes...</p>
      </div>
    </div>

  </main>

  <script src="logica_solicitudes.js"></script>
</body>

</html>