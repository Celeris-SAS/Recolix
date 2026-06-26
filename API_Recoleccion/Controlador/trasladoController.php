<?php
    class TrasladoController {

        private $trasladoModel;

        public function __construct() {
            require_once __DIR__ . '/../Modelo/TrasladoModel.php';
            $this->trasladoModel = new TrasladoModel();
        }

        private function validarId($valor) {
            $nombre = 'ID';
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

        private function validarDecimal($valor, $nombre) {
            $min = 0;
            if (!is_numeric($valor)) {
                throw new InvalidArgumentException("$nombre debe ser un número");
            }
            $val = floatval($valor);
            if ($val < $min) {
                throw new InvalidArgumentException("$nombre no puede ser menor que $min");
            }
            return $val;
        }

        public function reglasValidacion() {
            return [
                'camionId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID del camión'],
                'centroId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID del centro'],
                'peso' => ['tipo' => 'decimal', 'min' => 0.001, 'label' => 'Peso'],
                'fechaIngreso' => ['tipo' => 'fecha', 'pattern' => '\\d{4}-\\d{2}-\\d{2}', 'label' => 'Fecha de ingreso', 'mensaje' => 'Debe tener formato YYYY-MM-DD']
            ];
        }

        public function getAllTrasladosResiduos() {
            return $this->trasladoModel->getAllTrasladosResiduos();
        }

        public function getTrasladoResiduo($id) {
            $id = $this->validarId($id);
            return $this->trasladoModel->getTrasladoResiduo($id);
        }

        public function insertTrasladoResiduo($camionId, $centroId, $peso, $fechaIngreso) {
            $camionId = $this->validarId($camionId);
            $centroId = $this->validarId($centroId);
            $peso = $this->validarDecimal($peso, 'Peso');
            $fechaIngreso = $this->validarFecha($fechaIngreso, 'Fecha de ingreso');
            return $this->trasladoModel->insertTrasladoResiduo($camionId, $centroId, $peso, $fechaIngreso);
        }

        public function updateTrasladoResiduo($id, $camionId, $centroId, $peso, $fechaIngreso) {
            $id = $this->validarId($id);
            $camionId = $this->validarId($camionId);
            $centroId = $this->validarId($centroId);
            $peso = $this->validarDecimal($peso, 'Peso');
            $fechaIngreso = $this->validarFecha($fechaIngreso, 'Fecha de ingreso');
            return $this->trasladoModel->updateTrasladoResiduo($id, $camionId, $centroId, $peso, $fechaIngreso);
        }

        public function deleteTrasladoResiduo($id) {
            $id = $this->validarId($id);
            return $this->trasladoModel->deleteTrasladoResiduo($id);
        }

        public function obtenerPorCamion($camionId) {
            $camionId = $this->validarId($camionId);
            return $this->trasladoModel->obtenerPorCamion($camionId);
        }

        public function obtenerPorCentro($centroId) {
            $centroId = $this->validarId($centroId);
            return $this->trasladoModel->obtenerPorCentro($centroId);
        }

        public function filtrarPorFecha($fecha) {
            $fecha = $this->validarFecha($fecha, 'Fecha');
            return $this->trasladoModel->filtrarPorFecha($fecha);
        }

        public function obtenerPesoTotal($camionId) {
            $camionId = $this->validarId($camionId);
            return $this->trasladoModel->obtenerPesoTotal($camionId);
        }

    }
?>
