<?php

require_once __DIR__ . '/../../conexion.php';

class LevanteModel {

    private $conn;

    public function __construct() {
        $this->conn = new conexion();
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
            SELECT l.*, c.matricula, cu.id_municipio
            FROM levante l
            LEFT JOIN camion c ON c.id_camion = l.id_camion
            LEFT JOIN cuadrilla cu ON cu.id_cuadrilla = l.id_cuadrilla
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

    public function obtenerPorRuta($idRuta) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT l.*, c.matricula
            FROM levante l
            LEFT JOIN camion c ON c.id_camion = l.id_camion
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

    public function planificarLevante($idRuta, $idCamion, $idCuadrilla, $horaSalida, $horaLevante) {
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

        $this->precargarContenedores($idLevante, $idRuta);

        return $this->getLevante($idLevante);
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

        return $this->getLevante($idLevante);
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

        if ($estadoEncontrado !== 'todo_ok' && (trim((string) $motivo) === '')) {
            throw new InvalidArgumentException("El motivo es obligatorio cuando el contenedor no se recolecta");
        }
        if ($estadoEncontrado === 'todo_ok' && $peso === null) {
            throw new InvalidArgumentException("El peso es obligatorio cuando el contenedor se recolecta");
        }

        $pesoVal = $peso !== null && $peso !== '' ? (int) $peso : null;
        $motivoVal = ($estadoEncontrado === 'todo_ok') ? null : trim((string) $motivo);

        $horaVaciado = $estadoEncontrado === 'todo_ok' ? 'NOW()' : 'NULL';

        if ($existe) {
            $stmt = mysqli_prepare($this->conn->getConexion(), "
                UPDATE contenedor_levante
                SET estado_encontrado = ?, motivo = ?, peso = ?, hora_vaciado = $horaVaciado
                WHERE id_levante = ? AND id_contenedor = ?
            ");
            mysqli_stmt_bind_param($stmt, "ssiii", $estadoEncontrado, $motivoVal, $pesoVal, $idLevante, $idContenedor);
        } else {
            $stmt = mysqli_prepare($this->conn->getConexion(), "
                INSERT INTO contenedor_levante (id_levante, id_contenedor, estado_encontrado, motivo, peso, hora_vaciado)
                VALUES (?, ?, ?, ?, ?, $horaVaciado)
            ");
            mysqli_stmt_bind_param($stmt, "iissi", $idLevante, $idContenedor, $estadoEncontrado, $motivoVal, $pesoVal);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($pesoVal !== null && $pesoVal > 0) {
            $this->actualizarNivelLlenado($idContenedor, $pesoVal);
        }

        return $this->obtenerContenedor($idLevante, $idContenedor);
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

        $contenedores = $this->obtenerContenedores($idLevante);
        foreach ($contenedores as $c) {
            if ($c['estado_encontrado'] !== 'todo_ok' && trim((string) $c['motivo']) === '') {
                throw new InvalidArgumentException("El contenedor #" . $c['id_contenedor'] . " requiere estado y motivo de no recolección");
            }
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

        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camion SET estado = 'disponible' WHERE id_camion = ?");
        mysqli_stmt_bind_param($stmt, "i", $levante['id_camion']);
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

        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE camion SET estado = 'disponible' WHERE id_camion = ?");
        mysqli_stmt_bind_param($stmt, "i", $levante['id_camion']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        return $this->getLevante($idLevante);
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
            SELECT l.*, r.nombre AS ruta_nombre, r.actividad, c.matricula
            FROM levante l
            JOIN ruta_recoleccion r ON r.id_ruta = l.id_ruta
            LEFT JOIN camion c ON c.id_camion = l.id_camion
            WHERE l.estado IN ('pendiente', 'en_curso')
              AND r.actividad IS NOT NULL
              AND l.hora_levante IS NOT NULL
              AND l.hora_levante < CURDATE()
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
