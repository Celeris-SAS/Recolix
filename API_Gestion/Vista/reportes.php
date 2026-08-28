<?php
require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php';
require_once __DIR__ . '/../../i18n/i18n.php';
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($RECOLIX_IDIOMA) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="google" content="notranslate">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('reportes.title') ?></title>
    <link rel="stylesheet" href="styleReportes.css">
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
        <h1 class="hero-title"><?= t('reportes.hero_title') ?></h1>
        <p class="hero-sub"><?= t('reportes.hero_sub') ?></p>
        <div class="filtro-rango" id="filtroRangoDashboard">
            <button type="button" class="filtro-btn filtro-btn-active" data-rango="hoy"><?= t('reportes.filtro_hoy') ?></button>
            <button type="button" class="filtro-btn" data-rango="semana"><?= t('reportes.filtro_semana') ?></button>
            <button type="button" class="filtro-btn" data-rango="mes"><?= t('reportes.filtro_mes') ?></button>
            <button type="button" class="filtro-btn" data-rango="siempre"><?= t('reportes.filtro_siempre') ?></button>
        </div>
    </section>

    <section class="reportes-top">
        <div class="stat-card">
            <div class="stat-grid">
                <div class="stat-item">
                    <div class="stat-value" id="statContenedores">—</div>
                    <div class="stat-label"><?= t('reportes.kpi_contenedores_recolectados') ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="statUsuarios">—</div>
                    <div class="stat-label"><?= t('reportes.kpi_usuarios_registrados') ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="statResiduos">—</div>
                    <div class="stat-label"><?= t('reportes.kpi_residuos_recolectados') ?></div>
                </div>
            </div>
        </div>
        <div class="chart-card">
            <h2><?= t('reportes.chart_llenado_title') ?></h2>
            <div class="chart-canvas-wrap">
                <canvas id="chartLlenado"></canvas>
            </div>
        </div>
    </section>

    <section class="gauges-card">
        <div class="gauge-item">
            <div class="gauge-canvas-wrap">
                <canvas id="gaugeFuncionales"></canvas>
                <div class="gauge-value" id="valFuncionales">—</div>
            </div>
            <div class="gauge-label"><?= t('reportes.gauge_contenedores_funcionales') ?></div>
        </div>
        <div class="gauge-item">
            <div class="gauge-canvas-wrap">
                <canvas id="gaugeSinRecolectar"></canvas>
                <div class="gauge-value" id="valSinRecolectar">—</div>
            </div>
            <div class="gauge-label"><?= t('reportes.gauge_contenedores_sin_recolectar') ?></div>
        </div>
        <div class="gauge-item">
            <div class="gauge-canvas-wrap">
                <canvas id="gaugeIncidencias"></canvas>
                <div class="gauge-value" id="valIncidencias">—</div>
            </div>
            <div class="gauge-label"><?= t('reportes.gauge_incidencias_resueltas') ?></div>
        </div>
        <div class="gauge-item">
            <div class="gauge-canvas-wrap">
                <canvas id="gaugeLevantes"></canvas>
                <div class="gauge-value" id="valLevantes">—</div>
            </div>
            <div class="gauge-label"><?= t('reportes.gauge_levantes_completados') ?></div>
        </div>
    </section>

    <section class="hub-section">
        <h2 class="hub-title"><?= t('reportes.hub_title') ?></h2>
        <p class="hub-sub"><?= t('reportes.hub_sub') ?></p>
    </section>

    <section class="cards-section">
        <div class="cards-grid">

            <a href="reporteDetalle.php?tipo=zonas_criticas" class="card-operar">
                <div class="card-content">
                    <div class="card-icon"><img src="../../Backoffice/img/exclamacion.png" alt="" class="card-svg-icon"></div>
                    <h3><?= t('reportes.card_zonas_criticas_titulo') ?></h3>
                    <p><?= t('reportes.card_zonas_criticas_desc') ?></p>
                </div>
            </a>

            <a href="reporteDetalle.php?tipo=volumen" class="card-operar">
                <div class="card-content">
                    <div class="card-icon"><img src="../../Backoffice/img/contenedor.png" alt="" class="card-svg-icon"></div>
                    <h3><?= t('reportes.card_volumen_titulo') ?></h3>
                    <p><?= t('reportes.card_volumen_desc') ?></p>
                </div>
            </a>

            <a href="reporteDetalle.php?tipo=incidencias" class="card-operar">
                <div class="card-content">
                    <div class="card-icon"><img src="img/refresh.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('reportes.card_incidencias_titulo') ?></h3>
                    <p><?= t('reportes.card_incidencias_desc') ?></p>
                </div>
            </a>

            <a href="reporteDetalle.php?tipo=frecuencia" class="card-operar">
                <div class="card-content">
                    <div class="card-icon"><img src="../../API_Recoleccion/Vista/img/truck-svgrepo-com.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('reportes.card_frecuencia_titulo') ?></h3>
                    <p><?= t('reportes.card_frecuencia_desc') ?></p>
                </div>
            </a>

            <a href="reporteDetalle.php?tipo=vertederos" class="card-operar">
                <div class="card-content">
                    <div class="card-icon"><img src="../../Backoffice/img/vertedero.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('reportes.card_vertederos_titulo') ?></h3>
                    <p><?= t('reportes.card_vertederos_desc') ?></p>
                </div>
            </a>

            <a href="reporteDetalle.php?tipo=clasificacion" class="card-operar">
                <div class="card-content">
                    <div class="card-icon"><img src="../../Backoffice/img/circuito.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('reportes.card_clasificacion_titulo') ?></h3>
                    <p><?= t('reportes.card_clasificacion_desc') ?></p>
                </div>
            </a>

            <a href="reporteDetalle.php?tipo=usuarios" class="card-operar">
                <div class="card-content">
                    <div class="card-icon"><img src="user.svg" alt="" class="card-svg-icon"></div>
                    <h3><?= t('reportes.card_usuarios_titulo') ?></h3>
                    <p><?= t('reportes.card_usuarios_desc') ?></p>
                </div>
            </a>

        </div>
    </section>

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

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        const API_BASE = '/recolix/API_Gestion';
        const COLOR_LEAF = '#8bbd3f';
        const COLOR_MOSS = '#3f5a33';
        const COLOR_TRACK = '#e4e2de';

        function formatearNumero(valor) {
            return new Intl.NumberFormat('es-UY').format(Math.round(valor));
        }

        let currentRango = 'hoy';

        function destruirChart(canvasId) {
            const existente = Chart.getChart(canvasId);
            if (existente) existente.destroy();
        }

        async function cargarResumen() {
            try {
                const res  = await fetch(`${API_BASE}/reportes/getResumenGeneral?rango=${currentRango}`);
                const json = await res.json();
                if (json.status !== 'success') return;
                document.getElementById('statContenedores').textContent = formatearNumero(json.data.contenedoresRecolectados);
                document.getElementById('statUsuarios').textContent = formatearNumero(json.data.usuariosRegistrados);
                document.getElementById('statResiduos').textContent = formatearNumero(json.data.residuosRecolectados);
            } catch {
                ['statContenedores', 'statUsuarios', 'statResiduos'].forEach(id => {
                    document.getElementById(id).textContent = '0';
                });
            }
        }

        function crearGauge(canvasId, valorId, porcentaje) {
            destruirChart(canvasId);
            const ctx = document.getElementById(canvasId);
            document.getElementById(valorId).textContent = `${porcentaje}%`;
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [porcentaje, Math.max(0, 100 - porcentaje)],
                        backgroundColor: [COLOR_LEAF, COLOR_TRACK],
                        borderWidth: 0
                    }]
                },
                options: {
                    cutout: '72%',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { enabled: false } }
                }
            });
        }

        async function cargarIndicadores() {
            try {
                const res  = await fetch(`${API_BASE}/reportes/getIndicadores?rango=${currentRango}`);
                const json = await res.json();
                const data = json.status === 'success' ? json.data : {};
                crearGauge('gaugeFuncionales', 'valFuncionales', data.contenedoresFuncionales ?? 0);
                crearGauge('gaugeSinRecolectar', 'valSinRecolectar', data.contenedoresSinRecolectar ?? 0);
                crearGauge('gaugeIncidencias', 'valIncidencias', data.incidenciasResueltas ?? 0);
                crearGauge('gaugeLevantes', 'valLevantes', data.levantesCompletados ?? 0);
            } catch {
                ['gaugeFuncionales', 'gaugeSinRecolectar', 'gaugeIncidencias', 'gaugeLevantes'].forEach((id, i) => {
                    const valIds = ['valFuncionales', 'valSinRecolectar', 'valIncidencias', 'valLevantes'];
                    crearGauge(id, valIds[i], 0);
                });
            }
        }

        async function cargarDistribucionLlenado() {
            let etiquetas = [];
            let valores   = [];
            try {
                const res  = await fetch(`${API_BASE}/reportes/getDistribucionLlenado?rango=${currentRango}`);
                const json = await res.json();
                if (json.status === 'success') {
                    etiquetas = json.data.etiquetas;
                    valores   = json.data.valores;
                }
            } catch {}

            destruirChart('chartLlenado');
            new Chart(document.getElementById('chartLlenado'), {
                type: 'bar',
                data: {
                    labels: etiquetas,
                    datasets: [{
                        data: valores,
                        backgroundColor: COLOR_LEAF,
                        borderRadius: 4,
                        maxBarThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { title: { display: true, text: <?= json_encode(t('reportes.chart_llenado_eje_x')) ?> }, grid: { display: false } },
                        y: { title: { display: true, text: <?= json_encode(t('reportes.chart_llenado_eje_y')) ?> }, beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }

        function cargarDashboard() {
            cargarResumen();
            cargarIndicadores();
            cargarDistribucionLlenado();
        }

        document.addEventListener('DOMContentLoaded', () => {
            cargarDashboard();
            document.querySelectorAll('#filtroRangoDashboard .filtro-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (btn.classList.contains('filtro-btn-active')) return;
                    document.querySelectorAll('#filtroRangoDashboard .filtro-btn').forEach(b => b.classList.remove('filtro-btn-active'));
                    btn.classList.add('filtro-btn-active');
                    currentRango = btn.dataset.rango;
                    cargarDashboard();
                });
            });
        });

        function toggleMobileMenu() {
            document.getElementById('navMobile').classList.toggle('nav-mobile-open');
        }
    </script>

</body>
</html>
