<?php
class conexion {
    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $db = 'recolix';
    private $conn = null;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);
        if ($this->conn->connect_errno) {
            throw new Exception('Error de conexión MySQL: ' . $this->conn->connect_error);
        }
        $this->conn->set_charset('utf8');
    }

    public function getConexion() {
        return $this->conn;
    }
}

