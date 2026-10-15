<?php
// Página de notificaciones, compartida por los cuatro portales (vecino, admin, cuadrilla,
// operario): no hay datos específicos de rol en una notificación, así que una sola página
// alcanza para los cuatro en vez de duplicarla por rol como perfil.php/perfilCuadrilla.php/
// perfilOperario.php (esas sí difieren por rol).
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/recolix/extra/i18n/i18n.php';

// Mismo fallback de sesión que usa el resto del código: admin/cuadrilla/operario guardan el CI
// bajo 'usuario', el portal de vecinos lo guarda suelto.
$ci = $_SESSION['usuario']['ci_usuario'] ?? $_SESSION['ci_usuario'] ?? null;
if (!$ci) {
    header('Location: /recolix/LandingPage/login.php');
    exit;
}

// El "Inicio" y el ícono de perfil del navbar apuntan a un lugar distinto según el rol,
// porque cada portal tiene su propia página de inicio y, para cuadrilla/operario, su propio
// perfil (perfilCuadrilla.php/perfilOperario.php en vez de perfil.php).
$rolSesion = $_SESSION['usuario']['rol'] ?? (isset($_SESSION['ci_usuario']) ? 'vecino' : null);
$enlacesPorRol = [
    'administrador' => ['inicio' => '/recolix/Backoffice/indexBackoffice.php', 'perfil' => '/recolix/API_Usuarios/perfil.php'],
    'cuadrilla'      => ['inicio' => '/recolix/API_Usuarios/Vista/cuadrilla.php', 'perfil' => '/recolix/API_Usuarios/Vista/perfilCuadrilla.php'],
    'operario'       => ['inicio' => '/recolix/API_Usuarios/Vista/operario.php', 'perfil' => '/recolix/API_Usuarios/Vista/perfilOperario.php'],
    'vecino'         => ['inicio' => '/recolix/API_Usuarios/vecino.php', 'perfil' => '/recolix/API_Usuarios/vecino.php'],
];
$enlaces = $enlacesPorRol[$rolSesion] ?? $enlacesPorRol['vecino'];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($RECOLIX_IDIOMA) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(t('common.campana_titulo')) ?> — Recolix</title>
    <link rel="stylesheet" href="/recolix/Backoffice/styleBackoffice.css">
</head>
<body>

<header class="navbar">
    <div class="nav-inner">
        <div class="nav-left">
            <a href="<?= htmlspecialchars($enlaces['inicio']) ?>" class="brand">
                <img src="/recolix/API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="brand-logo">
                <span class="brand-name">Recolix</span>
            </a>
        </div>
        <div class="nav-right">
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/recolix/extra/i18n/switcher.php'; ?>
            <a href="<?= htmlspecialchars($enlaces['perfil']) ?>" class="btn-user" title="<?= htmlspecialchars(t('backoffice.nav_perfil_usuario')) ?>">
                <img src="/recolix/API_Recoleccion/Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
            </a>
        </div>
    </div>
    <div class="nav-hr"></div>
</header>

<section class="hero">
    <h1 class="hero-title"><?= htmlspecialchars(t('common.campana_titulo')) ?></h1>
    <p class="hero-sub"><?= htmlspecialchars(t('common.campana_subtitulo')) ?></p>
</section>

<div class="notif-toolbar">
    <a href="#" id="volverLink" class="notif-volver">&larr; <?= htmlspecialchars(t('common.campana_volver')) ?></a>
</div>

<main class="container dash-main">
    <section class="dash-section" id="seccionNotificaciones">
        <div id="notifLista">
            <p class="estado-carga"><?= htmlspecialchars(t('common.campana_cargando')) ?></p>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <div class="footer-logo">
                <img src="/recolix/API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="footer-logo-img">
                <span class="footer-logo-name">Recolix</span>
            </div>
            <p><?= t('common.footer_desc') ?></p>
        </div>
        <div class="footer-col">
            <h3><?= t('common.footer_navegacion') ?></h3>
            <ul>
                <li><a href="/recolix/LandingPage/index_LandingPage.html"><?= t('backoffice.footer_pagina_principal') ?></a></li>
                <li><a href="/recolix/LandingPage/mapa.html"><?= t('backoffice.footer_mapa_publico') ?></a></li>
            </ul>
        </div>
    </div>
    <div class="footer-hr"></div>
    <p class="footer-copy"><?= t('backoffice.footer_copyright') ?></p>
</footer>

<style>
    .notif-toolbar {
        padding: 0 32px 18px;
    }
    #seccionNotificaciones {
        padding: 0 32px 40px;
    }
    .notif-volver {
        color: var(--muted);
        font-size: 0.85rem;
        font-weight: 600;
        transition: color 0.2s;
    }
    .notif-volver:hover {
        color: var(--moss);
    }
    .estado-carga,
    .estado-vacio {
        color: var(--ink-soft);
        padding: 16px 0;
        font-size: 0.9rem;
    }
    .campana-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 18px 22px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        margin-bottom: 12px;
    }
    .campana-item.no-leida {
        border-left: 3px solid var(--leaf);
    }
    .campana-punto {
        width: 9px;
        height: 9px;
        margin-top: 6px;
        border-radius: 50%;
        background: var(--leaf);
        flex-shrink: 0;
    }
    .campana-item.leida .campana-punto {
        background: #ccc;
    }
    .campana-cuerpo {
        flex: 1;
        min-width: 0;
    }
    .campana-mensaje {
        font-size: 0.92rem;
        color: var(--ink);
        margin: 0 0 6px;
    }
    .campana-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        font-size: 0.72rem;
        color: var(--ink-soft);
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .campana-acciones {
        display: flex;
        gap: 8px;
        flex-shrink: 0;
    }
    .campana-accion {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 1px solid var(--border);
        background: transparent;
        color: var(--ink-soft);
        font-size: 12px;
        line-height: 1;
        cursor: pointer;
        transition: all 0.15s;
    }
    .campana-marcar:hover {
        background: var(--moss);
        border-color: var(--moss);
        color: #fff;
    }
    .campana-borrar:hover {
        background: var(--status-crit);
        border-color: var(--status-crit);
        color: #fff;
    }
</style>
<script src="/recolix/extra/Comun/notificaciones.js"></script>
<script>
    document.getElementById('volverLink').addEventListener('click', (e) => {
        e.preventDefault();
        if (document.referrer && document.referrer.includes('/recolix/')) {
            history.back();
        } else {
            window.location.href = <?= json_encode($enlaces['inicio']) ?>;
        }
    });

    initListaNotificaciones({
        ci: <?= json_encode((int) $ci) ?>,
        apiUsuarios: '/recolix/API_Usuarios/api.php',
        i18n: <?= json_encode([
            'vacio' => t('common.campana_vacio'),
            'error' => t('common.campana_error'),
            'marcarLeida' => t('common.campana_marcar_leida'),
            'borrar' => t('common.campana_borrar'),
        ]) ?>
    });
</script>
</body>
</html>
