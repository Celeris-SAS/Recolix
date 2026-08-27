<?php
require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php';
require_once __DIR__ . '/../../conexion.php';
require_once __DIR__ . '/../Modelo/reporteModel.php';

$tiposExportables = [
    'zonas_criticas' => 'Zonas Urbanas Críticas',
];

$tipo = $_GET['tipo'] ?? '';
if (!isset($tiposExportables[$tipo])) {
    http_response_code(400);
    echo 'Tipo de reporte no exportable.';
    exit;
}

$reporteModel = new ReporteModel();
$zonas        = $reporteModel->getZonasCriticas();

$nombreArchivo = 'recolix_' . $tipo . '_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

$salida = fopen('php://output', 'w');
fwrite($salida, "\xEF\xBB\xBF");

$motivoLabels = ['incidencias' => 'Incidencias', 'desbordados' => 'Desbordados', 'ambos' => 'Ambos'];

fputcsv($salida, ['Municipio', 'Departamento', 'Incidencias (últ. mes)', 'Contenedores Desbordados', 'Total Contenedores', '% Desbordado', 'Motivo']);
foreach ($zonas as $zona) {
    fputcsv($salida, [
        $zona['municipio'],
        $zona['departamento'],
        $zona['incidenciasMes'],
        $zona['desbordados'],
        $zona['totalContenedores'],
        $zona['porcentaje'] . '%',
        $motivoLabels[$zona['motivo']] ?? $zona['motivo'],
    ]);
}
fclose($salida);

$conn      = new conexion();
$ciUsuario = $_SESSION['usuario']['ci_usuario'] ?? null;
if ($ciUsuario) {
    $stmt = mysqli_prepare($conn->getConexion(), "INSERT INTO REPORTE (ci_usuario, tipo, descripcion, formato) VALUES (?, 'csv', ?, 'CSV')");
    $descripcion = $tiposExportables[$tipo];
    mysqli_stmt_bind_param($stmt, "is", $ciUsuario, $descripcion);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

exit;
