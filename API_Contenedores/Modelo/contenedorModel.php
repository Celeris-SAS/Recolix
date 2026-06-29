<?php

require_once 'conexion.php';

class contenedorModel {

    protected $Id = "";
    protected $IdCircuito = "";
    protected $IdTipoResiduo = "";
    protected $Calle = "";
    protected $NumCalle = "";
    protected $Latitud = 0.0;
    protected $Longitud = 0.0;
    protected $Capacidad = 0;
    protected $NivelLlenado = 0;
    protected $Estado = "";
    protected $FechaReparacion = null;
    protected $conn;

    public function __construct($Id = null, $IdCircuito = null, $IdTipoResiduo = null, $Calle = "", $NumCalle = "", $Latitud = 0.0, $Longitud = 0.0, $Capacidad = 0, $NivelLlenado = 0, $Estado = "funcional", $FechaReparacion = null) {
        $this->Id = $Id;
        $this->IdCircuito = $IdCircuito;
        $this->IdTipoResiduo = $IdTipoResiduo;
        $this->Calle = $Calle;
        $this->NumCalle = $NumCalle;
        $this->Latitud = $Latitud;
        $this->Longitud = $Longitud;
        $this->Capacidad = $Capacidad;
        $this->NivelLlenado = $NivelLlenado;
        $this->Estado = $Estado;
        $this->FechaReparacion = $FechaReparacion;
        $this->conn = new conexion();
    }
    // ==========================================
    // GETTERS
    // ==========================================
    
    public function getId() { 
        return $this->Id; 
    }
    
    public function getIdCircuito() { 
        return $this->IdCircuito; 
    }
    
    public function getIdTipoResiduo() { 
        return $this->IdTipoResiduo; 
    }
    
    public function getCalle() { 
        return $this->Calle; 
    }
    
    public function getNumCalle() { 
        return $this->NumCalle; 
    }
    
    public function getLatitud() { 
        return $this->Latitud; 
    }
    
    public function getLongitud() { 
        return $this->Longitud; 
    }
    
    public function getCapacidad() { 
        return $this->Capacidad; 
    }
    
    public function getNivelLlenado() { 
        return $this->NivelLlenado; 
    }
    
    public function getEstado() { 
        return $this->Estado; 
    }
    
    public function getFechaReparacion() { 
        return $this->FechaReparacion; 
    }

    // ==========================================
    // SETTERS (Modificar atributos)
    // ==========================================
    
    public function setIdCircuito($IdCircuito) { 
        $this->IdCircuito = $IdCircuito; 
    }
    
    public function setIdTipoResiduo($IdTipoResiduo) { 
        $this->IdTipoResiduo = $IdTipoResiduo; 
    }
    
    public function setCalle($Calle) { 
        $this->Calle = $Calle; 
    }
    
    public function setNumCalle($NumCalle) { 
        $this->NumCalle = $NumCalle; 
    }
    
    public function setLatitud($Latitud) { 
        $this->Latitud = $Latitud; 
    }
    
    public function setLongitud($Longitud) { 
        $this->Longitud = $Longitud; 
    }
    
    public function setCapacidad($Capacidad) { 
        $this->Capacidad = $Capacidad; 
    }
    
    public function __toString() {
        return "Contenedor [Id: " . $this->Id . 
               ", Circuito: " . $this->IdCircuito . 
               ", Residuo: " . $this->IdTipoResiduo . 
               ", Ubicación: " . $this->Calle . " " . $this->NumCalle . 
               ", Capacidad: " . $this->Capacidad . "L" . 
               ", Estado: " . $this->Estado . 
               ", Nivel: " . $this->NivelLlenado . "%]";
    }

    // ==========================================
    // ACCIONES PRINCIPALES (CRUD)
    // ==========================================

    public function altaContenedor($id, $idCircuito, $idTipoResiduo, $calle, $numCalle, $latitud, $longitud, $capacidad, $estado) {
        $stmt = mysqli_prepare(
            $this->conn->getConexion(), 
            "INSERT INTO contenedores (Id, IdCircuito, IdTipoResiduo, Calle, NumCalle, Latitud, Longitud, Capacidad, Estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "iiissddis", $id, $idCircuito, $idTipoResiduo, $calle, $numCalle, $latitud, $longitud, $capacidad, $estado);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function modificarContenedor($id, $calle, $numCalle, $latitud, $longitud, $capacidad) {
        $stmt = mysqli_prepare(
            $this->conn->getConexion(), 
            "UPDATE contenedores SET Calle = ?, NumCalle = ?, Latitud = ?, Longitud = ?, Capacidad = ? WHERE Id = ?"
        );
        mysqli_stmt_bind_param($stmt, "ssddii", $calle, $numCalle, $latitud, $longitud, $capacidad, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function bajaContenedor($id) {
        $stmt = mysqli_prepare(
            $this->conn->getConexion(), 
            "UPDATE contenedores SET Estado = 'inactivo' WHERE Id = ?"
        );
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // ==========================================
    // REGLAS DE NEGOCIO Y OPERACIONES
    // ==========================================

    public function cambiarEstado($nuevoEstado) {
        $estadosValidos = ["funcional", "roto", "desbordado", "inactivo", "en depósito"];
        
        if (in_array($nuevoEstado, $estadosValidos)) {
            $this->Estado = $nuevoEstado;
            
            $stmt = mysqli_prepare(
                $this->conn->getConexion(), 
                "UPDATE contenedores SET Estado = ? WHERE Id = ?"
            );
            mysqli_stmt_bind_param($stmt, "si", $this->Estado, $this->Id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        } else {
            throw new Exception("Estado no válido. Opciones: " . implode(", ", $estadosValidos));
        }
    }

    public function registrarReparacion() {
        $this->Estado = "funcional";
        $this->FechaReparacion = date("Y-m-d H:i:s");
        
        $stmt = mysqli_prepare(
            $this->conn->getConexion(), 
            "UPDATE contenedores SET Estado = ?, FechaReparacion = ? WHERE Id = ?"
        );
        mysqli_stmt_bind_param($stmt, "ssi", $this->Estado, $this->FechaReparacion, $this->Id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function clasificarPorTipoResiduo($idTipoResiduo) {
        $this->IdTipoResiduo = $idTipoResiduo;
        
        $stmt = mysqli_prepare(
            $this->conn->getConexion(), 
            "UPDATE contenedores SET IdTipoResiduo = ? WHERE Id = ?"
        );
        mysqli_stmt_bind_param($stmt, "ii", $this->IdTipoResiduo, $this->Id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function asignarACircuito($idCircuito) {
        $this->IdCircuito = $idCircuito;
        
        $stmt = mysqli_prepare(
            $this->conn->getConexion(), 
            "UPDATE contenedores SET IdCircuito = ? WHERE Id = ?"
        );
        mysqli_stmt_bind_param($stmt, "ii", $this->IdCircuito, $this->Id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    public function consultarNivelLlenado() {
        $stmt = mysqli_prepare(
            $this->conn->getConexion(), 
            "SELECT NivelEncontrado FROM levantes WHERE IdContenedor = ? ORDER BY `Fecha Levante` DESC LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "i", $this->Id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $nivelLlenado);
        
        if (mysqli_stmt_fetch($stmt)) {
            $this->NivelLlenado = $nivelLlenado;
        } else {
            $this->NivelLlenado = 0; 
        }
        
        mysqli_stmt_close($stmt);
        return $this->NivelLlenado;
    }

    public function obtenerHistorial() {
        $stmt = mysqli_prepare(
            $this->conn->getConexion(), 
            "SELECT * FROM eventos_contenedor WHERE IdContenedor = ? ORDER BY Fecha DESC"
        );
        mysqli_stmt_bind_param($stmt, "i", $this->Id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $historial = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $historial[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $historial;
    }

    // ==========================================
    // LISTADOS GENERALES
    // ==========================================

    public function listarContenedores() {
        $stmt = mysqli_prepare(
            $this->conn->getConexion(), 
            "SELECT * FROM contenedores"
        );
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $contenedores = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $contenedores[] = new ContenedorModel(
                $row['Id'], 
                $row['IdCircuito'], 
                $row['IdTipoResiduo'], 
                $row['Calle'], 
                $row['NumCalle'], 
                $row['Latitud'], 
                $row['Longitud'], 
                $row['Capacidad'], 
                $row['NivelLlenado'], 
                $row['Estado'], 
                $row['FechaReparacion']
            );
        }
        
        mysqli_stmt_close($stmt);
        return $contenedores;
    }
}
?>
