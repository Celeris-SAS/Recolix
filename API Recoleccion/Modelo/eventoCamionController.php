<?php
    class EventoCamionController {

        private $eventoCamionModel;

        public function __construct() {
            require_once 'EventoCamionModel.php';
            $this->eventoCamionModel = new EventoCamionModel();
        }

        public function getAllEventosCamion() {
            return $this->eventoCamionModel->getAllEventosCamion();
        }

        public function getEventoCamion($id) {
            return $this->eventoCamionModel->getEventoCamion($id);
        }

        public function insertEventoCamion($camionId, $tipoEvento, $fecha, $descripcion) {
            return $this->eventoCamionModel->insertEventoCamion($camionId, $tipoEvento, $fecha, $descripcion);
        }

        public function updateEventoCamion($id, $camionId, $tipoEvento, $fecha, $descripcion) {
            return $this->eventoCamionModel->updateEventoCamion($id, $camionId, $tipoEvento, $fecha, $descripcion);
        }

        public function deleteEventoCamion($id) {
            return $this->eventoCamionModel->deleteEventoCamion($id);
        }

        public function registrarEvento($camionId, $tipoEvento, $detalle, $fecha) {
            return $this->eventoCamionModel->registrarEvento($camionId, $tipoEvento, $detalle, $fecha);
        }

        public function obtenerPorCamion($camionId) {
            return $this->eventoCamionModel->obtenerPorCamion($camionId);
        }

        public function listarEventosCamion() {
            return $this->eventoCamionModel->listarEventosCamion();
        }

        public function filtrarPorTipo($tipoEvento) {
            return $this->eventoCamionModel->filtrarPorTipo($tipoEvento);
        }

        public function obtenerEventosPorFecha($fecha) {
            return $this->eventoCamionModel->obtenerEventosPorFecha($fecha);
        }

    }
?>
