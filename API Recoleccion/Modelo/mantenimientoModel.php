<?php

require_once 'conexion.php';

class Mantenimiento {

    protected $Id = "";
    protected $CamionId = "";
    protected $Tipo = "";        // preventivo o correctivo
    protected $Descripcion = "";
    protected $FechaInicio = "";
    protected $FechaFin = "";
    protected $Costo = 0;
    protected $conn;

    public function __construct($Id, $CamionId, $Tipo, $Descripcion, $FechaInicio, $FechaFin, $Costo) {
        $this->Id = $Id;
        $this->CamionId = $CamionId;
        $this->Tipo = $Tipo;
        $this->Descripcion = $Descripcion;
        $this->FechaInicio = $FechaInicio;
        $this->FechaFin = $FechaFin;
        $this->Costo = $Costo;
        $this->conn = new conexion();
        $this->altaMantenimiento($Id, $CamionId, $Tipo, $Descripcion, $FechaInicio, $FechaFin, $Costo);
    }

    public function getId() {
        return $this->Id;
    }

    public function getCamionId() {
        return $this->CamionId;
    }

    public function getTipo() {
        return $this->Tipo;
    }

    public function getDescripcion() {
        return $this->Descripcion;
    }

    public function getFechaInicio() {
        return $this->FechaInicio;
    }

    public function getFechaFin() {
        return $this->FechaFin;
    }

    public function getCosto() {
        return $this->Costo;
    }

    public function setTipo($Tipo) {
        $tiposValidos = ["preventivo", "correctivo"];
        if (in_array($Tipo, $tiposValidos)) {
            $this->Tipo = $Tipo;
        } else {
            throw new Exception("Tipo no válido. Los tipos válidos son: " . implode(", ", $tiposValidos));
        }
    }

    public function setDescripcion($Descripcion) {
        $this->Descripcion = $Descripcion;
    }

    public function setFechaInicio($FechaInicio) {
        $this->FechaInicio = $FechaInicio;
    }

    public function setFechaFin($FechaFin) {
        $this->FechaFin = $FechaFin;
    }

    public function setCosto($Costo) {
        $this->Costo = $Costo;
    }

    // Guarda un mantenimiento ya realizado (preventivo o correctivo)
    public function registrar($camionId, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO mantenimientos (CamionId, Tipo, Descripcion, FechaInicio, FechaFin, Costo) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issssd", $camionId, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Planifica un mantenimiento preventivo para una fecha futura (FechaFin queda pendiente)
    public function programar($camionId, $descripcion, $fechaInicio) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO mantenimientos (CamionId, Tipo, Descripcion, FechaInicio, FechaFin, Costo) VALUES (?, 'preventivo', ?, ?, NULL, 0)");
        mysqli_stmt_bind_param($stmt, "iss", $camionId, $descripcion, $fechaInicio);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function actualizarMantenimiento($id, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE mantenimientos SET Tipo = ?, Descripcion = ?, FechaInicio = ?, FechaFin = ?, Costo = ? WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "ssssdi", $tipo, $descripcion, $fechaInicio, $fechaFin, $costo, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function obtenerPorCamion($camionId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM mantenimientos WHERE CamionId = ? ORDER BY FechaInicio DESC");
        mysqli_stmt_bind_param($stmt, "i", $camionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $mantenimientos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $mantenimientos[] = new Mantenimiento($row['Id'], $row['CamionId'], $row['Tipo'], $row['Descripcion'], $row['FechaInicio'], $row['FechaFin'], $row['Costo']);
        }
        mysqli_stmt_close($stmt);
        return $mantenimientos;
    }
}
