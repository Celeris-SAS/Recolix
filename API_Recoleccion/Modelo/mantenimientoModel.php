<?php

require_once __DIR__ . '/conexion.php';

class MantenimientoModel {

    private $conn;

    public function __construct() {
        $this->conn = new conexion();
    }

    public function registrar($camionId, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO mantenimientos (CamionId, Tipo, Descripcion, FechaInicio, FechaFin, Costo) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issssd", $camionId, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $id;
    }

    public function programar($camionId, $descripcion, $fechaInicio) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO mantenimientos (CamionId, Tipo, Descripcion, FechaInicio, FechaFin, Costo) VALUES (?, 'preventivo', ?, ?, NULL, 0)");
        mysqli_stmt_bind_param($stmt, "iss", $camionId, $descripcion, $fechaInicio);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $id;
    }

    public function actualizarMantenimiento($id, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE mantenimientos SET Tipo = ?, Descripcion = ?, FechaInicio = ?, FechaFin = ?, Costo = ? WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "ssssdi", $tipo, $descripcion, $fechaInicio, $fechaFin, $costo, $id);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $affected > 0;
    }

    public function obtenerPorCamion($camionId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM mantenimientos WHERE CamionId = ? ORDER BY FechaInicio DESC");
        mysqli_stmt_bind_param($stmt, "i", $camionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $mantenimientos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $mantenimientos[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $mantenimientos;
    }

    public function getAllMantenimientos() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM mantenimientos ORDER BY FechaInicio DESC");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $mantenimientos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $mantenimientos[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $mantenimientos;
    }

    public function getMantenimiento($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM mantenimientos WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function deleteMantenimiento($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "DELETE FROM mantenimientos WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function filtrarPorTipo($tipo) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM mantenimientos WHERE Tipo = ? ORDER BY FechaInicio DESC");
        mysqli_stmt_bind_param($stmt, "s", $tipo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function obtenerCostoTotal($camionId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT SUM(Costo) as total FROM mantenimientos WHERE CamionId = ?");
        mysqli_stmt_bind_param($stmt, "i", $camionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return floatval($row['total']);
    }
}