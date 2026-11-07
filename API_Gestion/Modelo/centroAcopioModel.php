<?php
require_once __DIR__ . '/../../conexion.php';

class CentroAcopioModel {
    private $conn;

    public function __construct() {
        $this->conn = new conexion();
    }


    public function insertCentro($idMunicipio, $nombre, $tipo, $calle, $numCalle, $latitud, $longitud, $capacidadTotal, $estado) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "INSERT INTO CENTRO_ACOPIO (id_municipio, nombre, tipo, calle, num_calle, latitud, longitud, capacidad_total, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isssiddis", $idMunicipio, $nombre, $tipo, $calle, $numCalle, $latitud, $longitud, $capacidadTotal, $estado);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $id;
    }

    public function updateCentro($id, $idMunicipio, $nombre, $tipo, $calle, $numCalle, $latitud, $longitud, $capacidadTotal, $estado) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "UPDATE CENTRO_ACOPIO SET id_municipio = ?, nombre = ?, tipo = ?, calle = ?, num_calle = ?, latitud = ?, longitud = ?, capacidad_total = ?, estado = ? WHERE id_centro = ?");
        mysqli_stmt_bind_param($stmt, "isssiddisi", $idMunicipio, $nombre, $tipo, $calle, $numCalle, $latitud, $longitud, $capacidadTotal, $estado, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function bajaCentro($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE CENTRO_ACOPIO SET estado = 'cerrado' WHERE id_centro = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function centroDeUsuario($ci) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT id_centro FROM USUARIO WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, "i", $ci);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $fila['id_centro'] ?? null;
    }

    public function getCentro($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM CENTRO_ACOPIO WHERE id_centro = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function getAllCentros() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM CENTRO_ACOPIO");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }


    public function filtrarMunicipio($idMunicipio) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM CENTRO_ACOPIO WHERE id_municipio = ?");
        mysqli_stmt_bind_param($stmt, "i", $idMunicipio);
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
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM CENTRO_ACOPIO WHERE tipo = ?");
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

    public function filtrarEstado($estado) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM CENTRO_ACOPIO WHERE estado = ?");
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


    public function getOcupacion($idCentro) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT SUM(ocupacion_actual) AS ocupado, SUM(capacidad) AS capacidad FROM CENTRO_RESIDUO WHERE id_centro = ?");
        mysqli_stmt_bind_param($stmt, "i", $idCentro);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function asignarTipoResiduo($idCentro, $idResiduo, $capacidad) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "INSERT INTO CENTRO_RESIDUO (id_centro, id_residuo, ocupacion_actual, capacidad) VALUES (?, ?, 0, ?)
             ON DUPLICATE KEY UPDATE capacidad = VALUES(capacidad)");
        mysqli_stmt_bind_param($stmt, "iii", $idCentro, $idResiduo, $capacidad);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function getResiduosPorCentro($idCentro) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT cr.id_residuo, tr.color, tr.descripcion, cr.capacidad, cr.ocupacion_actual
             FROM CENTRO_RESIDUO cr
             JOIN TIPO_RESIDUO tr ON cr.id_residuo = tr.id_tipo
             WHERE cr.id_centro = ?");
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

    public function getCentroResiduo($idCentro, $idResiduo) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT id_centro, id_residuo, capacidad, ocupacion_actual FROM CENTRO_RESIDUO WHERE id_centro = ? AND id_residuo = ?");
        mysqli_stmt_bind_param($stmt, "ii", $idCentro, $idResiduo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function actualizarOcupacion($idCentro, $idResiduo, $ocupacionActual) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "UPDATE CENTRO_RESIDUO SET ocupacion_actual = ? WHERE id_centro = ? AND id_residuo = ?");
        mysqli_stmt_bind_param($stmt, "iii", $ocupacionActual, $idCentro, $idResiduo);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }


    public function municipioExiste($idMunicipio) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 1 FROM MUNICIPIO WHERE id_municipio = ?");
        mysqli_stmt_bind_param($stmt, "i", $idMunicipio);
        mysqli_stmt_execute($stmt);
        $existe = mysqli_stmt_get_result($stmt)->fetch_assoc() !== null;
        mysqli_stmt_close($stmt);
        return $existe;
    }

    public function tipoResiduoExiste($idTipo) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 1 FROM TIPO_RESIDUO WHERE id_tipo = ?");
        mysqli_stmt_bind_param($stmt, "i", $idTipo);
        mysqli_stmt_execute($stmt);
        $existe = mysqli_stmt_get_result($stmt)->fetch_assoc() !== null;
        mysqli_stmt_close($stmt);
        return $existe;
    }
}
?>
