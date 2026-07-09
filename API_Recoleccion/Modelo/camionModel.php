<?php

require_once __DIR__ . '/conexion.php';

class CamionModel
{

    private $conn;

    public function __construct()
    {
        $this->conn = new conexion();
    }
    // Métodos públicos del modelo que usan la BD
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

    public function filtrarDisponibilidad()
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camion WHERE estado = 'disponible'");
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
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camion WHERE estado = 'en_mantenimiento'");
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

        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 'traslado' AS tipo_evento, CONCAT('Ingreso a centro, peso: ', peso, ' kg') AS descripcion, fecha_ingreso AS fecha FROM traslado_residuo WHERE id_camion = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $historial[] = $row;
        }
        mysqli_stmt_close($stmt);

        return $historial;
    }

}

