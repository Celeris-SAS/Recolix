<?php
session_start();
require_once __DIR__ . '/../i18n/i18n.php';

if (!isset($_SESSION['usuario'])) {
    header('Location: /recolix/LandingPage/login.html');
    exit;
}

$rol = $_SESSION['usuario']['rol'] ?? '';

if ($rol === 'vecino' || empty($rol)) {
    http_response_code(403);
    ?><!DOCTYPE html>
<html lang="<?= htmlspecialchars($RECOLIX_IDIOMA) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('backoffice.denegado_title') ?></title>
    <link rel="stylesheet" href="/recolix/Backoffice/styleBackoffice.css">
    <link rel="stylesheet" href="/recolix/Backoffice/styleAccesoDenegado.css">
</head>
<body>
<div class="denegado-wrap">
    <?php include __DIR__ . '/../i18n/switcher.php'; ?>
    <div class="denegado-logo">
        <img src="/recolix/API_Recoleccion/Vista/logoRecolix.png" alt="Recolix">
        <span>Recolix</span>
    </div>
    <div class="denegado-icono">⛔</div>
    <h1 class="denegado-titulo"><?= t('backoffice.denegado_titulo') ?></h1>
    <p class="denegado-desc">
        <?= t('backoffice.denegado_desc') ?>
    </p>
    <a href="/recolix/LandingPage/index_LandingPage.html" class="btn-volver"><?= t('backoffice.denegado_volver') ?></a>
</div>
</body>
</html><?php
    exit;
}
