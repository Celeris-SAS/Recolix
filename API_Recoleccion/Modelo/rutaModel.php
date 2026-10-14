<?php

require_once __DIR__ . '/../../conexion.php';

class RutaModel {

    private $conn;

    public function __construct() {
        $this->conn = new conexion();
    }

    public function insertRuta($circuitoId, $nombre, $actividad) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO ruta_recoleccion (id_circuito, nombre, actividad) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iss", $circuitoId, $nombre, $actividad);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $id;
    }

    public function updateRuta($id, $circuitoId, $nombre, $actividad) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE ruta_recoleccion SET id_circuito = ?, nombre = ?, actividad = ? WHERE id_ruta = ?");
        mysqli_stmt_bind_param($stmt, "issi", $circuitoId, $nombre, $actividad, $id);
        mysqli_stmt_execute($stmt);
        $affected = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        return $affected > 0;
    }

    // Baja lógica: la ruta queda inactiva y conserva sus contenedores, cuadrillas y levantes.
    public function deleteRuta($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE ruta_recoleccion SET activa = 0 WHERE id_ruta = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    // Todas las lecturas de ruta incluyen levantes_count (cantidad de levantes asignados)
    // para que la UI pueda mostrar "Asignado"/"Sin asignar" sin importar por qué endpoint
    // haya llegado la fila (listado completo, por id, o filtrada).
    private const SELECT_CON_LEVANTES = "
        SELECT r.*, ci.turno, ci.frecuencia,
               (SELECT COUNT(*) FROM levante l WHERE l.id_ruta = r.id_ruta) AS levantes_count,
               (SELECT COUNT(*) FROM levante l WHERE l.id_ruta = r.id_ruta AND l.estado = 'en_curso') AS en_curso_count,
               (SELECT COUNT(*) FROM levante l WHERE l.id_ruta = r.id_ruta AND l.estado = 'pendiente') AS pendiente_count,
               (SELECT COUNT(*) FROM levante l WHERE l.id_ruta = r.id_ruta AND l.estado = 'completado') AS completado_count
        FROM ruta_recoleccion r
        LEFT JOIN circuito ci ON ci.id_circuito = r.id_circuito
    ";

    // Las rutas dadas de baja (activa = 0) se conservan por el historial (levantes, reportes) pero
    // no aparecen en ningún listado; solo se llega a ellas por id.
    public function getAllRutas() {
        $stmt = mysqli_prepare($this->conn->getConexion(), self::SELECT_CON_LEVANTES . " WHERE r.activa = 1");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rutas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rutas[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rutas;
    }

    public function getRuta($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), self::SELECT_CON_LEVANTES . " WHERE r.id_ruta = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function obtenerPorCircuito($circuitoId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), self::SELECT_CON_LEVANTES . " WHERE r.id_circuito = ? AND r.activa = 1");
        mysqli_stmt_bind_param($stmt, "i", $circuitoId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rutas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rutas[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rutas;
    }

    public function filtrarPorActividad($actividad) {
        $stmt = mysqli_prepare($this->conn->getConexion(), self::SELECT_CON_LEVANTES . " WHERE r.actividad = ? AND r.activa = 1");
        mysqli_stmt_bind_param($stmt, "s", $actividad);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rutas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rutas[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rutas;
    }

    public function filtrarPorNombre($nombre) {
        $stmt = mysqli_prepare($this->conn->getConexion(), self::SELECT_CON_LEVANTES . " WHERE r.nombre LIKE ? AND r.activa = 1");
        $like = '%' . $nombre . '%';
        mysqli_stmt_bind_param($stmt, "s", $like);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rutas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rutas[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rutas;
    }

    public function agregarContenedor($rutaId, $contenedorId, $ordenVisita) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO ruta_contenedor (id_ruta, id_contenedor, orden_visita) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iii", $rutaId, $contenedorId, $ordenVisita);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function quitarContenedor($rutaId, $contenedorId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "DELETE FROM ruta_contenedor WHERE id_ruta = ? AND id_contenedor = ?");
        mysqli_stmt_bind_param($stmt, "ii", $rutaId, $contenedorId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function obtenerContenedores($rutaId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT rc.id_contenedor, rc.orden_visita, c.calle, c.num_calle, c.latitud, c.longitud, c.capacidad, c.nivel_llenado, c.estado FROM ruta_contenedor rc JOIN contenedor c ON c.id_contenedor = rc.id_contenedor WHERE rc.id_ruta = ? ORDER BY rc.orden_visita ASC");
        mysqli_stmt_bind_param($stmt, "i", $rutaId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $contenedores = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $contenedores[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $contenedores;
    }

    public function asignarCuadrilla($rutaId, $cuadrillaId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO ruta_cuadrilla (id_ruta, id_cuadrilla) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ii", $rutaId, $cuadrillaId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function quitarCuadrilla($rutaId, $cuadrillaId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "DELETE FROM ruta_cuadrilla WHERE id_ruta = ? AND id_cuadrilla = ?");
        mysqli_stmt_bind_param($stmt, "ii", $rutaId, $cuadrillaId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function obtenerCuadrillas($rutaId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT cu.* FROM ruta_cuadrilla rcu JOIN cuadrilla cu ON cu.id_cuadrilla = rcu.id_cuadrilla WHERE rcu.id_ruta = ?");
        mysqli_stmt_bind_param($stmt, "i", $rutaId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cuadrillas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $cuadrillas[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $cuadrillas;
    }

    // RF54: eventos de la ruta. Cada levante es una ejecución con su cuadrilla, camión y estado
    // (la asignación a una cuadrilla se conserva ahí aunque después se libere de ruta_cuadrilla);
    // además se listan las cuadrillas asignadas hoy.
    public function obtenerHistorial($rutaId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT 'ejecucion' AS tipo_evento, l.id_levante, l.hora_levante AS fecha, l.estado,
                   l.id_cuadrilla, ca.matricula, l.total_recolectado,
                   (SELECT COUNT(*) FROM contenedor_levante cl WHERE cl.id_levante = l.id_levante AND cl.hora_vaciado IS NOT NULL) AS contenedores_recolectados
            FROM levante l
            LEFT JOIN camion ca ON ca.id_camion = l.id_camion
            WHERE l.id_ruta = ?
            ORDER BY l.hora_levante DESC, l.id_levante DESC
        ");
        mysqli_stmt_bind_param($stmt, "i", $rutaId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $historial = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $historial[] = $row;
        }
        mysqli_stmt_close($stmt);

        foreach ($this->obtenerCuadrillas($rutaId) as $cuadrilla) {
            $historial[] = [
                'tipo_evento'  => 'asignacion_vigente',
                'id_cuadrilla' => (int) $cuadrilla['id_cuadrilla'],
                'estado'       => 'asignada',
                'fecha'        => null,
            ];
        }
        return $historial;
    }

    public function obtenerLevantes($rutaId) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM levante WHERE id_ruta = ? ORDER BY hora_salida DESC");
        mysqli_stmt_bind_param($stmt, "i", $rutaId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $levantes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $levantes[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $levantes;
    }

    // Rutas asignadas a una cuadrilla (dashboard de cuadrilla.php), con el id y
    // estado de su levante más reciente en cada ruta para saber si hay uno
    // pendiente/en_curso que mostrar.
    public function obtenerPorCuadrilla($idCuadrilla) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT r.id_ruta, r.nombre, r.actividad, ci.turno,
                   l.id_levante, l.estado AS estado_levante, l.hora_salida,
                   ca.id_camion, ca.matricula, ca.estado AS estado_camion
            FROM ruta_cuadrilla rcu
            JOIN ruta_recoleccion r ON r.id_ruta = rcu.id_ruta
            LEFT JOIN circuito ci ON ci.id_circuito = r.id_circuito
            LEFT JOIN levante l ON l.id_levante = (
                SELECT l2.id_levante FROM levante l2
                WHERE l2.id_ruta = r.id_ruta
                ORDER BY (l2.estado IN ('pendiente', 'en_curso')) DESC, l2.id_levante DESC
                LIMIT 1
            )
            LEFT JOIN camion ca ON ca.id_camion = l.id_camion
            WHERE rcu.id_cuadrilla = ? AND r.activa = 1
            ORDER BY r.id_ruta DESC
        ");
        mysqli_stmt_bind_param($stmt, "i", $idCuadrilla);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rutas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rutas[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rutas;
    }

    public function obtenerResumen() {
        $sql = "SELECT r.*, ci.turno,
                (SELECT COUNT(*) FROM levante l WHERE l.id_ruta = r.id_ruta AND l.estado = 'en_curso') AS en_curso_count,
                (SELECT COUNT(*) FROM levante l WHERE l.id_ruta = r.id_ruta AND l.estado = 'pendiente') AS pendiente_count,
                (SELECT COUNT(*) FROM levante l WHERE l.id_ruta = r.id_ruta AND l.estado = 'completado') AS completado_count,
                (SELECT COUNT(*) FROM levante l WHERE l.id_ruta = r.id_ruta) AS levantes_count,
                (SELECT COUNT(*) FROM ruta_contenedor rc WHERE rc.id_ruta = r.id_ruta) AS contenedores_count,
                (SELECT AVG(c.nivel_llenado) FROM ruta_contenedor rc JOIN contenedor c ON c.id_contenedor = rc.id_contenedor WHERE rc.id_ruta = r.id_ruta) AS llenado_promedio
                FROM ruta_recoleccion r
                LEFT JOIN circuito ci ON ci.id_circuito = r.id_circuito
                WHERE r.activa = 1
                ORDER BY r.id_ruta DESC";
        $stmt = mysqli_prepare($this->conn->getConexion(), $sql);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rutas = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rutas[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rutas;
    }
}
