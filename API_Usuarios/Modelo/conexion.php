<?php

class conexion {

    private $host = 'localhost';
    private $db   = 'recolix';
    private $user = 'root';
    private $pass = '';
    private $conn;

    public function __construct() {
        $this->conn = mysqli_connect($this->host, $this->user, $this->pass, $this->db);

        if (!$this->conn) {
            throw new Exception('Error de conexión con la base de datos: ' . mysqli_connect_error());
        }

        mysqli_set_charset($this->conn, 'utf8mb4');
    }

    public function getConexion() {
        return $this->conn;
    }
}
?>