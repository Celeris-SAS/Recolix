<?php
    class LevanteController {

        private $levanteModel;

        public function __construct() {
            require_once __DIR__ . '/../Modelo/levanteModel.php';
            $this->levanteModel = new LevanteModel();
        }

        private function validarId($valor) {
            if (!is_numeric($valor) || intval($valor) <= 0) {
                throw new InvalidArgumentException("ID debe ser un número entero positivo");
            }
            return intval($valor);
        }

        private function validarString($valor, $nombre) {
            $max = 150;
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

        private function validarPeso($valor) {
            if ($valor === null || $valor === '') {
                return null;
            }
            $peso = $this->validarEntero($valor, 'Peso');
            if ($peso < 0) {
                throw new InvalidArgumentException("Peso no puede ser negativo");
            }
            return $peso;
        }

        private function validarMotivo($valor) {
            if ($valor === null || $valor === '') {
                return null;
            }
            return $this->validarString($valor, 'Motivo');
        }

        public function reglasValidacion() {
            return [
                'rutaId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID de la ruta'],
                'camionId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID del camión'],
                'cuadrillaId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID de la cuadrilla'],
                'horaSalida' => ['tipo' => 'texto', 'label' => 'Hora de salida'],
                'horaLevante' => ['tipo' => 'texto', 'label' => 'Fecha del levante'],
                'contenedorId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID del contenedor'],
                'estadoEncontrado' => ['max' => 30, 'label' => 'Estado encontrado'],
                'peso' => ['tipo' => 'entero', 'min' => 0, 'label' => 'Peso'],
                'motivo' => ['max' => 150, 'label' => 'Motivo']
            ];
        }

        public function getAllLevantes() {
            return $this->levanteModel->getAllLevantes();
        }

        public function getLevante($id) {
            $id = $this->validarId($id);
            return $this->levanteModel->getLevante($id);
        }

        public function obtenerPorRuta($idRuta) {
            $idRuta = $this->validarId($idRuta);
            return $this->levanteModel->obtenerPorRuta($idRuta);
        }

        public function obtenerCuadrillas() {
            return $this->levanteModel->obtenerCuadrillas();
        }

        public function planificar($rutaId, $camionId, $cuadrillaId, $horaSalida = null, $horaLevante = null) {
            $rutaId = $this->validarId($rutaId);
            $camionId = $this->validarId($camionId);
            $cuadrillaId = $this->validarId($cuadrillaId);

            if ($horaSalida === null || $horaSalida === '') {
                $horaSalida = date('Y-m-d H:i:s');
            }
            if ($horaLevante === null || $horaLevante === '') {
                $horaLevante = date('Y-m-d');
            }

            return $this->levanteModel->planificarLevante($rutaId, $camionId, $cuadrillaId, $horaSalida, $horaLevante);
        }

        public function iniciar($id) {
            $id = $this->validarId($id);
            return $this->levanteModel->iniciarLevante($id);
        }

        public function registrarContenedor($idLevante, $idContenedor, $estadoEncontrado, $peso = null, $motivo = null) {
            $idLevante = $this->validarId($idLevante);
            $idContenedor = $this->validarId($idContenedor);
            $estadoEncontrado = $this->validarString($estadoEncontrado, 'Estado encontrado');
            $peso = $this->validarPeso($peso);
            $motivo = $this->validarMotivo($motivo);
            return $this->levanteModel->registrarContenedor($idLevante, $idContenedor, $estadoEncontrado, $peso, $motivo);
        }

        public function completar($id) {
            $id = $this->validarId($id);
            return $this->levanteModel->completarLevante($id);
        }

        public function cancelar($id) {
            $id = $this->validarId($id);
            return $this->levanteModel->cancelarLevante($id);
        }

        public function obtenerResumen() {
            return $this->levanteModel->obtenerResumen();
        }

        public function obtenerPendientesVencidos() {
            return $this->levanteModel->obtenerPendientesVencidos();
        }

    }
?>
