<?php
require_once __DIR__ . '/../../conexion.php';

class IncidenciaModel {
    private $conn;

    public function __construct() {
        $this->conn = new conexion();
    }

    public function crearIncidencia($ciUsuario, $tipo, $descripcion, $urlImagen = null, $urgencia = 0, $prioridad = 'baja') {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "INSERT INTO INCIDENCIA (ci_usuario, tipo, descripcion, url_imagen, urgencia, prioridad) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isssis", $ciUsuario, $tipo, $descripcion, $urlImagen, $urgencia, $prioridad);
        mysqli_stmt_execute($stmt);
        $idIncidencia = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $idIncidencia;
    }

    public function vincularContenedor($idIncidencia, $idContenedor) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "INSERT INTO INCIDENCIA_CONTENEDOR (id_incidencia, id_contenedor) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ii", $idIncidencia, $idContenedor);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function contarIncidenciasAbiertasPorContenedor($idContenedor) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT COUNT(*) AS total FROM INCIDENCIA i
             JOIN INCIDENCIA_CONTENEDOR ic ON i.id_incidencia = ic.id_incidencia
             WHERE ic.id_contenedor = ? AND i.estado != 'resuelta'");
        mysqli_stmt_bind_param($stmt, "i", $idContenedor);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return (int) $row['total'];
    }

    private function condicionSinDuplicadosAbiertos($alias) {
        return "($alias.estado = 'resuelta' OR NOT EXISTS (
            SELECT 1 FROM INCIDENCIA i2
            JOIN INCIDENCIA_CONTENEDOR ic2 ON i2.id_incidencia = ic2.id_incidencia
            JOIN INCIDENCIA_CONTENEDOR ic1 ON ic1.id_incidencia = $alias.id_incidencia
            WHERE ic2.id_contenedor = ic1.id_contenedor
              AND i2.estado != 'resuelta'
              AND (i2.fecha_apertura > $alias.fecha_apertura
                   OR (i2.fecha_apertura = $alias.fecha_apertura AND i2.id_incidencia > $alias.id_incidencia))
        ))";
    }

    public function usuarioTieneIncidenciaAbiertaEnContenedor($ciUsuario, $idContenedor) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT 1 FROM INCIDENCIA i
             JOIN INCIDENCIA_CONTENEDOR ic ON i.id_incidencia = ic.id_incidencia
             WHERE i.ci_usuario = ? AND ic.id_contenedor = ? AND i.estado != 'resuelta'
             LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ii", $ciUsuario, $idContenedor);
        mysqli_stmt_execute($stmt);
        $existe = mysqli_stmt_get_result($stmt)->fetch_assoc() !== null;
        mysqli_stmt_close($stmt);
        return $existe;
    }

    public function usuarioExiste($ciUsuario) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 1 FROM USUARIO WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, "i", $ciUsuario);
        mysqli_stmt_execute($stmt);
        $existe = mysqli_stmt_get_result($stmt)->fetch_assoc() !== null;
        mysqli_stmt_close($stmt);
        return $existe;
    }

    private function columnaMunicipio($alias) {
        return "(SELECT GROUP_CONCAT(DISTINCT m.nombre ORDER BY m.nombre SEPARATOR ', ')
                 FROM INCIDENCIA_CONTENEDOR icm
                 JOIN CONTENEDOR cm ON cm.id_contenedor = icm.id_contenedor
                 JOIN CIRCUITO cim ON cim.id_circuito = cm.id_circuito
                 JOIN MUNICIPIO m ON m.id_municipio = cim.id_municipio
                 WHERE icm.id_incidencia = $alias.id_incidencia) AS municipio";
    }

    public function getAllIncidencias() {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT i.*, " . $this->columnaMunicipio('i') . " FROM INCIDENCIA i WHERE " . $this->condicionSinDuplicadosAbiertos('i') . " ORDER BY fecha_apertura DESC");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function getIncidencia($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT i.*, " . $this->columnaMunicipio('i') . " FROM INCIDENCIA i WHERE i.id_incidencia = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function filtrarEstado($estado) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT i.*, " . $this->columnaMunicipio('i') . " FROM INCIDENCIA i WHERE i.estado = ? AND " . $this->condicionSinDuplicadosAbiertos('i') . " ORDER BY fecha_apertura DESC");
        mysqli_stmt_bind_param($stmt, "s", $estado);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function filtrarTipo($tipo) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT i.*, " . $this->columnaMunicipio('i') . " FROM INCIDENCIA i WHERE i.tipo = ? AND " . $this->condicionSinDuplicadosAbiertos('i') . " ORDER BY fecha_apertura DESC");
        mysqli_stmt_bind_param($stmt, "s", $tipo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function filtrarPrioridad($prioridad) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT i.*, " . $this->columnaMunicipio('i') . " FROM INCIDENCIA i WHERE i.prioridad = ? AND " . $this->condicionSinDuplicadosAbiertos('i') . " ORDER BY fecha_apertura DESC");
        mysqli_stmt_bind_param($stmt, "s", $prioridad);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function getActivasConUbicacion() {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT i.id_incidencia, i.tipo, i.prioridad, i.estado, i.urgencia, i.fecha_apertura,
                    c.id_contenedor, c.latitud, c.longitud, c.estado AS estado_contenedor,
                    c.calle, c.num_calle
             FROM INCIDENCIA i
             JOIN INCIDENCIA_CONTENEDOR ic ON i.id_incidencia = ic.id_incidencia
             JOIN CONTENEDOR c ON ic.id_contenedor = c.id_contenedor
             WHERE i.estado != 'resuelta' AND " . $this->condicionSinDuplicadosAbiertos('i') . "
             ORDER BY i.fecha_apertura DESC");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function listarUrgentes() {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT DISTINCT i.*, c.id_contenedor, c.estado AS estado_contenedor, c.nivel_llenado,
                    c.calle, c.num_calle, " . $this->columnaMunicipio('i') . "
             FROM INCIDENCIA i
             JOIN INCIDENCIA_CONTENEDOR ic ON i.id_incidencia = ic.id_incidencia
             JOIN CONTENEDOR c ON ic.id_contenedor = c.id_contenedor
             WHERE i.urgencia = 1 AND i.estado != 'resuelta' AND " . $this->condicionSinDuplicadosAbiertos('i') . "
             ORDER BY (c.estado = 'desbordado' OR c.nivel_llenado > 90) DESC, c.nivel_llenado DESC, i.fecha_apertura ASC");
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function getContenedoresVinculados($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT c.* FROM CONTENEDOR c
             JOIN INCIDENCIA_CONTENEDOR ic ON c.id_contenedor = ic.id_contenedor
             WHERE ic.id_incidencia = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function cuadrillaDeUsuario($ci) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT id_cuadrilla FROM USUARIO WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, "i", $ci);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $fila['id_cuadrilla'] ?? null;
    }

    public function obtenerPorCuadrilla($idCuadrilla) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "
            SELECT i.id_incidencia, i.tipo, i.descripcion, i.estado, i.prioridad, i.fecha_apertura,
                   c.id_contenedor, c.calle, c.num_calle, c.estado AS estado_contenedor,
                   c.nivel_llenado, t.descripcion AS tipo_residuo
            FROM INCIDENCIA i
            LEFT JOIN INCIDENCIA_CONTENEDOR ic ON ic.id_incidencia = i.id_incidencia
            LEFT JOIN CONTENEDOR c ON c.id_contenedor = ic.id_contenedor
            LEFT JOIN TIPO_RESIDUO t ON t.id_tipo = c.id_tipo
            WHERE i.id_cuadrilla = ? AND i.estado IN ('asignada', 'en_curso')
            ORDER BY i.fecha_apertura ASC, c.id_contenedor ASC
        ");
        mysqli_stmt_bind_param($stmt, "i", $idCuadrilla);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $incidencias = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $id = $row['id_incidencia'];
            if (!isset($incidencias[$id])) {
                $incidencias[$id] = [
                    'id_incidencia' => $row['id_incidencia'],
                    'tipo' => $row['tipo'],
                    'descripcion' => $row['descripcion'],
                    'estado' => $row['estado'],
                    'prioridad' => $row['prioridad'],
                    'fecha_apertura' => $row['fecha_apertura'],
                    'contenedores' => [],
                ];
            }
            if ($row['id_contenedor'] !== null) {
                $incidencias[$id]['contenedores'][] = [
                    'id_contenedor' => $row['id_contenedor'],
                    'calle' => $row['calle'],
                    'num_calle' => $row['num_calle'],
                    'estado' => $row['estado_contenedor'],
                    'nivel_llenado' => (int) $row['nivel_llenado'],
                    'tipo_residuo' => $row['tipo_residuo'],
                ];
            }
        }
        mysqli_stmt_close($stmt);
        return array_values($incidencias);
    }

    public function iniciarAtencion($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE INCIDENCIA SET estado = 'en_curso' WHERE id_incidencia = ? AND estado = 'asignada'");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function asignarCuadrilla($id, $idCuadrilla) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE INCIDENCIA SET id_cuadrilla = ?, estado = 'asignada' WHERE id_incidencia = ?");
        mysqli_stmt_bind_param($stmt, "ii", $idCuadrilla, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    // Cierra $id y, en cascada, cualquier otra incidencia sin resolver que comparta
    // contenedor con ella (las que estaban ocultas por condicionSinDuplicadosAbiertos):
    // si se solucionó el contenedor, se solucionó el problema que reportó cada una.
    // A propósito no filtra por 'tipo': la cuadrilla va físicamente al contenedor y
    // atiende lo que encuentra ahí (esté roto, desbordado, etc.), así que una sola
    // visita cierra todas las incidencias pendientes de ese contenedor, sin importar
    // qué tipo reportó cada una (confirmado con el usuario 2026-08-28).
    // $id se resuelve siempre (el "OR i2.id_incidencia = ?"), incluso si por algún
    // motivo no llegó a vincularse a ningún contenedor.
    // ERR-07: $peso ("Total recolectado (Kg)") se registra en todas las incidencias que
    // cierra la cascada, porque corresponden a una misma visita/limpieza del contenedor.
    public function cerrar($id, $peso) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "UPDATE INCIDENCIA i2
             SET i2.estado = 'resuelta', i2.fecha_cierre = NOW(), i2.peso_recolectado = ?
             WHERE " . $this->condicionCascadaCierre());
        mysqli_stmt_bind_param($stmt, "dii", $peso, $id, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function incidenciasQueSeCerrarian($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(),
            "SELECT i2.id_incidencia, i2.ci_usuario, i2.tipo FROM INCIDENCIA i2
             WHERE " . $this->condicionCascadaCierre());
        mysqli_stmt_bind_param($stmt, "ii", $id, $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    private function condicionCascadaCierre() {
        return "i2.estado != 'resuelta'
               AND (
                   i2.id_incidencia = ?
                   OR i2.id_incidencia IN (
                       SELECT ic2.id_incidencia FROM INCIDENCIA_CONTENEDOR ic2
                       JOIN INCIDENCIA_CONTENEDOR ic1 ON ic1.id_contenedor = ic2.id_contenedor
                       WHERE ic1.id_incidencia = ?
                   )
               )";
    }

    public function cuadrillaExiste($idCuadrilla) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 1 FROM CUADRILLA WHERE id_cuadrilla = ?");
        mysqli_stmt_bind_param($stmt, "i", $idCuadrilla);
        mysqli_stmt_execute($stmt);
        $existe = mysqli_stmt_get_result($stmt)->fetch_assoc() !== null;
        mysqli_stmt_close($stmt);
        return $existe;
    }
}
?>
