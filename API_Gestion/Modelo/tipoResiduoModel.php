<?php

require_once 'conexion.php';

class tipoResiduoModel {

    protected $id;
    protected $color;
    protected $descripcion;
    protected $reciclable;
    protected $conn;

    public function __construct($id,$color,$descripcion,$reciclable) {
        $this->id          = $id;
        $this->color       = $color;
        $this->descripcion = $descripcion;
        $this->reciclable  = $reciclable;
        $this->conn        = new conexion();
    }

    // ==========================================
    // GETTERS
    // ==========================================

    public function getId() {
        return $this->id;
    }

    public function getColor() {
        return $this->color;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getReciclable() {
        return $this->reciclable;
    }

    // ==========================================
    // SETTERS
    // ==========================================

    public function setColor($color) {
        $this->color = $color;
    }

    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function setReciclable($reciclable) {
        $this->reciclable = $reciclable;
    }

    // ==========================================
    // CRUD PRINCIPAL
    // ==========================================

    public function insertTipoResiduo($color, $descripcion, $reciclable) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO TIPO_RESIDUO (color, descripcion, reciclable) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssi", $color, $descripcion, $reciclable);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $id;
    }

    public function updateTipoResiduo($id, $color, $descripcion, $reciclable) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE TIPO_RESIDUO SET color = ?, descripcion = ?, reciclable = ? WHERE id_tipo = ?");
        mysqli_stmt_bind_param($stmt, "ssii", $color, $descripcion, $reciclable, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function bajaTipoResiduo($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "DELETE FROM TIPO_RESIDUO WHERE id_tipo = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $afectadas = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $afectadas > 0;
    }

    public function getTipoResiduo($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM TIPO_RESIDUO WHERE id_tipo = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function getAllTiposResiduo() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM TIPO_RESIDUO");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows   = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    // ==========================================
    // FILTROS
    // ==========================================

    public function filtrarReciclable($reciclable) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM TIPO_RESIDUO WHERE reciclable = ?");
        mysqli_stmt_bind_param($stmt, "i", $reciclable);
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
?>
