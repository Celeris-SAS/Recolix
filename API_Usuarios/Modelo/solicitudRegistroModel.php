<?php

require_once __DIR__ . '/../../conexion.php';

class SolicitudRegistroModel {

    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConexion();
    }

    public function crearSolicitud($ci, $primerNombre, $pApellido, $email, $tel, $idMunicipio, $contrasenia) {
        $check = mysqli_prepare($this->conn, "SELECT ci_usuario FROM USUARIO WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($check, 'i', $ci);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);
        $existe = mysqli_stmt_num_rows($check) > 0;
        mysqli_stmt_close($check);

        if ($existe) {
            throw new Exception("Ya existe una solicitud registrada con la cédula $ci.");
        }

        $acceso = 'pendiente';
        $rol    = 'vecino';
        $hash   = password_hash($contrasenia, PASSWORD_DEFAULT);

        $stmt = mysqli_prepare(
            $this->conn,
            "INSERT INTO USUARIO (ci_usuario, id_municipio, primer_nombre, p_apellido, email, tel, contrasenia, rol, acceso, fecha_registro)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        mysqli_stmt_bind_param(
            $stmt,
            'iisssssss',
            $ci, $idMunicipio, $primerNombre, $pApellido, $email, $tel,
            $hash, $rol, $acceso
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ci;
    }


    public function existePorCI($ci) {
        $stmt = mysqli_prepare($this->conn, "SELECT ci_usuario FROM USUARIO WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, 'i', $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $existe = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        return $existe;
    }
    public function getSolicitudesPendientes() {
    $stmt = mysqli_prepare($this->conn,
        "SELECT ci_usuario, primer_nombre, p_apellido, email, tel
         FROM USUARIO WHERE acceso = 'pendiente'"
    );
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $rows;
}

public function aprobar($ci) {
    $stmt = mysqli_prepare($this->conn,
        "UPDATE USUARIO SET acceso = 'habilitado' WHERE ci_usuario = ?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $ci);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return true;
}

public function rechazar($ci) {
    $stmt = mysqli_prepare($this->conn,
        "UPDATE USUARIO SET acceso = 'deshabilitado' WHERE ci_usuario = ?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $ci);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return true;
}
}
?>