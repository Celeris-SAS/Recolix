<?php

require_once __DIR__ . '/../../conexion.php';
require_once __DIR__ . '/notificacionModel.php';
require_once __DIR__ . '/../../extra/Comun/Etiquetas.php';

class LevanteModel {

    private $conn;
    private $notificacionModel;

    public function __construct() {
        $this->conn = new conexion();
        $this->notificacionModel = new NotificacionModel();
    }

    private function buscarIncidencias($sql, $types, $params) {
        $stmt = mysqli_prepare($this->conn->getConexion(), $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    private function actualizarNivelLlenado($contenedorId, $peso) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT capacidad FROM contenedor WHERE id_contenedor = ?");
        mysqli_stmt_bind_param($stmt, "i", $contenedorId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (!$row || $row['capacidad'] === null || $row['capacidad'] <= 0) {
            return;
        }
        $nivel = (int) round(($peso / $row['capacidad']) * 100);
        $nivel = max(0, min(100, $nivel));
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE contenedor SET nivel_llenado = ? WHERE id_contenedor = ?");
        mysqli_stmt_bind_param($stmt, "ii", $nivel, $contenedorId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function getLevante($idLevante) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM levante WHERE id_levante = ?");
        mysqli_stmt_bind_param($stmt, "i", $idLevante);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $levante = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (!$levante) {
            return null;
        }
        $levante['contenedores'] = $this->obtenerContenedores($idLevante);
        return $levante;
    }

    public function obtenerCuadrillas() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT cu.id_cuadrilla, cu.estado, m.nombre AS municipio
            FROM cuadrilla cu
            LEFT JOIN municipio m ON m.id_municipio = cu.id_municipio
            WHERE cu.estado = 'activa'
            ORDER BY cu.id_cuadrilla ASC
        ");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cuadrillas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $cuadrillas[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $cuadrillas;
    }

    public function getAllLevantes() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT l.*, c.matricula, cu.id_municipio, ce.nombre AS centro_nombre
            FROM levante l
            LEFT JOIN camion c ON c.id_camion = l.id_camion
            LEFT JOIN cuadrilla cu ON cu.id_cuadrilla = l.id_cuadrilla
            LEFT JOIN centro_levante lc ON lc.id_levante = l.id_levante
            LEFT JOIN centro_acopio ce ON ce.id_centro = lc.id_centro
            ORDER BY l.hora_levante DESC, l.id_levante DESC
        ");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $levantes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $levantes[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $levantes;
    }

    public function obtenerActivoPorCuadrilla($idCuadrilla) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT id_levante FROM levante
            WHERE id_cuadrilla = ? AND estado IN ('pendiente', 'en_curso')
            ORDER BY (estado = 'en_curso') DESC, hora_salida DESC
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "i", $idCuadrilla);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row ? (int) $row['id_levante'] : null;
    }

    public function obtenerCompletadosPorCuadrilla($idCuadrilla) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT l.id_levante, l.id_ruta, r.nombre, ci.turno, l.hora_salida, l.hora_llegada,
                   l.total_recolectado, c.matricula
            FROM levante l
            JOIN ruta_recoleccion r ON r.id_ruta = l.id_ruta
            LEFT JOIN circuito ci ON ci.id_circuito = r.id_circuito
            LEFT JOIN camion c ON c.id_camion = l.id_camion
            WHERE l.id_cuadrilla = ? AND l.estado = 'completado'
            ORDER BY l.hora_llegada DESC, l.id_levante DESC
        ");
        mysqli_stmt_bind_param($stmt, "i", $idCuadrilla);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $levantes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $levantes[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $levantes;
    }

    public function obtenerPorRuta($idRuta) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT l.*, c.matricula, ce.nombre AS centro_nombre
            FROM levante l
            LEFT JOIN camion c ON c.id_camion = l.id_camion
            LEFT JOIN centro_levante lc ON lc.id_levante = l.id_levante
            LEFT JOIN centro_acopio ce ON ce.id_centro = lc.id_centro
            WHERE l.id_ruta = ?
            ORDER BY l.hora_salida DESC
        ");
        mysqli_stmt_bind_param($stmt, "i", $idRuta);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $levantes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $levantes[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $levantes;
    }

    public function planificarLevante($idRuta, $idCamion, $idCuadrilla, $horaSalida, $horaLevante, $idCentro = null) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT activa FROM ruta_recoleccion WHERE id_ruta = ?");
        mysqli_stmt_bind_param($stmt, "i", $idRuta);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $ruta = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (!$ruta) {
            throw new InvalidArgumentException("Ruta no encontrada");
        }
        if (!(int) $ruta['activa']) {
            throw new InvalidArgumentException("Solo se puede planificar un levante sobre una ruta activa");
        }

        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT COUNT(*) AS activos FROM levante
            WHERE id_ruta = ? AND estado IN ('pendiente', 'en_curso')
        ");
        mysqli_stmt_bind_param($stmt, "i", $idRuta);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $activosRuta = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (intval($activosRuta['activos']) > 0) {
            throw new InvalidArgumentException("Esta ruta ya tiene un levante pendiente o en curso");
        }

        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT estado FROM camion WHERE id_camion = ?");
        mysqli_stmt_bind_param($stmt, "i", $idCamion);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $camion = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (!$camion) {
            throw new InvalidArgumentException("Camión no encontrado");
        }
        if ($camion['estado'] !== 'disponible') {
            throw new InvalidArgumentException("Solo se puede asignar un camión con estado disponible");
        }

        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT id_cuadrilla FROM cuadrilla WHERE id_cuadrilla = ? AND estado = 'activa'");
        mysqli_stmt_bind_param($stmt, "i", $idCuadrilla);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cuadrilla = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (!$cuadrilla) {
            throw new InvalidArgumentException("Cuadrilla no encontrada o inactiva");
        }

        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT COUNT(*) AS activos FROM levante
            WHERE id_camion = ? AND estado IN ('pendiente', 'en_curso')
        ");
        mysqli_stmt_bind_param($stmt, "i", $idCamion);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $activos = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if (intval($activos['activos']) > 0) {
            throw new InvalidArgumentException("El camión ya tiene un levante pendiente o en curso");
        }

        if ($idCentro !== null) {
            $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT estado FROM centro_acopio WHERE id_centro = ?");
            mysqli_stmt_bind_param($stmt, "i", $idCentro);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $centro = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            if (!$centro) {
                throw new InvalidArgumentException("Sitio de disposición final no encontrado");
            }
            if ($centro['estado'] !== 'operativo') {
                throw new InvalidArgumentException("El sitio de disposición final seleccionado no está operativo");
            }
        }

        $conexion = $this->conn->getConexion();
        mysqli_begin_transaction($conexion);
        try {
            $stmt = mysqli_prepare($this->conn->getConexion(), "
                INSERT INTO levante (id_camion, id_cuadrilla, id_ruta, hora_salida, hora_levante, estado)
                VALUES (?, ?, ?, ?, ?, 'pendiente')
            ");
            mysqli_stmt_bind_param($stmt, "iisss", $idCamion, $idCuadrilla, $idRuta, $horaSalida, $horaLevante);
            mysqli_stmt_execute($stmt);
            $idLevante = mysqli_insert_id($this->conn->getConexion());
            mysqli_stmt_close($stmt);

            $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camion SET estado = 'en_deposito' WHERE id_camion = ?");
            mysqli_stmt_bind_param($stmt, "i", $idCamion);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        $this->notificacionModel->crearParaCuadrilla(
            $idCuadrilla,
            "Se te asignó la ruta #$idRuta para el levante del $horaLevante.",
            'levante',
            'informativa'
        );

        $this->precargarContenedores($idLevante, $idRuta);
        $this->asignarIncidenciasDeLaRuta($idRuta, $idCuadrilla);

            $stmt = mysqli_prepare($this->conn->getConexion(), "
                INSERT IGNORE INTO ruta_cuadrilla (id_ruta, id_cuadrilla) VALUES (?, ?)
            ");
            mysqli_stmt_bind_param($stmt, "ii", $idRuta, $idCuadrilla);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $this->precargarContenedores($idLevante, $idRuta);
            $this->asignarIncidenciasDeLaRuta($idRuta, $idCuadrilla);

            if ($idCentro !== null) {
                $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO centro_levante (id_levante, id_centro) VALUES (?, ?)");
                mysqli_stmt_bind_param($stmt, "ii", $idLevante, $idCentro);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            mysqli_commit($conexion);
        } catch (Throwable $e) {
            mysqli_rollback($conexion);
            throw $e;
        }

        if ($idCentro !== null) {
            $this->notificacionModel->crearParaOperariosDeCentro(
                $idCentro,
                "Tu centro de acopio fue asignado como destino del levante #$idLevante.",
                'centro_acopio',
                'informativa'
            );
        }

        return $this->getLevante($idLevante);
    }

    private function asignarIncidenciasDeLaRuta($idRuta, $idCuadrilla) {
        $afectadas = $this->buscarIncidencias("
            SELECT DISTINCT i.id_incidencia, i.ci_usuario, i.tipo, ic.id_contenedor
            FROM INCIDENCIA i
            JOIN INCIDENCIA_CONTENEDOR ic ON ic.id_incidencia = i.id_incidencia
            JOIN ruta_contenedor rc ON rc.id_contenedor = ic.id_contenedor
            WHERE rc.id_ruta = ?
              AND i.estado = 'abierta'
              AND i.tipo IN ('desborde', 'falta_recoleccion')
        ", "i", [$idRuta]);

        $stmt = mysqli_prepare($this->conn->getConexion(), "
            UPDATE INCIDENCIA i
            JOIN INCIDENCIA_CONTENEDOR ic ON ic.id_incidencia = i.id_incidencia
            JOIN ruta_contenedor rc ON rc.id_contenedor = ic.id_contenedor
            SET i.id_cuadrilla = ?, i.estado = 'asignada'
            WHERE rc.id_ruta = ?
              AND i.estado = 'abierta'
              AND i.tipo IN ('desborde', 'falta_recoleccion')
        ");
        mysqli_stmt_bind_param($stmt, "ii", $idCuadrilla, $idRuta);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $this->notificacionModel->notificarCambioIncidencias($afectadas, 'asignada');

        foreach ($afectadas as $inc) {
            $this->notificacionModel->crearParaCuadrilla(
                $idCuadrilla,
                "Se te asignó el contenedor #{$inc['id_contenedor']} (" . Etiquetas::legible($inc['tipo']) . ") por una incidencia abierta.",
                'incidencia',
                'informativa'
            );
        }
    }

    private function precargarContenedores($idLevante, $idRuta) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            INSERT INTO contenedor_levante (id_levante, id_contenedor, estado_encontrado)
            SELECT ?, rc.id_contenedor, 'todo_ok'
            FROM ruta_contenedor rc
            LEFT JOIN contenedor_levante cl
                ON cl.id_levante = ? AND cl.id_contenedor = rc.id_contenedor
            WHERE rc.id_ruta = ? AND cl.id_contenedor IS NULL
        ");
        mysqli_stmt_bind_param($stmt, "iii", $idLevante, $idLevante, $idRuta);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function obtenerContenedores($idLevante) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT cl.id_contenedor, cl.estado_encontrado, cl.motivo, cl.hora_vaciado, cl.peso,
                   c.calle, c.num_calle, c.latitud, c.longitud, c.capacidad,
                   COALESCE(rc.orden_visita, 0) AS orden_visita
            FROM contenedor_levante cl
            JOIN contenedor c ON c.id_contenedor = cl.id_contenedor
            LEFT JOIN ruta_contenedor rc
                ON rc.id_ruta = (SELECT id_ruta FROM levante WHERE id_levante = ?)
               AND rc.id_contenedor = cl.id_contenedor
            WHERE cl.id_levante = ?
            ORDER BY orden_visita ASC
        ");
        mysqli_stmt_bind_param($stmt, "ii", $idLevante, $idLevante);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $contenedores = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['orden_visita'] = (int) $row['orden_visita'];
            $row['peso'] = $row['peso'] !== null ? (int) $row['peso'] : null;
            $contenedores[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $contenedores;
    }

    public function iniciarLevante($idLevante) {
        $levante = $this->getEstado($idLevante);
        if (!$levante) {
            throw new InvalidArgumentException("Levante no encontrado");
        }
        if ($levante['estado'] !== 'pendiente') {
            throw new InvalidArgumentException("Solo se puede iniciar un levante en estado pendiente");
        }

        $stmt = mysqli_prepare($this->conn->getConexion(), "
            UPDATE levante SET estado = 'en_curso', hora_salida = COALESCE(hora_salida, NOW()) WHERE id_levante = ?
        ");
        mysqli_stmt_bind_param($stmt, "i", $idLevante);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camion SET estado = 'en_ruta' WHERE id_camion = ?");
        mysqli_stmt_bind_param($stmt, "i", $levante['id_camion']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $this->iniciarIncidenciasDeLaRuta($idLevante);

        return $this->getLevante($idLevante);
    }

    private function iniciarIncidenciasDeLaRuta($idLevante) {
        $afectadas = $this->buscarIncidencias("
            SELECT DISTINCT i.id_incidencia, i.ci_usuario, i.tipo
            FROM incidencia i
            JOIN incidencia_contenedor ic ON ic.id_incidencia = i.id_incidencia
            JOIN contenedor_levante cl ON cl.id_contenedor = ic.id_contenedor
            WHERE cl.id_levante = ? AND i.estado = 'asignada'
        ", "i", [$idLevante]);

        $stmt = mysqli_prepare($this->conn->getConexion(), "
            UPDATE incidencia i
            JOIN incidencia_contenedor ic ON ic.id_incidencia = i.id_incidencia
            JOIN contenedor_levante cl ON cl.id_contenedor = ic.id_contenedor
            SET i.estado = 'en_curso'
            WHERE cl.id_levante = ? AND i.estado = 'asignada'
        ");
        mysqli_stmt_bind_param($stmt, "i", $idLevante);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $this->notificacionModel->notificarCambioIncidencias($afectadas, 'en_curso');
    }

    private function cerrarIncidenciasRecolectadas($idContenedor) {
        $afectadas = $this->buscarIncidencias("
            SELECT DISTINCT i.id_incidencia, i.ci_usuario, i.tipo
            FROM INCIDENCIA i
            JOIN INCIDENCIA_CONTENEDOR ic ON ic.id_incidencia = i.id_incidencia
            WHERE ic.id_contenedor = ?
              AND i.estado = 'en_curso'
              AND i.tipo IN ('desborde', 'falta_recoleccion')
        ", "i", [$idContenedor]);

        $stmt = mysqli_prepare($this->conn->getConexion(), "
            UPDATE INCIDENCIA i
            JOIN INCIDENCIA_CONTENEDOR ic ON ic.id_incidencia = i.id_incidencia
            SET i.estado = 'resuelta', i.fecha_cierre = NOW()
            WHERE ic.id_contenedor = ?
              AND i.estado = 'en_curso'
              AND i.tipo IN ('desborde', 'falta_recoleccion')
        ");
        mysqli_stmt_bind_param($stmt, "i", $idContenedor);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $this->notificacionModel->notificarCambioIncidencias($afectadas, 'resuelta');
    }

    public function registrarContenedor($idLevante, $idContenedor, $estadoEncontrado, $peso, $motivo) {
        $estadosValidos = ['sobrepeso', 'riesgo_de_incendio', 'acceso_bloqueado', 'contenedor_ausente', 'todo_ok', 'otro'];
        if (!in_array($estadoEncontrado, $estadosValidos, true)) {
            throw new InvalidArgumentException("Estado encontrado inválido");
        }

        $levante = $this->getEstado($idLevante);
        if (!$levante) {
            throw new InvalidArgumentException("Levante no encontrado");
        }
        if ($levante['estado'] !== 'en_curso') {
            throw new InvalidArgumentException("Solo se pueden registrar contenedores en un levante en curso");
        }

        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT id_contenedor FROM contenedor_levante WHERE id_levante = ? AND id_contenedor = ?
        ");
        mysqli_stmt_bind_param($stmt, "ii", $idLevante, $idContenedor);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $existe = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$existe) {
            throw new InvalidArgumentException("El contenedor #$idContenedor no pertenece a la ruta de este levante");
        }

        if ($estadoEncontrado === 'todo_ok' && $peso === null) {
            throw new InvalidArgumentException("El peso es obligatorio cuando el contenedor se recolecta");
        }

        $pesoVal = $peso !== null && $peso !== '' ? (int) $peso : null;
        $motivoTrim = trim((string) $motivo);
        $motivoVal = ($estadoEncontrado === 'todo_ok' || $motivoTrim === '') ? null : $motivoTrim;

        $horaVaciado = $estadoEncontrado === 'todo_ok' ? 'NOW()' : 'NULL';

        $stmt = mysqli_prepare($this->conn->getConexion(), "
            UPDATE contenedor_levante
            SET estado_encontrado = ?, motivo = ?, peso = ?, hora_vaciado = $horaVaciado
            WHERE id_levante = ? AND id_contenedor = ?
        ");
        mysqli_stmt_bind_param($stmt, "ssiii", $estadoEncontrado, $motivoVal, $pesoVal, $idLevante, $idContenedor);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($pesoVal !== null && $pesoVal > 0) {
            $this->actualizarNivelLlenado($idContenedor, $pesoVal);
        }

        if ($estadoEncontrado === 'todo_ok') {
            $this->cerrarIncidenciasRecolectadas($idContenedor);
        } else {
            $detalle = $motivoVal !== null && $motivoVal !== '' ? ": $motivoVal" : '';
            $this->notificacionModel->crearParaAdministradores(
                "El levante #$idLevante no pudo recolectar el contenedor #$idContenedor (" . Etiquetas::legible($estadoEncontrado) . ")$detalle",
                'contenedor',
                'advertencia'
            );
        }

        $contenedor = $this->obtenerContenedor($idLevante, $idContenedor);

        if (empty($this->contenedoresPendientes($idLevante))) {
            $this->completarLevante($idLevante);
        }

        return $contenedor;
    }

    private function contenedoresPendientes($idLevante) {
        $pendientes = [];
        foreach ($this->obtenerContenedores($idLevante) as $c) {
            $fueProcesado = $c['hora_vaciado'] !== null || $c['estado_encontrado'] !== 'todo_ok' || trim((string) $c['motivo']) !== '';
            if (!$fueProcesado) {
                $pendientes[] = $c['id_contenedor'];
            }
        }
        return $pendientes;
    }

    private function obtenerContenedor($idLevante, $idContenedor) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT cl.id_contenedor, cl.estado_encontrado, cl.motivo, cl.hora_vaciado, cl.peso,
                   c.calle, c.num_calle, c.capacidad
            FROM contenedor_levante cl
            JOIN contenedor c ON c.id_contenedor = cl.id_contenedor
            WHERE cl.id_levante = ? AND cl.id_contenedor = ?
        ");
        mysqli_stmt_bind_param($stmt, "ii", $idLevante, $idContenedor);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        if ($row) {
            $row['peso'] = $row['peso'] !== null ? (int) $row['peso'] : null;
        }
        return $row;
    }

    public function completarLevante($idLevante) {
        $levante = $this->getEstado($idLevante);
        if (!$levante) {
            throw new InvalidArgumentException("Levante no encontrado");
        }
        if ($levante['estado'] !== 'en_curso') {
            throw new InvalidArgumentException("Solo se puede completar un levante en curso");
        }

        $pendientes = $this->contenedoresPendientes($idLevante);
        if (!empty($pendientes)) {
            throw new InvalidArgumentException("Todavía faltan contenedores por registrar: #" . implode(', #', $pendientes));
        }

        $stmt = mysqli_prepare($this->conn->getConexion(), "
            UPDATE levante
            SET estado = 'completado', hora_llegada = NOW(),
                total_recolectado = COALESCE((SELECT SUM(peso) FROM contenedor_levante WHERE id_levante = ?), 0)
            WHERE id_levante = ?
        ");
        mysqli_stmt_bind_param($stmt, "ii", $idLevante, $idLevante);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camion SET estado = 'disponible' WHERE id_camion = ? AND estado NOT IN ('en_mantenimiento', 'de_baja')");
        mysqli_stmt_bind_param($stmt, "i", $levante['id_camion']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->conn->getConexion(), "
            DELETE FROM ruta_cuadrilla WHERE id_ruta = ? AND id_cuadrilla = ?
        ");
        mysqli_stmt_bind_param($stmt, "ii", $levante['id_ruta'], $levante['id_cuadrilla']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $this->getLevante($idLevante);
    }

    public function cancelarLevante($idLevante) {
        $levante = $this->getEstado($idLevante);
        if (!$levante) {
            throw new InvalidArgumentException("Levante no encontrado");
        }
        if (!in_array($levante['estado'], ['pendiente', 'en_curso'], true)) {
            throw new InvalidArgumentException("Solo se puede cancelar un levante pendiente o en curso");
        }

        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE levante SET estado = 'cancelado' WHERE id_levante = ?");
        mysqli_stmt_bind_param($stmt, "i", $idLevante);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camion SET estado = 'disponible' WHERE id_camion = ? AND estado NOT IN ('en_mantenimiento', 'de_baja')");
        mysqli_stmt_bind_param($stmt, "i", $levante['id_camion']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $this->getLevante($idLevante);
    }

    public function cancelarActivosDeRutaCuadrilla($idRuta, $idCuadrilla) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT id_levante FROM levante WHERE id_ruta = ? AND id_cuadrilla = ? AND estado IN ('pendiente', 'en_curso')
        ");
        mysqli_stmt_bind_param($stmt, "ii", $idRuta, $idCuadrilla);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $ids = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $ids[] = (int) $row['id_levante'];
        }
        mysqli_stmt_close($stmt);

        foreach ($ids as $id) {
            $this->cancelarLevante($id);
        }
        return $ids;
    }

    public function cancelarActivosDelCamion($idCamion) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT id_levante FROM levante WHERE id_camion = ? AND estado IN ('pendiente', 'en_curso')
        ");
        mysqli_stmt_bind_param($stmt, "i", $idCamion);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $ids = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $ids[] = (int) $row['id_levante'];
        }
        mysqli_stmt_close($stmt);

        foreach ($ids as $id) {
            $this->cancelarLevante($id);
        }
        return $ids;
    }

    public function obtenerResumen() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT estado, COUNT(*) AS cantidad
            FROM levante
            GROUP BY estado
        ");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $resumen = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $resumen[$row['estado']] = (int) $row['cantidad'];
        }
        mysqli_stmt_close($stmt);
        return [
            'pendiente' => $resumen['pendiente'] ?? 0,
            'en_curso' => $resumen['en_curso'] ?? 0,
            'completado' => $resumen['completado'] ?? 0,
            'cancelado' => $resumen['cancelado'] ?? 0,
            'total' => array_sum($resumen)
        ];
    }

    public function obtenerPendientesVencidos() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT l.*, r.nombre AS ruta_nombre, r.actividad, c.matricula, ci.turno
            FROM levante l
            JOIN ruta_recoleccion r ON r.id_ruta = l.id_ruta
            LEFT JOIN circuito ci ON ci.id_circuito = r.id_circuito
            LEFT JOIN camion c ON c.id_camion = l.id_camion
            WHERE l.estado IN ('pendiente', 'en_curso')
              AND r.actividad IS NOT NULL
              AND l.hora_levante IS NOT NULL
              AND (
                    l.hora_levante < CURDATE()
                    OR (l.hora_levante = CURDATE() AND CURTIME() >= CASE ci.turno
                            WHEN 'mañana' THEN '12:00:00'
                            WHEN 'tarde'  THEN '18:00:00'
                            ELSE '23:59:59'
                        END)
              )
            ORDER BY l.hora_levante ASC
        ");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $levantes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $levantes[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $levantes;
    }

    public function generarAlertaVencidos() {
        $avisados = 0;
        foreach ($this->obtenerPendientesVencidos() as $levante) {
            $mensaje = "El levante #{$levante['id_levante']} de la ruta \"{$levante['ruta_nombre']}\" quedó " . Etiquetas::legible($levante['estado']) . " al finalizar el turno (programado para el {$levante['hora_levante']}).";
            if ($this->notificacionModel->existeMensaje($mensaje)) {
                continue;
            }
            $this->notificacionModel->crearParaAdministradores($mensaje, 'levante', 'advertencia');
            $avisados++;
        }
        return $avisados;
    }

    private function getEstado($idLevante) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM levante WHERE id_levante = ?");
        mysqli_stmt_bind_param($stmt, "i", $idLevante);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

}
