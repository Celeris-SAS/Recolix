<?php
    class MantenimientoController {

        private $mantenimientoModel;
        private $tiposValidos = ['preventivo', 'correctivo'];

        public function __construct() {
            require_once __DIR__ . '/../Modelo/MantenimientoModel.php';
            $this->mantenimientoModel = new MantenimientoModel();
        }

        private function validarId($valor, $nombre = 'ID') {
            if (!is_numeric($valor) || intval($valor) <= 0) {
                throw new InvalidArgumentException("$nombre debe ser un número entero positivo");
            }
            return intval($valor);
        }

        private function validarString($valor, $nombre, $max = 255) {
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

        private function validarDecimal($valor, $nombre, $min = 0) {
            if (!is_numeric($valor)) {
                throw new InvalidArgumentException("$nombre debe ser un número");
            }
            $val = floatval($valor);
            if ($val < $min) {
                throw new InvalidArgumentException("$nombre no puede ser menor que $min");
            }
            return $val;
        }

        public function getAllMantenimientos() {
            return $this->mantenimientoModel->getAllMantenimientos();
        }

        public function getMantenimiento($id) {
            $id = $this->validarId($id, 'ID del mantenimiento');
            return $this->mantenimientoModel->getMantenimiento($id);
        }

        public function insertMantenimiento($camionId, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo) {
            $camionId = $this->validarId($camionId, 'ID del camión');
            $tipo = $this->validarString($tipo, 'Tipo', 20);
            if (!in_array($tipo, $this->tiposValidos)) {
                throw new InvalidArgumentException("Tipo no válido. Valores: " . implode(', ', $this->tiposValidos));
            }
            $descripcion = $this->validarString($descripcion, 'Descripción', 500);
            $fechaInicio = $this->validarFecha($fechaInicio, 'Fecha de inicio');
            $fechaFin = ($fechaFin !== null && trim($fechaFin) !== '')
                ? $this->validarFecha($fechaFin, 'Fecha de fin')
                : null;
            $costo = $this->validarDecimal($costo, 'Costo');
            return $this->mantenimientoModel->registrar($camionId, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo);
        }

        public function updateMantenimiento($id, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo) {
            $id = $this->validarId($id, 'ID del mantenimiento');
            $tipo = $this->validarString($tipo, 'Tipo', 20);
            if (!in_array($tipo, $this->tiposValidos)) {
                throw new InvalidArgumentException("Tipo no válido. Valores: " . implode(', ', $this->tiposValidos));
            }
            $descripcion = $this->validarString($descripcion, 'Descripción', 500);
            $fechaInicio = $this->validarFecha($fechaInicio, 'Fecha de inicio');
            $fechaFin = ($fechaFin !== null && trim($fechaFin) !== '')
                ? $this->validarFecha($fechaFin, 'Fecha de fin')
                : null;
            $costo = $this->validarDecimal($costo, 'Costo');
            return $this->mantenimientoModel->actualizarMantenimiento($id, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo);
        }

        public function deleteMantenimiento($id) {
            $id = $this->validarId($id, 'ID del mantenimiento');
            return $this->mantenimientoModel->deleteMantenimiento($id);
        }

        public function programar($camionId, $descripcion, $fechaInicio) {
            $camionId = $this->validarId($camionId, 'ID del camión');
            $descripcion = $this->validarString($descripcion, 'Descripción', 500);
            $fechaInicio = $this->validarFecha($fechaInicio, 'Fecha de inicio');
            return $this->mantenimientoModel->programar($camionId, $descripcion, $fechaInicio);
        }

        public function obtenerPorCamion($camionId) {
            $camionId = $this->validarId($camionId, 'ID del camión');
            return $this->mantenimientoModel->obtenerPorCamion($camionId);
        }

        public function filtrarPorTipo($tipo) {
            $tipo = $this->validarString($tipo, 'Tipo', 20);
            if (!in_array($tipo, $this->tiposValidos)) {
                throw new InvalidArgumentException("Tipo no válido. Valores: " . implode(', ', $this->tiposValidos));
            }
            return $this->mantenimientoModel->filtrarPorTipo($tipo);
        }

        public function obtenerCostoTotal($camionId) {
            $camionId = $this->validarId($camionId, 'ID del camión');
            return $this->mantenimientoModel->obtenerCostoTotal($camionId);
        }

    }
?>