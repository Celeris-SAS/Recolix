<?php

class MaquinariaController {

    private $maquinariaModel;
    private $centroAcopioModel;
    private $tiposValidos = ['compactadora', 'cinta', 'pala', 'bascula', 'herramienta', 'otro'];
    private $estadosValidos = ['operativa', 'en_mantenimiento', 'fuera_servicio'];

    public function __construct() {
        require_once __DIR__ . '/../Modelo/maquinariaModel.php';
        require_once __DIR__ . '/../Modelo/centroAcopioModel.php';
        $this->maquinariaModel = new MaquinariaModel();
        $this->centroAcopioModel = new CentroAcopioModel();
    }

    // ==========================================
    // VALIDACIONES
    // ==========================================

    private function validarId($valor) {
        if (!is_numeric($valor) || intval($valor) <= 0) {
            throw new InvalidArgumentException("El ID debe ser un número entero positivo.");
        }
        return intval($valor);
    }

    private function validarString($valor, $nombre, $max = 100) {
        if (!is_string($valor) || trim($valor) === '') {
            throw new InvalidArgumentException("$nombre debe ser un texto válido.");
        }
        if (strlen(trim($valor)) > $max) {
            throw new InvalidArgumentException("$nombre no puede superar los $max caracteres.");
        }
        return trim($valor);
    }

    private function validarFecha($valor, $nombre) {
        if (!is_string($valor) || trim($valor) === '') {
            throw new InvalidArgumentException("$nombre debe ser una fecha válida (YYYY-MM-DD).");
        }
        $fecha = trim($valor);
        $formato = 'Y-m-d';
        $d = DateTime::createFromFormat($formato, $fecha);
        if (!$d || $d->format($formato) !== $fecha) {
            throw new InvalidArgumentException("$nombre debe tener el formato YYYY-MM-DD.");
        }
        return $fecha;
    }

    private function validarTipo($tipo) {
        $tipo = $this->validarString($tipo, 'Tipo');
        if (!in_array($tipo, $this->tiposValidos)) {
            throw new InvalidArgumentException("Tipo no válido. Valores permitidos: " . implode(', ', $this->tiposValidos));
        }
        return $tipo;
    }

    private function validarEstado($estado) {
        $estado = $this->validarString($estado, 'Estado');
        if (!in_array($estado, $this->estadosValidos)) {
            throw new InvalidArgumentException("Estado no válido. Valores permitidos: " . implode(', ', $this->estadosValidos));
        }
        return $estado;
    }

    public function reglasValidacion() {
        return [
            'idCentro' => ['tipo' => 'entero', 'min' => 1, 'label' => 'Centro de acopio'],
            'nombre' => ['max' => 100, 'label' => 'Nombre'],
            'tipo' => ['valores' => $this->tiposValidos, 'label' => 'Tipo'],
            'fechaAdquisicion' => [
                'tipo' => 'fecha',
                'pattern' => '\\d{4}-\\d{2}-\\d{2}',
                'label' => 'Fecha de adquisición',
                'mensaje' => 'Debe tener formato YYYY-MM-DD'
            ],
            'estado' => ['valores' => $this->estadosValidos, 'label' => 'Estado']
        ];
    }

    // ==========================================
    // CRUD PRINCIPAL
    // ==========================================

    // RF79 — Registro de maquinaria de un centro de acopio.
    public function insertMaquinaria($idCentro, $nombre, $tipo, $fechaAdquisicion, $estado) {
        $idCentro = $this->validarId($idCentro);
        $nombre = $this->validarString($nombre, 'Nombre');
        $tipo = $this->validarTipo($tipo);
        $fechaAdquisicion = $this->validarFecha($fechaAdquisicion, 'Fecha de adquisición');
        $estado = $this->validarEstado($estado);

        $centro = $this->centroAcopioModel->getCentro($idCentro);
        if (!$centro) {
            throw new InvalidArgumentException("No existe un centro de acopio con el ID $idCentro.");
        }

        return $this->maquinariaModel->insertMaquinaria($idCentro, $nombre, $tipo, $fechaAdquisicion, $estado);
    }

    public function updateMaquinaria($id, $nombre, $tipo, $fechaAdquisicion, $estado) {
        $id = $this->validarId($id);
        $nombre = $this->validarString($nombre, 'Nombre');
        $tipo = $this->validarTipo($tipo);
        $fechaAdquisicion = $this->validarFecha($fechaAdquisicion, 'Fecha de adquisición');
        $estado = $this->validarEstado($estado);

        $maquinaria = $this->maquinariaModel->getMaquinaria($id);
        if (!$maquinaria) {
            throw new InvalidArgumentException("No existe una maquinaria con el ID $id.");
        }

        return $this->maquinariaModel->updateMaquinaria($id, $nombre, $tipo, $fechaAdquisicion, $estado);
    }

    // RF80 — Actualización del estado de la maquinaria.
    public function cambiarEstado($id, $nuevoEstado) {
        $id = $this->validarId($id);
        $nuevoEstado = $this->validarEstado($nuevoEstado);

        $maquinaria = $this->maquinariaModel->getMaquinaria($id);
        if (!$maquinaria) {
            throw new InvalidArgumentException("No existe una maquinaria con el ID $id.");
        }
        $this->verificarCentroPropio($this->maquinariaModel->centroDeMaquinaria($id), $this->centroAcopioModel);

        return $this->maquinariaModel->cambiarEstado($id, $nuevoEstado);
    }

    // Baja lógica de maquinaria (no hay DELETE físico en la base).
    public function bajaMaquinaria($id) {
        $id = $this->validarId($id);

        $maquinaria = $this->maquinariaModel->getMaquinaria($id);
        if (!$maquinaria) {
            throw new InvalidArgumentException("No existe una maquinaria con el ID $id.");
        }

        return $this->maquinariaModel->bajaMaquinaria($id);
    }

    public function getMaquinaria($id) {
        $id = $this->validarId($id);
        return $this->maquinariaModel->getMaquinaria($id);
    }

    public function getAllMaquinarias() {
        return $this->maquinariaModel->getAllMaquinarias();
    }

    // ==========================================
    // CONSULTA POR CENTRO Y FILTROS
    // ==========================================

    // Consulta que reemplaza al listado propio: la maquinaria de un centro puntual.
    // El operario solo opera sobre el centro de acopio asignado a su usuario.
    private function verificarCentroPropio($idCentro, $centroModel) {
        if (($_SESSION['usuario']['rol'] ?? '') !== 'operario') {
            return;
        }
        $ci = $_SESSION['usuario']['ci_usuario'] ?? $_SESSION['ci_usuario'] ?? null;
        $propio = $ci !== null ? $centroModel->centroDeUsuario($ci) : null;
        if ($propio === null || $idCentro === null || (int) $propio !== (int) $idCentro) {
            throw new AccesoDenegadoException('Solo podés operar sobre el centro de acopio asignado a tu usuario.');
        }
    }

    public function obtenerPorCentro($idCentro) {
        $idCentro = $this->validarId($idCentro);
        $this->verificarCentroPropio($idCentro, $this->centroAcopioModel);
        return $this->maquinariaModel->obtenerPorCentro($idCentro);
    }

    public function filtrarPorTipo($tipo) {
        $tipo = $this->validarTipo($tipo);
        return $this->maquinariaModel->filtrarPorTipo($tipo);
    }

    public function filtrarPorEstado($estado) {
        $estado = $this->validarEstado($estado);
        return $this->maquinariaModel->filtrarPorEstado($estado);
    }
}
?>
