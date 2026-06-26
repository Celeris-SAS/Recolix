<?php
    class CamionController {

        private $camionModel;

        public function __construct() {
            require_once '../Modelo/CamionModel.php';
            $this->camionModel = new CamionModel();
        }

        public function getAllCamiones() {
            return $this->camionModel->getAllCamiones();
        }

        public function getCamion($id) {
            return $this->camionModel->getCamion($id);
        }

        public function insertCamion($matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado) {
            return $this->camionModel->insertCamion($matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado);
        }

        public function updateCamion($id, $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado) {
            return $this->camionModel->updateCamion($id, $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado);
        }

        public function deleteCamion($id) {
            return $this->camionModel->bajaCamion($id);
        }

        public function esDisponible($id) {
            return $this->camionModel->esDisponible($id);
        }

        public function filtrarEstado($estado) {
            return $this->camionModel->filtrarEstado($estado);
        }

        public function filtrarTipoCarga($tipoCarga) {
            return $this->camionModel->filtrarTipoCarga($tipoCarga);
        }

        public function filtrarDisponibilidad() {
            return $this->camionModel->filtrarDisponibilidad();
        }

        public function filtrarMantenimiento() {
            return $this->camionModel->filtrarMantenimiento();
        }

        public function filtrarAnio($anio) {
            return $this->camionModel->filtrarAnio($anio);
        }

        public function filtrarModelo($modelo) {
            return $this->camionModel->filtrarModelo($modelo);
        }

        public function filtrarCapacidad($capacidad) {
            return $this->camionModel->filtrarCapacidad($capacidad);
        }

        public function obtenerHistorial($camionId) {
            return $this->camionModel->obtenerHistorial($camionId);
        }

    }
?>