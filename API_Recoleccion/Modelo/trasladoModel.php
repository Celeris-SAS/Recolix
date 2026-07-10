<?php

require_once __DIR__ . '/../../conexion.php';
class TrasladoModel {

    private $conn;

    public function __construct() {
        $this->conn = new conexion();
    }

    public function insertTrasladoResiduo($camionId, $centroId, $peso, $fechaIngreso) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO traslado_residuo (CamionId, CentroId, Peso, FechaIngreso) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iids", $camionId, $centroId, $peso, $fechaIngreso);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $id;
    }

    public function obtenerPorCamion($camionId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM traslado_residuo WHERE CamionId = ? ORDER BY FechaIngreso DESC");
        mysqli_stmt_bind_param($stmt, "i", $camionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $traslados = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $traslados[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $traslados;
    }

    public function getAllTrasladosResiduos() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM traslado_residuo ORDER BY FechaIngreso DESC");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $traslados = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $traslados[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $traslados;
    }

    public function getTrasladoResiduo($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM traslado_residuo WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function updateTrasladoResiduo($id, $camionId, $centroId, $peso, $fechaIngreso) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE traslado_residuo SET CamionId = ?, CentroId = ?, Peso = ?, FechaIngreso = ? WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "iidsi", $camionId, $centroId, $peso, $fechaIngreso, $id);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $affected > 0;
    }

    public function deleteTrasladoResiduo($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "DELETE FROM traslado_residuo WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function obtenerPorCentro($centroId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM traslado_residuo WHERE CentroId = ? ORDER BY FechaIngreso DESC");
        mysqli_stmt_bind_param($stmt, "i", $centroId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $traslados = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $traslados[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $traslados;
    }

    public function filtrarPorFecha($fecha) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM traslado_residuo WHERE FechaIngreso LIKE ? ORDER BY FechaIngreso DESC");
        $like = $fecha . '%';
        mysqli_stmt_bind_param($stmt, "s", $like);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $traslados = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $traslados[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $traslados;
    }

    public function obtenerPesoTotal($camionId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT SUM(Peso) as total FROM traslado_residuo WHERE CamionId = ?");
        mysqli_stmt_bind_param($stmt, "i", $camionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return floatval($row['total']);
    }
}