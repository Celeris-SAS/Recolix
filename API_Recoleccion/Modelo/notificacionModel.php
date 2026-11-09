<?php
require_once __DIR__ . '/../../conexion.php';
require_once __DIR__ . '/../../extra/Comun/Mailer.php';
require_once __DIR__ . '/../../extra/Comun/Etiquetas.php';

class NotificacionModel {
    private $conn;

    private $textosEstadoIncidencia = [
        'asignada' => 'fue asignada a una cuadrilla',
        'en_curso' => 'está siendo atendida',
        'resuelta' => 'fue resuelta',
    ];

    public function __construct() {
        $this->conn = (new conexion())->getConexion();
    }

    public function crearParaAdministradores($mensaje, $tipoOrigen, $prioridad) {
        $idNotificacion = $this->insertarNotificacion($mensaje, $tipoOrigen, $prioridad);
        foreach ($this->administradoresHabilitados() as $destinatario) {
            $this->entregar($idNotificacion, (int) $destinatario['ci_usuario'], $destinatario['email'], $mensaje, $tipoOrigen);
        }
        return $idNotificacion;
    }

    public function crearParaUsuario($ci, $mensaje, $tipoOrigen, $prioridad) {
        $idNotificacion = $this->insertarNotificacion($mensaje, $tipoOrigen, $prioridad);
        $this->entregar($idNotificacion, (int) $ci, $this->emailDe($ci), $mensaje, $tipoOrigen);
        return $idNotificacion;
    }

    public function crearParaCuadrilla($idCuadrilla, $mensaje, $tipoOrigen, $prioridad) {
        $idNotificacion = $this->insertarNotificacion($mensaje, $tipoOrigen, $prioridad);
        foreach ($this->cuadrillaHabilitada($idCuadrilla) as $destinatario) {
            $this->entregar($idNotificacion, (int) $destinatario['ci_usuario'], $destinatario['email'], $mensaje, $tipoOrigen);
        }
        return $idNotificacion;
    }

    public function crearParaOperariosDeCentro($idCentro, $mensaje, $tipoOrigen, $prioridad) {
        $idNotificacion = $this->insertarNotificacion($mensaje, $tipoOrigen, $prioridad);
        foreach ($this->operariosDeCentro($idCentro) as $destinatario) {
            $this->entregar($idNotificacion, (int) $destinatario['ci_usuario'], $destinatario['email'], $mensaje, $tipoOrigen);
        }
        return $idNotificacion;
    }

    public function notificarCambioIncidencias(array $incidencias, $nuevoEstado) {
        if (!isset($this->textosEstadoIncidencia[$nuevoEstado])) {
            return;
        }
        foreach ($incidencias as $inc) {
            if ($inc['ci_usuario'] === null) {
                continue;
            }
            $this->crearParaUsuario(
                (int) $inc['ci_usuario'],
                "Tu incidencia #{$inc['id_incidencia']} ({$this->etiqueta($inc['tipo'])}) {$this->textosEstadoIncidencia[$nuevoEstado]}.",
                'incidencia',
                'informativa'
            );
        }
    }

    public function existeMensaje($mensaje) {
        $stmt = mysqli_prepare($this->conn, "SELECT 1 FROM NOTIFICACION WHERE mensaje = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $mensaje);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $existe = mysqli_stmt_num_rows($stmt) > 0;
        mysqli_stmt_close($stmt);
        return $existe;
    }

    private function administradoresHabilitados() {
        return $this->filas("SELECT ci_usuario, email FROM USUARIO WHERE rol = 'administrador' AND acceso = 'habilitado'", '', []);
    }

    private function cuadrillaHabilitada($idCuadrilla) {
        return $this->filas(
            "SELECT ci_usuario, email FROM USUARIO WHERE rol = 'cuadrilla' AND id_cuadrilla = ? AND acceso = 'habilitado'",
            'i',
            [$idCuadrilla]
        );
    }

    private function operariosDeCentro($idCentro) {
        return $this->filas(
            "SELECT ci_usuario, email FROM USUARIO WHERE rol = 'operario' AND id_centro = ? AND acceso = 'habilitado'",
            'i',
            [$idCentro]
        );
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

    private function entregar($idNotificacion, $ci, $email, $mensaje, $tipoOrigen) {
        $stmt = mysqli_prepare($this->conn, "INSERT INTO NOTIFICACION_USUARIO (id_notificacion, ci_usuario) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, 'ii', $idNotificacion, $ci);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        Mailer::enviar($email, $this->asunto($tipoOrigen), $mensaje);
    }

    private function etiqueta($valor) {
        return Etiquetas::legible($valor);
    }

    private function asunto($tipoOrigen) {
        $etiquetas = [
            'incidencia' => 'Incidencia',
            'contenedor' => 'Contenedor',
            'levante' => 'Levante',
            'camion' => 'Camión',
            'centro_acopio' => 'Centro de acopio',
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
