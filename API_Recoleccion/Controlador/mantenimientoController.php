<?php
    class MantenimientoController {

    private $mantenimientoModel;
    private $camionModel;
    private $notificacionModel;
    private $levanteModel;
    private $tiposValidos = ['preventivo', 'correctivo'];

        public function __construct() {
            require_once __DIR__ . '/../Modelo/mantenimientoModel.php';
            require_once __DIR__ . '/../Modelo/camionModel.php';
            require_once __DIR__ . '/../Modelo/notificacionModel.php';
            require_once __DIR__ . '/../Modelo/levanteModel.php';
            $this->mantenimientoModel = new MantenimientoModel();
            $this->camionModel = new CamionModel();
            $this->notificacionModel = new NotificacionModel();
            $this->levanteModel = new LevanteModel();
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
                throw new InvalidArgumentException("$nombre debe ser una fecha y hora válidas");
            }
            $valor = trim($valor);
            $formatoSalida = 'Y-m-d H:i:s';
            $formatosAceptados = ['Y-m-d\TH:i:s', 'Y-m-d\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'];
            $d = null;
            foreach ($formatosAceptados as $formato) {
                $tmp = DateTime::createFromFormat($formato, $valor);
                if ($tmp && $tmp->format($formato) === $valor) {
                    $d = $tmp;
                    break;
                }
            }
            if (!$d) {
                throw new InvalidArgumentException("$nombre debe tener el formato YYYY-MM-DD HH:MM");
            }
            return $d->format($formatoSalida);
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

        // RNE 24: la fecha de fin no puede ser anterior a la de inicio.
        private function validarRangoFechas($fechaInicio, $fechaFin) {
            if ($fechaFin !== null && strtotime($fechaFin) < strtotime($fechaInicio)) {
                throw new InvalidArgumentException("La fecha de fin no puede ser anterior a la fecha de inicio.");
            }
        }

        // El camión pasa a mantenimiento y ya no puede completar su ruta: se cancelan sus levantes
        // pendientes o en curso (no se dan por terminados) para poder replanificarlos con otro camión.
        private function ponerEnMantenimiento($camionId) {
            $this->camionModel->marcarCamionEnMantenimiento($camionId);
            $this->levanteModel->cancelarActivosDelCamion($camionId);
        }

        public function reglasValidacion() {
            return [
                'camionId' => ['tipo' => 'entero', 'min' => 1, 'label' => 'ID del camión'],
                'tipo' => ['valores' => $this->tiposValidos, 'max' => 20, 'label' => 'Tipo'],
                'descripcion' => ['max' => 500, 'label' => 'Descripción'],
                'fechaInicio' => ['tipo' => 'fecha', 'pattern' => '\\d{4}-\\d{2}-\\d{2}([T ]\\d{2}:\\d{2}(:\\d{2})?)?', 'label' => 'Fecha de inicio', 'mensaje' => 'Debe tener formato YYYY-MM-DD HH:MM'],
                'fechaFin' => ['tipo' => 'fecha', 'pattern' => '\\d{4}-\\d{2}-\\d{2}([T ]\\d{2}:\\d{2}(:\\d{2})?)?', 'label' => 'Fecha de fin', 'mensaje' => 'Debe tener formato YYYY-MM-DD HH:MM'],
                'costo' => ['tipo' => 'decimal', 'min' => 0, 'label' => 'Costo']
            ];
        }

        public function getAllMantenimientos() {
            return $this->mantenimientoModel->getAllMantenimientos();
        }

        public function proximos($tipo = null) {
            if ($tipo !== null && trim($tipo) !== '') {
                $tipo = $this->validarString($tipo, 'Tipo');
                if (!in_array($tipo, $this->tiposValidos)) {
                    throw new InvalidArgumentException("Tipo no válido. Valores: " . implode(', ', $this->tiposValidos));
                }
            } else {
                $tipo = null;
            }
            return $this->mantenimientoModel->getProximos($tipo);
        }

        public function getMantenimiento($id) {
            $id = $this->validarId($id);
            return $this->mantenimientoModel->getMantenimiento($id);
        }

        public function insertMantenimiento($camionId, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo) {
            $camionId = $this->validarId($camionId);
            $tipo = $this->validarString($tipo, 'Tipo');
            if (!in_array($tipo, $this->tiposValidos)) {
                throw new InvalidArgumentException("Tipo no válido. Valores: " . implode(', ', $this->tiposValidos));
            }
            $descripcion = $this->validarString($descripcion, 'Descripción');
            $fechaInicio = $this->validarFecha($fechaInicio, 'Fecha de inicio');
            $fechaFin = ($fechaFin !== null && trim($fechaFin) !== '')
                ? $this->validarFecha($fechaFin, 'Fecha de fin')
                : null;
            $costo = $this->validarDecimal($costo, 'Costo');
            $this->validarRangoFechas($fechaInicio, $fechaFin);
            $id = $this->mantenimientoModel->registrar($camionId, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo);
            $this->ponerEnMantenimiento($camionId);
            return $id;
        }

        public function updateMantenimiento($id, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo) {
            $id = $this->validarId($id);
            $tipo = $this->validarString($tipo, 'Tipo');
            if (!in_array($tipo, $this->tiposValidos)) {
                throw new InvalidArgumentException("Tipo no válido. Valores: " . implode(', ', $this->tiposValidos));
            }
            $descripcion = $this->validarString($descripcion, 'Descripción');
            $fechaInicio = $this->validarFecha($fechaInicio, 'Fecha de inicio');
            $fechaFin = ($fechaFin !== null && trim($fechaFin) !== '')
                ? $this->validarFecha($fechaFin, 'Fecha de fin')
                : null;
            $costo = $this->validarDecimal($costo, 'Costo');
            $this->validarRangoFechas($fechaInicio, $fechaFin);
            return $this->mantenimientoModel->actualizarMantenimiento($id, $tipo, $descripcion, $fechaInicio, $fechaFin, $costo);
        }

        public function deleteMantenimiento($id) {
            $id = $this->validarId($id);
            return $this->mantenimientoModel->deleteMantenimiento($id);
        }

        public function programar($camionId, $tipo, $descripcion, $fechaInicio, $fechaFin) {
            $camionId = $this->validarId($camionId);
            $tipo = $this->validarString($tipo, 'Tipo');
            if (!in_array($tipo, $this->tiposValidos)) {
                throw new InvalidArgumentException("Tipo no válido. Valores: " . implode(', ', $this->tiposValidos));
            }
            $descripcion = $this->validarString($descripcion, 'Descripción');
            $fechaInicio = $this->validarFecha($fechaInicio, 'Fecha de inicio');
            $fechaFin = $this->validarFecha($fechaFin, 'Fecha de fin');
            $this->validarRangoFechas($fechaInicio, $fechaFin);
            $id = $this->mantenimientoModel->programar($camionId, $tipo, $descripcion, $fechaInicio, $fechaFin);
            $this->ponerEnMantenimiento($camionId);
            return $id;
        }

        public function obtenerPorCamion($camionId) {
            $camionId = $this->validarId($camionId);
            return $this->mantenimientoModel->obtenerPorCamion($camionId);
        }

        public function filtrarPorTipo($tipo) {
            $tipo = $this->validarString($tipo, 'Tipo');
            if (!in_array($tipo, $this->tiposValidos)) {
                throw new InvalidArgumentException("Tipo no válido. Valores: " . implode(', ', $this->tiposValidos));
            }
            return $this->mantenimientoModel->filtrarPorTipo($tipo);
        }

        public function obtenerCostoTotal($camionId) {
            $camionId = $this->validarId($camionId);
            return $this->mantenimientoModel->obtenerCostoTotal($camionId);
        }

        // RF37: la cuadrilla avisa a los administradores de un desperfecto mecánico en su camión.
        public function notificarDesperfecto($camionId, $descripcion) {
            $camionId    = $this->validarId($camionId);
            $descripcion = $this->validarString($descripcion, 'Descripción');

            $camion = $this->camionModel->getCamion($camionId);
            if (!$camion) {
                throw new InvalidArgumentException("No existe un camión con el ID $camionId.");
            }

            return $this->notificacionModel->crearParaAdministradores(
                "Desperfecto mecánico en el camión {$camion['matricula']}: $descripcion",
                'camion',
                'advertencia'
            );
        }

    }
?>
