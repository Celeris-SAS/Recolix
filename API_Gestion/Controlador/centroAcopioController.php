<?php

class CentroAcopioController {

    private $centroModel;

    public function __construct() {
        require_once __DIR__ . '/../Modelo/centroAcopioModel.php';
        $this->centroModel = new CentroAcopioModel();
    }

    private function validarId($id) {
        if (!is_numeric($id) || intval($id) <= 0) {
            throw new InvalidArgumentException("El ID debe ser un número entero positivo.");
        }
        return intval($id);
    }

    public function getCentro($id) {
        $id = $this->validarId($id);
        return $this->centroModel->getCentro($id);
    }

    public function getAllCentros() {
        return $this->centroModel->getAllCentros();
    }
}
