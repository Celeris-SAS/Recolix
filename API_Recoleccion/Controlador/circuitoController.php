<?php
    class CircuitoController {

        private $circuitoModel;
        private $turnosValidos = ['mañana', 'tarde', 'noche'];

        public function __construct() {
            require_once __DIR__ . '/../Modelo/circuitoModel.php';
            $this->circuitoModel = new CircuitoModel();
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

        public function reglasValidacion() {
            return [
                'municipioId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID del municipio'],
                'nombre' => ['max' => 100, 'label' => 'Nombre'],
                'codigo' => ['max' => 20, 'label' => 'Código'],
                'actividad' => ['max' => 60, 'label' => 'Actividad'],
                'turno' => ['valores' => $this->turnosValidos, 'label' => 'Turno'],
                'frecuencia' => ['max' => 40, 'label' => 'Frecuencia']
            ];
        }

        public function getAllCircuitos() {
            return $this->circuitoModel->getAllCircuitos();
        }

        public function getCircuito($id) {
            $id = $this->validarId($id);
            return $this->circuitoModel->getCircuito($id);
        }

        public function insertCircuito($municipioId, $nombre, $codigo, $actividad, $turno, $frecuencia) {
            $municipioId = $this->validarId($municipioId);
            $nombre = $this->validarString($nombre, 'Nombre');
            $codigo = $this->validarString($codigo, 'Código');
            $actividad = $this->validarString($actividad, 'Actividad');
            $turno = $this->validarString($turno, 'Turno');
            if (!in_array($turno, $this->turnosValidos)) {
                throw new InvalidArgumentException("Turno no válido. Valores: " . implode(', ', $this->turnosValidos));
            }
            $frecuencia = $this->validarString($frecuencia, 'Frecuencia');
            if ($this->circuitoModel->getCodigo($codigo)) {
                throw new InvalidArgumentException("Ya hay un circuito con el código " . $codigo . ".");
            }
            return $this->circuitoModel->insertCircuito($municipioId, $nombre, $codigo, $actividad, $turno, $frecuencia);
        }

        public function updateCircuito($id, $municipioId, $nombre, $codigo, $actividad, $turno, $frecuencia) {
            $id = $this->validarId($id);
            $municipioId = $this->validarId($municipioId);
            $nombre = $this->validarString($nombre, 'Nombre');
            $codigo = $this->validarString($codigo, 'Código');
            $actividad = $this->validarString($actividad, 'Actividad');
            $turno = $this->validarString($turno, 'Turno');
            if (!in_array($turno, $this->turnosValidos)) {
                throw new InvalidArgumentException("Turno no válido. Valores: " . implode(', ', $this->turnosValidos));
            }
            $frecuencia = $this->validarString($frecuencia, 'Frecuencia');
            return $this->circuitoModel->updateCircuito($id, $municipioId, $nombre, $codigo, $actividad, $turno, $frecuencia);
        }

        public function deleteCircuito($id) {
            $id = $this->validarId($id);
            return $this->circuitoModel->deleteCircuito($id);
        }

        public function obtenerPorMunicipio($municipioId) {
            $municipioId = $this->validarId($municipioId);
            return $this->circuitoModel->obtenerPorMunicipio($municipioId);
        }

        public function filtrarPorTurno($turno) {
            $turno = $this->validarString($turno, 'Turno');
            if (!in_array($turno, $this->turnosValidos)) {
                throw new InvalidArgumentException("Turno no válido. Valores: " . implode(', ', $this->turnosValidos));
            }
            return $this->circuitoModel->filtrarPorTurno($turno);
        }

        public function filtrarPorActividad($actividad) {
            $actividad = $this->validarString($actividad, 'Actividad');
            return $this->circuitoModel->filtrarPorActividad($actividad);
        }

        public function filtrarPorFrecuencia($frecuencia) {
            $frecuencia = $this->validarString($frecuencia, 'Frecuencia');
            return $this->circuitoModel->filtrarPorFrecuencia($frecuencia);
        }

        public function obtenerRutas($circuitoId) {
            $circuitoId = $this->validarId($circuitoId);
            return $this->circuitoModel->obtenerRutas($circuitoId);
        }

        public function obtenerContenedores($circuitoId) {
            $circuitoId = $this->validarId($circuitoId);
            return $this->circuitoModel->obtenerContenedores($circuitoId);
        }

    }
?>
