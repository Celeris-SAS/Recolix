<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: /recolix/LandingPage/login.html');
    exit;
}

$rol = $_SESSION['usuario']['rol'] ?? '';

if ($rol === 'vecino' || empty($rol)) {
    http_response_code(403);
    ?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso no autorizado — Recolix</title>
    <link rel="stylesheet" href="/recolix/Backoffice/styleBackoffice.css">
    <link rel="stylesheet" href="/recolix/Backoffice/styleAccesoDenegado.css">
</head>
<body>
<div class="denegado-wrap">
    <div class="denegado-logo">
        <img src="/recolix/API_Recoleccion/Vista/logoRecolix.png" alt="Recolix">
        <span>Recolix</span>
    </div>
    <div class="denegado-icono">⛔</div>
    <h1 class="denegado-titulo">Acceso no autorizado</h1>
    <p class="denegado-desc">
        Tu cuenta no tiene permisos para acceder al panel de administración de Recolix.
    </p>
    <a href="/recolix/LandingPage/index_LandingPage.html" class="btn-volver">Volver a la página principal</a>
</div>
</body>
</html><?php
    exit;
}
