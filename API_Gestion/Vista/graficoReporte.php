<?php // Gráfico del informe, encima de la tabla (ver graficoReporte.js). ?>
<div class="chart-reporte" id="chartReporteWrap">
    <h3 class="chart-reporte-titulo" id="chartReporteTitulo"></h3>
    <div class="chart-reporte-canvas"><canvas id="chartReporte"></canvas></div>
    <p class="chart-reporte-vacio" id="chartReporteVacio" hidden><?= t('reportes.grafico_vacio') ?></p>
</div>
