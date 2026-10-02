<?php

require_once __DIR__ . '/../../conexion.php';

class CentroModel {

    private $conn;

    public function __construct() {
        $this->conn = new conexion();
    }

    public function getAllCentros() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM centro_acopio ORDER BY nombre ASC");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $centros = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $centros[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $centros;
    }

    public function getCentro($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM centro_acopio WHERE id_centro = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    // Sitios de disposición final que una cuadrilla puede elegir como destino al
    // planificar un levante (deben estar operativos para poder recibir carga).
    public function filtrarOperativos() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM centro_acopio WHERE estado = 'operativo' ORDER BY nombre ASC");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $centros = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $centros[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $centros;
    }
}
