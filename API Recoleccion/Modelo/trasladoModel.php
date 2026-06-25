<?php

require_once 'conexion.php';

class TrasladoResiduo {

    protected $Id = "";
    protected $CamionId = "";
    protected $CentroId = "";
    protected $Peso = 0;
    protected $FechaIngreso = "";
    protected $conn;

    public function __construct($CamionId, $CentroId, $Peso, $FechaIngreso) {
        $this->CamionId = $CamionId;
        $this->CentroId = $CentroId;
        $this->Peso = $Peso;
        $this->FechaIngreso = $FechaIngreso;
        $this->conn = new conexion();
        $this->registrarTraslado($CamionId, $CentroId, $Peso, $FechaIngreso);
    }

    public function getId() {
        return $this->Id;
    }

    public function getCamionId() {
        return $this->CamionId;
    }

    public function getCentroId() {
        return $this->CentroId;
    }

    public function getPeso() {
        return $this->Peso;
    }

    public function getFechaIngreso() {
        return $this->FechaIngreso;
    }

    // Guarda un traslado de residuos asociado a un camión
    public function registrarTraslado($camionId, $centroId, $peso, $fechaIngreso) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO traslado_residuo (CamionId, CentroId, Peso, FechaIngreso) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iids", $camionId, $centroId, $peso, $fechaIngreso);
        mysqli_stmt_execute($stmt);
        
        // Obtener el ID generado automáticamente
        $this->Id = mysqli_insert_id($this->conn->getConexion());
        
        mysqli_stmt_close($stmt);
    }

    public function obtenerPorCamion($camionId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM traslado_residuo WHERE CamionId = ? ORDER BY FechaIngreso DESC");
        mysqli_stmt_bind_param($stmt, "i", $camionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $traslados = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $traslado = new TrasladoResiduo($row['CamionId'], $row['CentroId'], $row['Peso'], $row['FechaIngreso']);
            $traslado->Id = $row['Id']; // Asignar el ID obtenido de la BD
            $traslados[] = $traslado;
        }
        mysqli_stmt_close($stmt);
        return $traslados;
    }
}
