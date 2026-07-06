<?php

require_once __DIR__ . '/conexion.php';

class prediccionLlenadoModel {
    protected $conn;

    public function __construct() {
    $this->conn = new conexion();
}

    // ==========================================
    // GETTERS
    // ==========================================

    public function getId() {
        return $this->id;
    }

    public function getIdContenedor() {
        return $this->idContenedor;
    }

    public function getNivelProyectado() {
        return $this->nivelProyectado;
    }

    public function getFechaCalculo() {
        return $this->fechaCalculo;
    }

    // ==========================================
    // SETTERS
    // ==========================================
    public function setId($id) {
    $this->id = $id;
}

    public function setIdContenedor($idContenedor) {
        $this->idContenedor = $idContenedor;
    }

    public function setNivelProyectado($nivelProyectado) {
        $this->nivelProyectado = $nivelProyectado;
    }

    public function setFechaCalculo($fechaCalculo) {
        $this->fechaCalculo = $fechaCalculo;
    }

    // ==========================================
    // CRUD PRINCIPAL
    // ==========================================

    public function insertPrediccion($idContenedor, $nivelProyectado) {
        $fechaCalculo = date("Y-m-d H:i:s");
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO PREDICCION_LLENADO (id_contenedor, nivel_proyectado, fecha_calculo) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iis", $idContenedor, $nivelProyectado, $fechaCalculo);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $id;
    }

    public function getPrediccion($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM PREDICCION_LLENADO WHERE id_prediccion = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function getUltimaPrediccion($idContenedor) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM PREDICCION_LLENADO WHERE id_contenedor = ? ORDER BY fecha_calculo DESC LIMIT 1");
        mysqli_stmt_bind_param($stmt, "i", $idContenedor);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function getAllPredicciones($idContenedor) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM PREDICCION_LLENADO WHERE id_contenedor = ? ORDER BY fecha_calculo DESC");
        mysqli_stmt_bind_param($stmt, "i", $idContenedor);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows   = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function getDatosLevantes($idContenedor) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT cl.peso, l.hora_levante FROM CONTENEDOR_LEVANTE cl JOIN LEVANTE l ON cl.id_levante = l.id_levante WHERE cl.id_contenedor = ? ORDER BY l.hora_levante DESC");
        mysqli_stmt_bind_param($stmt, "i", $idContenedor);
        mysqli_stmt_execute($stmt);
        $result   = mysqli_stmt_get_result($stmt);
        $rows     = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }
}
?>
