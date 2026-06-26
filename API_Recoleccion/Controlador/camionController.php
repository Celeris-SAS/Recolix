<?php
    class CamionController {

        private $camionModel;
        private $tiposCargaValidos = ['residuos', 'reciclaje', 'voluminosos', 'mixto'];
        private $estadosValidos = ['disponible', 'en mantenimiento', 'de baja'];

        public function __construct() {
            require_once __DIR__ . '/../Modelo/CamionModel.php';
            $this->camionModel = new CamionModel();
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
            $min = null;
            $max = null;
            if (!is_numeric($valor) || intval($valor) != $valor) {
                throw new InvalidArgumentException("$nombre debe ser un número entero");
            }
            $val = intval($valor);
            if ($min !== null && $val < $min) {
                throw new InvalidArgumentException("$nombre no puede ser menor que $min");
            }
            if ($max !== null && $val > $max) {
                throw new InvalidArgumentException("$nombre no puede ser mayor que $max");
            }
            return $val;
        }

        public function reglasValidacion() {
            return [
                'matricula' => [
                    'pattern' => '^[A-Z]{3}\\d{3}$',
                    'max' => 6,
                    'label' => 'Matrícula',
                    'mensaje' => 'Debe tener 3 letras seguidas de 3 números (ej: ABC123)'
                ],
                'tipoCarga' => [
                    'valores' => $this->tiposCargaValidos,
                    'max' => 50,
                    'label' => 'Tipo de carga'
                ],
                'capacidad' => [
                    'tipo' => 'entero',
                    'min' => 1,
                    'max' => 100000,
                    'label' => 'Capacidad'
                ],
                'anio' => [
                    'tipo' => 'entero',
                    'min' => 1990,
                    'max' => 2030,
                    'label' => 'Año'
                ],
                'modelo' => [
                    'max' => 100,
                    'label' => 'Modelo'
                ],
                'estado' => [
                    'valores' => $this->estadosValidos,
                    'max' => 50,
                    'label' => 'Estado'
                ]
            ];
        }

        public function getAllCamiones() {
            return $this->camionModel->getAllCamiones();
        }

        public function getCamion($id) {
            $id = $this->validarId($id);
            return $this->camionModel->getCamion($id);
        }

        public function insertCamion($matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado) {
            $matricula = $this->validarString($matricula, 'Matrícula');
            $matricula = strtoupper($matricula);
            if (!preg_match('/^[A-Z]{3}\d{3}$/', $matricula)) {
                throw new InvalidArgumentException("Matrícula debe tener formato 3 letras y 3 números (ej: ABC123)");
            }
            $tipoCarga = $this->validarString($tipoCarga, 'Tipo de carga');
            if (!in_array($tipoCarga, $this->tiposCargaValidos)) {
                throw new InvalidArgumentException("Tipo de carga no válido. Valores: " . implode(', ', $this->tiposCargaValidos));
            }
            $capacidad = $this->validarEntero($capacidad, 'Capacidad');
            $anio = $this->validarEntero($anio, 'Año');
            $modelo = $this->validarString($modelo, 'Modelo');
            $estado = $this->validarString($estado, 'Estado');
            if (!in_array($estado, $this->estadosValidos)) {
                throw new InvalidArgumentException("Estado no válido. Valores: " . implode(', ', $this->estadosValidos));
            }
            return $this->camionModel->insertCamion($matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado);
        }

        public function updateCamion($id, $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado) {
            $id = $this->validarId($id);
            $matricula = $this->validarString($matricula, 'Matrícula');
            $matricula = strtoupper($matricula);
            if (!preg_match('/^[A-Z]{3}\d{3}$/', $matricula)) {
                throw new InvalidArgumentException("Matrícula debe tener formato 3 letras y 3 números (ej: ABC123)");
            }
            $tipoCarga = $this->validarString($tipoCarga, 'Tipo de carga');
            if (!in_array($tipoCarga, $this->tiposCargaValidos)) {
                throw new InvalidArgumentException("Tipo de carga no válido. Valores: " . implode(', ', $this->tiposCargaValidos));
            }
            $capacidad = $this->validarEntero($capacidad, 'Capacidad');
            $anio = $this->validarEntero($anio, 'Año');
            $modelo = $this->validarString($modelo, 'Modelo');
            $estado = $this->validarString($estado, 'Estado');
            if (!in_array($estado, $this->estadosValidos)) {
                throw new InvalidArgumentException("Estado no válido. Valores: " . implode(', ', $this->estadosValidos));
            }
            return $this->camionModel->updateCamion($id, $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado);
        }

        public function deleteCamion($id) {
            $id = $this->validarId($id);
            return $this->camionModel->bajaCamion($id);
        }

        public function esDisponible($id) {
            $id = $this->validarId($id);
            return $this->camionModel->esDisponible($id);
        }

        public function filtrarEstado($estado) {
            $estado = $this->validarString($estado, 'Estado');
            if (!in_array($estado, $this->estadosValidos)) {
                throw new InvalidArgumentException("Estado no válido. Valores: " . implode(', ', $this->estadosValidos));
            }
            return $this->camionModel->filtrarEstado($estado);
        }

        public function filtrarTipoCarga($tipoCarga) {
            $tipoCarga = $this->validarString($tipoCarga, 'Tipo de carga');
            if (!in_array($tipoCarga, $this->tiposCargaValidos)) {
                throw new InvalidArgumentException("Tipo de carga no válido. Valores: " . implode(', ', $this->tiposCargaValidos));
            }
            return $this->camionModel->filtrarTipoCarga($tipoCarga);
        }

        public function filtrarDisponibilidad() {
            return $this->camionModel->filtrarDisponibilidad();
        }

        public function filtrarMantenimiento() {
            return $this->camionModel->filtrarMantenimiento();
        }

        public function filtrarAnio($anio) {
            $anio = $this->validarEntero($anio, 'Año');
            return $this->camionModel->filtrarAnio($anio);
        }

        public function filtrarModelo($modelo) {
            $modelo = $this->validarString($modelo, 'Modelo');
            return $this->camionModel->filtrarModelo($modelo);
        }

        public function filtrarCapacidad($capacidad) {
            $capacidad = $this->validarEntero($capacidad, 'Capacidad');
            return $this->camionModel->filtrarCapacidad($capacidad);
        }

        public function obtenerHistorial($camionId) {
            $camionId = $this->validarId($camionId);
            return $this->camionModel->obtenerHistorial($camionId);
        }

    }
?>
