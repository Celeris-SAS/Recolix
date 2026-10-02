<?php

require_once __DIR__ . '/../../conexion.php';

class CamionModel
{

    // Velocidad promedio asumida para estimar el tiempo hasta la próxima parada
    // (no hay ruteo real por calles disponible en el proyecto).
    private const VELOCIDAD_PROMEDIO_KMH = 25.0;

    private $conn;

    public function __construct()
    {
        $this->conn = new conexion();
    }
    public function esDisponible($id)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT estado FROM camion WHERE id_camion = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (!$row)
            return false;
        if ($row['estado'] !== 'disponible')
            return false;
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT COUNT(*) as cnt FROM levante WHERE id_camion = ? AND estado IN ('pendiente', 'en_curso')");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $c = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        return intval($c['cnt']) === 0;
    }

    public function insertCamion($matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO camion (matricula, tipo_carga, capacidad, anio, modelo, estado) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssiiss", $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $id;
    }

    public function bajaCamion($id)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camion SET estado = 'de_baja' WHERE id_camion = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function updateCamion($id, $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camion SET matricula = ?, tipo_carga = ?, capacidad = ?, anio = ?, modelo = ?, estado = ? WHERE id_camion = ?");
        mysqli_stmt_bind_param($stmt, "ssiissi", $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function marcarCamionEnMantenimiento($id)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camion SET estado = 'en_mantenimiento' WHERE id_camion = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function finalizarMantenimientosVencidos()
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            UPDATE camion
            SET estado = 'disponible'
            WHERE estado = 'en_mantenimiento'
              AND NOT EXISTS (
                  SELECT 1 FROM mantenimiento
                  WHERE mantenimiento.id_camion = camion.id_camion
                    AND mantenimiento.fecha_fin >= NOW()
              )
        ");
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function getAllCamiones()
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camion");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $camiones = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $camiones[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $camiones;
    }

    public function getMatricula($matricula)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT id_camion FROM camion WHERE matricula = ?");
        mysqli_stmt_bind_param($stmt, "s", $matricula);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function getCamion($id)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camion WHERE id_camion = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function filtrarEstado($estado)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camion WHERE estado = ?");
        mysqli_stmt_bind_param($stmt, "s", $estado);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $camiones = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $camiones[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $camiones;
    }

    public function filtrarTipoCarga($tipoCarga)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camion WHERE tipo_carga = ?");
        mysqli_stmt_bind_param($stmt, "s", $tipoCarga);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $camiones = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $camiones[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $camiones;
    }

    public function filtrarDisponibilidad($capacidadMinima = null)
    {
        $sql = "
            SELECT c.* FROM camion c
            WHERE c.estado = 'disponible'
              AND NOT EXISTS (
                  SELECT 1 FROM levante l
                  WHERE l.id_camion = c.id_camion AND l.estado IN ('pendiente', 'en_curso')
              )
        ";
        if ($capacidadMinima !== null) {
            $sql .= " AND c.capacidad >= ?";
        }

        $stmt = mysqli_prepare($this->conn->getConexion(), $sql);
        if ($capacidadMinima !== null) {
            mysqli_stmt_bind_param($stmt, "i", $capacidadMinima);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $camiones = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $camiones[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $camiones;
    }

    public function filtrarMantenimiento()
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT c.*,
                   (SELECT MAX(m.fecha_fin) FROM mantenimiento m WHERE m.id_camion = c.id_camion AND m.fecha_fin >= NOW()) AS fecha_fin_mantenimiento
            FROM camion c
            WHERE c.estado = 'en_mantenimiento'
        ");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $camiones = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $camiones[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $camiones;
    }

    public function filtrarAnio($anio)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camion WHERE anio = ?");
        mysqli_stmt_bind_param($stmt, "i", $anio);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $camiones = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $camiones[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $camiones;
    }

    public function filtrarModelo($modelo)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camion WHERE modelo = ?");
        mysqli_stmt_bind_param($stmt, "s", $modelo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $camiones = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $camiones[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $camiones;
    }

    public function filtrarCapacidad($capacidad)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camion WHERE capacidad >= ?");
        mysqli_stmt_bind_param($stmt, "i", $capacidad);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $camiones = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $camiones[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $camiones;
    }

    public function obtenerHistorial($id) {
        $historial = [];

        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 'levante' AS tipo_evento, estado AS descripcion, hora_levante AS fecha FROM levante WHERE id_camion = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $historial[] = $row;
        }
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 'mantenimiento' AS tipo_evento, CONCAT(tipo, ': ', descripcion) AS descripcion, fecha_inicio AS fecha FROM mantenimiento WHERE id_camion = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $historial[] = $row;
        }
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 'traslado' AS tipo_evento, CONCAT('Ingreso a centro, peso: ', peso, ' kg') AS descripcion, fecha_ingreso AS fecha FROM camion_centro WHERE id_camion = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $historial[] = $row;
        }
        mysqli_stmt_close($stmt);

        return $historial;
    }

    public function obtenerEnOperativa()
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT c.id_camion, c.matricula, c.modelo, c.tipo_carga,
                   l.id_levante, l.id_ruta, l.hora_salida
            FROM camion c
            JOIN levante l ON l.id_camion = c.id_camion AND l.estado = 'en_curso'
            WHERE c.estado = 'en_ruta'
            ORDER BY c.matricula ASC
        ");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $camiones = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $camiones[] = $row;
        }
        mysqli_stmt_close($stmt);

        $flota = [];
        foreach ($camiones as $c) {
            $ubicacion = $this->obtenerUltimaUbicacion((int) $c['id_levante']);
            if (!$ubicacion) {
                // Sin ningún contenedor vaciado todavía en este levante: no hay
                // posición derivable, así que el camión no se muestra en el mapa.
                continue;
            }

            $proxima = $this->obtenerProximaParada((int) $c['id_levante'], (int) $c['id_ruta']);
            $eta = $proxima
                ? $this->calcularEta(
                      $ubicacion['latitud'], $ubicacion['longitud'],
                      $proxima['latitud'], $proxima['longitud']
                  )
                : null;

            $flota[] = [
                'id_camion' => (int) $c['id_camion'],
                'matricula' => $c['matricula'],
                'modelo' => $c['modelo'],
                'tipo_carga' => $c['tipo_carga'],
                'levante' => [
                    'id_levante' => (int) $c['id_levante'],
                    'id_ruta' => (int) $c['id_ruta'],
                    'hora_salida' => $c['hora_salida']
                ],
                'ubicacion' => $ubicacion,
                'proxima_parada' => $proxima,
                'eta' => $eta
            ];
        }
        return $flota;
    }

    private function obtenerUltimaUbicacion($idLevante)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT cl.id_contenedor, cl.hora_vaciado, cn.latitud, cn.longitud, cn.calle, cn.num_calle
            FROM contenedor_levante cl
            JOIN contenedor cn ON cn.id_contenedor = cl.id_contenedor
            WHERE cl.id_levante = ?
              AND cl.hora_vaciado IS NOT NULL
              AND cn.latitud IS NOT NULL AND cn.longitud IS NOT NULL
            ORDER BY cl.hora_vaciado DESC
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "i", $idLevante);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (!$row)
            return null;
        $row['latitud'] = (float) $row['latitud'];
        $row['longitud'] = (float) $row['longitud'];
        return $row;
    }

    private function obtenerProximaParada($idLevante, $idRuta)
    {
        // "Pendiente real": nunca vaciado NI marcado con motivo (o sea, sigue
        // en el estado por defecto de la precarga). Evita que un contenedor ya
        // visitado-pero-saltado (p. ej. 'contenedor_ausente') reaparezca como
        // próxima parada solo por tener hora_vaciado NULL.
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT cl.id_contenedor, cn.latitud, cn.longitud, cn.calle, cn.num_calle,
                   COALESCE(rc.orden_visita, 999999) AS orden_visita
            FROM contenedor_levante cl
            JOIN contenedor cn ON cn.id_contenedor = cl.id_contenedor
            LEFT JOIN ruta_contenedor rc
                   ON rc.id_ruta = ? AND rc.id_contenedor = cl.id_contenedor
            WHERE cl.id_levante = ?
              AND cl.hora_vaciado IS NULL
              AND cl.motivo IS NULL
              AND cn.latitud IS NOT NULL AND cn.longitud IS NOT NULL
            ORDER BY orden_visita ASC, cl.id_contenedor ASC
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "ii", $idRuta, $idLevante);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (!$row)
            return null;
        $row['latitud'] = (float) $row['latitud'];
        $row['longitud'] = (float) $row['longitud'];
        $row['orden_visita'] = (int) $row['orden_visita'];
        return $row;
    }

    private function distanciaHaversineKm($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

    private function calcularEta($lat1, $lon1, $lat2, $lon2)
    {
        $distanciaKm = $this->distanciaHaversineKm($lat1, $lon1, $lat2, $lon2);
        $minutos = ($distanciaKm / self::VELOCIDAD_PROMEDIO_KMH) * 60;
        $minutos = max(1, (int) round($minutos));
        return [
            'distancia_km' => round($distanciaKm, 2),
            'velocidad_kmh' => self::VELOCIDAD_PROMEDIO_KMH,
            'minutos' => $minutos,
            'texto' => $minutos < 60
                ? "≈ {$minutos} min"
                : sprintf('≈ %dh %02dmin', intdiv($minutos, 60), $minutos % 60)
        ];
    }

}

