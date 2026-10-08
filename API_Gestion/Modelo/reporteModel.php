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

    // ERR-17: además de los rangos fijos, se acepta un período por fechas "AAAA-MM-DD_AAAA-MM-DD".
    // Devuelve [desde, hasta], null si $rango no es un período personalizado, o lanza si es inválido.
    private function rangoPersonalizado($rango) {
        if (!is_string($rango) || !preg_match('/^(\d{4}-\d{2}-\d{2})_(\d{4}-\d{2}-\d{2})$/', $rango, $m)) {
            return null;
        }
        foreach ([$m[1], $m[2]] as $fecha) {
            $d = DateTime::createFromFormat('Y-m-d', $fecha);
            if (!$d || $d->format('Y-m-d') !== $fecha) {
                throw new InvalidArgumentException("Fecha inválida: $fecha");
            }
        }
        if ($m[1] > $m[2]) {
            throw new InvalidArgumentException('La fecha de fin no puede ser anterior a la fecha de inicio.');
        }
        return [$m[1], $m[2]];
    }

    // Fragmento SQL para acotar una columna DATE (ej. LEVANTE.hora_levante) al rango elegido.
    private function condicionRangoFecha($columna, $rango) {
        if ($periodo = $this->rangoPersonalizado($rango)) {
            return "$columna BETWEEN '{$periodo[0]}' AND '{$periodo[1]}'";
        }
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
        if ($periodo = $this->rangoPersonalizado($rango)) {
            return "DATE($columna) BETWEEN '{$periodo[0]}' AND '{$periodo[1]}'";
        }
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

    private function getIncidenciasPorMunicipio($rango) {
        $sql = "SELECT m.id_municipio, COUNT(DISTINCT i.id_incidencia) AS incidencias
                FROM INCIDENCIA i
                JOIN INCIDENCIA_CONTENEDOR ic ON ic.id_incidencia = i.id_incidencia
                JOIN CONTENEDOR c ON c.id_contenedor = ic.id_contenedor
                JOIN CIRCUITO ci ON ci.id_circuito = c.id_circuito
                JOIN MUNICIPIO m ON m.id_municipio = ci.id_municipio
                WHERE " . $this->condicionRangoFechaHora('i.fecha_apertura', $rango) . "
                GROUP BY m.id_municipio";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $porMunicipio = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $porMunicipio[(int) $row['id_municipio']] = (int) $row['incidencias'];
        }
        return $porMunicipio;
    }

    // Contenedores en alerta crítica (24 h) o máxima (48 h) sin recolectar, por municipio (RF70).
    private function getContenedoresEnAlertaPorMunicipio() {
        $sql = "SELECT t.id_municipio, COUNT(*) AS enAlerta
                FROM (
                    SELECT ci.id_municipio, c.id_contenedor
                    FROM CONTENEDOR c
                    JOIN CIRCUITO ci ON ci.id_circuito = c.id_circuito
                    JOIN CONTENEDOR_LEVANTE cl ON cl.id_contenedor = c.id_contenedor AND cl.hora_vaciado IS NOT NULL
                    WHERE c.estado NOT IN ('en_deposito', 'inactivo')
                    GROUP BY ci.id_municipio, c.id_contenedor
                    HAVING TIMESTAMPDIFF(HOUR, MAX(cl.hora_vaciado), NOW()) >= " . self::HORAS_ALERTA_CRITICA . "
                ) t
                GROUP BY t.id_municipio";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $porMunicipio = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $porMunicipio[(int) $row['id_municipio']] = (int) $row['enAlerta'];
        }
        return $porMunicipio;
    }

    // Una zona es crítica si supera los umbrales de incidencias en el período (por defecto el último
    // mes) o de contenedores desbordados, o si más del 20 % de sus contenedores está en alerta
    // crítica o máxima por tiempo sin recolección.
    public function getZonasCriticas($rango = 'mes') {
        $contenedores = $this->getContenedoresPorMunicipio();
        $incidencias  = $this->getIncidenciasPorMunicipio($rango);
        $enAlerta     = $this->getContenedoresEnAlertaPorMunicipio();

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

            $contenedoresEnAlerta = $enAlerta[$idMunicipio] ?? 0;
            if ($total > 0) {
                $porcentajeAlerta = round(($contenedoresEnAlerta / $total) * 100, 1);
            } else {
                $porcentajeAlerta = 0.0;
            }

            $superaIncidencias  = $incidenciasMes > self::UMBRAL_INCIDENCIAS_MES;
            $superaDesbordados  = $porcentaje > self::UMBRAL_PORCENTAJE_DESBORDADOS;
            $superaAlertas      = $porcentajeAlerta > self::UMBRAL_PORCENTAJE_DESBORDADOS;

            if (!$superaIncidencias && !$superaDesbordados && !$superaAlertas) {
                continue;
            }

            $motivo = 'desbordados';
            if ($superaIncidencias && $superaDesbordados) {
                $motivo = 'ambos';
            } elseif ($superaIncidencias) {
                $motivo = 'incidencias';
            } elseif (!$superaDesbordados) {
                $motivo = 'alertas';
            }

            $zonas[] = [
                'municipio'          => $datos['municipio'],
                'departamento'       => $datos['departamento'],
                'incidenciasMes'     => $incidenciasMes,
                'totalContenedores'  => $total,
                'desbordados'        => $desbordados,
                'porcentaje'         => $porcentaje,
                'contenedoresEnAlerta' => $contenedoresEnAlerta,
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

    // RF59: la agregación temporal puede ser por día, semana (ISO) o mes.
    public function getVolumenPorTipoYPeriodo($rango = 'siempre', $agrupacion = 'mes') {
        switch ($agrupacion) {
            case 'dia':
                $formato = '%Y-%m-%d';
                break;
            case 'semana':
                $formato = '%x-S%v';
                break;
            case 'mes':
            default:
                $formato = '%Y-%m';
                break;
        }
        $sql = "SELECT t.descripcion AS tipoResiduo, t.color,
                       DATE_FORMAT(l.hora_levante, '$formato') AS periodo,
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

    // RF57 / RF58: $estado 'abiertas' (abierta, asignada o en curso) o 'resueltas'; sin valor, todas.
    public function getIncidenciasDetalle($rango = 'siempre', $estado = null) {
        switch ($estado) {
            case 'abiertas':
                $condicionEstado = "estado IN ('abierta', 'asignada', 'en_curso')";
                break;
            case 'resueltas':
                $condicionEstado = "estado = 'resuelta'";
                break;
            default:
                $condicionEstado = '1=1';
                break;
        }
        $sql = "SELECT tipo, prioridad, estado, fecha_apertura,
                       CASE WHEN estado = 'resuelta' AND fecha_cierre IS NOT NULL
                            THEN ROUND(TIMESTAMPDIFF(HOUR, fecha_apertura, fecha_cierre), 1)
                       END AS horasResolucion
                FROM INCIDENCIA
                WHERE " . $this->condicionRangoFechaHora('fecha_apertura', $rango) . " AND $condicionEstado
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

    // RF61: por cuadrilla, cantidad de incidencias resueltas en el rango y tiempo de resolución
    // (horas entre apertura y cierre) promedio, mínimo y máximo.
    public function getTiemposResolucion($rango = 'siempre') {
        $sql = "SELECT i.id_cuadrilla, m.nombre AS municipio,
                       COUNT(*) AS incidenciasCerradas,
                       AVG(TIMESTAMPDIFF(MINUTE, i.fecha_apertura, i.fecha_cierre)) / 60 AS horasPromedio,
                       MIN(TIMESTAMPDIFF(MINUTE, i.fecha_apertura, i.fecha_cierre)) / 60 AS horasMinimo,
                       MAX(TIMESTAMPDIFF(MINUTE, i.fecha_apertura, i.fecha_cierre)) / 60 AS horasMaximo
                FROM INCIDENCIA i
                JOIN CUADRILLA cu ON cu.id_cuadrilla = i.id_cuadrilla
                JOIN MUNICIPIO m ON m.id_municipio = cu.id_municipio
                WHERE i.estado = 'resuelta' AND i.fecha_cierre IS NOT NULL
                      AND " . $this->condicionRangoFechaHora('i.fecha_apertura', $rango) . "
                GROUP BY i.id_cuadrilla, m.nombre
                ORDER BY horasPromedio ASC, i.id_cuadrilla ASC";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = [
                'cuadrilla'           => (int) $row['id_cuadrilla'],
                'municipio'           => $row['municipio'],
                'incidenciasCerradas' => (int) $row['incidenciasCerradas'],
                'horasPromedio'       => round((float) $row['horasPromedio'], 1),
                'horasMinimo'         => round((float) $row['horasMinimo'], 1),
                'horasMaximo'         => round((float) $row['horasMaximo'], 1),
            ];
        }
        return $filas;
    }

    // RF63: municipios con más incidencias en el período (por defecto el último mes), para el panel.
    public function getTopZonasIncidencias($limite = 5, $rango = 'mes') {
        $limite = max(1, min(20, (int) $limite));
        $sql = "SELECT m.nombre AS municipio, COUNT(DISTINCT i.id_incidencia) AS incidencias
                FROM INCIDENCIA i
                JOIN INCIDENCIA_CONTENEDOR ic ON ic.id_incidencia = i.id_incidencia
                JOIN CONTENEDOR c ON c.id_contenedor = ic.id_contenedor
                JOIN CIRCUITO ci ON ci.id_circuito = c.id_circuito
                JOIN MUNICIPIO m ON m.id_municipio = ci.id_municipio
                WHERE " . $this->condicionRangoFechaHora('i.fecha_apertura', $rango) . "
                GROUP BY m.id_municipio, m.nombre
                ORDER BY incidencias DESC, m.nombre ASC
                LIMIT $limite";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = ['municipio' => $row['municipio'], 'incidencias' => (int) $row['incidencias']];
        }
        return $filas;
    }

    // RF70: contenedores sin recolectar. Crítica a las 24 h desde su último vaciado y máxima a las 48 h.
    const HORAS_ALERTA_CRITICA = 24;
    const HORAS_ALERTA_MAXIMA = 48;

    public function getAlertasContenedores() {
        $sql = "SELECT c.id_contenedor, c.calle, c.num_calle, c.estado, ci.nombre AS circuito,
                       MAX(cl.hora_vaciado) AS ultimaRecoleccion,
                       TIMESTAMPDIFF(HOUR, MAX(cl.hora_vaciado), NOW()) AS horasSinRecoleccion
                FROM CONTENEDOR c
                JOIN CIRCUITO ci ON ci.id_circuito = c.id_circuito
                JOIN CONTENEDOR_LEVANTE cl ON cl.id_contenedor = c.id_contenedor AND cl.hora_vaciado IS NOT NULL
                WHERE c.estado NOT IN ('en_deposito', 'inactivo')
                GROUP BY c.id_contenedor, c.calle, c.num_calle, c.estado, ci.nombre
                HAVING horasSinRecoleccion >= " . self::HORAS_ALERTA_CRITICA . "
                ORDER BY horasSinRecoleccion DESC";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $horas = (int) $row['horasSinRecoleccion'];
            if ($horas >= self::HORAS_ALERTA_MAXIMA) {
                $nivel = 'maxima';
            } else {
                $nivel = 'critica';
            }
            $filas[] = [
                'idContenedor'        => (int) $row['id_contenedor'],
                'direccion'           => trim($row['calle'] . ' ' . $row['num_calle']),
                'circuito'            => $row['circuito'],
                'estado'              => $row['estado'],
                'ultimaRecoleccion'   => $row['ultimaRecoleccion'],
                'horasSinRecoleccion' => $horas,
                'nivelAlerta'         => $nivel,
            ];
        }
        return $filas;
    }

    // RF69: última proyección de llenado calculada por contenedor (PREDICCION_LLENADO), de mayor a
    // menor nivel proyectado. Con 90 % o más se considera riesgo de sobrecarga.
    const UMBRAL_SOBRECARGA = 90;

    public function getProyeccionesLlenado() {
        $sql = "SELECT c.id_contenedor, c.calle, c.num_calle, ci.nombre AS circuito,
                       c.nivel_llenado AS nivelActual, p.nivel_proyectado AS nivelProyectado, p.fecha_calculo
                FROM PREDICCION_LLENADO p
                JOIN CONTENEDOR c ON c.id_contenedor = p.id_contenedor
                JOIN CIRCUITO ci ON ci.id_circuito = c.id_circuito
                WHERE p.id_prediccion = (
                    SELECT p2.id_prediccion FROM PREDICCION_LLENADO p2
                    WHERE p2.id_contenedor = p.id_contenedor
                    ORDER BY p2.fecha_calculo DESC, p2.id_prediccion DESC LIMIT 1
                )
                ORDER BY p.nivel_proyectado DESC, c.id_contenedor ASC";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $proyectado = (int) $row['nivelProyectado'];
            $filas[] = [
                'idContenedor'    => (int) $row['id_contenedor'],
                'direccion'       => trim($row['calle'] . ' ' . $row['num_calle']),
                'circuito'        => $row['circuito'],
                'nivelActual'     => (int) $row['nivelActual'],
                'nivelProyectado' => $proyectado,
                'fechaCalculo'    => $row['fecha_calculo'],
                'sobrecarga'      => $proyectado >= self::UMBRAL_SOBRECARGA,
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

    public function getFrecuenciaRecoleccion($turno = null, $dia = null, $rango = 'siempre') {
        $sql = "SELECT ci.nombre AS circuito, ci.codigo, m.nombre AS municipio, ci.turno, ci.frecuencia,
                       COUNT(l.id_levante) AS levantesCompletados
                FROM CIRCUITO ci
                JOIN MUNICIPIO m ON m.id_municipio = ci.id_municipio
                LEFT JOIN RUTA_RECOLECCION r ON r.id_circuito = ci.id_circuito
                LEFT JOIN LEVANTE l ON l.id_ruta = r.id_ruta AND l.estado = 'completado'
                     AND " . $this->condicionRangoFecha('l.hora_levante', $rango) . "
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

    // ERR-19: ingresos de residuos por centro de acopio (plantas, vertederos y rellenos) en el período.
    public function getIngresosPorCentro($rango = 'siempre') {
        $sql = "SELECT ca.nombre, m.nombre AS municipio, ca.tipo,
                       COUNT(cc.id) AS ingresos, COALESCE(SUM(cc.peso), 0) AS totalKg
                FROM CENTRO_ACOPIO ca
                JOIN MUNICIPIO m ON m.id_municipio = ca.id_municipio
                LEFT JOIN CAMION_CENTRO cc ON cc.id_centro = ca.id_centro
                     AND " . $this->condicionRangoFecha('cc.fecha_ingreso', $rango) . "
                GROUP BY ca.id_centro, ca.nombre, m.nombre, ca.tipo
                ORDER BY totalKg DESC, ca.nombre ASC";

        $result = mysqli_query($this->conn->getConexion(), $sql);
        $filas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $filas[] = [
                'nombre'    => $row['nombre'],
                'municipio' => $row['municipio'],
                'tipo'      => $row['tipo'],
                'ingresos'  => (int) $row['ingresos'],
                'totalKg'   => (float) $row['totalKg'],
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
    public function getReciclaje($rango = 'siempre') {
        $clasificacion = $this->getClasificacionResiduos($rango);
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

    public function getClasificacionResiduos($rango = 'siempre') {
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
                       AND cl.id_levante IN (SELECT l.id_levante FROM LEVANTE l WHERE " . $this->condicionRangoFecha('l.hora_levante', $rango) . ")
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
        if ($periodo = $this->rangoPersonalizado($rango)) {
            $rango = 'personalizado';
        }
        switch ($rango) {
            case 'personalizado':
                $formato   = '%Y-%m-%d';
                $condicion = "WHERE DATE(fecha_registro) BETWEEN '{$periodo[0]}' AND '{$periodo[1]}'";
                break;
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
