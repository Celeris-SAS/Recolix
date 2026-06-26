<?php
// Archivo de conexión simple (ajustar credenciales según entorno)
class conexion {
    private $host = '127.0.0.1';
    private $user = 'root';
    private $pass = '';
    private $db = 'prueba';
    private $conn = null;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);
        if ($this->conn->connect_errno) {
            // No detener la ejecución con die; lanzar excepción para que el app la maneje
            throw new Exception('Error de conexión MySQL: ' . $this->conn->connect_error);
        }
        $this->conn->set_charset('utf8');
    }

    public function getConexion() {
        return $this->conn;
    }
}

