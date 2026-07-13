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

  <header class="dash-header">
    <div class="container dash-header-inner">
      <a href="indexBackoffice.html" class="logo">
        <img src="../LandingPage/Logo.png" alt="Logo Recolix" class="logo-image" />
        <span class="logo-text">Recolix</span>
      </a>
      <div class="header-actions">
        <a href="logout.php" class="btn btn-outline btn-sm">Cerrar sesión</a>
      </div>
    </div>
  </header>

  <main class="container dash-main">

    <div class="page-header">
      <div>
        <p class="section-eyebrow">Administración</p>
        <h1>Solicitudes de registro</h1>
        <p class="page-desc">Revisá las solicitudes pendientes y aprobá o rechazá cada una.</p>
      </div>
      <a href="indexBackoffice.html" class="btn btn-outline btn-sm">← Volver</a>
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