<?php

require_once __DIR__ . '/../../conexion.php';

class CircuitoModel {

    private $conn;

    public function __construct() {
        $this->conn = new conexion();
    }

    public function altaCircuito($municipioId, $nombre, $actividad, $turno, $frecuencia) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO circuitos (MunicipioId, Nombre, Actividad, Turno, Frecuencia) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issss", $municipioId, $nombre, $actividad, $turno, $frecuencia);
        mysqli_stmt_execute($stmt);
        $this->Id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $this->Id;
    }

    public function bajaCircuito($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE circuitos SET Actividad = 'inactivo' WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function actualizarCircuito($id, $municipioId, $nombre, $actividad, $turno, $frecuencia) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE circuitos SET MunicipioId = ?, Nombre = ?, Actividad = ?, Turno = ?, Frecuencia = ? WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "issssi", $municipioId, $nombre, $actividad, $turno, $frecuencia, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function registrarFrecuencia($id, $frecuencia) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE circuitos SET Frecuencia = ? WHERE Id = ?");
        mysqli_stmt_bind_param($stmt, "si", $frecuencia, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function listarCircuitos() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM circuitos");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $circuitos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $circuitos[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $circuitos;
    }

    public function filtrarPorMunicipio($municipioId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM circuitos WHERE MunicipioId = ?");
        mysqli_stmt_bind_param($stmt, "i", $municipioId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $circuitos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $circuitos[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $circuitos;
    }

    public function filtrarPorTurno($turno) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM circuitos WHERE Turno = ?");
        mysqli_stmt_bind_param($stmt, "s", $turno);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $circuitos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $circuitos[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $circuitos;
    }

    public function filtrarPorActividad($actividad) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM circuitos WHERE Actividad = ?");
        mysqli_stmt_bind_param($stmt, "s", $actividad);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $circuitos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $circuitos[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $circuitos;
    }
}
