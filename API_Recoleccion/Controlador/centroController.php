<?php
    class CentroController {

        private $centroModel;

        public function __construct() {
            require_once __DIR__ . '/../Modelo/centroModel.php';
            $this->centroModel = new CentroModel();
        }

        private function validarId($valor) {
            if (!is_numeric($valor) || intval($valor) <= 0) {
                throw new InvalidArgumentException("ID debe ser un número entero positivo");
            }
            return intval($valor);
        }

        public function getAllCentros() {
            return $this->centroModel->getAllCentros();
        }

        public function getCentro($id) {
            $id = $this->validarId($id);
            return $this->centroModel->getCentro($id);
        }

        public function filtrarOperativos() {
            return $this->centroModel->filtrarOperativos();
        }

    }
?>
