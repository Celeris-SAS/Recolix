
<?php
require_once __DIR__ . '/conexion.php';

class ContenedorModel {

    protected $id;
    protected $idCircuito;
    protected $idTipo;
    protected $calle;
    protected $numCalle;
    protected $latitud;
    protected $longitud;
    protected $capacidad;
    protected $nivelLlenado;
    protected $estado;
    protected $fechaReparacion;
    protected $conn;

    public function __construct($idCircuito, $idTipo, $calle, $numCalle, $latitud, $longitud, $capacidad, $estado, $fechaReparacion) {
        $this->idCircuito      = $idCircuito;
        $this->idTipo          = $idTipo;
        $this->calle           = $calle;
        $this->numCalle        = $numCalle;
        $this->latitud         = $latitud;
        $this->longitud        = $longitud;
        $this->capacidad       = $capacidad;
        $this->estado          = $estado;
        $this->fechaReparacion = $fechaReparacion;
        $this->conn            = new conexion();
        $this->id              = $this->insertContenedor($idCircuito, $idTipo, $calle, $numCalle, $latitud, $longitud, $capacidad, $estado);
    }

    // ==========================================
    // GETTERS
    // ==========================================

    public function getId() {
        return $this->id;
    }

    public function getIdCircuito() {
        return $this->idCircuito;
    }

    public function getIdTipo() {
        return $this->idTipo;
    }

    public function getCalle() {
        return $this->calle;
    }

    public function getNumCalle() {
        return $this->numCalle;
    }

    public function getLatitud() {
        return $this->latitud;
    }

    public function getLongitud() {
        return $this->longitud;
    }

    public function getCapacidad() {
        return $this->capacidad;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getFechaReparacion() {
        return $this->fechaReparacion;
    }
    public function getNivelLlenado() {
    return $this->nivelLlenado;
    }

    // ==========================================
    // SETTERS
    // ==========================================

    public function setIdCircuito($idCircuito) {
        $this->idCircuito = $idCircuito;
    }

    public function setIdTipo($idTipo) {
        $this->idTipo = $idTipo;
    }

    public function setCalle($calle) {
        $this->calle = $calle;
    }

    public function setNumCalle($numCalle) {
        $this->numCalle = $numCalle;
    }

    public function setLatitud($latitud) {
        $this->latitud = $latitud;
    }

    public function setLongitud($longitud) {
        $this->longitud = $longitud;
    }

    public function setCapacidad($capacidad) {
        $this->capacidad = $capacidad;
    }

    public function setEstado($estado) {
        $this->estado = $estado;
    }

    public function setFechaReparacion($fechaReparacion) {
        $this->fechaReparacion = $fechaReparacion;
    }
    public function setNivelLlenado($nivelLlenado) {
    $this->nivelLlenado = $nivelLlenado;
    }

    // ==========================================
    // CRUD PRINCIPAL
    // ==========================================

    public function insertContenedor($idCircuito, $idTipo, $calle, $numCalle, $latitud, $longitud, $capacidad, $estado) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "INSERT INTO CONTENEDOR (id_circuito, id_tipo, calle, num_calle, latitud, longitud, capacidad, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iissddds", $idCircuito, $idTipo, $calle, $numCalle, $latitud, $longitud, $capacidad, $estado);
        mysqli_stmt_execute($stmt);
        $id = mysqli_insert_id($this->conn->getConexion());
        mysqli_stmt_close($stmt);
        return $id;
    }

    public function updateContenedor($id, $idCircuito, $idTipo, $calle, $numCalle, $latitud, $longitud, $capacidad) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE CONTENEDOR SET id_circuito = ?, id_tipo = ?, calle = ?, num_calle = ?, latitud = ?, longitud = ?, capacidad = ? WHERE id_contenedor = ?");
        mysqli_stmt_bind_param($stmt, "iissdddi", $idCircuito, $idTipo, $calle, $numCalle, $latitud, $longitud, $capacidad, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function bajaContenedor($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE CONTENEDOR SET estado = 'inactivo' WHERE id_contenedor = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function getContenedor($id) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM CONTENEDOR WHERE id_contenedor = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $row;
    }

    public function getAllContenedores() {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM CONTENEDOR");
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
    // OPERACIONES DE NEGOCIO
    // ==========================================

    public function cambiarEstado($id, $nuevoEstado) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE CONTENEDOR SET estado = ? WHERE id_contenedor = ?");
        mysqli_stmt_bind_param($stmt, "si", $nuevoEstado, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function clasificarPorTipoResiduo($id, $idTipo) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE CONTENEDOR SET id_tipo = ? WHERE id_contenedor = ?");
        mysqli_stmt_bind_param($stmt, "ii", $idTipo, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function asignarACircuito($id, $idCircuito) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE CONTENEDOR SET id_circuito = ? WHERE id_contenedor = ?");
        mysqli_stmt_bind_param($stmt, "ii", $idCircuito, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function registrarReparacion($id) {
        $fechaHoy = date("Y-m-d");
        $stmt = mysqli_prepare($this->conn->getConexion(), "UPDATE CONTENEDOR SET estado = 'funcional', fecha_reparacion = ? WHERE id_contenedor = ?");
        mysqli_stmt_bind_param($stmt, "si", $fechaHoy, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;
    }

    public function consultarNivelLlenado($id) {
    $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT cl.peso, c.capacidad FROM CONTENEDOR_LEVANTE cl JOIN LEVANTE l ON cl.id_levante = l.id_levante JOIN CONTENEDOR c ON cl.id_contenedor = c.id_contenedor WHERE cl.id_contenedor = ? ORDER BY l.hora_levante DESC LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
   }

    public function getHistorial($id) {
        $historial = [];

        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 'incidencia' AS tipo_evento, i.tipo AS descripcion, i.fecha_apertura AS fecha FROM INCIDENCIA i JOIN INCIDENCIA_CONTENEDOR ic ON i.id_incidencia = ic.id_incidencia WHERE ic.id_contenedor = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) 
        {
            $historial[] = $row; 
        }
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 'levante' AS tipo_evento, cl.estado_encontrado AS descripcion, l.hora_levante AS fecha FROM CONTENEDOR_LEVANTE cl JOIN LEVANTE l ON cl.id_levante = l.id_levante WHERE cl.id_contenedor = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result))
            { 
                $historial[] = $row;
            }
        mysqli_stmt_close($stmt);

        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT 'reparacion' AS tipo_evento, 'Reparación registrada' AS descripcion, fecha_reparacion AS fecha FROM CONTENEDOR WHERE id_contenedor = ? AND fecha_reparacion IS NOT NULL");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result))
            { 
                $historial[] = $row; 
            }
        mysqli_stmt_close($stmt);

        return $historial;
    }

    public function filtrarMunicipio($idMunicipio) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT c.* FROM CONTENEDOR c JOIN CIRCUITO ci ON c.id_circuito = ci.id_circuito WHERE ci.id_municipio = ?");
        mysqli_stmt_bind_param($stmt, "i", $idMunicipio);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows   = [];
        while ($row = mysqli_fetch_assoc($result))
            { 
                $rows[] = $row;
            }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function filtrarTipoResiduo($idTipo) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM CONTENEDOR WHERE id_tipo = ?");
        mysqli_stmt_bind_param($stmt, "i", $idTipo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows   = [];
        while ($row = mysqli_fetch_assoc($result)) 
        { 
            $rows[] = $row; 
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }

    public function filtrarEstado($estado) {
        $stmt = mysqli_prepare($this->conn->getConexion(), "SELECT * FROM CONTENEDOR WHERE estado = ?");
        mysqli_stmt_bind_param($stmt, "s", $estado);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $rows   = [];
        while ($row = mysqli_fetch_assoc($result)) 
        { 
            $rows[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }
}
?>
