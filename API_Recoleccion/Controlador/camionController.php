<?php
    class CamionController {

        private $camionModel;
        private $tiposCargaValidos = ['carga_lateral', 'carga_trasera', 'puerta_a_puerta'];
        private $estadosValidos = ['disponible', 'en_ruta', 'en_mantenimiento', 'de_baja', 'en_deposito'];

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
            if (!is_numeric($valor) || intval($valor) != $valor) {
                throw new InvalidArgumentException("$nombre debe ser un número entero");
            }
            return intval($valor);
        }

        private function validarCapacidad($valor) {
            $val = $this->validarEntero($valor, 'Capacidad');
            if ($val < 1 || $val > 100000) {
                throw new InvalidArgumentException("Capacidad debe estar entre 1 y 100000");
            }
            return $val;
        }

        private function validarAnio($valor) {
            $val = $this->validarEntero($valor, 'Año');
            if ($val < 1990 || $val > 2030) {
                throw new InvalidArgumentException("Año debe estar entre 1990 y 2030");
            }
            return $val;
        }

        public function reglasValidacion() {
            return [
                'matricula' => [
                    'pattern' => '^[A-Z]{3}\\d{4}$',
                    'max' => 7,
                    'label' => 'Matrícula',
                    'mensaje' => 'Debe tener 3 letras seguidas de 4 números (ej: ABC1234)'
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
            $this->camionModel->finalizarMantenimientosVencidos();
            return $this->camionModel->getAllCamiones();
        }

        public function getCamion($id) {
            $id = $this->validarId($id);
            $this->camionModel->finalizarMantenimientosVencidos();
            return $this->camionModel->getCamion($id);
        }

        public function insertCamion($matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado) {
            $matricula = $this->validarString($matricula, 'Matrícula');
            $matricula = strtoupper($matricula);
            if (!preg_match('/^[A-Z]{3}\d{4}$/', $matricula)) {
                throw new InvalidArgumentException("Matrícula debe tener formato 3 letras y 4 números (ej: ABC1234)");
            }
            $tipoCarga = $this->validarString($tipoCarga, 'Tipo de carga');
            if (!in_array($tipoCarga, $this->tiposCargaValidos)) {
                throw new InvalidArgumentException("Tipo de carga no válido. Valores: " . implode(', ', $this->tiposCargaValidos));
            }
            $capacidad = $this->validarCapacidad($capacidad);
            $anio = $this->validarAnio($anio);
            $modelo = $this->validarString($modelo, 'Modelo');
            $estado = $this->validarString($estado, 'Estado');
            if (!in_array($estado, $this->estadosValidos)) {
                throw new InvalidArgumentException("Estado no válido. Valores: " . implode(', ', $this->estadosValidos));
            }
            if ($estado === 'en_mantenimiento') {
                throw new InvalidArgumentException($this->mensajeMantenimientoManual());
            }
            if ($this->camionModel->getMatricula($matricula)) {
                throw new InvalidArgumentException("Ya hay un camion con la matricula " . $matricula . ".");
            }
            return $this->camionModel->insertCamion($matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado);
        }

        public function updateCamion($id, $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado) {
            $id = $this->validarId($id);
            $matricula = $this->validarString($matricula, 'Matrícula');
            $matricula = strtoupper($matricula);
            if (!preg_match('/^[A-Z]{3}\d{4}$/', $matricula)) {
                throw new InvalidArgumentException("Matrícula debe tener formato 3 letras y 4 números (ej: ABC1234)");
            }
            $tipoCarga = $this->validarString($tipoCarga, 'Tipo de carga');
            if (!in_array($tipoCarga, $this->tiposCargaValidos)) {
                throw new InvalidArgumentException("Tipo de carga no válido. Valores: " . implode(', ', $this->tiposCargaValidos));
            }
            $capacidad = $this->validarCapacidad($capacidad);
            $anio = $this->validarAnio($anio);
            $modelo = $this->validarString($modelo, 'Modelo');
            $estado = $this->validarString($estado, 'Estado');
            if (!in_array($estado, $this->estadosValidos)) {
                throw new InvalidArgumentException("Estado no válido. Valores: " . implode(', ', $this->estadosValidos));
            }
            if ($estado === 'en_mantenimiento') {
                $actual = $this->camionModel->getCamion($id);
                if ($actual && $actual['estado'] !== 'en_mantenimiento') {
                    throw new InvalidArgumentException($this->mensajeMantenimientoManual());
                }
            }
            return $this->camionModel->updateCamion($id, $matricula, $tipoCarga, $capacidad, $anio, $modelo, $estado);
        }

        private function mensajeMantenimientoManual() {
            return "No se puede pasar un camión a mantenimiento cambiando su estado: registrá o programá un mantenimiento.";
        }

        public function deleteCamion($id) {
            $id = $this->validarId($id);
            return $this->camionModel->bajaCamion($id);
        }

        public function esDisponible($id) {
            $id = $this->validarId($id);
            $this->camionModel->finalizarMantenimientosVencidos();
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

        public function filtrarDisponibilidad($capacidadMinima = null) {
            if ($capacidadMinima !== null && $capacidadMinima !== '') {
                if (!is_numeric($capacidadMinima) || (float) $capacidadMinima < 0) {
                    throw new InvalidArgumentException("Capacidad mínima debe ser un número positivo");
                }
                $capacidadMinima = (int) ceil((float) $capacidadMinima);
            } else {
                $capacidadMinima = null;
            }
            return $this->camionModel->filtrarDisponibilidad($capacidadMinima);
        }

        public function filtrarMantenimiento() {
            $this->camionModel->finalizarMantenimientosVencidos();
            return $this->camionModel->filtrarMantenimiento();
        }

        public function filtrarAnio($anio) {
            $anio = $this->validarAnio($anio);
            return $this->camionModel->filtrarAnio($anio);
        }

        public function filtrarModelo($modelo) {
            $modelo = $this->validarString($modelo, 'Modelo');
            return $this->camionModel->filtrarModelo($modelo);
        }

        public function filtrarCapacidad($capacidad) {
            $capacidad = $this->validarCapacidad($capacidad);
            return $this->camionModel->filtrarCapacidad($capacidad);
        }

        public function obtenerHistorial($camionId) {
            $camionId = $this->validarId($camionId);
            return $this->camionModel->obtenerHistorial($camionId);
        }

        public function obtenerEstados($camionId) {
            $camionId = $this->validarId($camionId);
            return $this->camionModel->obtenerEstados($camionId);
        }

        public function obtenerEnOperativa() {
            $flota = $this->camionModel->obtenerEnOperativa();

            $rol = $_SESSION['usuario']['rol'] ?? '';
            if ($rol !== '' && $rol !== 'vecino') {
                return $flota;
            }
            return array_map(function ($camion) {
                return [
                    'ubicacion' => [
                        'latitud'  => $camion['ubicacion']['latitud'],
                        'longitud' => $camion['ubicacion']['longitud'],
                    ],
                    'eta' => $camion['eta'] ? ['minutos' => $camion['eta']['minutos']] : null,
                ];
            }, $flota);
        }

    }
?>
