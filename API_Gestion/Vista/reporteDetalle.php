<?php
require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php';
require_once __DIR__ . '/../../i18n/i18n.php';

$tiposReporte = [
    'zonas_criticas' => ['titulo' => 'reportes.card_zonas_criticas_titulo', 'desc' => 'reportes.zonas_hero_sub',    'implementado' => true],
    'volumen'        => ['titulo' => 'reportes.card_volumen_titulo',        'desc' => 'reportes.card_volumen_desc',        'implementado' => true],
    'incidencias'    => ['titulo' => 'reportes.card_incidencias_titulo',    'desc' => 'reportes.card_incidencias_desc',    'implementado' => true],
    'frecuencia'     => ['titulo' => 'reportes.card_frecuencia_titulo',     'desc' => 'reportes.card_frecuencia_desc',     'implementado' => true],
    'vertederos'     => ['titulo' => 'reportes.card_vertederos_titulo',     'desc' => 'reportes.card_vertederos_desc',     'implementado' => true],
    'clasificacion'  => ['titulo' => 'reportes.card_clasificacion_titulo',  'desc' => 'reportes.card_clasificacion_desc',  'implementado' => true],
    'usuarios'       => ['titulo' => 'reportes.card_usuarios_titulo',       'desc' => 'reportes.card_usuarios_desc',       'implementado' => true],
    'camiones'       => ['titulo' => 'reportes.card_camiones_titulo',       'desc' => 'reportes.card_camiones_desc',       'implementado' => true],
    'maquinaria'     => ['titulo' => 'reportes.card_maquinaria_titulo',     'desc' => 'reportes.card_maquinaria_desc',     'implementado' => true],
];

$tipo = $_GET['tipo'] ?? '';
if (!isset($tiposReporte[$tipo])) {
    header('Location: reportes.php');
    exit;
}

$config = $tiposReporte[$tipo];

$reportEndpoints = [
    'volumen'       => 'getVolumenPorTipoYPeriodo',
    'incidencias'   => 'getIncidenciasDetalle',
    'frecuencia'    => 'getFrecuenciaRecoleccion',
    'vertederos'    => 'getCapacidadVertederos',
    'clasificacion' => 'getClasificacionResiduos',
    'usuarios'      => 'getEvolucionUsuarios',
    'camiones'      => 'getDisponibilidadCamiones',
    'maquinaria'    => 'getDisponibilidadMaquinaria',
];

$reportColumns = [
    'volumen' => [
        ['key' => 'periodo',            'label' => 'reportes.volumen_th_periodo',       'fmt' => 'text'],
        ['key' => 'tipoResiduo',        'label' => 'reportes.volumen_th_tipo',          'fmt' => 'tipoResiduo'],
        ['key' => 'totalKg',            'label' => 'reportes.volumen_th_kg',            'fmt' => 'kg'],
        ['key' => 'totalRecolecciones', 'label' => 'reportes.volumen_th_recolecciones', 'fmt' => 'num'],
    ],
    'incidencias' => [
        ['key' => 'tipo',            'label' => 'reportes.incidencias_th_tipo',      'fmt' => 'tipoIncidencia'],
        ['key' => 'prioridad',       'label' => 'reportes.incidencias_th_prioridad', 'fmt' => 'prioridadBadge'],
        ['key' => 'estado',          'label' => 'reportes.incidencias_th_estado',    'fmt' => 'estadoIncidencia'],
        ['key' => 'fechaApertura',   'label' => 'reportes.incidencias_th_fecha',     'fmt' => 'text'],
        ['key' => 'horasResolucion', 'label' => 'reportes.incidencias_th_horas',     'fmt' => 'horas'],
    ],
    'frecuencia' => [
        ['key' => 'circuito',            'label' => 'reportes.frecuencia_th_circuito',   'fmt' => 'text'],
        ['key' => 'codigo',              'label' => 'reportes.frecuencia_th_codigo',     'fmt' => 'text'],
        ['key' => 'municipio',           'label' => 'reportes.frecuencia_th_municipio',  'fmt' => 'text'],
        ['key' => 'turno',               'label' => 'reportes.frecuencia_th_turno',      'fmt' => 'turno'],
        ['key' => 'frecuencia',          'label' => 'reportes.frecuencia_th_frecuencia', 'fmt' => 'text'],
        ['key' => 'levantesCompletados', 'label' => 'reportes.frecuencia_th_levantes',   'fmt' => 'num'],
    ],
    'vertederos' => [
        ['key' => 'nombre',          'label' => 'reportes.vertederos_th_nombre',     'fmt' => 'text'],
        ['key' => 'municipio',       'label' => 'reportes.vertederos_th_municipio',  'fmt' => 'text'],
        ['key' => 'tipo',            'label' => 'reportes.vertederos_th_tipo',       'fmt' => 'tipoVertedero'],
        ['key' => 'estado',          'label' => 'reportes.vertederos_th_estado',     'fmt' => 'estadoVertedero'],
        ['key' => 'capacidadTotal',  'label' => 'reportes.vertederos_th_capacidad',  'fmt' => 'num'],
        ['key' => 'ocupacionActual', 'label' => 'reportes.vertederos_th_ocupacion',  'fmt' => 'num'],
        ['key' => 'porcentaje',      'label' => 'reportes.vertederos_th_porcentaje', 'fmt' => 'porcentajeBadge'],
    ],
    'clasificacion' => [
        ['key' => 'tipoResiduo',       'label' => 'reportes.clasificacion_th_tipo',        'fmt' => 'tipoResiduo'],
        ['key' => 'reciclable',        'label' => 'reportes.clasificacion_th_reciclable',  'fmt' => 'reciclableBadge'],
        ['key' => 'totalContenedores', 'label' => 'reportes.clasificacion_th_total',       'fmt' => 'num'],
        ['key' => 'funcionales',       'label' => 'reportes.clasificacion_th_funcionales', 'fmt' => 'num'],
        ['key' => 'totalKg',           'label' => 'reportes.clasificacion_th_kg',          'fmt' => 'kg'],
        ['key' => 'porcentaje',        'label' => 'reportes.clasificacion_th_porcentaje',  'fmt' => 'porcentajeBadge'],
    ],
    'usuarios' => [
        ['key' => 'periodo',         'label' => 'reportes.usuarios_th_periodo',         'fmt' => 'text'],
        ['key' => 'nuevosUsuarios',  'label' => 'reportes.usuarios_th_nuevos',          'fmt' => 'num'],
        ['key' => 'vecinos',         'label' => 'reportes.usuarios_th_vecinos',         'fmt' => 'num'],
        ['key' => 'operarios',       'label' => 'reportes.usuarios_th_operarios',       'fmt' => 'num'],
        ['key' => 'cuadrillas',      'label' => 'reportes.usuarios_th_cuadrillas',      'fmt' => 'num'],
        ['key' => 'administradores', 'label' => 'reportes.usuarios_th_administradores', 'fmt' => 'num'],
    ],
    'camiones' => [
        ['key' => 'matricula',          'label' => 'reportes.camiones_th_matricula',   'fmt' => 'text'],
        ['key' => 'modelo',             'label' => 'reportes.camiones_th_modelo',      'fmt' => 'text'],
        ['key' => 'tipoCarga',          'label' => 'reportes.camiones_th_tipo_carga',  'fmt' => 'text'],
        ['key' => 'estado',             'label' => 'reportes.camiones_th_estado',      'fmt' => 'estadoCamion'],
        ['key' => 'viajesCompletados',  'label' => 'reportes.camiones_th_viajes',      'fmt' => 'num'],
        ['key' => 'kgRecolectados',     'label' => 'reportes.camiones_th_kg',          'fmt' => 'kg'],
        ['key' => 'horasEnRuta',        'label' => 'reportes.camiones_th_horas_ruta',  'fmt' => 'horas'],
        ['key' => 'horasMantenimiento', 'label' => 'reportes.camiones_th_horas_mant',  'fmt' => 'horas'],
    ],
    'maquinaria' => [
        ['key' => 'nombre',           'label' => 'reportes.maquinaria_th_nombre',    'fmt' => 'text'],
        ['key' => 'tipo',             'label' => 'reportes.maquinaria_th_tipo',      'fmt' => 'tipoMaquinaria'],
        ['key' => 'centro',           'label' => 'reportes.maquinaria_th_centro',    'fmt' => 'text'],
        ['key' => 'municipio',        'label' => 'reportes.maquinaria_th_municipio', 'fmt' => 'text'],
        ['key' => 'estado',           'label' => 'reportes.maquinaria_th_estado',    'fmt' => 'estadoMaquinaria'],
        ['key' => 'fechaAdquisicion', 'label' => 'reportes.maquinaria_th_fecha',     'fmt' => 'text'],
    ],
];

$reportEmptyKey = [
    'volumen'       => 'reportes.volumen_empty',
    'incidencias'   => 'reportes.incidencias_empty',
    'frecuencia'    => 'reportes.frecuencia_empty',
    'vertederos'    => 'reportes.vertederos_empty',
    'clasificacion' => 'reportes.clasificacion_empty',
    'usuarios'      => 'reportes.usuarios_empty',
    'camiones'      => 'reportes.camiones_empty',
    'maquinaria'    => 'reportes.maquinaria_empty',
];

$esReporteGenerico = isset($reportEndpoints[$tipo]);
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

    <?php elseif ($esReporteGenerico): ?>

        <section class="table-section">
            <div class="table-header">
                <h2 class="table-title"><?= t($config['titulo']) ?></h2>
                <span class="table-count" id="tableCount"></span>
            </div>
            <?php if ($tipo === 'usuarios'): ?>
            <div class="filtro-rango" id="filtroRango">
                <button type="button" class="filtro-btn" data-rango="24h"><?= t('reportes.usuarios_filtro_24h') ?></button>
                <button type="button" class="filtro-btn" data-rango="semana"><?= t('reportes.usuarios_filtro_semana') ?></button>
                <button type="button" class="filtro-btn" data-rango="mes"><?= t('reportes.usuarios_filtro_mes') ?></button>
                <button type="button" class="filtro-btn filtro-btn-active" data-rango="siempre"><?= t('reportes.usuarios_filtro_siempre') ?></button>
            </div>
            <?php elseif (in_array($tipo, ['volumen', 'incidencias', 'camiones'], true)): ?>
            <div class="filtro-rango" id="filtroRango">
                <button type="button" class="filtro-btn" data-rango="hoy"><?= t('reportes.filtro_hoy') ?></button>
                <button type="button" class="filtro-btn" data-rango="semana"><?= t('reportes.filtro_semana') ?></button>
                <button type="button" class="filtro-btn" data-rango="mes"><?= t('reportes.filtro_mes') ?></button>
                <button type="button" class="filtro-btn filtro-btn-active" data-rango="siempre"><?= t('reportes.filtro_siempre') ?></button>
            </div>
            <?php elseif ($tipo === 'frecuencia'): ?>
            <div class="filtro-dropdowns" id="filtroFrecuencia">
                <div class="filtro-dropdown-item">
                    <label for="selectTurno"><?= t('reportes.frecuencia_filtro_turno') ?></label>
                    <select id="selectTurno" class="filtro-select">
                        <option value=""><?= t('reportes.frecuencia_filtro_todos') ?></option>
                        <option value="mañana"><?= t('reportes.frecuencia_turno_manana') ?></option>
                        <option value="tarde"><?= t('reportes.frecuencia_turno_tarde') ?></option>
                        <option value="noche"><?= t('reportes.frecuencia_turno_noche') ?></option>
                    </select>
                </div>
                <div class="filtro-dropdown-item">
                    <label for="selectDia"><?= t('reportes.frecuencia_filtro_dia') ?></label>
                    <select id="selectDia" class="filtro-select">
                        <option value=""><?= t('reportes.frecuencia_filtro_todos') ?></option>
                        <option value="lunes"><?= t('reportes.dia_lunes') ?></option>
                        <option value="martes"><?= t('reportes.dia_martes') ?></option>
                        <option value="miércoles"><?= t('reportes.dia_miercoles') ?></option>
                        <option value="jueves"><?= t('reportes.dia_jueves') ?></option>
                        <option value="viernes"><?= t('reportes.dia_viernes') ?></option>
                        <option value="sábado"><?= t('reportes.dia_sabado') ?></option>
                        <option value="domingo"><?= t('reportes.dia_domingo') ?></option>
                    </select>
                </div>
            </div>
            <?php endif; ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <?php foreach ($reportColumns[$tipo] as $col): ?>
                                <th><?= t($col['label']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody id="reporteBody"></tbody>
                </table>
                <div id="emptyState" class="empty-state" style="display:none"><?= t($reportEmptyKey[$tipo]) ?></div>
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
            if (motivo === 'ambos') {
                return 'badge-danger';
            }
            return 'badge-warning';
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
                let zonas;
                if (json.status === 'success') {
                    zonas = json.data;
                } else {
                    zonas = [];
                }

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

    <?php if ($esReporteGenerico): ?>
    <script>
        const API_BASE     = '/recolix/API_Gestion';
        const REPORT_TIPO  = <?= json_encode($tipo) ?>;
        const REPORT_ENDPOINT = <?= json_encode($reportEndpoints[$tipo]) ?>;
        const REPORT_COLUMNS  = <?= json_encode(array_map(function ($col) {
            return ['key' => $col['key'], 'label' => t($col['label']), 'fmt' => $col['fmt']];
        }, $reportColumns[$tipo])) ?>;

        const LABELS_TIPO_INCIDENCIA = {
            rotura:            <?= json_encode(t('reportes.incidencias_tipo_rotura')) ?>,
            desborde:          <?= json_encode(t('reportes.incidencias_tipo_desborde')) ?>,
            falta_recoleccion: <?= json_encode(t('reportes.incidencias_tipo_falta_recoleccion')) ?>,
            vandalismo:        <?= json_encode(t('reportes.incidencias_tipo_vandalismo')) ?>,
            otro:              <?= json_encode(t('reportes.incidencias_tipo_otro')) ?>
        };
        const LABELS_PRIORIDAD = {
            baja:    <?= json_encode(t('reportes.incidencias_prioridad_baja')) ?>,
            media:   <?= json_encode(t('reportes.incidencias_prioridad_media')) ?>,
            alta:    <?= json_encode(t('reportes.incidencias_prioridad_alta')) ?>,
            critica: <?= json_encode(t('reportes.incidencias_prioridad_critica')) ?>
        };
        const LABELS_ESTADO_INCIDENCIA = {
            abierta:  <?= json_encode(t('reportes.incidencias_estado_abierta')) ?>,
            asignada: <?= json_encode(t('reportes.incidencias_estado_asignada')) ?>,
            resuelta: <?= json_encode(t('reportes.incidencias_estado_resuelta')) ?>
        };
        const LABELS_TURNO = {
            'mañana': <?= json_encode(t('reportes.frecuencia_turno_manana')) ?>,
            tarde:    <?= json_encode(t('reportes.frecuencia_turno_tarde')) ?>,
            noche:    <?= json_encode(t('reportes.frecuencia_turno_noche')) ?>
        };
        const LABELS_TIPO_VERTEDERO = {
            vertedero:        <?= json_encode(t('reportes.vertederos_tipo_vertedero')) ?>,
            relleno_sanitario: <?= json_encode(t('reportes.vertederos_tipo_relleno_sanitario')) ?>
        };
        const LABELS_ESTADO_VERTEDERO = {
            operativo: <?= json_encode(t('reportes.vertederos_estado_operativo')) ?>,
            saturado:  <?= json_encode(t('reportes.vertederos_estado_saturado')) ?>,
            cerrado:   <?= json_encode(t('reportes.vertederos_estado_cerrado')) ?>
        };
        const LABELS_ESTADO_CAMION = {
            disponible:        <?= json_encode(t('reportes.camiones_estado_disponible')) ?>,
            en_ruta:           <?= json_encode(t('reportes.camiones_estado_en_ruta')) ?>,
            en_mantenimiento:  <?= json_encode(t('reportes.camiones_estado_en_mantenimiento')) ?>,
            de_baja:           <?= json_encode(t('reportes.camiones_estado_de_baja')) ?>,
            en_deposito:       <?= json_encode(t('reportes.camiones_estado_en_deposito')) ?>
        };
        const LABELS_TIPO_MAQUINARIA = {
            compactadora: <?= json_encode(t('reportes.maquinaria_tipo_compactadora')) ?>,
            cinta:        <?= json_encode(t('reportes.maquinaria_tipo_cinta')) ?>,
            pala:         <?= json_encode(t('reportes.maquinaria_tipo_pala')) ?>,
            bascula:      <?= json_encode(t('reportes.maquinaria_tipo_bascula')) ?>,
            herramienta:  <?= json_encode(t('reportes.maquinaria_tipo_herramienta')) ?>,
            otro:         <?= json_encode(t('reportes.maquinaria_tipo_otro')) ?>
        };
        const LABELS_ESTADO_MAQUINARIA = {
            operativa:         <?= json_encode(t('reportes.maquinaria_estado_operativa')) ?>,
            en_mantenimiento:  <?= json_encode(t('reportes.maquinaria_estado_en_mantenimiento')) ?>,
            fuera_servicio:    <?= json_encode(t('reportes.maquinaria_estado_fuera_servicio')) ?>
        };
        const LABEL_SI = <?= json_encode(t('reportes.badge_si')) ?>;
        const LABEL_NO = <?= json_encode(t('reportes.badge_no')) ?>;
        const LABEL_RESULTADOS = <?= json_encode(t('reportes.detalle_resultados')) ?>;
        const LABEL_SIN_DATOS = '—';

        function formatearNumero(valor) {
            return new Intl.NumberFormat('es-UY').format(Math.round(valor));
        }

        const MAPA_COLORES = {
            gris: '#9e9e9e', verde: '#4caf50', marron: '#795548', 'marrón': '#795548',
            negro: '#212121', amarillo: '#e3b23c', azul: '#3f51b5', rojo: '#c0392b',
            naranja: '#e67e22', blanco: '#e0e0e0', celeste: '#4fc3f7', violeta: '#8e44ad'
        };

        function colorParaTipoResiduo(nombre) {
            if (!nombre) return '#999';
            return MAPA_COLORES[nombre.trim().toLowerCase()] || '#999';
        }

        function badgeParaPorcentaje(pct) {
            if (pct >= 50) return 'badge-danger';
            if (pct > 20) return 'badge-warning';
            return 'badge-success';
        }

        function badgeParaPrioridad(prioridad) {
            if (prioridad === 'critica') return 'badge-danger';
            if (prioridad === 'alta') return 'badge-warning';
            return 'badge-success';
        }

        function badgeParaEstadoIncidencia(estado) {
            if (estado === 'resuelta') return 'badge-success';
            if (estado === 'asignada') return 'badge-warning';
            return 'badge-danger';
        }

        function badgeParaEstadoVertedero(estado) {
            if (estado === 'saturado') return 'badge-warning';
            if (estado === 'cerrado') return 'badge-danger';
            return 'badge-success';
        }

        function badgeParaEstadoCamion(estado) {
            if (estado === 'disponible' || estado === 'en_ruta') return 'badge-success';
            if (estado === 'de_baja') return 'badge-danger';
            return 'badge-warning';
        }

        function badgeParaEstadoMaquinaria(estado) {
            if (estado === 'operativa') return 'badge-success';
            if (estado === 'fuera_servicio') return 'badge-danger';
            return 'badge-warning';
        }

        function formatCelda(row, col) {
            const valor = row[col.key];
            switch (col.fmt) {
                case 'kg':
                    return `${formatearNumero(valor)} kg`;
                case 'num':
                    if (valor === null || valor === undefined) {
                        return LABEL_SIN_DATOS;
                    }
                    return formatearNumero(valor);
                case 'horas':
                    if (valor === null || valor === undefined) {
                        return LABEL_SIN_DATOS;
                    }
                    return `${formatearNumero(valor)} h`;
                case 'tipoResiduo':
                    return `<span class="color-dot" style="background:${colorParaTipoResiduo(row.color)}"></span>${valor ?? ''}`;
                case 'reciclableBadge': {
                    let claseBadge, textoBadge;
                    if (valor) {
                        claseBadge = 'badge-success';
                        textoBadge = LABEL_SI;
                    } else {
                        claseBadge = 'badge-warning';
                        textoBadge = LABEL_NO;
                    }
                    return `<span class="badge ${claseBadge}">${textoBadge}</span>`;
                }
                case 'porcentajeBadge':
                    return `<span class="badge ${badgeParaPorcentaje(valor)}">${valor}%</span>`;
                case 'tipoIncidencia':
                    return LABELS_TIPO_INCIDENCIA[valor] ?? valor;
                case 'prioridadBadge':
                    return `<span class="badge ${badgeParaPrioridad(valor)}">${LABELS_PRIORIDAD[valor] ?? valor}</span>`;
                case 'estadoIncidencia':
                    return `<span class="badge ${badgeParaEstadoIncidencia(valor)}">${LABELS_ESTADO_INCIDENCIA[valor] ?? valor}</span>`;
                case 'turno':
                    return LABELS_TURNO[valor] ?? (valor ?? LABEL_SIN_DATOS);
                case 'tipoVertedero':
                    return LABELS_TIPO_VERTEDERO[valor] ?? valor;
                case 'estadoVertedero':
                    return `<span class="badge ${badgeParaEstadoVertedero(valor)}">${LABELS_ESTADO_VERTEDERO[valor] ?? valor}</span>`;
                case 'estadoCamion':
                    return `<span class="badge ${badgeParaEstadoCamion(valor)}">${LABELS_ESTADO_CAMION[valor] ?? valor}</span>`;
                case 'estadoMaquinaria':
                    return `<span class="badge ${badgeParaEstadoMaquinaria(valor)}">${LABELS_ESTADO_MAQUINARIA[valor] ?? valor}</span>`;
                case 'tipoMaquinaria':
                    return LABELS_TIPO_MAQUINARIA[valor] ?? (valor ?? LABEL_SIN_DATOS);
                default:
                    if (valor === null || valor === undefined || valor === '') {
                        return LABEL_SIN_DATOS;
                    }
                    return valor;
            }
        }

        const TIPOS_CON_FILTRO_RANGO = ['usuarios', 'volumen', 'incidencias', 'camiones'];
        let currentRango = 'siempre';
        let currentTurno = '';
        let currentDia   = '';

        async function cargarReporteGenerico() {
            const tbody   = document.getElementById('reporteBody');
            const loading = document.getElementById('loadingState');
            const empty   = document.getElementById('emptyState');
            const count   = document.getElementById('tableCount');

            loading.style.display = 'flex';
            empty.style.display   = 'none';
            tbody.innerHTML = '';

            try {
                let url = `${API_BASE}/reportes/${REPORT_ENDPOINT}`;
                if (TIPOS_CON_FILTRO_RANGO.includes(REPORT_TIPO)) {
                    url += `?rango=${currentRango}`;
                } else if (REPORT_TIPO === 'frecuencia') {
                    url += `?turno=${encodeURIComponent(currentTurno)}&dia=${encodeURIComponent(currentDia)}`;
                }
                const res  = await fetch(url);
                const json = await res.json();
                let filas;
                if (json.status === 'success') {
                    filas = json.data;
                } else {
                    filas = [];
                }

                loading.style.display = 'none';

                if (filas.length === 0) {
                    empty.style.display = 'flex';
                    count.textContent = '';
                    return;
                }

                count.textContent = `${filas.length} ${LABEL_RESULTADOS}`;

                filas.forEach(fila => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = REPORT_COLUMNS.map(col =>
                        `<td data-label="${col.label}">${formatCelda(fila, col)}</td>`
                    ).join('');
                    tbody.appendChild(tr);
                });
            } catch {
                loading.style.display = 'none';
                empty.style.display   = 'flex';
            }
        }

        function exportarCsv() {
            let url = `exportarReporte.php?tipo=${REPORT_TIPO}`;
            if (TIPOS_CON_FILTRO_RANGO.includes(REPORT_TIPO)) {
                url += `&rango=${currentRango}`;
            } else if (REPORT_TIPO === 'frecuencia') {
                url += `&turno=${encodeURIComponent(currentTurno)}&dia=${encodeURIComponent(currentDia)}`;
            }
            window.location.href = url;
        }

        if (TIPOS_CON_FILTRO_RANGO.includes(REPORT_TIPO)) {
            document.querySelectorAll('#filtroRango .filtro-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (btn.classList.contains('filtro-btn-active')) return;
                    document.querySelectorAll('#filtroRango .filtro-btn').forEach(b => b.classList.remove('filtro-btn-active'));
                    btn.classList.add('filtro-btn-active');
                    currentRango = btn.dataset.rango;
                    cargarReporteGenerico();
                });
            });
        }

        if (REPORT_TIPO === 'frecuencia') {
            document.getElementById('selectTurno').addEventListener('change', e => {
                currentTurno = e.target.value;
                cargarReporteGenerico();
            });
            document.getElementById('selectDia').addEventListener('change', e => {
                currentDia = e.target.value;
                cargarReporteGenerico();
            });
        }

        document.addEventListener('DOMContentLoaded', cargarReporteGenerico);
    </script>
    <?php endif; ?>

    <script>
        function toggleMobileMenu() {
            document.getElementById('navMobile').classList.toggle('nav-mobile-open');
        }
    </script>

</body>
</html>
