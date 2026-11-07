<?php
session_start();
require_once __DIR__ . '/../extra/i18n/i18n.php';

$rol = $_SESSION['usuario']['rol'] ?? '';

$denegado = !isset($_SESSION['usuario']) || $rol === 'vecino' || empty($rol);

if (!$denegado && isset($rolesPermitidos) && !in_array($rol, $rolesPermitidos, true)) {
    $denegado = true;
}

if (!$denegado) {
    $_SESSION['ultima_pagina_permitida'] = $_SERVER['REQUEST_URI'];
} else {
    $destino = $_SESSION['ultima_pagina_permitida'] ?? '/recolix/LandingPage/index_LandingPage.html';

    header('Location: ' . $destino);
    exit;
}