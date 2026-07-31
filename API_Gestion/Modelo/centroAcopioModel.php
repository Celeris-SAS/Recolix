<?php
require_once __DIR__ . '/../../conexion.php';

class CentroAcopioModel {
    private $conn;

    public function __construct() {
        $this->conn = new conexion();
    }

    public function getCentro($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT ca.*, m.nombre AS municipio
             FROM CENTRO_ACOPIO ca
             LEFT JOIN MUNICIPIO m ON ca.id_municipio = m.id_municipio
             WHERE ca.id_centro = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function getAllCentros() {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT ca.*, m.nombre AS municipio
             FROM CENTRO_ACOPIO ca
             LEFT JOIN MUNICIPIO m ON ca.id_municipio = m.id_municipio
             ORDER BY ca.nombre");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows   = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }
}
