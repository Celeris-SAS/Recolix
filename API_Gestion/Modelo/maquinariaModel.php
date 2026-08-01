<?php
require_once __DIR__ . '/../../conexion.php';

class MaquinariaModel {
    private $conn;

    public function __construct() {
        $this->conn = new conexion();
    }

    // ==========================================
    // CRUD PRINCIPAL
    // ==========================================

    
    public function insertMaquinaria($idCentro, $nombre, $tipo, $fechaAdquisicion, $estado) {
        $conexion = $this->conn->getConexion();

        $stmt = mysqli_prepare($conexion, "INSERT INTO MAQUINARIA (nombre, tipo, fecha_adquisicion, estado) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $nombre, $tipo, $fechaAdquisicion, $estado);
        mysqli_stmt_execute($stmt);
        $idMaquinaria = mysqli_insert_id($conexion);
        mysqli_stmt_close($stmt);

        $stmt2 = mysqli_prepare($conexion, "INSERT INTO CENTRO_MAQUINARIA (id_centro, id_maquinaria) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt2, "ii", $idCentro, $idMaquinaria);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);

        return $idMaquinaria;
    }

    
    public function updateMaquinaria($id, $nombre, $tipo, $fechaAdquisicion, $estado) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "UPDATE MAQUINARIA SET nombre = ?, tipo = ?, fecha_adquisicion = ?, estado = ? WHERE id_maquinaria = ?");
        mysqli_stmt_bind_param($stmt, "ssssi", $nombre, $tipo, $fechaAdquisicion, $estado, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    
    public function cambiarEstado($id, $nuevoEstado) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE MAQUINARIA SET estado = ? WHERE id_maquinaria = ?");
        mysqli_stmt_bind_param($stmt, "si", $nuevoEstado, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    
    public function bajaMaquinaria($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE MAQUINARIA SET estado = 'fuera_servicio' WHERE id_maquinaria = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    
    public function getMaquinaria($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT m.id_maquinaria, m.nombre, m.tipo, m.fecha_adquisicion, m.estado, cm.id_centro
             FROM MAQUINARIA m
             JOIN CENTRO_MAQUINARIA cm ON m.id_maquinaria = cm.id_maquinaria
             WHERE m.id_maquinaria = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function getAllMaquinarias() {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT m.id_maquinaria, m.nombre, m.tipo, m.fecha_adquisicion, m.estado, cm.id_centro
             FROM MAQUINARIA m
             JOIN CENTRO_MAQUINARIA cm ON m.id_maquinaria = cm.id_maquinaria");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    // ==========================================
    // CONSULTA POR CENTRO Y FILTROS
    // ==========================================

    
    public function obtenerPorCentro($idCentro) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT m.id_maquinaria, m.nombre, m.tipo, m.fecha_adquisicion, m.estado
             FROM MAQUINARIA m
             JOIN CENTRO_MAQUINARIA cm ON m.id_maquinaria = cm.id_maquinaria
             WHERE cm.id_centro = ?");
        mysqli_stmt_bind_param($stmt, "i", $idCentro);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    
    public function filtrarPorTipo($tipo) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM MAQUINARIA WHERE tipo = ?");
        mysqli_stmt_bind_param($stmt, "s", $tipo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function filtrarPorEstado($estado) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM MAQUINARIA WHERE estado = ?");
        mysqli_stmt_bind_param($stmt, "s", $estado);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }
}
?>
