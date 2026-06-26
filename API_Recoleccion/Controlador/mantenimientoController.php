<?php
    class MantenimientoController {

        private $mantenimientoModel;

        public function __construct() {
            require_once '../Modelo/MantenimientoModel.php';
            $this->mantenimientoModel = new MantenimientoModel();
        }

        public function getAllMantenimientos() {
            return $this->mantenimientoModel->getAllMantenimientos();
        }

        public function getMantenimiento($id) {
            return $this->mantenimientoModel->getMantenimiento($id);
        }

        public function insertMantenimiento($camionId, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo) {
            return $this->mantenimientoModel->registrar($camionId, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo);
        }

        public function updateMantenimiento($id, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo) {
            return $this->mantenimientoModel->actualizarMantenimiento($id, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo);
        }

        public function deleteMantenimiento($id) {
            return $this->mantenimientoModel->deleteMantenimiento($id);
        }

        public function programar($camionId, $descripcion, $fechaInicio) {
            return $this->mantenimientoModel->programar($camionId, $descripcion, $fechaInicio);
        }

        public function obtenerPorCamion($camionId) {
            return $this->mantenimientoModel->obtenerPorCamion($camionId);
        }

        public function filtrarPorTipo($tipo) {
            return $this->mantenimientoModel->filtrarPorTipo($tipo);
        }

        public function obtenerCostoTotal($camionId) {
            return $this->mantenimientoModel->obtenerCostoTotal($camionId);
        }

    }
?>