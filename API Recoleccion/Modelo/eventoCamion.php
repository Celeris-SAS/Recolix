<?php

require_once 'conexion.php';

class EventoCamion {

    protected $Id = "";
    protected $CamionId = "";
    protected $TipoEvento = "";
    protected $Detalle = "";
    protected $Fecha = "";
    protected $conn;

    public function __construct($CamionId, $TipoEvento, $Detalle, $Fecha) {
        $this->CamionId = $CamionId;
        $this->TipoEvento = $TipoEvento;
        $this->Detalle = $Detalle;
        $this->Fecha = $Fecha;
        $this->conn = new conexion();
        $this->registrarEvento($CamionId, $TipoEvento, $Detalle, $Fecha);
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

    // Guarda un nuevo evento en el historial del camión
    public function registrarEvento($camionId, $tipoEvento, $detalle, $fecha) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO eventos_camion (CamionId, TipoEvento, Detalle, Fecha) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isss", $camionId, $tipoEvento, $detalle, $fecha);
        mysqli_stmt_execute($stmt);
        $this->Id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
    }

    public function obtenerPorCamion($camionId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM eventos_camion WHERE CamionId = ? ORDER BY Fecha DESC");
        mysqli_stmt_bind_param($stmt, "i", $camionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $eventos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $evento = new EventoCamion($row['CamionId'], $row['TipoEvento'], $row['Detalle'], $row['Fecha']);
            $evento->Id = $row['Id']; // Asignar el ID obtenido de la BD
            $eventos[] = $evento;
        }
        mysqli_stmt_close($stmt);
        return $eventos;
    }
}
