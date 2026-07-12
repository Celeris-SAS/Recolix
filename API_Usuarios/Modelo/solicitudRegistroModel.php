<?php

require_once __DIR__ . '/../../conexion.php';

class SolicitudRegistroModel {

    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->getConexion();
    }

    // ==========================================
    // RF02 — Solicitud de registro de vecino
    // ==========================================

    // Inserta el vecino en USUARIO con acceso = 'pendiente' y estado = 'habilitado'.
    // No recibe contraseña: el administrador la asigna al aprobar la solicitud (RF84).
    // RNF05 — se almacena un placeholder hasheado; el admin la reemplaza al activar la cuenta.
    public function crearSolicitud($ci, $primerNombre, $pApellido, $email, $tel) {
        // Verificar que no exista ya un registro con esa CI.
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
        $estado = 'habilitado';
        $rol    = 'vecino';
        // Contraseña temporal: el administrador la reemplaza al aprobar (RF84).
        $contraseniaTemp = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

        $stmt = mysqli_prepare(
            $this->conn,
            "INSERT INTO USUARIO (ci_usuario, primer_nombre, p_apellido, email, tel, contrasenia, rol, acceso, estado, fecha_registro)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        mysqli_stmt_bind_param(
            $stmt,
            'issssssss',
            $ci, $primerNombre, $pApellido, $email, $tel,
            $contraseniaTemp, $rol, $acceso, $estado
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $ci;
    }

    // Verifica si ya existe una solicitud con esa CI (para validación en el controller).
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
        "UPDATE USUARIO SET acceso = 'aprobado' WHERE ci_usuario = ?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $ci);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return true;
}

public function rechazar($ci) {
    $stmt = mysqli_prepare($this->conn,
        "UPDATE USUARIO SET acceso = 'rechazado' WHERE ci_usuario = ?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $ci);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return true;
}
}
?>