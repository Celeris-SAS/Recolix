<?php

require_once __DIR__ . '/../../conexion.php';

class UsuarioModel {

    private $conn;

    public function __construct() {
        $conexion = new conexion();
        $this->conn = $conexion->getConexion();
    }


    public function login($ci, $contrasenia) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT ci_usuario, id_cuadrilla, primer_nombre, p_apellido, email, tel, rol, acceso, fecha_registro, contrasenia
             FROM USUARIO WHERE ci_usuario = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $ci);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$row) return null;

        if (!password_verify($contrasenia, $row['contrasenia'])) return null;

        unset($row['contrasenia']);
        return $row;
    }


    public function getPerfil($ci) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT u.ci_usuario, u.primer_nombre, u.p_apellido, u.email, u.tel, u.rol, u.acceso, u.fecha_registro,
                    u.id_municipio, m.nombre AS municipio
             FROM USUARIO u
             LEFT JOIN MUNICIPIO m ON m.id_municipio = u.id_municipio
             WHERE u.ci_usuario = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $ci);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }


    public function updatePerfil($ci, $email, $tel, $primerNombre, $pApellido) {
        $stmt = mysqli_prepare(
            $this->conn,
            "UPDATE USUARIO SET email = ?, tel = ?, primer_nombre = ?, p_apellido = ? WHERE ci_usuario = ?"
        );
        mysqli_stmt_bind_param($stmt, 'ssssi', $email, $tel, $primerNombre, $pApellido, $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    // Las incidencias del usuario quedan sin autor (FK ON DELETE SET NULL);
    // notificaciones y asignaciones se borran en cascada.
    public function eliminarUsuario($ci) {
        $stmt = mysqli_prepare($this->conn, "DELETE FROM USUARIO WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, 'i', $ci);
        mysqli_stmt_execute($stmt);
        $borrados = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $borrados > 0;
    }

    public function existeEmail($email) {
        $stmt = mysqli_prepare($this->conn, "SELECT ci_usuario FROM USUARIO WHERE LOWER(email) = LOWER(?)");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $existe = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        return $existe;
    }

    public function existeTel($tel) {
        $stmt = mysqli_prepare($this->conn, "SELECT ci_usuario FROM USUARIO WHERE tel = ?");
        mysqli_stmt_bind_param($stmt, 's', $tel);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $existe = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        return $existe;
    }


    public function cambiarContrasenia($ci, $nuevaContrasenia) {
        $hash = password_hash($nuevaContrasenia, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare(
            $this->conn,
            "UPDATE USUARIO SET contrasenia = ? WHERE ci_usuario = ?"
        );
        mysqli_stmt_bind_param($stmt, 'si', $hash, $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }


    public function getContrasenia($ci) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT contrasenia FROM USUARIO WHERE ci_usuario = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $ci);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ? $row['contrasenia'] : null;
    }



    public function getIncidencias($ci) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT i.id_incidencia, i.tipo, i.descripcion, i.estado, i.urgencia,
                    i.url_imagen, i.fecha_apertura, i.fecha_cierre
             FROM INCIDENCIA i
             WHERE i.ci_usuario = ?
             ORDER BY i.fecha_apertura DESC"
        );
        mysqli_stmt_bind_param($stmt, 'i', $ci);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows   = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }


    public function getNotificaciones($ci) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT n.id_notificacion, n.mensaje, n.tipo_origen, n.prioridad,
                    n.estado, n.fecha_creacion, n.fecha_lectura
             FROM NOTIFICACION n
             JOIN NOTIFICACION_USUARIO nu ON n.id_notificacion = nu.id_notificacion
             WHERE nu.ci_usuario = ?
             ORDER BY n.fecha_creacion DESC,
                      FIELD(n.prioridad, 'critica', 'advertencia', 'informativa')"
        );
        mysqli_stmt_bind_param($stmt, 'i', $ci);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows   = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }


    public function marcarNotificacionLeida($idNotificacion, $ci) {
        $ahora = date('Y-m-d H:i:s');
        $stmt  = mysqli_prepare(
            $this->conn,
            "UPDATE NOTIFICACION n
             JOIN NOTIFICACION_USUARIO nu ON n.id_notificacion = nu.id_notificacion
             SET n.estado = 'leida', n.fecha_lectura = ?
             WHERE n.id_notificacion = ? AND nu.ci_usuario = ?"
        );
        mysqli_stmt_bind_param($stmt, 'sii', $ahora, $idNotificacion, $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    // Borra la notificación solo para este usuario: saca su fila de NOTIFICACION_USUARIO,
    // no la de NOTIFICACION (que puede estar compartida con otros destinatarios).
    public function borrarNotificacion($idNotificacion, $ci) {
        $stmt = mysqli_prepare(
            $this->conn,
            "DELETE FROM NOTIFICACION_USUARIO WHERE id_notificacion = ? AND ci_usuario = ?"
        );
        mysqli_stmt_bind_param($stmt, 'ii', $idNotificacion, $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }


    public function listarUsuarios() {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT ci_usuario, primer_nombre, p_apellido, email, tel, rol, acceso, fecha_registro
             FROM USUARIO ORDER BY fecha_registro DESC"
        );
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows   = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function filtrarRol($rol) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT ci_usuario, id_cuadrilla, id_centro, primer_nombre, p_apellido, email, tel, rol, acceso, fecha_registro
             FROM USUARIO WHERE rol = ? ORDER BY fecha_registro DESC"
        );
        mysqli_stmt_bind_param($stmt, 's', $rol);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows   = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function filtrarAcceso($acceso) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT ci_usuario, primer_nombre, p_apellido, email, tel, rol, acceso, fecha_registro
             FROM USUARIO WHERE acceso = ? ORDER BY fecha_registro DESC"
        );
        mysqli_stmt_bind_param($stmt, 's', $acceso);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows   = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function cambiarRol($ci, $nuevoRol) {
        $stmt = mysqli_prepare($this->conn, "UPDATE USUARIO SET rol = ? WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, 'si', $nuevoRol, $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function cambiarAcceso($ci, $nuevoAcceso) {
        $stmt = mysqli_prepare($this->conn, "UPDATE USUARIO SET acceso = ? WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, 'si', $nuevoAcceso, $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function existeCi($ci) {
        $stmt = mysqli_prepare($this->conn, "SELECT ci_usuario FROM USUARIO WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, 'i', $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $existe = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        return $existe;
    }


    public function crearUsuario($ci, $primerNombre, $pApellido, $email, $tel, $contrasenia, $rol, $idMunicipio) {
        $hash   = password_hash($contrasenia, PASSWORD_DEFAULT);
        $acceso = 'habilitado';
        $stmt = mysqli_prepare(
            $this->conn,
            "INSERT INTO USUARIO (ci_usuario, id_municipio, primer_nombre, p_apellido, email, tel, contrasenia, rol, acceso, fecha_registro)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        mysqli_stmt_bind_param(
            $stmt,
            'iisssssss',
            $ci, $idMunicipio, $primerNombre, $pApellido, $email, $tel, $hash, $rol, $acceso
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ci;
    }
    // Usuarios con rol 'cuadrilla' asignados a una cuadrilla puntual (USUARIO.id_cuadrilla, FK real).
    public function obtenerPorCuadrilla($idCuadrilla) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT ci_usuario, primer_nombre, p_apellido, email, acceso
             FROM USUARIO WHERE id_cuadrilla = ? AND rol = 'cuadrilla' ORDER BY primer_nombre ASC"
        );
        mysqli_stmt_bind_param($stmt, 'i', $idCuadrilla);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function asignarCuadrilla($ci, $idCuadrilla) {
        $stmt = mysqli_prepare($this->conn, "UPDATE USUARIO SET id_cuadrilla = ? WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $idCuadrilla, $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function quitarCuadrilla($ci) {
        $stmt = mysqli_prepare($this->conn, "UPDATE USUARIO SET id_cuadrilla = NULL WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, 'i', $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    // Usuarios con rol 'operario' asignados a un centro de acopio puntual (USUARIO.id_centro, FK real).
    public function obtenerPorCentro($idCentro) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT ci_usuario, primer_nombre, p_apellido, email, acceso
             FROM USUARIO WHERE id_centro = ? AND rol = 'operario' ORDER BY primer_nombre ASC"
        );
        mysqli_stmt_bind_param($stmt, 'i', $idCentro);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function asignarCentro($ci, $idCentro) {
        $stmt = mysqli_prepare($this->conn, "UPDATE USUARIO SET id_centro = ? WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, 'ii', $idCentro, $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function quitarCentro($ci) {
        $stmt = mysqli_prepare($this->conn, "UPDATE USUARIO SET id_centro = NULL WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, 'i', $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function setMunicipio($ci, $municipio) {

    $municipioSql = "SELECT id_municipio FROM MUNICIPIO WHERE nombre = ?";
    $stmt = mysqli_prepare($this->conn, $municipioSql);
    mysqli_stmt_bind_param($stmt, "s", $municipio);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row) {
        return false; 
    }
    $id_municipio = $row['id_municipio'];


    $updateSql = "UPDATE USUARIO SET id_municipio = ? WHERE ci_usuario = ?";
    $stmt2 = mysqli_prepare($this->conn, $updateSql);
    mysqli_stmt_bind_param($stmt2, "ii", $id_municipio, $ci);
    $ok = mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);
    return $ok;
    }
}
?>