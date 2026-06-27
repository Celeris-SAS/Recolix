<?php

require_once __DIR__ . '/conexion.php';

class CamionModel {

    protected $Id = "";
    protected $Matricula = "";
    protected $TipoCarga = "";
    protected $Capacidad = 0;
    protected $Anio = 0;
    protected $Modelo = "";
    protected $Estado = "";
    protected $conn;

    public function __construct($Matricula, $TipoCarga, $Capacidad, $Anio, $Modelo, $Estado) {
        $this->Matricula = $Matricula;
        $this->TipoCarga = $TipoCarga;
        $this->Capacidad = $Capacidad;
        $this->Anio = $Anio;
        $this->Modelo = $Modelo;
        $this->Estado = $Estado;
        $this->conn = new conexion();
        $this->Id = $this->insertCamion($Matricula, $TipoCarga, $Capacidad, $Anio, $Modelo, $Estado);
    }
    // Métodos públicos del modelo que usan la BD
    public function esDisponible($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT Estado FROM camiones WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (!$row) return false;
        if ($row['Estado'] !== 'disponible') return false;
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT COUNT(*) as cnt FROM levantes WHERE CamionId = ? AND Estado IN ('pendiente', 'en curso')");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $c = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        return intval($c['cnt']) === 0;
    }

    public function insertCamion($matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO camiones (Matricula, TipoCarga, Capacidad, Anio, Modelo, Estado) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssiiss", $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $id;
    }

    public function bajaCamion($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camiones SET Estado = 'de baja' WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function updateCamion($id, $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camiones SET Matricula = ?, TipoCarga = ?, Capacidad = ?, Anio = ?, Modelo = ?, Estado = ? WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "ssiissi", $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function getAllCamiones() {
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

    public function getCamion($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camiones WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function filtrarEstado($estado) {
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

    public function filtrarTipoCarga($tipoCarga) {
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

    public function filtrarDisponibilidad() {
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

    public function filtrarMantenimiento() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camiones WHERE Estado = 'en mantenimiento'");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $camiones = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $camiones[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $camiones;
    }

    public function filtrarAnio($anio) {
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

    public function filtrarModelo($modelo) {
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

    public function filtrarCapacidad($capacidad) {
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

    public function obtenerHistorial($camionId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM eventos_camion WHERE CamionId = ? ORDER BY Fecha DESC");
        mysqli_stmt_bind_param($stmt, "i", $camionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $eventos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $eventos[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $eventos;
    }
}

