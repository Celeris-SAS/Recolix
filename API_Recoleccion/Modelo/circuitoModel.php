<?php

require_once __DIR__ . '/../../conexion.php';

class CircuitoModel {

    private $conn;

    public function __construct() {
        $this->conn = new conexion();
    }

    public function insertCircuito($municipioId, $nombre, $codigo, $actividad, $turno, $frecuencia, $poligono = null) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO circuito (id_municipio, nombre, codigo, actividad, turno, frecuencia, poligono) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issssss", $municipioId, $nombre, $codigo, $actividad, $turno, $frecuencia, $poligono);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $id;
    }

    public function updateCircuito($id, $municipioId, $nombre, $codigo, $actividad, $turno, $frecuencia, $poligono = null) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE circuito SET id_municipio = ?, nombre = ?, codigo = ?, actividad = ?, turno = ?, frecuencia = ?, poligono = ? WHERE id_circuito = ?");
        mysqli_stmt_bind_param($stmt, "issssssi", $municipioId, $nombre, $codigo, $actividad, $turno, $frecuencia, $poligono, $id);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $affected > 0;
    }

    public function actualizarPoligono($id, $poligonoJson) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE circuito SET poligono = ? WHERE id_circuito = ?");
        mysqli_stmt_bind_param($stmt, "si", $poligonoJson, $id);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $affected > 0;
    }

    public function deleteCircuito($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "DELETE FROM circuito WHERE id_circuito = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function getAllCircuitos() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM circuito");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $circuitos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $circuitos[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $circuitos;
    }

    public function getCircuito($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM circuito WHERE id_circuito = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function getCodigo($codigo) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT id_circuito FROM circuito WHERE codigo = ?");
        mysqli_stmt_bind_param($stmt, "s", $codigo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function obtenerPorMunicipio($municipioId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM circuito WHERE id_municipio = ?");
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
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM circuito WHERE turno = ?");
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
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM circuito WHERE actividad = ?");
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

    public function filtrarPorFrecuencia($frecuencia) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM circuito WHERE frecuencia = ?");
        mysqli_stmt_bind_param($stmt, "s", $frecuencia);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $circuitos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $circuitos[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $circuitos;
    }

    public function obtenerRutas($circuitoId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM ruta_recoleccion WHERE id_circuito = ?");
        mysqli_stmt_bind_param($stmt, "i", $circuitoId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rutas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rutas[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rutas;
    }

    public function obtenerContenedores($circuitoId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM contenedor WHERE id_circuito = ?");
        mysqli_stmt_bind_param($stmt, "i", $circuitoId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $contenedores = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $contenedores[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $contenedores;
    }
}
