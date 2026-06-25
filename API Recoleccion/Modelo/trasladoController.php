<?php
    class TrasladoController {

        private $trasladoModel;

        public function __construct() {
            require_once 'TrasladoModel.php';
            $this->trasladoModel = new TrasladoModel();
        }

        public function getAllTrasladosResiduos() {
            return $this->trasladoModel->getAllTrasladosResiduos();
        }

        public function getTrasladoResiduo($id) {
            return $this->trasladoModel->getTrasladoResiduo($id);
        }

        public function insertTrasladoResiduo($camionId, $centroId, $peso, $fechaIngreso) {
            return $this->trasladoModel->insertTrasladoResiduo($camionId, $centroId, $peso, $fechaIngreso);
        }

        public function updateTrasladoResiduo($id, $camionId, $centroId, $peso, $fechaIngreso) {
            return $this->trasladoModel->updateTrasladoResiduo($id, $camionId, $centroId, $peso, $fechaIngreso);
        }

        public function deleteTrasladoResiduo($id) {
            return $this->trasladoModel->deleteTrasladoResiduo($id);
        }

        public function obtenerPorCamion($camionId) {
            return $this->trasladoModel->obtenerPorCamion($camionId);
        }

        public function obtenerPorCentro($centroId) {
            return $this->trasladoModel->obtenerPorCentro($centroId);
        }

        public function filtrarPorFecha($fecha) {
            return $this->trasladoModel->filtrarPorFecha($fecha);
        }

        public function obtenerPesoTotal($camionId) {
            return $this->trasladoModel->obtenerPesoTotal($camionId);
        }

    }
?>
