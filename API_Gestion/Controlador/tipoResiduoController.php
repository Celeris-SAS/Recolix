<?php
 
class tipoResiduoController {
 
    private $tipoResiduoModel;
 
    public function __construct() {
        require_once __DIR__ . '/../Modelo/tipoResiduoModel.php';
        $this->tipoResiduoModel = new tipoResiduoModel();
    }
    // ==========================================
    // VALIDACIONES
    // ==========================================
 
    private function validarId($id) {
        if (!is_numeric($id) || intval($id) <= 0) {
            throw new InvalidArgumentException("El ID debe ser un número entero positivo.");
        }
        return intval($id);
    }
 
    private function validarString($valor, $nombre, $max = 255) {
        if (!is_string($valor) || trim($valor) === '') {
            throw new InvalidArgumentException("$nombre debe ser un texto válido.");
        }
        if (strlen(trim($valor)) > $max) {
            throw new InvalidArgumentException("$nombre no puede superar los $max caracteres.");
        }
        return trim($valor);
    }
 
    private function validarBooleano($valor, $nombre) {
        if (!is_bool($valor) && $valor !== 0 && $valor !== 1 && $valor !== '0' && $valor !== '1') {
            throw new InvalidArgumentException("$nombre debe ser un valor booleano (true o false).");
        }
        return (bool) $valor;
    }
 
    public function reglasValidacion() {
        return [
            'color' => [
                'tipo'  => 'string',
                'max'   => 30,
                'label' => 'Color'
            ],
            'descripcion' => [
                'tipo'  => 'string',
                'max'   => 150,
                'label' => 'Descripción'
            ],
            'reciclable' => [
                'tipo'  => 'booleano',
                'label' => 'Reciclable'
            ]
        ];
    }
 
    // ==========================================
    // CRUD PRINCIPAL
    // ==========================================
 
    // RF14, RF21 — Alta de tipo de residuo.
    public function insertTipoResiduo($color, $descripcion, $reciclable) {
        $color       = $this->validarString($color, 'Color', 30);
        $descripcion = $this->validarString($descripcion, 'Descripción', 150);
        $reciclable  = $this->validarBooleano($reciclable, 'Reciclable');
 
        return $this->tipoResiduoModel->insertTipoResiduo($color, $descripcion, $reciclable);
    }
 
    // RF15 — Modificación de tipo de residuo.
    public function updateTipoResiduo($id, $color, $descripcion, $reciclable) {
        $id          = $this->validarId($id);
        $color       = $this->validarString($color, 'Color', 30);
        $descripcion = $this->validarString($descripcion, 'Descripción', 150);
        $reciclable  = $this->validarBooleano($reciclable, 'Reciclable');
 
        $tipoResiduo = $this->tipoResiduoModel->getTipoResiduo($id);
        if (!$tipoResiduo) {
            throw new InvalidArgumentException("No existe un tipo de residuo con el ID $id.");
        }
 
        return $this->tipoResiduoModel->updateTipoResiduo($id, $color, $descripcion, $reciclable);
    }
 
    // RF16 — Baja de tipo de residuo.
    // La FK de CONTENEDOR hacia TIPO_RESIDUO es ON DELETE SET NULL, por lo que
    // MySQL no bloquea el DELETE por sí solo: hay que verificar explícitamente
    // que no haya contenedores asociados antes de borrar.
    public function bajaTipoResiduo($id) {
        $id = $this->validarId($id);

        $tipoResiduo = $this->tipoResiduoModel->getTipoResiduo($id);
        if (!$tipoResiduo) {
            throw new InvalidArgumentException("No existe un tipo de residuo con el ID $id.");
        }
        if ($this->tipoResiduoModel->tieneContenedoresAsociados($id)) {
            throw new InvalidArgumentException("No se puede eliminar: hay contenedores asignados a este tipo de residuo.");
        }

        $resultado = $this->tipoResiduoModel->bajaTipoResiduo($id);
        if (!$resultado) {
            throw new RuntimeException("No se pudo eliminar el tipo de residuo.");
        }

        return $resultado;
    }
 
    // RF21 — Obtener un tipo de residuo por id.
    public function getTipoResiduo($id) {
        $id = $this->validarId($id);
        return $this->tipoResiduoModel->getTipoResiduo($id);
    }
 
    // RF21 — Listado de todos los tipos de residuo.
    public function getAllTiposResiduo() {
        return $this->tipoResiduoModel->getAllTiposResiduo();
    }
 
    // ==========================================
    // FILTROS
    // ==========================================
 
    // RF66, RF67 — Filtrar por condición de reciclable.
    public function filtrarReciclable($reciclable) {
        $reciclable = $this->validarBooleano($reciclable, 'Reciclable');
        return $this->tipoResiduoModel->filtrarReciclable($reciclable);
    }
}
?>
