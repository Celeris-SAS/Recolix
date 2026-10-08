<?php
require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php';
require_once __DIR__ . '/../../conexion.php';
require_once __DIR__ . '/../Modelo/reporteModel.php';

$tiposExportables = [
    'zonas_criticas' => 'Zonas Urbanas Críticas',
    'volumen'        => 'Volumen de Residuos',
    'incidencias'    => 'Tiempos de Resolución de Incidencias',
    'tiempos_resolucion' => 'Tiempos de Resolución por Cuadrilla',
    'alertas'        => 'Alertas por Tiempo sin Recolección',
    'proyecciones'   => 'Proyecciones de Llenado de Contenedores',
    'frecuencia'     => 'Frecuencia de Recolección',
    'vertederos'     => 'Capacidad de los Vertederos',
    'ingresos'       => 'Ingresos de Residuos por Centro de Acopio',
    'clasificacion'  => 'Clasificación y Reciclaje de Residuos',
    'reciclaje'      => 'Reciclaje de Residuos',
    'usuarios'       => 'Usuarios Registrados',
    'camiones'       => 'Disponibilidad y Uso de la Flota',
    'maquinaria'     => 'Disponibilidad de Maquinaria',
];

$tipo = $_GET['tipo'] ?? '';
if (!isset($tiposExportables[$tipo])) {
    http_response_code(400);
    echo 'Tipo de reporte no exportable.';
    exit;
}

$reporteModel = new ReporteModel();

// Rango fijo permitido o período por fechas "AAAA-MM-DD_AAAA-MM-DD" (el modelo valida que sea coherente).
function rangoDesdeGet(array $permitidos, $defecto) {
    $valor = $_GET['rango'] ?? '';
    if (in_array($valor, $permitidos, true)) {
        return $valor;
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}_\d{4}-\d{2}-\d{2}$/', $valor)) {
        return $valor;
    }
    return $defecto;
}

$formato = ($_GET['formato'] ?? 'csv') === 'pdf' ? 'pdf' : 'csv';

// Cada fila (la primera es el encabezado) se acumula y se vuelca al final como CSV o PDF.
$tabla = [];
$emitir = function (array $fila) use (&$tabla) {
    $tabla[] = $fila;
};

$motivoLabels     = ['incidencias' => 'Incidencias', 'desbordados' => 'Desbordados', 'ambos' => 'Ambos', 'alertas' => 'Alertas sin recolección'];
$tipoIncLabels     = ['rotura' => 'Rotura', 'desborde' => 'Desborde', 'falta_recoleccion' => 'Falta de recolección', 'vandalismo' => 'Vandalismo', 'otro' => 'Otro'];
$prioridadLabels   = ['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta', 'critica' => 'Crítica'];
$tipoCentroLabels  = ['vertedero' => 'Vertedero', 'relleno_sanitario' => 'Relleno Sanitario', 'planta_clasificacion' => 'Planta de Clasificación', 'ecocentro' => 'Ecocentro'];
$tipoVertLabels    = ['vertedero' => 'Vertedero', 'relleno_sanitario' => 'Relleno Sanitario'];
$estadoVertLabels  = ['operativo' => 'Operativo', 'saturado' => 'Saturado', 'cerrado' => 'Cerrado'];
$estadoCamionLabels = [
    'disponible' => 'Disponible', 'en_ruta' => 'En Ruta', 'en_mantenimiento' => 'En Mantenimiento',
    'de_baja' => 'De Baja', 'en_deposito' => 'En Depósito',
];
$tipoMaquinariaLabels = [
    'compactadora' => 'Compactadora', 'cinta' => 'Cinta Transportadora', 'pala' => 'Pala Mecánica',
    'bascula' => 'Báscula', 'herramienta' => 'Herramienta', 'otro' => 'Otro',
];
$estadoMaquinariaLabels = [
    'operativa' => 'Operativa', 'en_mantenimiento' => 'En Mantenimiento', 'fuera_servicio' => 'Fuera de Servicio',
];
$siNo              = function ($valor) {
    if ($valor) {
        return 'Sí';
    }
    return 'No';
};

try {
switch ($tipo) {
    case 'zonas_criticas':
        $rango = rangoDesdeGet(['hoy', 'semana', 'mes', 'siempre'], 'mes');
        $emitir(['Municipio', 'Departamento', 'Incidencias (período)', 'Contenedores Desbordados', 'Total Contenedores', '% Desbordado', 'Contenedores en Alerta', 'Motivo']);
        foreach ($reporteModel->getZonasCriticas($rango) as $fila) {
            $emitir([
                $fila['municipio'], $fila['departamento'], $fila['incidenciasMes'], $fila['desbordados'],
                $fila['totalContenedores'], $fila['porcentaje'] . '%', $fila['contenedoresEnAlerta'], $motivoLabels[$fila['motivo']] ?? $fila['motivo'],
            ]);
        }
        break;

    case 'volumen':
        $rango = rangoDesdeGet(['hoy', 'semana', 'mes', 'siempre'], 'siempre');
        $agrupacionesValidas = ['dia', 'semana', 'mes'];
        if (in_array($_GET['agrupacion'] ?? '', $agrupacionesValidas, true)) {
            $agrupacion = $_GET['agrupacion'];
        } else {
            $agrupacion = 'mes';
        }
        $emitir(['Período', 'Tipo de Residuo', 'Total (kg)', 'Recolecciones']);
        foreach ($reporteModel->getVolumenPorTipoYPeriodo($rango, $agrupacion) as $fila) {
            $emitir([$fila['periodo'], $fila['tipoResiduo'], $fila['totalKg'], $fila['totalRecolecciones']]);
        }
        break;

    case 'incidencias':
        $rango = rangoDesdeGet(['hoy', 'semana', 'mes', 'siempre'], 'siempre');
        $estadoIncLabels = ['abierta' => 'Abierta', 'asignada' => 'Asignada', 'en_curso' => 'En curso', 'resuelta' => 'Resuelta'];
        $emitir(['Tipo', 'Prioridad', 'Estado', 'Fecha Apertura', 'Tiempo de Resolución (h)']);
        $estadosFiltro = ['abiertas', 'resueltas'];
        if (in_array($_GET['estado'] ?? '', $estadosFiltro, true)) {
            $estadoFiltro = $_GET['estado'];
        } else {
            $estadoFiltro = null;
        }
        foreach ($reporteModel->getIncidenciasDetalle($rango, $estadoFiltro) as $fila) {
            $emitir([
                $tipoIncLabels[$fila['tipo']] ?? $fila['tipo'],
                $prioridadLabels[$fila['prioridad']] ?? $fila['prioridad'],
                $estadoIncLabels[$fila['estado']] ?? $fila['estado'],
                $fila['fechaApertura'], $fila['horasResolucion'] ?? '',
            ]);
        }
        break;

    case 'tiempos_resolucion':
        $rango = rangoDesdeGet(['hoy', 'semana', 'mes', 'siempre'], 'siempre');
        $emitir(['Cuadrilla', 'Municipio', 'Incidencias Cerradas', 'Tiempo Promedio (h)', 'Tiempo Mínimo (h)', 'Tiempo Máximo (h)']);
        foreach ($reporteModel->getTiemposResolucion($rango) as $fila) {
            $emitir([
                $fila['cuadrilla'], $fila['municipio'], $fila['incidenciasCerradas'],
                $fila['horasPromedio'], $fila['horasMinimo'], $fila['horasMaximo'],
            ]);
        }
        break;

    case 'alertas':
        $emitir(['Contenedor', 'Dirección', 'Circuito', 'Última Recolección', 'Horas sin Recolección', 'Nivel de Alerta']);
        $nivelAlertaLabels = ['critica' => 'Crítica', 'maxima' => 'Máxima'];
        foreach ($reporteModel->getAlertasContenedores() as $fila) {
            $emitir([
                '#' . $fila['idContenedor'], $fila['direccion'], $fila['circuito'], $fila['ultimaRecoleccion'],
                $fila['horasSinRecoleccion'], $nivelAlertaLabels[$fila['nivelAlerta']] ?? $fila['nivelAlerta'],
            ]);
        }
        break;

    case 'proyecciones':
        $emitir(['Contenedor', 'Dirección', 'Circuito', 'Nivel Actual', 'Nivel Proyectado', 'Fecha de Cálculo', 'Riesgo de Sobrecarga']);
        foreach ($reporteModel->getProyeccionesLlenado() as $fila) {
            $emitir([
                '#' . $fila['idContenedor'], $fila['direccion'], $fila['circuito'], $fila['nivelActual'] . '%',
                $fila['nivelProyectado'] . '%', $fila['fechaCalculo'], $siNo($fila['sobrecarga']),
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
        $emitir(['Circuito', 'Código', 'Municipio', 'Turno', 'Frecuencia', 'Levantes Completados']);
        foreach ($reporteModel->getFrecuenciaRecoleccion($turno, $dia, rangoDesdeGet(['hoy', 'semana', 'mes', 'siempre'], 'siempre')) as $fila) {
            $emitir([
                $fila['circuito'], $fila['codigo'], $fila['municipio'], $fila['turno'],
                $fila['frecuencia'], $fila['levantesCompletados'],
            ]);
        }
        break;

    case 'ingresos':
        $rango = rangoDesdeGet(['hoy', 'semana', 'mes', 'siempre'], 'mes');
        $emitir(['Centro de Acopio', 'Municipio', 'Tipo', 'Ingresos', 'Total Ingresado (kg)']);
        foreach ($reporteModel->getIngresosPorCentro($rango) as $fila) {
            $emitir([$fila['nombre'], $fila['municipio'], $tipoCentroLabels[$fila['tipo']] ?? $fila['tipo'], $fila['ingresos'], $fila['totalKg']]);
        }
        break;

    case 'vertederos':
        $emitir(['Nombre', 'Municipio', 'Tipo', 'Estado', 'Capacidad Total (t)', 'Ocupación Actual (t)', '% Ocupación']);
        foreach ($reporteModel->getCapacidadVertederos() as $fila) {
            $emitir([
                $fila['nombre'], $fila['municipio'], $tipoVertLabels[$fila['tipo']] ?? $fila['tipo'],
                $estadoVertLabels[$fila['estado']] ?? $fila['estado'], $fila['capacidadTotal'],
                $fila['ocupacionActual'], $fila['porcentaje'] . '%',
            ]);
        }
        break;

    case 'clasificacion':
        $rango = rangoDesdeGet(['hoy', 'semana', 'mes', 'siempre'], 'siempre');
        $emitir(['Tipo de Residuo', 'Reciclable', 'Total Contenedores', 'Funcionales', 'Total Recolectado (kg)', '% del Total']);
        foreach ($reporteModel->getClasificacionResiduos($rango) as $fila) {
            $emitir([
                $fila['tipoResiduo'], $siNo($fila['reciclable']), $fila['totalContenedores'],
                $fila['funcionales'], $fila['totalKg'], $fila['porcentaje'] . '%',
            ]);
        }
        break;

    case 'reciclaje':
        $rango = rangoDesdeGet(['hoy', 'semana', 'mes', 'siempre'], 'siempre');
        $emitir(['Concepto', 'Total Recolectado (kg)', '% del Total']);
        $etiquetasReciclaje = ['reciclable' => 'Reciclable', 'no_reciclable' => 'No reciclable', 'total' => 'Total'];
        foreach ($reporteModel->getReciclaje($rango) as $fila) {
            $emitir([$etiquetasReciclaje[$fila['concepto']] ?? $fila['concepto'], $fila['totalKg'], $fila['porcentaje'] . '%']);
        }
        break;

    case 'usuarios':
        $rango = rangoDesdeGet(['24h', 'semana', 'mes', 'siempre'], 'siempre');
        $emitir(['Período', 'Nuevos Usuarios', 'Vecinos', 'Operarios', 'Cuadrillas', 'Administradores']);
        foreach ($reporteModel->getEvolucionUsuarios($rango) as $fila) {
            $emitir([
                $fila['periodo'], $fila['nuevosUsuarios'], $fila['vecinos'],
                $fila['operarios'], $fila['cuadrillas'], $fila['administradores'],
            ]);
        }
        break;

    case 'camiones':
        $rango = rangoDesdeGet(['hoy', 'semana', 'mes', 'siempre'], 'siempre');
        $emitir(['Matrícula', 'Modelo', 'Tipo de Carga', 'Estado', 'Viajes Completados', 'Recolectado (kg)', 'Horas en Ruta', 'Horas en Mantenimiento']);
        foreach ($reporteModel->getDisponibilidadCamiones($rango) as $fila) {
            $emitir([
                $fila['matricula'], $fila['modelo'], $fila['tipoCarga'],
                $estadoCamionLabels[$fila['estado']] ?? $fila['estado'],
                $fila['viajesCompletados'], $fila['kgRecolectados'],
                $fila['horasEnRuta'], $fila['horasMantenimiento'],
            ]);
        }
        break;

    case 'maquinaria':
        $emitir(['Nombre', 'Tipo', 'Centro de Acopio', 'Municipio', 'Estado', 'Fecha de Adquisición']);
        foreach ($reporteModel->getDisponibilidadMaquinaria() as $fila) {
            $emitir([
                $fila['nombre'], $tipoMaquinariaLabels[$fila['tipo']] ?? $fila['tipo'],
                $fila['centro'], $fila['municipio'],
                $estadoMaquinariaLabels[$fila['estado']] ?? $fila['estado'], $fila['fechaAdquisicion'],
            ]);
        }
        break;
}
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo $e->getMessage();
    exit;
}

// Queda registrado quién exportó qué y en qué formato (tabla REPORTE).
$ciUsuario = $_SESSION['usuario']['ci_usuario'] ?? null;
if ($ciUsuario) {
    $conn = new conexion();
    $descripcion = $tiposExportables[$tipo];
    $formatoRegistro = strtoupper($formato);
    $stmt = mysqli_prepare($conn->getConexion(), "INSERT INTO REPORTE (ci_usuario, tipo, descripcion, formato) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isss", $ciUsuario, $formato, $descripcion, $formatoRegistro);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

$nombreArchivo = 'recolix_' . $tipo . '_' . date('Ymd_His') . '.' . $formato;

if ($formato === 'pdf') {
    require_once __DIR__ . '/../Util/PdfTabla.php';
    $encabezados = array_map('strval', array_shift($tabla) ?? []);
    $filasPdf = array_map(function ($fila) {
        return array_map(function ($v) { return (string) ($v ?? ''); }, $fila);
    }, $tabla);
    $pdf = (new PdfTabla($tiposExportables[$tipo], 'Generado el ' . date('d/m/Y H:i'), $encabezados, $filasPdf))->generar();
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

exit;
