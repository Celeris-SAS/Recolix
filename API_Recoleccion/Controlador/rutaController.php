<?php
    class RutaController {

        private $rutaModel;

        public function __construct() {
            require_once __DIR__ . '/../Modelo/rutaModel.php';
            $this->rutaModel = new RutaModel();
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

        private function validarEntero($valor, $nombre) {
            if (!is_numeric($valor) || intval($valor) != $valor) {
                throw new InvalidArgumentException("$nombre debe ser un número entero");
            }
            return intval($valor);
        }

        private function validarOrdenVisita($valor) {
            $min = 1;
            $val = $this->validarEntero($valor, 'Orden de visita');
            if ($val < $min) {
                throw new InvalidArgumentException("Orden de visita debe ser mayor o igual a $min");
            }
            return $val;
        }

        public function reglasValidacion() {
            return [
                'circuitoId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID del circuito'],
                'nombre' => ['max' => 100, 'label' => 'Nombre'],
                'actividad' => ['max' => 60, 'label' => 'Actividad'],
                'rutaId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID de la ruta'],
                'contenedorId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID del contenedor'],
                'ordenVisita' => ['tipo' => 'entero', 'min' => 1, 'label' => 'Orden de visita'],
                'cuadrillaId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID de la cuadrilla']
            ];
        }

        public function getAllRutas() {
            return $this->rutaModel->getAllRutas();
        }

        public function getRuta($id) {
            $id = $this->validarId($id);
            return $this->rutaModel->getRuta($id);
        }

        public function insertRuta($circuitoId, $nombre, $actividad) {
            $circuitoId = $this->validarId($circuitoId);
            $nombre = $this->validarString($nombre, 'Nombre');
            $actividad = $this->validarString($actividad, 'Actividad');
            return $this->rutaModel->insertRuta($circuitoId, $nombre, $actividad);
        }

        public function updateRuta($id, $circuitoId, $nombre, $actividad) {
            $id = $this->validarId($id);
            $circuitoId = $this->validarId($circuitoId);
            $nombre = $this->validarString($nombre, 'Nombre');
            $actividad = $this->validarString($actividad, 'Actividad');
            return $this->rutaModel->updateRuta($id, $circuitoId, $nombre, $actividad);
        }

        public function deleteRuta($id) {
            $id = $this->validarId($id);
            return $this->rutaModel->deleteRuta($id);
        }

        public function obtenerPorCircuito($circuitoId) {
            $circuitoId = $this->validarId($circuitoId);
            return $this->rutaModel->obtenerPorCircuito($circuitoId);
        }

        public function filtrarPorActividad($actividad) {
            $actividad = $this->validarString($actividad, 'Actividad');
            return $this->rutaModel->filtrarPorActividad($actividad);
        }

        public function filtrarPorNombre($nombre) {
            $nombre = $this->validarString($nombre, 'Nombre');
            return $this->rutaModel->filtrarPorNombre($nombre);
        }

        public function agregarContenedor($rutaId, $contenedorId, $ordenVisita) {
            $rutaId = $this->validarId($rutaId);
            $contenedorId = $this->validarId($contenedorId);
            $ordenVisita = $this->validarOrdenVisita($ordenVisita);
            return $this->rutaModel->agregarContenedor($rutaId, $contenedorId, $ordenVisita);
        }

        public function quitarContenedor($rutaId, $contenedorId) {
            $rutaId = $this->validarId($rutaId);
            $contenedorId = $this->validarId($contenedorId);
            return $this->rutaModel->quitarContenedor($rutaId, $contenedorId);
        }

        public function obtenerContenedores($rutaId) {
            $rutaId = $this->validarId($rutaId);
            return $this->rutaModel->obtenerContenedores($rutaId);
        }

        public function asignarCuadrilla($rutaId, $cuadrillaId) {
            $rutaId = $this->validarId($rutaId);
            $cuadrillaId = $this->validarId($cuadrillaId);
            return $this->rutaModel->asignarCuadrilla($rutaId, $cuadrillaId);
        }

        public function quitarCuadrilla($rutaId, $cuadrillaId) {
            $rutaId = $this->validarId($rutaId);
            $cuadrillaId = $this->validarId($cuadrillaId);
            return $this->rutaModel->quitarCuadrilla($rutaId, $cuadrillaId);
        }

        public function obtenerCuadrillas($rutaId) {
            $rutaId = $this->validarId($rutaId);
            return $this->rutaModel->obtenerCuadrillas($rutaId);
        }

        public function obtenerLevantes($rutaId) {
            $rutaId = $this->validarId($rutaId);
            return $this->rutaModel->obtenerLevantes($rutaId);
        }

    }
?>
