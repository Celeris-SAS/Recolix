<?php
require_once __DIR__ . '/../../Backoffice/verificacion_sesion.php';
require_once __DIR__ . '/../../conexion.php';
require_once __DIR__ . '/../Modelo/reporteModel.php';
require_once __DIR__ . '/../../extra/i18n/i18n.php';

// Los archivos exportados salen en el idioma activo de la página (cookie recolix_lang / ?lang=).
$r = function ($clave) { return t('reportes.' . $clave); };

$tiposExportables = [
    'zonas_criticas'     => $r('card_zonas_criticas_titulo'),
    'volumen'            => $r('card_volumen_titulo'),
    'incidencias'        => $r('card_incidencias_titulo'),
    'tiempos_resolucion' => $r('card_tiempos_resolucion_titulo'),
    'alertas'            => $r('card_alertas_titulo'),
    'proyecciones'       => $r('card_proyecciones_titulo'),
    'frecuencia'         => $r('card_frecuencia_titulo'),
    'vertederos'         => $r('card_vertederos_titulo'),
    'ingresos'           => $r('card_ingresos_titulo'),
    'clasificacion'      => $r('card_clasificacion_titulo'),
    'reciclaje'          => $r('card_reciclaje_titulo'),
    'usuarios'           => $r('card_usuarios_titulo'),
    'camiones'           => $r('card_camiones_titulo'),
    'maquinaria'         => $r('card_maquinaria_titulo'),
];

$tipo = $_GET['tipo'] ?? '';
if (!isset($tiposExportables[$tipo])) {
    http_response_code(400);
    echo t('exportacion.tipo_no_exportable');
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

$motivoLabels     = ['incidencias' => $r('zonas_motivo_incidencias'), 'desbordados' => $r('zonas_motivo_desbordados'), 'ambos' => $r('zonas_motivo_ambos'), 'alertas' => $r('zonas_motivo_alertas')];
$tipoIncLabels     = ['rotura' => $r('incidencias_tipo_rotura'), 'desborde' => $r('incidencias_tipo_desborde'), 'falta_recoleccion' => $r('incidencias_tipo_falta_recoleccion'), 'vandalismo' => $r('incidencias_tipo_vandalismo'), 'otro' => $r('incidencias_tipo_otro')];
$prioridadLabels   = ['baja' => $r('incidencias_prioridad_baja'), 'media' => $r('incidencias_prioridad_media'), 'alta' => $r('incidencias_prioridad_alta'), 'critica' => $r('incidencias_prioridad_critica')];
$tipoCentroLabels  = ['vertedero' => $r('vertederos_tipo_vertedero'), 'relleno_sanitario' => $r('vertederos_tipo_relleno_sanitario'), 'planta_clasificacion' => $r('ingresos_tipo_planta'), 'ecocentro' => $r('ingresos_tipo_centro')];
$tipoVertLabels    = ['vertedero' => $r('vertederos_tipo_vertedero'), 'relleno_sanitario' => $r('vertederos_tipo_relleno_sanitario')];
$estadoVertLabels  = ['operativo' => $r('vertederos_estado_operativo'), 'saturado' => $r('vertederos_estado_saturado'), 'cerrado' => $r('vertederos_estado_cerrado')];
$estadoCamionLabels = [
    'disponible' => $r('camiones_estado_disponible'), 'en_ruta' => $r('camiones_estado_en_ruta'), 'en_mantenimiento' => $r('camiones_estado_en_mantenimiento'),
    'de_baja' => $r('camiones_estado_de_baja'), 'en_deposito' => $r('camiones_estado_en_deposito'),
];
$tipoMaquinariaLabels = [
    'compactadora' => $r('maquinaria_tipo_compactadora'), 'cinta' => $r('maquinaria_tipo_cinta'), 'pala' => $r('maquinaria_tipo_pala'),
    'bascula' => $r('maquinaria_tipo_bascula'), 'herramienta' => $r('maquinaria_tipo_herramienta'), 'otro' => $r('maquinaria_tipo_otro'),
];
$estadoMaquinariaLabels = [
    'operativa' => $r('maquinaria_estado_operativa'), 'en_mantenimiento' => $r('maquinaria_estado_en_mantenimiento'), 'fuera_servicio' => $r('maquinaria_estado_fuera_servicio'),
];
$tipoCargaLabels = ['carga_lateral' => t('camiones.val_carga_lateral'), 'carga_trasera' => t('camiones.val_carga_trasera'), 'puerta_a_puerta' => t('camiones.val_puerta_a_puerta')];
$turnoLabels = ['mañana' => $r('frecuencia_turno_manana'), 'tarde' => $r('frecuencia_turno_tarde'), 'noche' => $r('frecuencia_turno_noche')];
$siNo              = function ($valor) use ($r) {
    if ($valor) {
        return $r('badge_si');
    }
    return $r('badge_no');
};

try {
switch ($tipo) {
    case 'zonas_criticas':
        $rango = rangoDesdeGet(['hoy', 'semana', 'mes', 'siempre'], 'mes');
        $emitir([$r('zonas_th_municipio'), $r('zonas_th_departamento'), t('exportacion.zonas_incidencias_periodo'), $r('zonas_th_desbordados'), $r('zonas_th_total'), $r('zonas_th_porcentaje'), $r('zonas_th_alerta'), $r('zonas_th_motivo')]);
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
        $emitir([$r('volumen_th_periodo'), $r('volumen_th_tipo'), $r('volumen_th_kg'), $r('volumen_th_recolecciones')]);
        foreach ($reporteModel->getVolumenPorTipoYPeriodo($rango, $agrupacion) as $fila) {
            $emitir([$fila['periodo'], $fila['tipoResiduo'], $fila['totalKg'], $fila['totalRecolecciones']]);
        }
        break;

    case 'incidencias':
        $rango = rangoDesdeGet(['hoy', 'semana', 'mes', 'siempre'], 'siempre');
        $estadoIncLabels = ['abierta' => $r('incidencias_estado_abierta'), 'asignada' => $r('incidencias_estado_asignada'), 'en_curso' => t('exportacion.estado_en_curso'), 'resuelta' => $r('incidencias_estado_resuelta')];
        $emitir([$r('incidencias_th_tipo'), $r('incidencias_th_prioridad'), $r('incidencias_th_estado'), $r('incidencias_th_fecha'), $r('incidencias_th_horas')]);
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
        $emitir([$r('tiempos_th_cuadrilla'), $r('tiempos_th_municipio'), $r('tiempos_th_cerradas'), $r('tiempos_th_promedio') . ' (h)', $r('tiempos_th_minimo') . ' (h)', $r('tiempos_th_maximo') . ' (h)']);
        foreach ($reporteModel->getTiemposResolucion($rango) as $fila) {
            $emitir([
                $fila['cuadrilla'], $fila['municipio'], $fila['incidenciasCerradas'],
                $fila['horasPromedio'], $fila['horasMinimo'], $fila['horasMaximo'],
            ]);
        }
        break;

    case 'alertas':
        $emitir([$r('alertas_th_contenedor'), $r('alertas_th_direccion'), $r('alertas_th_circuito'), $r('alertas_th_ultima'), $r('alertas_th_horas') . ' (h)', $r('alertas_th_nivel')]);
        $nivelAlertaLabels = ['critica' => $r('alertas_nivel_critica'), 'maxima' => $r('alertas_nivel_maxima')];
        foreach ($reporteModel->getAlertasContenedores() as $fila) {
            $emitir([
                '#' . $fila['idContenedor'], $fila['direccion'], $fila['circuito'], $fila['ultimaRecoleccion'],
                $fila['horasSinRecoleccion'], $nivelAlertaLabels[$fila['nivelAlerta']] ?? $fila['nivelAlerta'],
            ]);
        }
        break;

    case 'proyecciones':
        $emitir([$r('proyecciones_th_contenedor'), $r('proyecciones_th_direccion'), $r('proyecciones_th_circuito'), $r('proyecciones_th_actual'), $r('proyecciones_th_proyectado'), $r('proyecciones_th_fecha'), $r('proyecciones_th_sobrecarga')]);
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
        $emitir([$r('frecuencia_th_circuito'), $r('frecuencia_th_codigo'), $r('frecuencia_th_municipio'), $r('frecuencia_th_turno'), $r('frecuencia_th_frecuencia'), $r('frecuencia_th_levantes')]);
        foreach ($reporteModel->getFrecuenciaRecoleccion($turno, $dia, rangoDesdeGet(['hoy', 'semana', 'mes', 'siempre'], 'siempre')) as $fila) {
            $emitir([
                $fila['circuito'], $fila['codigo'], $fila['municipio'], $turnoLabels[$fila['turno']] ?? $fila['turno'],
                $fila['frecuencia'], $fila['levantesCompletados'],
            ]);
        }
        break;

    case 'ingresos':
        $rango = rangoDesdeGet(['hoy', 'semana', 'mes', 'siempre'], 'mes');
        $emitir([$r('ingresos_th_centro'), $r('ingresos_th_municipio'), $r('ingresos_th_tipo'), $r('ingresos_th_ingresos'), $r('ingresos_th_kg')]);
        foreach ($reporteModel->getIngresosPorCentro($rango) as $fila) {
            $emitir([$fila['nombre'], $fila['municipio'], $tipoCentroLabels[$fila['tipo']] ?? $fila['tipo'], $fila['ingresos'], $fila['totalKg']]);
        }
        break;

    case 'vertederos':
        $emitir([$r('vertederos_th_nombre'), $r('vertederos_th_municipio'), $r('vertederos_th_tipo'), $r('vertederos_th_estado'), $r('vertederos_th_capacidad'), $r('vertederos_th_ocupacion'), $r('vertederos_th_porcentaje')]);
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
        $emitir([$r('clasificacion_th_tipo'), $r('clasificacion_th_reciclable'), $r('clasificacion_th_total'), $r('clasificacion_th_funcionales'), $r('clasificacion_th_kg'), $r('clasificacion_th_porcentaje')]);
        foreach ($reporteModel->getClasificacionResiduos($rango) as $fila) {
            $emitir([
                $fila['tipoResiduo'], $siNo($fila['reciclable']), $fila['totalContenedores'],
                $fila['funcionales'], $fila['totalKg'], $fila['porcentaje'] . '%',
            ]);
        }
        break;

    case 'reciclaje':
        $rango = rangoDesdeGet(['hoy', 'semana', 'mes', 'siempre'], 'siempre');
        $emitir([$r('reciclaje_th_concepto'), $r('clasificacion_th_kg'), $r('clasificacion_th_porcentaje')]);
        $etiquetasReciclaje = ['reciclable' => $r('reciclaje_concepto_reciclable'), 'no_reciclable' => $r('reciclaje_concepto_no_reciclable'), 'total' => $r('reciclaje_concepto_total')];
        foreach ($reporteModel->getReciclaje($rango) as $fila) {
            $emitir([$etiquetasReciclaje[$fila['concepto']] ?? $fila['concepto'], $fila['totalKg'], $fila['porcentaje'] . '%']);
        }
        break;

    case 'usuarios':
        $rango = rangoDesdeGet(['24h', 'semana', 'mes', 'siempre'], 'siempre');
        $emitir([$r('usuarios_th_periodo'), $r('usuarios_th_nuevos'), $r('usuarios_th_vecinos'), $r('usuarios_th_operarios'), $r('usuarios_th_cuadrillas'), $r('usuarios_th_administradores')]);
        foreach ($reporteModel->getEvolucionUsuarios($rango) as $fila) {
            $emitir([
                $fila['periodo'], $fila['nuevosUsuarios'], $fila['vecinos'],
                $fila['operarios'], $fila['cuadrillas'], $fila['administradores'],
            ]);
        }
        break;

    case 'camiones':
        $rango = rangoDesdeGet(['hoy', 'semana', 'mes', 'siempre'], 'siempre');
        $emitir([$r('camiones_th_matricula'), $r('camiones_th_modelo'), $r('camiones_th_tipo_carga'), $r('camiones_th_estado'), $r('camiones_th_viajes'), $r('camiones_th_kg'), $r('camiones_th_horas_ruta'), $r('camiones_th_horas_mant')]);
        foreach ($reporteModel->getDisponibilidadCamiones($rango) as $fila) {
            $emitir([
                $fila['matricula'], $fila['modelo'], $tipoCargaLabels[$fila['tipoCarga']] ?? $fila['tipoCarga'],
                $estadoCamionLabels[$fila['estado']] ?? $fila['estado'],
                $fila['viajesCompletados'], $fila['kgRecolectados'],
                $fila['horasEnRuta'], $fila['horasMantenimiento'],
            ]);
        }
        break;

    case 'maquinaria':
        $emitir([$r('maquinaria_th_nombre'), $r('maquinaria_th_tipo'), $r('maquinaria_th_centro'), $r('maquinaria_th_municipio'), $r('maquinaria_th_estado'), $r('maquinaria_th_fecha')]);
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
    $pdf = (new PdfTabla(
        $tiposExportables[$tipo],
        str_replace('{fecha}', date('d/m/Y H:i'), t('exportacion.generado_el')),
        $encabezados,
        $filasPdf,
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

exit;
