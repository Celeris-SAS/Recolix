<?php
    class TrasladoController {

        private $trasladoModel;

        public function __construct() {
            require_once __DIR__ . '/../Modelo/TrasladoModel.php';
            $this->trasladoModel = new TrasladoModel();
        }

        private function validarId($valor, $nombre = 'ID') {
            if (!is_numeric($valor) || intval($valor) <= 0) {
                throw new InvalidArgumentException("$nombre debe ser un número entero positivo");
            }
            return intval($valor);
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

        public function getAllTrasladosResiduos() {
            return $this->trasladoModel->getAllTrasladosResiduos();
        }

        public function getTrasladoResiduo($id) {
            $id = $this->validarId($id, 'ID del traslado');
            return $this->trasladoModel->getTrasladoResiduo($id);
        }

        public function insertTrasladoResiduo($camionId, $centroId, $peso, $fechaIngreso) {
            $camionId = $this->validarId($camionId, 'ID del camión');
            $centroId = $this->validarId($centroId, 'ID del centro');
            $peso = $this->validarDecimal($peso, 'Peso', 0.001);
            $fechaIngreso = $this->validarFecha($fechaIngreso, 'Fecha de ingreso');
            return $this->trasladoModel->insertTrasladoResiduo($camionId, $centroId, $peso, $fechaIngreso);
        }

        public function updateTrasladoResiduo($id, $camionId, $centroId, $peso, $fechaIngreso) {
            $id = $this->validarId($id, 'ID del traslado');
            $camionId = $this->validarId($camionId, 'ID del camión');
            $centroId = $this->validarId($centroId, 'ID del centro');
            $peso = $this->validarDecimal($peso, 'Peso', 0.001);
            $fechaIngreso = $this->validarFecha($fechaIngreso, 'Fecha de ingreso');
            return $this->trasladoModel->updateTrasladoResiduo($id, $camionId, $centroId, $peso, $fechaIngreso);
        }

        public function deleteTrasladoResiduo($id) {
            $id = $this->validarId($id, 'ID del traslado');
            return $this->trasladoModel->deleteTrasladoResiduo($id);
        }

        public function obtenerPorCamion($camionId) {
            $camionId = $this->validarId($camionId, 'ID del camión');
            return $this->trasladoModel->obtenerPorCamion($camionId);
        }

        public function obtenerPorCentro($centroId) {
            $centroId = $this->validarId($centroId, 'ID del centro');
            return $this->trasladoModel->obtenerPorCentro($centroId);
        }

        public function filtrarPorFecha($fecha) {
            $fecha = $this->validarFecha($fecha, 'Fecha');
            return $this->trasladoModel->filtrarPorFecha($fecha);
        }

        public function obtenerPesoTotal($camionId) {
            $camionId = $this->validarId($camionId, 'ID del camión');
            return $this->trasladoModel->obtenerPesoTotal($camionId);
        }

    }
?>
