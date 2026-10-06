<?php
require_once __DIR__ . '/../../conexion.php';

class ReporteModel {
    private $conn;

    public function __construct() {
        $this->conn = new conexion();
    }

    private function contarFilas($sql) {
        $result = mysqli_query($this->conn->getConexion(), $sql);
        if ($result) {
            return (int) mysqli_fetch_row($result)[0];
        }
        return 0;
    }

    // Fragmento SQL para acotar una columna DATE (ej. LEVANTE.hora_levante) al rango elegido.
    private function condicionRangoFecha($columna, $rango) {
        switch ($rango) {
            case 'semana':
                return "$columna >= (CURDATE() - INTERVAL 7 DAY)";
            case 'mes':
                return "$columna >= (CURDATE() - INTERVAL 1 MONTH)";
            case 'siempre':
                return '1=1';
            case 'hoy':
            default:
                return "$columna = CURDATE()";
        }
    }

    // Fragmento SQL para acotar una columna DATETIME (ej. USUARIO.fecha_registro) al rango elegido.
    private function condicionRangoFechaHora($columna, $rango) {
        switch ($rango) {
            case 'semana':
                return "$columna >= (NOW() - INTERVAL 7 DAY)";
            case 'mes':
                return "$columna >= (NOW() - INTERVAL 1 MONTH)";
            case 'siempre':
                return '1=1';
            case 'hoy':
            default:
                return "DATE($columna) = CURDATE()";
        }
    }

    public function getContenedoresRecolectados($rango = 'hoy') {
        return $this->contarFilas("
            SELECT COUNT(DISTINCT cl.id_contenedor)
            FROM CONTENEDOR_LEVANTE cl
            JOIN LEVANTE l ON l.id_levante = cl.id_levante
            WHERE " . $this->condicionRangoFecha('l.hora_levante', $rango) . "
        ");
    }

    public function getUsuariosRegistrados($rango = 'hoy') {
        return $this->contarFilas("SELECT COUNT(*) FROM USUARIO WHERE " . $this->condicionRangoFechaHora('fecha_registro', $rango));
    }

    public function getResiduosRecolectados($rango = 'hoy') {
        $result = mysqli_query($this->conn->getConexion(), "
            SELECT COALESCE(SUM(l.total_recolectado), 0)
            FROM LEVANTE l
            WHERE " . $this->condicionRangoFecha('l.hora_levante', $rango) . "
        ");
        if ($result) {
            return (float) mysqli_fetch_row($result)[0];
        }
        return 0.0;
    }

    public function getNivelesLlenado($rango = 'hoy') {
        $result = mysqli_query($this->conn->getConexion(), "
            SELECT c.nivel_llenado
            FROM CONTENEDOR c
            JOIN CONTENEDOR_LEVANTE cl ON cl.id_contenedor = c.id_contenedor
            JOIN LEVANTE l ON l.id_levante = cl.id_levante
            WHERE " . $this->condicionRangoFecha('l.hora_levante', $rango) . " AND c.nivel_llenado IS NOT NULL
        ");
        $niveles = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $niveles[] = (int) $row['nivel_llenado'];
        }
        return $niveles;
    }

    private function totalContenedoresRecolectadosPorRango($rango) {
        return $this->contarFilas("
            SELECT COUNT(DISTINCT cl.id_contenedor)
            FROM CONTENEDOR_LEVANTE cl
            JOIN LEVANTE l ON l.id_levante = cl.id_levante
            WHERE " . $this->condicionRangoFecha('l.hora_levante', $rango) . "
        ");
    }

    private function contenedoresRecolectadosPorRangoConEstado($rango, $estado) {
        return $this->contarFilas("
            SELECT COUNT(DISTINCT cl.id_contenedor)
            FROM CONTENEDOR_LEVANTE cl
            JOIN LEVANTE l ON l.id_levante = cl.id_levante
            JOIN CONTENEDOR c ON c.id_contenedor = cl.id_contenedor
            WHERE " . $this->condicionRangoFecha('l.hora_levante', $rango) . " AND c.estado = '$estado'
        ");
    }

    public function getPorcentajeContenedoresFuncionales($rango = 'hoy') {
        $total = $this->totalContenedoresRecolectadosPorRango($rango);
        if ($total === 0) return 0.0;
        return round(($this->contenedoresRecolectadosPorRangoConEstado($rango, 'funcional') / $total) * 100, 1);
    }

    public function getPorcentajeContenedoresSinRecolectar($rango = 'hoy') {
        $total = $this->totalContenedoresRecolectadosPorRango($rango);
        if ($total === 0) return 0.0;
        return round(($this->contenedoresRecolectadosPorRangoConEstado($rango, 'desbordado') / $total) * 100, 1);
    }

    public function getPorcentajeIncidenciasResueltas($rango = 'hoy') {
        $condicion = $this->condicionRangoFechaHora('fecha_apertura', $rango);
        $total = $this->contarFilas("SELECT COUNT(*) FROM INCIDENCIA WHERE $condicion");
        if ($total === 0) return 0.0;
        $resueltas = $this->contarFilas("SELECT COUNT(*) FROM INCIDENCIA WHERE $condicion AND estado = 'resuelta'");
        return round(($resueltas / $total) * 100, 1);
    }

    public function getPorcentajeLevantesCompletados($rango = 'hoy') {
        $condicion = $this->condicionRangoFecha('hora_levante', $rango);
        $total = $this->contarFilas("SELECT COUNT(*) FROM LEVANTE WHERE $condicion");
        if ($total === 0) return 0.0;
        $completados = $this->contarFilas("SELECT COUNT(*) FROM LEVANTE WHERE $condicion AND estado = 'completado'");
        return round(($completados / $total) * 100, 1);
    }

    // Umbrales que definen una zona urbana crítica.
    const UMBRAL_INCIDENCIAS_MES = 10;
    const UMBRAL_PORCENTAJE_DESBORDADOS = 20;

    private function getContenedoresPorMunicipio() {
        $sql = "SELECT m.id_municipio, m.nombre AS municipio, m.departamento,
                       COUNT(c.id_contenedor) AS total,
                       SUM(CASE WHEN c.estado = 'desbordado' THEN 1 ELSE 0 END) AS desbordados
                FROM MUNICIPIO m
                JOIN CIRCUITO ci ON ci.id_municipio = m.id_municipio
                JOIN CONTENEDOR c ON c.id_circuito = ci.id_circuito
                GROUP BY m.id_municipio, m.nombre, m.departamento";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $porMunicipio = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $porMunicipio[(int) $row['id_municipio']] = [
                'municipio'    => $row['municipio'],
                'departamento' => $row['departamento'],
                'total'        => (int) $row['total'],
                'desbordados'  => (int) $row['desbordados'],
            ];
        }
        return $porMunicipio;
    }

    private function getIncidenciasUltimoMesPorMunicipio() {
        $sql = "SELECT m.id_municipio, COUNT(DISTINCT i.id_incidencia) AS incidencias
                FROM INCIDENCIA i
                JOIN INCIDENCIA_CONTENEDOR ic ON ic.id_incidencia = i.id_incidencia
                JOIN CONTENEDOR c ON c.id_contenedor = ic.id_contenedor
                JOIN CIRCUITO ci ON ci.id_circuito = c.id_circuito
                JOIN MUNICIPIO m ON m.id_municipio = ci.id_municipio
                WHERE i.fecha_apertura >= (NOW() - INTERVAL 1 MONTH)
                GROUP BY m.id_municipio";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $porMunicipio = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $porMunicipio[(int) $row['id_municipio']] = (int) $row['incidencias'];
        }
        return $porMunicipio;
    }

    public function getZonasCriticas() {
        $contenedores = $this->getContenedoresPorMunicipio();
        $incidencias  = $this->getIncidenciasUltimoMesPorMunicipio();

        $zonas = [];
        foreach ($contenedores as $idMunicipio => $datos) {
            $total              = $datos['total'];
            $desbordados        = $datos['desbordados'];
            if ($total > 0) {
                $porcentaje = round(($desbordados / $total) * 100, 1);
            } else {
                $porcentaje = 0.0;
            }
            $incidenciasMes     = $incidencias[$idMunicipio] ?? 0;

            $superaIncidencias  = $incidenciasMes > self::UMBRAL_INCIDENCIAS_MES;
            $superaDesbordados  = $porcentaje > self::UMBRAL_PORCENTAJE_DESBORDADOS;

            if (!$superaIncidencias && !$superaDesbordados) {
                continue;
            }

            $motivo = 'desbordados';
            if ($superaIncidencias && $superaDesbordados) {
                $motivo = 'ambos';
            } elseif ($superaIncidencias) {
                $motivo = 'incidencias';
            }

            $zonas[] = [
                'municipio'          => $datos['municipio'],
                'departamento'       => $datos['departamento'],
                'incidenciasMes'     => $incidenciasMes,
                'totalContenedores'  => $total,
                'desbordados'        => $desbordados,
                'porcentaje'         => $porcentaje,
                'motivo'             => $motivo,
            ];
        }

        usort($zonas, function ($a, $b) {
            $comparacion = $b['incidenciasMes'] <=> $a['incidenciasMes'];
            if ($comparacion !== 0) {
                return $comparacion;
            }
            return $b['porcentaje'] <=> $a['porcentaje'];
        });

        return $zonas;
    }

    public function getVolumenPorTipoYPeriodo($rango = 'siempre') {
        $sql = "SELECT t.descripcion AS tipoResiduo, t.color,
                       DATE_FORMAT(l.hora_levante, '%Y-%m') AS periodo,
                       COALESCE(SUM(cl.peso), 0) AS totalKg,
                       COUNT(cl.id_levante) AS totalRecolecciones
                FROM CONTENEDOR_LEVANTE cl
                JOIN CONTENEDOR c ON c.id_contenedor = cl.id_contenedor
                JOIN TIPO_RESIDUO t ON t.id_tipo = c.id_tipo
                JOIN LEVANTE l ON l.id_levante = cl.id_levante
                WHERE l.hora_levante IS NOT NULL AND l.estado = 'completado'
                      AND " . $this->condicionRangoFecha('l.hora_levante', $rango) . "
                GROUP BY t.id_tipo, t.descripcion, t.color, periodo
                ORDER BY periodo DESC, totalKg DESC";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = [
                'tipoResiduo'        => $row['tipoResiduo'],
                'color'              => $row['color'],
                'periodo'            => $row['periodo'],
                'totalKg'            => (float) $row['totalKg'],
                'totalRecolecciones' => (int) $row['totalRecolecciones'],
            ];
        }
        return $filas;
    }

    public function getIncidenciasDetalle($rango = 'siempre') {
        $sql = "SELECT tipo, prioridad, estado, fecha_apertura,
                       CASE WHEN estado = 'resuelta' AND fecha_cierre IS NOT NULL
                            THEN ROUND(TIMESTAMPDIFF(HOUR, fecha_apertura, fecha_cierre), 1)
                       END AS horasResolucion
                FROM INCIDENCIA
                WHERE " . $this->condicionRangoFechaHora('fecha_apertura', $rango) . "
                ORDER BY fecha_apertura DESC";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['horasResolucion'] !== null) {
                $horasResolucion = (float) $row['horasResolucion'];
            } else {
                $horasResolucion = null;
            }
            $filas[] = [
                'tipo'             => $row['tipo'],
                'prioridad'        => $row['prioridad'],
                'estado'           => $row['estado'],
                'fechaApertura'    => $row['fecha_apertura'],
                'horasResolucion'  => $horasResolucion,
            ];
        }
        return $filas;
    }

    private function condicionTurno($turno) {
        if (!$turno) return '1=1';
        $turnoEsc = mysqli_real_escape_string($this->conn->getConexion(), $turno);
        return "ci.turno = '$turnoEsc'";
    }

    // La frecuencia de un circuito es texto libre (ej. "interdiaria (lunes, miércoles y viernes)"),
    // por eso el filtro por día busca el nombre del día dentro del texto. Los circuitos "diaria
    // (lunes a viernes...)" también matchean los días de semana intermedios (martes/miércoles/jueves).
    private function condicionDia($dia) {
        if (!$dia) return '1=1';
        $conn    = $this->conn->getConexion();
        $diaEsc  = mysqli_real_escape_string($conn, $dia);
        $condiciones = ["ci.frecuencia LIKE '%$diaEsc%'"];

        $diasDeSemana = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes'];
        if (in_array(mb_strtolower($dia), $diasDeSemana, true)) {
            $condiciones[] = "ci.frecuencia LIKE '%lunes a viernes%'";
        }

        return '(' . implode(' OR ', $condiciones) . ')';
    }

    public function getFrecuenciaRecoleccion($turno = null, $dia = null) {
        $sql = "SELECT ci.nombre AS circuito, ci.codigo, m.nombre AS municipio, ci.turno, ci.frecuencia,
                       COUNT(l.id_levante) AS levantesCompletados
                FROM CIRCUITO ci
                JOIN MUNICIPIO m ON m.id_municipio = ci.id_municipio
                LEFT JOIN RUTA_RECOLECCION r ON r.id_circuito = ci.id_circuito
                LEFT JOIN LEVANTE l ON l.id_ruta = r.id_ruta AND l.estado = 'completado'
                WHERE " . $this->condicionTurno($turno) . " AND " . $this->condicionDia($dia) . "
                GROUP BY ci.id_circuito, ci.nombre, ci.codigo, m.nombre, ci.turno, ci.frecuencia
                ORDER BY levantesCompletados DESC";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = [
                'circuito'             => $row['circuito'],
                'codigo'               => $row['codigo'],
                'municipio'            => $row['municipio'],
                'turno'                => $row['turno'],
                'frecuencia'           => $row['frecuencia'],
                'levantesCompletados'  => (int) $row['levantesCompletados'],
            ];
        }
        return $filas;
    }

    public function getCapacidadVertederos() {
        $sql = "SELECT c.nombre, m.nombre AS municipio, c.tipo, c.estado, c.capacidad_total,
                       COALESCE(SUM(cr.ocupacion_actual), 0) AS ocupacionActual
                FROM CENTRO_ACOPIO c
                JOIN MUNICIPIO m ON m.id_municipio = c.id_municipio
                LEFT JOIN CENTRO_RESIDUO cr ON cr.id_centro = c.id_centro
                WHERE c.tipo IN ('vertedero', 'relleno_sanitario')
                GROUP BY c.id_centro, c.nombre, m.nombre, c.tipo, c.estado, c.capacidad_total
                ORDER BY ocupacionActual DESC";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $capacidad  = (int) $row['capacidad_total'];
            $ocupacion  = (int) $row['ocupacionActual'];
            if ($capacidad > 0) {
                $porcentaje = round(($ocupacion / $capacidad) * 100, 1);
            } else {
                $porcentaje = 0.0;
            }

            $filas[] = [
                'nombre'          => $row['nombre'],
                'municipio'       => $row['municipio'],
                'tipo'            => $row['tipo'],
                'estado'          => $row['estado'],
                'capacidadTotal'  => $capacidad,
                'ocupacionActual' => $ocupacion,
                'porcentaje'      => $porcentaje,
            ];
        }
        return $filas;
    }

    // RF68: cuántos kilos recolectados son de tipos reciclables, sobre el total.
    public function getReciclaje() {
        $clasificacion = $this->getClasificacionResiduos();
        $kgReciclable = 0.0;
        $kgNoReciclable = 0.0;
        foreach ($clasificacion as $fila) {
            if ($fila['reciclable']) {
                $kgReciclable += $fila['totalKg'];
            } else {
                $kgNoReciclable += $fila['totalKg'];
            }
        }
        $total = $kgReciclable + $kgNoReciclable;
        $porcentaje = function ($kg) use ($total) {
            return $total > 0 ? round($kg / $total * 100, 1) : 0.0;
        };
        return [
            ['concepto' => 'reciclable',    'totalKg' => round($kgReciclable, 1),   'porcentaje' => $porcentaje($kgReciclable)],
            ['concepto' => 'no_reciclable', 'totalKg' => round($kgNoReciclable, 1), 'porcentaje' => $porcentaje($kgNoReciclable)],
            ['concepto' => 'total',         'totalKg' => round($total, 1),          'porcentaje' => $total > 0 ? 100.0 : 0.0],
        ];
    }

    public function getClasificacionResiduos() {
        $sqlContenedores = "SELECT t.id_tipo, t.descripcion AS tipoResiduo, t.color, t.reciclable,
                                   COUNT(c.id_contenedor) AS totalContenedores,
                                   SUM(CASE WHEN c.estado = 'funcional' THEN 1 ELSE 0 END) AS funcionales
                            FROM TIPO_RESIDUO t
                            LEFT JOIN CONTENEDOR c ON c.id_tipo = t.id_tipo
                            GROUP BY t.id_tipo, t.descripcion, t.color, t.reciclable";

        $result = mysqli_query($this->conn->getConexion(), $sqlContenedores);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[(int) $row['id_tipo']] = [
                'tipoResiduo'        => $row['tipoResiduo'],
                'color'              => $row['color'],
                'reciclable'         => (bool) $row['reciclable'],
                'totalContenedores'  => (int) $row['totalContenedores'],
                'funcionales'        => (int) $row['funcionales'],
                'totalKg'            => 0.0,
                'porcentaje'         => 0.0,
            ];
        }

        $sqlKg = "SELECT t.id_tipo, COALESCE(SUM(cl.peso), 0) AS totalKg
                  FROM TIPO_RESIDUO t
                  LEFT JOIN CONTENEDOR c ON c.id_tipo = t.id_tipo
                  LEFT JOIN CONTENEDOR_LEVANTE cl ON cl.id_contenedor = c.id_contenedor
                  GROUP BY t.id_tipo";

        $result = mysqli_query($this->conn->getConexion(), $sqlKg);
        $totalGeneral = 0.0;
        $kgPorTipo = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $kg = (float) $row['totalKg'];
            $kgPorTipo[(int) $row['id_tipo']] = $kg;
            $totalGeneral += $kg;
        }

        foreach ($filas as $idTipo => &$fila) {
            $kg = $kgPorTipo[$idTipo] ?? 0.0;
            $fila['totalKg']    = $kg;
            if ($totalGeneral > 0) {
                $fila['porcentaje'] = round(($kg / $totalGeneral) * 100, 1);
            } else {
                $fila['porcentaje'] = 0.0;
            }
        }
        unset($fila);

        $filas = array_values($filas);
        usort($filas, function ($a, $b) {
            return $b['totalContenedores'] <=> $a['totalContenedores'];
        });

        return $filas;
    }

    public function getEvolucionUsuarios($rango = 'siempre') {
        switch ($rango) {
            case '24h':
                $formato   = '%Y-%m-%d %H:00';
                $condicion = 'WHERE fecha_registro >= (NOW() - INTERVAL 1 DAY)';
                break;
            case 'semana':
                $formato   = '%Y-%m-%d';
                $condicion = 'WHERE fecha_registro >= (NOW() - INTERVAL 7 DAY)';
                break;
            case 'mes':
                $formato   = '%Y-%m-%d';
                $condicion = 'WHERE fecha_registro >= (NOW() - INTERVAL 1 MONTH)';
                break;
            case 'siempre':
            default:
                $formato   = '%Y-%m';
                $condicion = '';
                break;
        }

        $sql = "SELECT DATE_FORMAT(fecha_registro, '$formato') AS periodo,
                       COUNT(*) AS nuevosUsuarios,
                       SUM(CASE WHEN rol = 'vecino' THEN 1 ELSE 0 END) AS vecinos,
                       SUM(CASE WHEN rol = 'operario' THEN 1 ELSE 0 END) AS operarios,
                       SUM(CASE WHEN rol = 'cuadrilla' THEN 1 ELSE 0 END) AS cuadrillas,
                       SUM(CASE WHEN rol = 'administrador' THEN 1 ELSE 0 END) AS administradores
                FROM USUARIO
                $condicion
                GROUP BY periodo
                ORDER BY periodo DESC";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = [
                'periodo'         => $row['periodo'],
                'nuevosUsuarios'  => (int) $row['nuevosUsuarios'],
                'vecinos'         => (int) $row['vecinos'],
                'operarios'       => (int) $row['operarios'],
                'cuadrillas'      => (int) $row['cuadrillas'],
                'administradores' => (int) $row['administradores'],
            ];
        }
        return $filas;
    }

    // Disponibilidad (estado actual) y uso (viajes, kg, horas en ruta y en mantenimiento
    // dentro del rango elegido) por camión.
    public function getDisponibilidadCamiones($rango = 'siempre') {
        $condicionLevante      = $this->condicionRangoFecha('l.hora_levante', $rango);
        $condicionMantenimiento = $this->condicionRangoFechaHora('m.fecha_inicio', $rango);

        $sql = "SELECT c.matricula, c.modelo, c.tipo_carga, c.estado,
                       COALESCE(u.viajesCompletados, 0) AS viajesCompletados,
                       COALESCE(u.kgRecolectados, 0)    AS kgRecolectados,
                       COALESCE(u.horasEnRuta, 0)        AS horasEnRuta,
                       COALESCE(mt.horasMantenimiento, 0) AS horasMantenimiento
                FROM CAMION c
                LEFT JOIN (
                    SELECT l.id_camion,
                           COUNT(*) AS viajesCompletados,
                           SUM(l.total_recolectado) AS kgRecolectados,
                           SUM(TIMESTAMPDIFF(MINUTE, l.hora_salida, l.hora_llegada)) / 60 AS horasEnRuta
                    FROM LEVANTE l
                    WHERE l.estado = 'completado' AND l.hora_llegada IS NOT NULL AND $condicionLevante
                    GROUP BY l.id_camion
                ) u ON u.id_camion = c.id_camion
                LEFT JOIN (
                    SELECT m.id_camion,
                           SUM(TIMESTAMPDIFF(MINUTE, m.fecha_inicio, COALESCE(m.fecha_fin, NOW()))) / 60 AS horasMantenimiento
                    FROM MANTENIMIENTO m
                    WHERE m.fecha_inicio IS NOT NULL AND $condicionMantenimiento
                    GROUP BY m.id_camion
                ) mt ON mt.id_camion = c.id_camion
                ORDER BY c.matricula ASC";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = [
                'matricula'          => $row['matricula'],
                'modelo'             => $row['modelo'],
                'tipoCarga'          => $row['tipo_carga'],
                'estado'             => $row['estado'],
                'viajesCompletados'  => (int) $row['viajesCompletados'],
                'kgRecolectados'     => (float) $row['kgRecolectados'],
                'horasEnRuta'        => round((float) $row['horasEnRuta'], 1),
                'horasMantenimiento' => round((float) $row['horasMantenimiento'], 1),
            ];
        }
        return $filas;
    }

    // Disponibilidad (estado actual) de la maquinaria por centro de acopio.
    // No hay eventos de uso registrados para maquinaria (a diferencia de los
    // camiones, que tienen LEVANTE), por eso no reporta métricas de uso.
    public function getDisponibilidadMaquinaria() {
        $sql = "SELECT mq.nombre, mq.tipo, mq.fecha_adquisicion, mq.estado,
                       ca.nombre AS centro, mu.nombre AS municipio
                FROM MAQUINARIA mq
                LEFT JOIN CENTRO_MAQUINARIA cm ON cm.id_maquinaria = mq.id_maquinaria
                LEFT JOIN CENTRO_ACOPIO ca ON ca.id_centro = cm.id_centro
                LEFT JOIN MUNICIPIO mu ON mu.id_municipio = ca.id_municipio
                ORDER BY mq.nombre ASC";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = [
                'nombre'            => $row['nombre'],
                'tipo'              => $row['tipo'],
                'centro'            => $row['centro'],
                'municipio'         => $row['municipio'],
                'estado'            => $row['estado'],
                'fechaAdquisicion'  => $row['fecha_adquisicion'],
            ];
        }
        return $filas;
    }
}
