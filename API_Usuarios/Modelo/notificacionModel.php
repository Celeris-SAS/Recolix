<?php
require_once __DIR__ . '/../../conexion.php';
require_once __DIR__ . '/../../extra/Comun/Mailer.php';

class NotificacionModel {
    private $conn;

    public function __construct() {
        $this->conn = (new conexion())->getConexion();
    }

    // Notificación dirigida a un usuario concreto (ej. solicitud de registro aprobada).
    public function crearParaUsuario($ci, $mensaje, $tipoOrigen, $prioridad) {
        $idNotificacion = $this->insertarNotificacion($mensaje, $tipoOrigen, $prioridad);
        $this->entregar($idNotificacion, (int) $ci, $this->emailDe($ci), $mensaje, $tipoOrigen);
        return $idNotificacion;
    }

    private function emailDe($ci) {
        $filas = $this->filas("SELECT email FROM USUARIO WHERE ci_usuario = ?", 'i', [$ci]);
        return $filas[0]['email'] ?? null;
    }

    private function filas($sql, $types, $params) {
        $stmt = mysqli_prepare($this->conn, $sql);
        if ($types !== '') {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    // Deja la notificación entregada en NOTIFICACION_USUARIO y, además, intenta mandar el mail
    // (Mailer::enviar no lanza ni bloquea la request si falla: ver Mailer.php).
    private function entregar($idNotificacion, $ci, $email, $mensaje, $tipoOrigen) {
        $stmt = mysqli_prepare($this->conn, "INSERT INTO NOTIFICACION_USUARIO (id_notificacion, ci_usuario) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, 'ii', $idNotificacion, $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        Mailer::enviar($email, $this->asunto($tipoOrigen), $mensaje);
    }

    private function asunto($tipoOrigen) {
        $etiquetas = [
            'solicitud_registro' => 'Solicitud de registro',
        ];
        return 'Recolix — ' . ($etiquetas[$tipoOrigen] ?? 'Notificación');
    }

    private function insertarNotificacion($mensaje, $tipoOrigen, $prioridad) {
        $stmt = mysqli_prepare($this->conn,
            "INSERT INTO NOTIFICACION (mensaje, tipo_origen, prioridad) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sss', $mensaje, $tipoOrigen, $prioridad);
        mysqli_stmt_execute($stmt);
        $idNotificacion = mysqli_insert_id($this->conn);
        mysqli_stmt_close($stmt);
        return $idNotificacion;
    }
}
