<?php
    class EventoCamionController {

        private $eventoCamionModel;

        public function __construct() {
            require_once __DIR__ . '/../Modelo/EventoCamionModel.php';
            $this->eventoCamionModel = new EventoCamionModel();
        }

        private function validarId($valor) {
            $nombre = 'ID';
            if (!is_numeric($valor) || intval($valor) <= 0) {
                throw new InvalidArgumentException("$nombre debe ser un número entero positivo");
            }
            return intval($valor);
        }

        private function validarString($valor, $nombre) {
            $max = 255;
            if (!is_string($valor) || trim($valor) === '') {
                throw new InvalidArgumentException("$nombre debe ser un texto válido");
            }
            if (strlen(trim($valor)) > $max) {
                throw new InvalidArgumentException("$nombre no puede superar los $max caracteres");
            }
            return trim($valor);
        }

        private function validarFecha($valor, $nombre) {
            if (!is_string($valor) || trim($valor) === '') {
                throw new InvalidArgumentException("$nombre debe ser una fecha válida (YYYY-MM-DD)");
            }
            $fecha = trim($valor);
            $formato = 'Y-m-d';
            $d = DateTime::createFromFormat($formato, $fecha);
            if (!$d || $d->format($formato) !== $fecha) {
                throw new InvalidArgumentException("$nombre debe tener el formato YYYY-MM-DD");
            }
            return $fecha;
        }

        public function reglasValidacion() {
            return [
                'camionId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID del camión'],
                'tipoEvento' => ['max' => 50, 'label' => 'Tipo de evento'],
                'fecha' => ['tipo' => 'fecha', 'pattern' => '\\d{4}-\\d{2}-\\d{2}', 'label' => 'Fecha', 'mensaje' => 'Debe tener formato YYYY-MM-DD'],
                'descripcion' => ['max' => 500, 'label' => 'Descripción']
            ];
        }

        public function getAllEventosCamion() {
            return $this->eventoCamionModel->getAllEventosCamion();
        }

        public function getEventoCamion($id) {
            $id = $this->validarId($id);
            return $this->eventoCamionModel->getEventoCamion($id);
        }

        public function insertEventoCamion($camionId, $tipoEvento, $fecha, $descripcion) {
            $camionId = $this->validarId($camionId);
            $tipoEvento = $this->validarString($tipoEvento, 'Tipo de evento');
            $fecha = $this->validarFecha($fecha, 'Fecha');
            $descripcion = $this->validarString($descripcion, 'Descripción');
            return $this->eventoCamionModel->registrarEvento($camionId, $tipoEvento, $descripcion, $fecha);
        }

        public function updateEventoCamion($id, $camionId, $tipoEvento, $fecha, $descripcion) {
            $id = $this->validarId($id);
            $camionId = $this->validarId($camionId);
            $tipoEvento = $this->validarString($tipoEvento, 'Tipo de evento');
            $fecha = $this->validarFecha($fecha, 'Fecha');
            $descripcion = $this->validarString($descripcion, 'Descripción');
            return $this->eventoCamionModel->updateEventoCamion($id, $camionId, $tipoEvento, $fecha, $descripcion);
        }

        public function deleteEventoCamion($id) {
            $id = $this->validarId($id);
            return $this->eventoCamionModel->deleteEventoCamion($id);
        }

        public function obtenerPorCamion($camionId) {
            $camionId = $this->validarId($camionId);
            return $this->eventoCamionModel->obtenerPorCamion($camionId);
        }

        public function filtrarPorTipo($tipoEvento) {
            $tipoEvento = $this->validarString($tipoEvento, 'Tipo de evento');
            return $this->eventoCamionModel->filtrarPorTipo($tipoEvento);
        }

        public function obtenerEventosPorFecha($fecha) {
            $fecha = $this->validarFecha($fecha, 'Fecha');
            return $this->eventoCamionModel->obtenerEventosPorFecha($fecha);
        }

    }
?>
