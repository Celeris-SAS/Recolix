<?php
require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php';
require_once __DIR__ . '/../../conexion.php';
require_once __DIR__ . '/../Modelo/reporteModel.php';

$tiposExportables = [
    'zonas_criticas' => 'Zonas Urbanas Críticas',
    'volumen'        => 'Volumen de Residuos',
    'incidencias'    => 'Tiempos de Resolución de Incidencias',
    'frecuencia'     => 'Frecuencia de Recolección',
    'vertederos'     => 'Capacidad de los Vertederos',
    'clasificacion'  => 'Clasificación y Reciclaje de Residuos',
    'usuarios'       => 'Usuarios Registrados',
];

$tipo = $_GET['tipo'] ?? '';
if (!isset($tiposExportables[$tipo])) {
    http_response_code(400);
    echo 'Tipo de reporte no exportable.';
    exit;
}

$reporteModel = new ReporteModel();

$nombreArchivo = 'recolix_' . $tipo . '_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');

$salida = fopen('php://output', 'w');
fwrite($salida, "\xEF\xBB\xBF");

$motivoLabels     = ['incidencias' => 'Incidencias', 'desbordados' => 'Desbordados', 'ambos' => 'Ambos'];
$tipoIncLabels     = ['rotura' => 'Rotura', 'desborde' => 'Desborde', 'falta_recoleccion' => 'Falta de recolección', 'vandalismo' => 'Vandalismo', 'otro' => 'Otro'];
$prioridadLabels   = ['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta', 'critica' => 'Crítica'];
$tipoVertLabels    = ['vertedero' => 'Vertedero', 'relleno_sanitario' => 'Relleno Sanitario'];
$estadoVertLabels  = ['operativo' => 'Operativo', 'saturado' => 'Saturado', 'cerrado' => 'Cerrado'];
$siNo              = function ($valor) {
    if ($valor) {
        return 'Sí';
    }
    return 'No';
};

switch ($tipo) {
    case 'zonas_criticas':
        fputcsv($salida, ['Municipio', 'Departamento', 'Incidencias (últ. mes)', 'Contenedores Desbordados', 'Total Contenedores', '% Desbordado', 'Motivo']);
        foreach ($reporteModel->getZonasCriticas() as $fila) {
            fputcsv($salida, [
                $fila['municipio'], $fila['departamento'], $fila['incidenciasMes'], $fila['desbordados'],
                $fila['totalContenedores'], $fila['porcentaje'] . '%', $motivoLabels[$fila['motivo']] ?? $fila['motivo'],
            ]);
        }
        break;

    case 'volumen':
        $rangosValidos = ['hoy', 'semana', 'mes', 'siempre'];
        if (in_array($_GET['rango'] ?? '', $rangosValidos, true)) {
            $rango = $_GET['rango'];
        } else {
            $rango = 'siempre';
        }
        fputcsv($salida, ['Período', 'Tipo de Residuo', 'Total (kg)', 'Recolecciones']);
        foreach ($reporteModel->getVolumenPorTipoYPeriodo($rango) as $fila) {
            fputcsv($salida, [$fila['periodo'], $fila['tipoResiduo'], $fila['totalKg'], $fila['totalRecolecciones']]);
        }
        break;

    case 'incidencias':
        $rangosValidos = ['hoy', 'semana', 'mes', 'siempre'];
        if (in_array($_GET['rango'] ?? '', $rangosValidos, true)) {
            $rango = $_GET['rango'];
        } else {
            $rango = 'siempre';
        }
        $estadoIncLabels = ['abierta' => 'Abierta', 'asignada' => 'Asignada', 'resuelta' => 'Resuelta'];
        fputcsv($salida, ['Tipo', 'Prioridad', 'Estado', 'Fecha Apertura', 'Tiempo de Resolución (h)']);
        foreach ($reporteModel->getIncidenciasDetalle($rango) as $fila) {
            fputcsv($salida, [
                $tipoIncLabels[$fila['tipo']] ?? $fila['tipo'],
                $prioridadLabels[$fila['prioridad']] ?? $fila['prioridad'],
                $estadoIncLabels[$fila['estado']] ?? $fila['estado'],
                $fila['fechaApertura'], $fila['horasResolucion'] ?? '',
            ]);
        }
        break;

    case 'frecuencia':
        $turnosValidos = ['mañana', 'tarde', 'noche'];
        if (in_array($_GET['turno'] ?? '', $turnosValidos, true)) {
            $turno = $_GET['turno'];
        } else {
            $turno = null;
        }
        if (trim($_GET['dia'] ?? '') !== '') {
            $dia = $_GET['dia'];
        } else {
            $dia = null;
        }
        fputcsv($salida, ['Circuito', 'Código', 'Municipio', 'Turno', 'Frecuencia', 'Levantes Completados']);
        foreach ($reporteModel->getFrecuenciaRecoleccion($turno, $dia) as $fila) {
            fputcsv($salida, [
                $fila['circuito'], $fila['codigo'], $fila['municipio'], $fila['turno'],
                $fila['frecuencia'], $fila['levantesCompletados'],
            ]);
        }
        break;

    case 'vertederos':
        fputcsv($salida, ['Nombre', 'Municipio', 'Tipo', 'Estado', 'Capacidad Total', 'Ocupación Actual', '% Ocupación']);
        foreach ($reporteModel->getCapacidadVertederos() as $fila) {
            fputcsv($salida, [
                $fila['nombre'], $fila['municipio'], $tipoVertLabels[$fila['tipo']] ?? $fila['tipo'],
                $estadoVertLabels[$fila['estado']] ?? $fila['estado'], $fila['capacidadTotal'],
                $fila['ocupacionActual'], $fila['porcentaje'] . '%',
            ]);
        }
        break;

    case 'clasificacion':
        fputcsv($salida, ['Tipo de Residuo', 'Reciclable', 'Total Contenedores', 'Funcionales', 'Total Recolectado (kg)', '% del Total']);
        foreach ($reporteModel->getClasificacionResiduos() as $fila) {
            fputcsv($salida, [
                $fila['tipoResiduo'], $siNo($fila['reciclable']), $fila['totalContenedores'],
                $fila['funcionales'], $fila['totalKg'], $fila['porcentaje'] . '%',
            ]);
        }
        break;

    case 'usuarios':
        $rangosValidos = ['24h', 'semana', 'mes', 'siempre'];
        if (in_array($_GET['rango'] ?? '', $rangosValidos, true)) {
            $rango = $_GET['rango'];
        } else {
            $rango = 'siempre';
        }
        fputcsv($salida, ['Período', 'Nuevos Usuarios', 'Vecinos', 'Operarios', 'Cuadrillas', 'Administradores']);
        foreach ($reporteModel->getEvolucionUsuarios($rango) as $fila) {
            fputcsv($salida, [
                $fila['periodo'], $fila['nuevosUsuarios'], $fila['vecinos'],
                $fila['operarios'], $fila['cuadrillas'], $fila['administradores'],
            ]);
        }
        break;
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
