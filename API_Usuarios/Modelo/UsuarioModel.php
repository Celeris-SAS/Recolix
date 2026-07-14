<?php

require_once __DIR__ . '/../../conexion.php';

class UsuarioModel {

    private $conn;

    public function __construct() {
        $conexion = new conexion();
        $this->conn = $conexion->getConexion();
    }

    // ==========================================
    // AUTENTICACIÓN (RF81)
    // ==========================================

    // Busca al usuario por CI y devuelve sus datos si la contraseña es correcta.
    // Nunca devuelve el hash de la contraseña al controller.
    public function login($ci, $contrasenia) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT ci_usuario, primer_nombre, p_apellido, email, tel, rol, acceso, fecha_registro, contrasenia
             FROM USUARIO WHERE ci_usuario = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $ci);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$row) return null;

        // RNF05 — verificar el hash almacenado.
        if (!password_verify($contrasenia, $row['contrasenia'])) return null;

        unset($row['contrasenia']);
        return $row;
    }

    // ==========================================
    // PERFIL (RF90, RF91)
    // ==========================================

    // Devuelve los datos del usuario. Nunca devuelve la contraseña (RF90).
    // Incluye acceso/fecha_registro para su uso también en el panel de administración.
    public function getPerfil($ci) {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT ci_usuario, primer_nombre, p_apellido, email, tel, rol, acceso, fecha_registro
             FROM USUARIO WHERE ci_usuario = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $ci);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    // RF91 — Modifica email y teléfono del perfil propio.
    public function updatePerfil($ci, $email, $tel) {
        $stmt = mysqli_prepare(
            $this->conn,
            "UPDATE USUARIO SET email = ?, tel = ? WHERE ci_usuario = ?"
        );
        mysqli_stmt_bind_param($stmt, 'ssi', $email, $tel, $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    // RF92 — Cambia la contraseña. Recibe la nueva contraseña ya validada por el controller.
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

    // Devuelve el hash de contraseña para que el controller pueda verificarlo (RF92).
    // Solo se usa para validar la contraseña actual antes de cambiarla.
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

    // ==========================================
    // INCIDENCIAS PROPIAS (RF03)
    // ==========================================

    // Devuelve las incidencias reportadas por este vecino.
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

    // ==========================================
    // NOTIFICACIONES (RF95, RF96)
    // ==========================================

    // RF95 — Notificaciones del vecino, ordenadas por fecha desc y prioridad.
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

    // RF96 — Marca una notificación como leída, registrando la fecha de lectura.
    public function marcarNotificacionLeida($idNotificacion) {
        $ahora = date('Y-m-d H:i:s');
        $stmt  = mysqli_prepare(
            $this->conn,
            "UPDATE NOTIFICACION SET estado = 'leida', fecha_lectura = ? WHERE id_notificacion = ?"
        );
        mysqli_stmt_bind_param($stmt, 'si', $ahora, $idNotificacion);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    // ==========================================
    // ADMINISTRACIÓN DE USUARIOS
    // ==========================================

    // Listado general de usuarios (sin contraseña) para el panel de administración.
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
            "SELECT ci_usuario, primer_nombre, p_apellido, email, tel, rol, acceso, fecha_registro
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

    // Habilita/deshabilita o revierte a pendiente el acceso de un usuario.
    public function cambiarAcceso($ci, $nuevoAcceso) {
        $stmt = mysqli_prepare($this->conn, "UPDATE USUARIO SET acceso = ? WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, 'si', $nuevoAcceso, $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    // Verifica si ya existe un usuario con esa cédula.
    public function existeCi($ci) {
        $stmt = mysqli_prepare($this->conn, "SELECT ci_usuario FROM USUARIO WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, 'i', $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $existe = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        return $existe;
    }

    // Alta directa de un usuario por parte de un administrador (sin pasar por el flujo de solicitud).
    // Queda con acceso 'habilitado' de inmediato, ya que el admin la está dando de alta él mismo.
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
}
?>