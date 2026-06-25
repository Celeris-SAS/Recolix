<?php

require_once 'conexion.php';

class EventoCamionModel {

    protected $Id = "";
    protected $CamionId = "";
    protected $TipoEvento = "";
    protected $Detalle = "";
    protected $Fecha = "";
    protected $conn;

    public function __construct() {
        $this->conn = new conexion();
    }

    public function getId() {
        return $this->Id;
    }

    public function getCamionId() {
        return $this->CamionId;
    }

    public function getTipoEvento() {
        return $this->TipoEvento;
    }

    public function getDetalle() {
        return $this->Detalle;
    }

    public function getFecha() {
        return $this->Fecha;
    }

    public function registrarEvento($camionId, $tipoEvento, $detalle, $fecha) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO eventos_camion (CamionId, TipoEvento, Detalle, Fecha) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isss", $camionId, $tipoEvento, $detalle, $fecha);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $id;
    }

    public function obtenerPorCamion($camionId) {
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

    public function getAllEventosCamion() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM eventos_camion ORDER BY Fecha DESC");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $eventos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $eventos[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $eventos;
    }

    public function getEventoCamion($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM eventos_camion WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function updateEventoCamion($id, $camionId, $tipoEvento, $fecha, $descripcion) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE eventos_camion SET CamionId = ?, TipoEvento = ?, Detalle = ?, Fecha = ? WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "isssi", $camionId, $tipoEvento, $descripcion, $fecha, $id);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $affected > 0;
    }

    public function deleteEventoCamion($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "DELETE FROM eventos_camion WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function filtrarPorTipo($tipoEvento) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM eventos_camion WHERE TipoEvento = ? ORDER BY Fecha DESC");
        mysqli_stmt_bind_param($stmt, "s", $tipoEvento);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function obtenerEventosPorFecha($fecha) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM eventos_camion WHERE Fecha LIKE ? ORDER BY Fecha DESC");
        $like = $fecha . '%';
        mysqli_stmt_bind_param($stmt, "s", $like);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }
}