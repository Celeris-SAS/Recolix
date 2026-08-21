<?php
    class CuadrillaController {

        private $cuadrillaModel;
        private $estadosValidos = ['activa', 'inactiva'];

        public function __construct() {
            require_once __DIR__ . '/../Modelo/cuadrillaModel.php';
            $this->cuadrillaModel = new CuadrillaModel();
        }

        private function validarId($id) {
            $nombre = 'ID';
            if (!is_numeric($id) || intval($id) <= 0) {
                throw new InvalidArgumentException("$nombre debe ser un número entero positivo");
            }
            return intval($id);
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

        private function validarEntero($valor, $nombre) {
            if (!is_numeric($valor) || intval($valor) != $valor) {
                throw new InvalidArgumentException("$nombre debe ser un número entero");
            }
            return intval($valor);
        }

        private function validarIdMunicipio($valor) {
            $val = $this->validarEntero($valor, 'Municipio');
            if ($val < 1) {
                throw new InvalidArgumentException("Municipio debe ser un ID válido");
            }
            if (!$this->cuadrillaModel->existeMunicipio($val)) {
                throw new InvalidArgumentException("El municipio indicado no existe");
            }
            return $val;
        }

        public function reglasValidacion() {
            return [
                'idMunicipio' => [
                    'tipo' => 'entero',
                    'min' => 1,
                    'label' => 'Municipio'
                ],
                'estado' => [
                    'valores' => $this->estadosValidos,
                    'max' => 20,
                    'label' => 'Estado'
                ]
            ];
        }

        public function getAllCuadrillas() {
            return $this->cuadrillaModel->getAllCuadrillas();
        }

        public function getCuadrilla($id) {
            $id = $this->validarId($id);
            return $this->cuadrillaModel->getCuadrilla($id);
        }

        public function insertCuadrilla($idMunicipio, $estado) {
            $idMunicipio = $this->validarIdMunicipio($idMunicipio);
            $estado = $this->validarString($estado, 'Estado');
            if (!in_array($estado, $this->estadosValidos)) {
                throw new InvalidArgumentException("Estado no válido. Valores: " . implode(', ', $this->estadosValidos));
            }
            return $this->cuadrillaModel->insertCuadrilla($idMunicipio, $estado);
        }

        public function updateCuadrilla($id, $idMunicipio, $estado) {
            $id = $this->validarId($id);
            $idMunicipio = $this->validarIdMunicipio($idMunicipio);
            $estado = $this->validarString($estado, 'Estado');
            if (!in_array($estado, $this->estadosValidos)) {
                throw new InvalidArgumentException("Estado no válido. Valores: " . implode(', ', $this->estadosValidos));
            }
            return $this->cuadrillaModel->updateCuadrilla($id, $idMunicipio, $estado);
        }

        public function deleteCuadrilla($id) {
            $id = $this->validarId($id);
            return $this->cuadrillaModel->bajaCuadrilla($id);
        }

        public function esDisponible($id) {
            $id = $this->validarId($id);
            return $this->cuadrillaModel->esDisponible($id);
        }

        public function filtrarEstado($estado) {
            $estado = $this->validarString($estado, 'Estado');
            if (!in_array($estado, $this->estadosValidos)) {
                throw new InvalidArgumentException("Estado no válido. Valores: " . implode(', ', $this->estadosValidos));
            }
            return $this->cuadrillaModel->filtrarEstado($estado);
        }

        public function filtrarMunicipio($idMunicipio) {
            $idMunicipio = $this->validarEntero($idMunicipio, 'Municipio');
            return $this->cuadrillaModel->filtrarMunicipio($idMunicipio);
        }

        public function filtrarDisponibilidad() {
            return $this->cuadrillaModel->filtrarDisponibilidad();
        }

        public function obtenerHistorial($cuadrillaId) {
            $cuadrillaId = $this->validarId($cuadrillaId);
            return $this->cuadrillaModel->obtenerHistorial($cuadrillaId);
        }

    }
?>