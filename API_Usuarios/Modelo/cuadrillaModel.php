<?php

require_once __DIR__ . '/../../conexion.php';

class CuadrillaModel
{

    private $conn;

    public function __construct()
    {
        $this->conn = new conexion();
    }
    public function esDisponible($id)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT estado FROM cuadrilla WHERE id_cuadrilla = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (!$row)
            return false;
        if ($row['estado'] !== 'activa')
            return false;
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT COUNT(*) as cnt FROM levante WHERE id_cuadrilla = ? AND estado IN ('pendiente', 'en_curso')");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $c = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        return intval($c['cnt']) === 0;
    }

    public function existeMunicipio($idMunicipio)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT COUNT(*) as cnt FROM municipio WHERE id_municipio = ?");
        mysqli_stmt_bind_param($stmt, "i", $idMunicipio);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return intval($row['cnt']) > 0;
    }

    public function insertCuadrilla($idMunicipio, $estado)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO cuadrilla (id_municipio, estado) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "is", $idMunicipio, $estado);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $id;
    }

    public function bajaCuadrilla($id)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE cuadrilla SET estado = 'inactiva' WHERE id_cuadrilla = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function updateCuadrilla($id, $idMunicipio, $estado)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE cuadrilla SET id_municipio = ?, estado = ? WHERE id_cuadrilla = ?");
        mysqli_stmt_bind_param($stmt, "isi", $idMunicipio, $estado, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function getAllCuadrillas()
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM cuadrilla");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cuadrillas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $cuadrillas[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $cuadrillas;
    }

    public function getCuadrilla($id)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM cuadrilla WHERE id_cuadrilla = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function filtrarEstado($estado)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM cuadrilla WHERE estado = ?");
        mysqli_stmt_bind_param($stmt, "s", $estado);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cuadrillas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $cuadrillas[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $cuadrillas;
    }

    public function filtrarMunicipio($idMunicipio)
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM cuadrilla WHERE id_municipio = ?");
        mysqli_stmt_bind_param($stmt, "i", $idMunicipio);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cuadrillas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $cuadrillas[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $cuadrillas;
    }

    public function filtrarDisponibilidad()
    {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM cuadrilla WHERE estado = 'activa'");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cuadrillas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $cuadrillas[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $cuadrillas;
    }

public function obtenerHistorial($id) {
        $historial = [];

        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 'levante' AS tipo_evento, estado AS descripcion, hora_levante AS fecha FROM levante WHERE id_cuadrilla = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $historial[] = $row;
        }
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 'incidencia' AS tipo_evento, CONCAT(tipo, ': ', estado) AS descripcion, fecha_apertura AS fecha FROM incidencia WHERE id_cuadrilla = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            $historial[] = $row;
        }
        mysqli_stmt_close($stmt);

        return $historial;
    }

}