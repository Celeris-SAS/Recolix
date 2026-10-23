<?php
// Exportación pública (CSV o PDF) del resumen de la página de reportes. Sin sesión: son los mismos
// totales que muestra reportes.html.
require_once __DIR__ . '/../extra/i18n/i18n.php';
require_once __DIR__ . '/../API_Gestion/Modelo/reporteModel.php';
require_once __DIR__ . '/../API_Gestion/Util/PdfTabla.php';

$formato = ($_GET['formato'] ?? 'csv') === 'pdf' ? 'pdf' : 'csv';

$etiquetas = [
    '24h'    => t('reportes_publicos.periodo_24h'),
    'semana' => t('reportes_publicos.periodo_semana'),
    'mes'    => t('reportes_publicos.periodo_mes'),
    'anio'   => t('reportes_publicos.periodo_anio'),
];

$tabla = [[t('reportes_publicos.th_periodo'), t('reportes_publicos.resueltas'), t('reportes_publicos.th_volumen_kg')]];
foreach ((new ReporteModel())->getResumenPublico() as $periodo => $datos) {
    $tabla[] = [$etiquetas[$periodo], $datos['incidenciasResueltas'], $datos['volumenKg']];
}

$nombreArchivo = 'recolix_reportes_' . date('Ymd_His') . '.' . $formato;

if ($formato === 'pdf') {
    $encabezados = array_shift($tabla);
    $filas = array_map(function ($fila) {
        return array_map('strval', $fila);
    }, $tabla);
    $pdf = (new PdfTabla(
        t('reportes_publicos.titulo'),
        str_replace('{fecha}', date('d/m/Y H:i'), t('exportacion.generado_el')),
        $encabezados,
        $filas,
        ['pagina' => t('exportacion.pagina_de'), 'sinDatos' => t('exportacion.sin_datos')]
    ))->generar();
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
    exit;
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
$salida = fopen('php://output', 'w');
fwrite($salida, "\xEF\xBB\xBF");
foreach ($tabla as $fila) {
    fputcsv($salida, $fila);
}
fclose($salida);
