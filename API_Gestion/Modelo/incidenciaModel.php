<?php
require_once __DIR__ . '/../../conexion.php';

class IncidenciaModel {
    private $conn;

    public function __construct() {
        $this->conn = new conexion();
    }

    // RF03/RF04: registro de una incidencia por un usuario autenticado.
    // RF07: url_imagen queda null cuando el reporte no adjunta foto.
    public function crearIncidencia($ciUsuario, $tipo, $descripcion, $urlImagen = null) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "INSERT INTO INCIDENCIA (ci_usuario, tipo, descripcion, url_imagen) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isss", $ciUsuario, $tipo, $descripcion, $urlImagen);
        mysqli_stmt_execute($stmt);
        $idIncidencia = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $idIncidencia;
    }

    public function vincularContenedor($idIncidencia, $idContenedor) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "INSERT INTO INCIDENCIA_CONTENEDOR (id_incidencia, id_contenedor) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ii", $idIncidencia, $idContenedor);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function usuarioExiste($ciUsuario) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 1 FROM USUARIO WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, "i", $ciUsuario);
        mysqli_stmt_execute($stmt);
        $existe = mysqli_stmt_get_result($stmt)->fetch_assoc() !== null;
        mysqli_stmt_close($stmt);
        return $existe;
    }

    public function getAllIncidencias() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM INCIDENCIA ORDER BY fecha_apertura DESC");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function getIncidencia($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM INCIDENCIA WHERE id_incidencia = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function filtrarEstado($estado) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM INCIDENCIA WHERE estado = ? ORDER BY fecha_apertura DESC");
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

    public function filtrarTipo($tipo) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM INCIDENCIA WHERE tipo = ? ORDER BY fecha_apertura DESC");
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

    public function filtrarPrioridad($prioridad) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM INCIDENCIA WHERE prioridad = ? ORDER BY fecha_apertura DESC");
        mysqli_stmt_bind_param($stmt, "s", $prioridad);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    // RF11/RF12: contenedores con incidencia urgente, priorizando desbordado y luego nivel_llenado.
    public function listarUrgentes() {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT DISTINCT i.*, c.id_contenedor, c.estado AS estado_contenedor, c.nivel_llenado
             FROM INCIDENCIA i
             JOIN INCIDENCIA_CONTENEDOR ic ON i.id_incidencia = ic.id_incidencia
             JOIN CONTENEDOR c ON ic.id_contenedor = c.id_contenedor
             WHERE i.urgencia = 1 AND i.estado != 'resuelta'
             ORDER BY (c.estado = 'desbordado' OR c.nivel_llenado > 90) DESC, c.nivel_llenado DESC, i.fecha_apertura ASC");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function getContenedoresVinculados($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT c.* FROM CONTENEDOR c
             JOIN INCIDENCIA_CONTENEDOR ic ON c.id_contenedor = ic.id_contenedor
             WHERE ic.id_incidencia = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function asignarCuadrilla($id, $idCuadrilla) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE INCIDENCIA SET id_cuadrilla = ?, estado = 'asignada' WHERE id_incidencia = ?");
        mysqli_stmt_bind_param($stmt, "ii", $idCuadrilla, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function cerrar($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE INCIDENCIA SET estado = 'resuelta', fecha_cierre = NOW() WHERE id_incidencia = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function cuadrillaExiste($idCuadrilla) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 1 FROM CUADRILLA WHERE id_cuadrilla = ?");
        mysqli_stmt_bind_param($stmt, "i", $idCuadrilla);
        mysqli_stmt_execute($stmt);
        $existe = mysqli_stmt_get_result($stmt)->fetch_assoc() !== null;
        mysqli_stmt_close($stmt);
        return $existe;
    }
}
?>
