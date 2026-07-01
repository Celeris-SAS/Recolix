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
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT Estado FROM camiones WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (!$row)
            return false;
        if ($row['Estado'] !== 'disponible')
            return false;
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT COUNT(*) as cnt FROM levantes WHERE CamionId = ? AND Estado IN ('pendiente', 'en curso')");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $c = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        return intval($c['cnt']) === 0;
    }

    public function insertCamion($matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO camiones (Matricula, TipoCarga, Capacidad, Anio, Modelo, Estado) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssiiis", $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $id;
    }

    public function bajaCamion($id)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camiones SET Estado = 'baja' WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function updateCamion($id, $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camiones SET Matricula = ?, TipoCarga = ?, Capacidad = ?, Anio = ?, Modelo = ?, Estado = ? WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "ssiiisi", $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function getAllCamiones()
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camiones");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $camiones = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $camiones[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $camiones;
    }

    public function getCamion($id)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camiones WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function filtrarEstado($estado)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camiones WHERE Estado = ?");
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
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camiones WHERE TipoCarga = ?");
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
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camiones WHERE Estado = 'disponible'");
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
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camiones WHERE Estado = 'en_mantenimiento'");
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
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camiones WHERE Anio = ?");
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
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camiones WHERE Modelo = ?");
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
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camiones WHERE Capacidad >= ?");
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

        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 'levante' AS tipo_evento, Estado AS descripcion, HoraLevante AS fecha FROM levantes WHERE CamionId = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $historial[] = $row;
        }
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 'mantenimiento' AS tipo_evento, CONCAT(Tipo, ': ', Descripcion) AS descripcion, FechaInicio AS fecha FROM mantenimientos WHERE CamionId = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $historial[] = $row;
        }
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 'traslado' AS tipo_evento, CONCAT('Ingreso a centro, peso: ', Peso, ' kg') AS descripcion, FechaIngreso AS fecha FROM traslado_residuo WHERE CamionId = ?");
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

