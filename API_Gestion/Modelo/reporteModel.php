<?php
require_once __DIR__ . '/../../conexion.php';

class ReporteModel {
    private $conn;

    public function __construct() {
        $this->conn = new conexion();
    }

    private function contarFilas($sql) {
        $result = mysqli_query($this->conn->getConexion(), $sql);
        return $result ? (int) mysqli_fetch_row($result)[0] : 0;
    }

    public function getContenedoresRecolectados() {
        return $this->contarFilas("SELECT COUNT(DISTINCT id_contenedor) FROM CONTENEDOR_LEVANTE");
    }

    public function getUsuariosRegistrados() {
        return $this->contarFilas("SELECT COUNT(*) FROM USUARIO");
    }

    public function getResiduosRecolectados() {
        $result = mysqli_query($this->conn->getConexion(), "SELECT COALESCE(SUM(total_recolectado), 0) FROM LEVANTE");
        return $result ? (float) mysqli_fetch_row($result)[0] : 0.0;
    }

    public function getNivelesLlenado() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT nivel_llenado FROM CONTENEDOR WHERE nivel_llenado IS NOT NULL");
        mysqli_stmt_execute($stmt);
        $result  = mysqli_stmt_get_result($stmt);
        $niveles = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $niveles[] = (int) $row['nivel_llenado'];
        }
        mysqli_stmt_close($stmt);
        return $niveles;
    }

    private function porcentajeEstado($tabla, $estado) {
        $total = $this->contarFilas("SELECT COUNT(*) FROM $tabla");
        if ($total === 0) return 0.0;
        $conEstado = $this->contarFilas("SELECT COUNT(*) FROM $tabla WHERE estado = '" . $estado . "'");
        return round(($conEstado / $total) * 100, 1);
    }

    public function getPorcentajeContenedoresFuncionales() {
        return $this->porcentajeEstado('CONTENEDOR', 'funcional');
    }

    public function getPorcentajeContenedoresSinRecolectar() {
        return $this->porcentajeEstado('CONTENEDOR', 'desbordado');
    }

    public function getPorcentajeIncidenciasResueltas() {
        return $this->porcentajeEstado('INCIDENCIA', 'resuelta');
    }

    public function getPorcentajeLevantesCompletados() {
        return $this->porcentajeEstado('LEVANTE', 'completado');
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
            $porcentaje         = $total > 0 ? round(($desbordados / $total) * 100, 1) : 0.0;
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
            return $b['incidenciasMes'] <=> $a['incidenciasMes'] ?: $b['porcentaje'] <=> $a['porcentaje'];
        });

        return $zonas;
    }
}
