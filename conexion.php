<?php
class conexion {
    private $host;
    private $user;
    private $pass;
    private $db;
    private $conn = null;

    public function __construct() {
        // Overrides por variable de entorno: solo las usa la suite de tests
        // (apunta a una base "recolix_test" separada). Sin esas env vars el
        // comportamiento es idéntico al de siempre.
        $this->host = getenv('RECOLIX_DB_HOST') ?: 'localhost';
        $this->user = getenv('RECOLIX_DB_USER') ?: 'root';
        $this->pass = getenv('RECOLIX_DB_PASS') ?: '';
        $this->db = getenv('RECOLIX_DB_NAME') ?: 'recolix';
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

