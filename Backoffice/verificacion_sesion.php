<?php
session_start();
require_once __DIR__ . '/../i18n/i18n.php';

$rol = $_SESSION['usuario']['rol'] ?? '';

// Regla base: sin sesión, vecino o rol vacío nunca pueden entrar al Backoffice/portal interno.
$denegado = !isset($_SESSION['usuario']) || $rol === 'vecino' || empty($rol);

// Si la página que incluye este archivo definió $rolesPermitidos ANTES del
// require_once, además se exige que el rol esté en esa lista puntual.
// Ejemplo (arriba de todo en cuadrilla.php):
//   $rolesPermitidos = ['cuadrilla'];
//   require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php';
if (!$denegado && isset($rolesPermitidos) && !in_array($rol, $rolesPermitidos, true)) {
    $denegado = true;
}

if (!$denegado) {
    // Guardamos esta página como la última visitada válidamente, para poder
    // volver acá si más adelante se intenta entrar a algo no permitido.
    $_SESSION['ultima_pagina_permitida'] = $_SERVER['REQUEST_URI'];
} else {
    // Volvemos a la última página permitida que la persona haya visitado.
    // Si todavía no visitó ninguna (ej. primer intento tras loguearse), caemos
    // a la landing page pública como respaldo.
    $destino = $_SESSION['ultima_pagina_permitida'] ?? '/recolix/LandingPage/index_LandingPage.html';

    header('Location: ' . $destino);
    exit;
}