<?php
require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php';
require_once __DIR__ . '/../../i18n/i18n.php';

$tiposReporte = [
    'zonas_criticas' => ['titulo' => 'reportes.card_zonas_criticas_titulo', 'desc' => 'reportes.zonas_hero_sub', 'implementado' => true],
    'volumen'        => ['titulo' => 'reportes.card_volumen_titulo',        'desc' => 'reportes.card_volumen_desc',        'implementado' => false],
    'incidencias'    => ['titulo' => 'reportes.card_incidencias_titulo',    'desc' => 'reportes.card_incidencias_desc',    'implementado' => false],
    'frecuencia'     => ['titulo' => 'reportes.card_frecuencia_titulo',     'desc' => 'reportes.card_frecuencia_desc',     'implementado' => false],
    'vertederos'     => ['titulo' => 'reportes.card_vertederos_titulo',     'desc' => 'reportes.card_vertederos_desc',     'implementado' => false],
    'clasificacion'  => ['titulo' => 'reportes.card_clasificacion_titulo',  'desc' => 'reportes.card_clasificacion_desc',  'implementado' => false],
    'reciclaje'      => ['titulo' => 'reportes.card_reciclaje_titulo',      'desc' => 'reportes.card_reciclaje_desc',      'implementado' => false],
    'usuarios'       => ['titulo' => 'reportes.card_usuarios_titulo',       'desc' => 'reportes.card_usuarios_desc',       'implementado' => false],
];

$tipo = $_GET['tipo'] ?? '';
if (!isset($tiposReporte[$tipo])) {
    header('Location: reportes.php');
    exit;
}

$config = $tiposReporte[$tipo];
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($RECOLIX_IDIOMA) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t($config['titulo']) ?> — Recolix</title>
    <link rel="stylesheet" href="styleReporteDetalle.css">
</head>

<body>

    <header class="navbar">
        <div class="nav-inner">
            <div class="nav-left">
                <a href="../../Backoffice/indexBackoffice.php" class="brand">
                    <img src="logoRecolix.png" alt="Recolix" class="brand-logo">
                    <span class="brand-name">Recolix</span>
                </a>
                <nav class="nav-links">
                    <a href="../../Backoffice/indexBackoffice.php"><?= t('backoffice.nav_inicio') ?></a>
                    <a href="../../API_Recoleccion/Vista/camiones.php"><?= t('backoffice.nav_camiones') ?></a>
                    <a href="../../API_Recoleccion/Vista/circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
                    <a href="../../API_Recoleccion/Vista/rutas.php"><?= t('backoffice.nav_rutas') ?></a>
                    <a href="../../API_Usuarios/Vista/cuadrillas.php"><?= t('backoffice.nav_cuadrillas') ?></a>
                    <a href="contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
                    <a href="centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
                    <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
                    <a href="reportes.php" class="nav-active"><?= t('backoffice.nav_reportes') ?></a>
                    <a href="#"><?= t('backoffice.nav_incidencias') ?></a>
                    <a href="../../API_Usuarios/Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
                </nav>
            </div>
            <div class="nav-right">
                <?php include __DIR__ . '/../../i18n/switcher.php'; ?>
                <a href="/recolix/API_Usuarios/perfil.php" class="btn-user" title="<?= htmlspecialchars(t('backoffice.nav_perfil_usuario')) ?>">
                    <img src="user.svg" alt="Usuario" class="nav-user-icon">
                </a>
                <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="<?= htmlspecialchars(t('backoffice.nav_menu')) ?>">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
        <nav class="nav-mobile" id="navMobile">
            <a href="../../Backoffice/indexBackoffice.php"><?= t('backoffice.nav_inicio') ?></a>
            <a href="../../API_Recoleccion/Vista/camiones.php"><?= t('backoffice.nav_camiones') ?></a>
            <a href="../../API_Recoleccion/Vista/circuitos.php"><?= t('backoffice.nav_circuitos') ?></a>
            <a href="../../API_Recoleccion/Vista/rutas.php"><?= t('backoffice.nav_rutas') ?></a>
            <a href="../../API_Usuarios/Vista/cuadrillas.php"><?= t('backoffice.nav_cuadrillas') ?></a>
            <a href="contenedores.php"><?= t('backoffice.nav_contenedores') ?></a>
            <a href="centrosAcopio.php"><?= t('backoffice.nav_centros_acopio') ?></a>
            <a href="../../Backoffice/mapa.php"><?= t('backoffice.nav_mapa') ?></a>
            <a href="reportes.php" class="nav-active"><?= t('backoffice.nav_reportes') ?></a>
            <a href="#"><?= t('backoffice.nav_incidencias') ?></a>
            <a href="../../API_Usuarios/Vista/usuarios.php"><?= t('backoffice.nav_usuarios') ?></a>
        </nav>
        <div class="nav-hr"></div>
    </header>

    <section class="hero">
        <h1 class="hero-title"><?= t($config['titulo']) ?></h1>
        <p class="hero-sub"><?= t($config['desc']) ?></p>
    </section>

    <div class="detalle-toolbar">
        <a href="reportes.php" class="detalle-volver">&larr; <?= t('reportes.detalle_volver') ?></a>
        <?php if ($config['implementado']): ?>
            <button class="btn-primary" id="btnExportar" onclick="exportarCsv()"><?= t('reportes.detalle_btn_exportar') ?></button>
        <?php endif; ?>
    </div>

    <?php if ($tipo === 'zonas_criticas'): ?>

        <section class="table-section">
            <div class="table-header">
                <h2 class="table-title"><?= t($config['titulo']) ?></h2>
                <span class="table-count" id="tableCount"></span>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th><?= t('reportes.zonas_th_municipio') ?></th>
                            <th><?= t('reportes.zonas_th_departamento') ?></th>
                            <th><?= t('reportes.zonas_th_incidencias') ?></th>
                            <th><?= t('reportes.zonas_th_desbordados') ?></th>
                            <th><?= t('reportes.zonas_th_total') ?></th>
                            <th><?= t('reportes.zonas_th_porcentaje') ?></th>
                            <th><?= t('reportes.zonas_th_motivo') ?></th>
                        </tr>
                    </thead>
                    <tbody id="zonasBody"></tbody>
                </table>
                <div id="emptyState" class="empty-state" style="display:none"><?= t('reportes.zonas_empty') ?></div>
                <div id="loadingState" class="loading-state"><?= t('crud.loading_state') ?></div>
            </div>
        </section>

    <?php else: ?>

        <div class="proximamente-card">
            <div class="proximamente-icon">🚧</div>
            <h2><?= t('reportes.detalle_proximamente_titulo') ?></h2>
            <p><?= t('reportes.detalle_proximamente_desc') ?></p>
        </div>

    <?php endif; ?>

    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="footer-logo">
                    <img src="logoRecolix.png" alt="Recolix" class="footer-logo-img">
                    <span class="footer-logo-name">Recolix</span>
                </div>
                <p><?= t('common.footer_desc') ?></p>
            </div>
            <div class="footer-col">
                <h3><?= t('common.footer_navegacion') ?></h3>
                <ul>
                    <li><a href="../../LandingPage/index_LandingPage.html"><?= t('backoffice.footer_pagina_principal') ?></a></li>
                    <li><a href="../../LandingPage/mapa.html"><?= t('backoffice.footer_mapa_publico') ?></a></li>
                </ul>
            </div>
        </div>
        <div class="footer-hr"></div>
        <p class="footer-copy"><?= t('backoffice.footer_copyright') ?></p>
    </footer>

    <?php if ($tipo === 'zonas_criticas'): ?>
    <script>
        const API_BASE = '/recolix/API_Gestion';

        const MOTIVO_LABELS = {
            incidencias: <?= json_encode(t('reportes.zonas_motivo_incidencias')) ?>,
            desbordados: <?= json_encode(t('reportes.zonas_motivo_desbordados')) ?>,
            ambos: <?= json_encode(t('reportes.zonas_motivo_ambos')) ?>
        };

        function badgeParaPorcentaje(pct) {
            if (pct >= 50) return 'badge-danger';
            if (pct > 20) return 'badge-warning';
            return 'badge-success';
        }

        function badgeParaMotivo(motivo) {
            return motivo === 'ambos' ? 'badge-danger' : 'badge-warning';
        }

        async function cargarZonasCriticas() {
            const tbody   = document.getElementById('zonasBody');
            const loading = document.getElementById('loadingState');
            const empty   = document.getElementById('emptyState');
            const count   = document.getElementById('tableCount');

            loading.style.display = 'flex';
            empty.style.display   = 'none';
            tbody.innerHTML = '';

            try {
                const res  = await fetch(`${API_BASE}/reportes/getZonasCriticas`);
                const json = await res.json();
                const zonas = json.status === 'success' ? json.data : [];

                loading.style.display = 'none';

                if (zonas.length === 0) {
                    empty.style.display = 'flex';
                    count.textContent = '';
                    return;
                }

                count.textContent = `${zonas.length} <?= addslashes(t('reportes.zonas_th_municipio')) ?>`;

                zonas.forEach(z => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td data-label="<?= addslashes(t('reportes.zonas_th_municipio')) ?>">${z.municipio}</td>
                        <td data-label="<?= addslashes(t('reportes.zonas_th_departamento')) ?>">${z.departamento}</td>
                        <td data-label="<?= addslashes(t('reportes.zonas_th_incidencias')) ?>">${z.incidenciasMes}</td>
                        <td data-label="<?= addslashes(t('reportes.zonas_th_desbordados')) ?>">${z.desbordados}</td>
                        <td data-label="<?= addslashes(t('reportes.zonas_th_total')) ?>">${z.totalContenedores}</td>
                        <td data-label="<?= addslashes(t('reportes.zonas_th_porcentaje')) ?>"><span class="badge ${badgeParaPorcentaje(z.porcentaje)}">${z.porcentaje}%</span></td>
                        <td data-label="<?= addslashes(t('reportes.zonas_th_motivo')) ?>"><span class="badge ${badgeParaMotivo(z.motivo)}">${MOTIVO_LABELS[z.motivo]}</span></td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch {
                loading.style.display = 'none';
                empty.style.display   = 'flex';
            }
        }

        function exportarCsv() {
            window.location.href = 'exportarReporte.php?tipo=zonas_criticas';
        }

        document.addEventListener('DOMContentLoaded', cargarZonasCriticas);
    </script>
    <?php endif; ?>

    <script>
        function toggleMobileMenu() {
            document.getElementById('navMobile').classList.toggle('nav-mobile-open');
        }
    </script>

</body>
</html>
