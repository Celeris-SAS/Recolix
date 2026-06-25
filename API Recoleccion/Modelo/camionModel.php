<?php

require_once 'conexion.php';

class Camion {

    protected $Id = "";
    protected $Matricula = "";
    protected $TipoCarga = "";
    protected $Capacidad = 0;
    protected $Anio = 0;
    protected $Modelo = "";
    protected $Estado = "";
    protected $conn;

    public function __construct($Id, $Matricula, $TipoCarga, $Capacidad, $Anio, $Modelo, $Estado) {
        $this->Id = $Id;
        $this->Matricula = $Matricula;
        $this->TipoCarga = $TipoCarga;
        $this->Capacidad = $Capacidad;
        $this->Anio = $Anio;
        $this->Modelo = $Modelo;
        $this->Estado = $Estado;
        $this->conn = new conexion();

        $this->altaCamion($Id, $Matricula, $TipoCarga, $Capacidad, $Anio, $Modelo, $Estado);
    }

    public function getId() {
        return $this->Id;
    }

    public function getMatricula() {
        return $this->Matricula;
    }

    public function getTipoCarga() {
        return $this->TipoCarga;
    }

    public function getCapacidad() {
        return $this->Capacidad;
    }

    public function getAnio() {
        return $this->Anio;
    }

    public function getModelo() {
        return $this->Modelo;
    }

    public function getEstado() {
        return $this->Estado;
    }

    public function setMatricula($Matricula) {
        $this->Matricula = $Matricula;
    }

    public function setTipoCarga($TipoCarga) {
        $this->TipoCarga = $TipoCarga;
    }

    public function setCapacidad($Capacidad) {
        $this->Capacidad = $Capacidad;
    }

    public function setAnio($Anio) {
        $this->Anio = $Anio;
    }

    public function setModelo($Modelo) {
        $this->Modelo = $Modelo;
    }

    public function setEstado($Estado) {
        $this->Estado = $Estado;
    }

    public function __toString() {
        return "Camión [Id: " . $this->Id . ", Matrícula: " . $this->Matricula . ", Tipo de Carga: " . $this->TipoCarga . ", Capacidad: " . $this->Capacidad . " m³, Año: " . $this->Anio . ", Modelo: " . $this->Modelo . ", Estado: " . $this->Estado . "]";
    }

    public function actualizarEstado($nuevoEstado) {
        $estadosValidos = ["disponible", "en ruta", "en mantenimiento", "de baja"];
        if (in_array($nuevoEstado, $estadosValidos)) {
            $this->Estado = $nuevoEstado;
        } else {
            throw new Exception("Estado no válido. Los estados válidos son: " . implode(", ", $estadosValidos));
        }
    }

    // Verifica estado y que no tenga levantes activos
    public function esDisponible() {
        if ($this->Estado !== "disponible") {
            return false;
        }
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT COUNT(*) FROM levantes WHERE CamionId = ? AND Estado IN ('pendiente', 'en curso')");
        mysqli_stmt_bind_param($stmt, "i", $this->Id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        return $count === 0;
    }

    public function altaCamion($id, $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO camiones (Id, Matricula, TipoCarga, Capacidad, Anio, Modelo, Estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issiiss", $id, $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Da de baja cambiando el Estado, sin eliminar el registro
    public function bajaCamion($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camiones SET Estado = 'de baja' WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function actualizarCamion($id, $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camiones SET Matricula = ?, TipoCarga = ?, Capacidad = ?, Anio = ?, Modelo = ?, Estado = ? WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "ssiissi", $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function listarCamiones() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camiones");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $camiones = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $camiones[] = new Camion($row['Id'], $row['Matricula'], $row['TipoCarga'], $row['Capacidad'], $row['Anio'], $row['Modelo'], $row['Estado']);
        }
        mysqli_stmt_close($stmt);
        return $camiones;
    }

    public function filtrarEstado() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camiones WHERE Estado = ?");
        mysqli_stmt_bind_param($stmt, "s", $this->Estado);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $camiones = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $camiones[] = new Camion($row['Id'], $row['Matricula'], $row['TipoCarga'], $row['Capacidad'], $row['Anio'], $row['Modelo'], $row['Estado']);
        }
        mysqli_stmt_close($stmt);
        return $camiones;
    }

    public function filtrarTipoCarga() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camiones WHERE TipoCarga = ?");
        mysqli_stmt_bind_param($stmt, "s", $this->TipoCarga);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $camiones = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $camiones[] = new Camion($row['Id'], $row['Matricula'], $row['TipoCarga'], $row['Capacidad'], $row['Anio'], $row['Modelo'], $row['Estado']);
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
            $camiones[] = new Camion($row['Id'], $row['Matricula'], $row['TipoCarga'], $row['Capacidad'], $row['Anio'], $row['Modelo'], $row['Estado']);
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
            $camiones[] = new Camion($row['Id'], $row['Matricula'], $row['TipoCarga'], $row['Capacidad'], $row['Anio'], $row['Modelo'], $row['Estado']);
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
            $camiones[] = new Camion($row['Id'], $row['Matricula'], $row['TipoCarga'], $row['Capacidad'], $row['Anio'], $row['Modelo'], $row['Estado']);
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
            $camiones[] = new Camion($row['Id'], $row['Matricula'], $row['TipoCarga'], $row['Capacidad'], $row['Anio'], $row['Modelo'], $row['Estado']);
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
            $camiones[] = new Camion($row['Id'], $row['Matricula'], $row['TipoCarga'], $row['Capacidad'], $row['Anio'], $row['Modelo'], $row['Estado']);
        }
        mysqli_stmt_close($stmt);
        return $camiones;
    }

    public function obtenerHistorial() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM eventos_camion WHERE CamionId = ? ORDER BY Fecha DESC");
        mysqli_stmt_bind_param($stmt, "i", $this->Id);
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
