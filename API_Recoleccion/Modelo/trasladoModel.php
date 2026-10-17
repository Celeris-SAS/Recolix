<?php

require_once __DIR__ . '/../../conexion.php';
class TrasladoModel {

    private $conn;

    public function __construct() {
        $this->conn = new conexion();
    }

    public function insertTrasladoResiduo($camionId, $centroId, $peso, $fechaIngreso) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO camion_centro (id_camion, id_centro, peso, fecha_ingreso) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iids", $camionId, $centroId, $peso, $fechaIngreso);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $id;
    }

    public function existeCamion($camionId) {
        return $this->existe("SELECT 1 FROM camion WHERE id_camion = ?", "i", $camionId);
    }

    public function existeCentro($centroId) {
        return $this->existe("SELECT 1 FROM centro_acopio WHERE id_centro = ?", "i", $centroId);
    }

    // Id del camión con esa matrícula, o null si no existe.
    public function idCamionPorMatricula($matricula) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT id_camion FROM camion WHERE matricula = ?");
        mysqli_stmt_bind_param($stmt, "s", $matricula);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $fila ? (int) $fila['id_camion'] : null;
    }

    // Centro de acopio asignado al usuario (USUARIO.id_centro), o null si no tiene.
    public function centroDeUsuario($ci) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT id_centro FROM usuario WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, "i", $ci);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return isset($fila['id_centro']) ? (int) $fila['id_centro'] : null;
    }

    private function existe($sql, $types, $valor) {
        $stmt = mysqli_prepare($this->conn->getConexion(), $sql);
        mysqli_stmt_bind_param($stmt, $types, $valor);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $existe = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        return $existe;
    }

    public function obtenerPorCamion($camionId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camion_centro WHERE id_camion = ? ORDER BY fecha_ingreso DESC");
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
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camion_centro ORDER BY fecha_ingreso DESC");
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
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camion_centro WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function updateTrasladoResiduo($id, $camionId, $centroId, $peso, $fechaIngreso) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camion_centro SET id_camion = ?, id_centro = ?, peso = ?, fecha_ingreso = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "iidsi", $camionId, $centroId, $peso, $fechaIngreso, $id);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $affected > 0;
    }

    public function deleteTrasladoResiduo($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "DELETE FROM camion_centro WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function obtenerPorCentro($centroId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camion_centro WHERE id_centro = ? ORDER BY fecha_ingreso DESC");
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
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM camion_centro WHERE fecha_ingreso LIKE ? ORDER BY fecha_ingreso DESC");
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
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT SUM(peso) as total FROM camion_centro WHERE id_camion = ?");
        mysqli_stmt_bind_param($stmt, "i", $camionId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return floatval($row['total']);
    }
}
