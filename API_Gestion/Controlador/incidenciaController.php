<?php

class IncidenciaController {

    private $incidenciaModel;
    private $tiposValidos = ['rotura', 'desborde', 'falta_recoleccion', 'vandalismo', 'otro'];
    private $prioridadesValidas = ['baja', 'media', 'alta', 'critica'];
    private $estadosValidos = ['abierta', 'asignada', 'en_curso', 'resuelta'];

    public function __construct() {
        require_once __DIR__ . '/../Modelo/incidenciaModel.php';
        $this->incidenciaModel = new IncidenciaModel();
    }

    private function validarId($valor, $nombre = 'ID') {
        if (!is_numeric($valor) || intval($valor) <= 0) {
            throw new InvalidArgumentException("$nombre debe ser un número entero positivo.");
        }
        return intval($valor);
    }

    private function validarEstado($estado) {
        if (!in_array($estado, $this->estadosValidos)) {
            throw new InvalidArgumentException("Estado no válido. Valores permitidos: " . implode(', ', $this->estadosValidos));
        }
        return $estado;
    }

    private function validarTipo($tipo) {
        if (!in_array($tipo, $this->tiposValidos)) {
            throw new InvalidArgumentException("Tipo no válido. Valores permitidos: " . implode(', ', $this->tiposValidos));
        }
        return $tipo;
    }

    private function validarPrioridad($prioridad) {
        if (!in_array($prioridad, $this->prioridadesValidas)) {
            throw new InvalidArgumentException("Prioridad no válida. Valores permitidos: " . implode(', ', $this->prioridadesValidas));
        }
        return $prioridad;
    }

    private function obtenerOFallar($id) {
        $incidencia = $this->incidenciaModel->getIncidencia($id);
        if (!$incidencia) {
            throw new InvalidArgumentException("No existe una incidencia con el ID $id.");
        }
        return $incidencia;
    }

    public function reglasValidacion() {
        return [
            'idCuadrilla' => ['tipo' => 'entero', 'min' => 1, 'label' => 'Cuadrilla'],
            'tipo'        => ['valores' => $this->tiposValidos, 'label' => 'Tipo'],
            'prioridad'   => ['valores' => $this->prioridadesValidas, 'label' => 'Prioridad'],
            'estado'      => ['valores' => $this->estadosValidos, 'label' => 'Estado']
        ];
    }

    public function getAllIncidencias() {
        return $this->incidenciaModel->getAllIncidencias();
    }

    public function getIncidencia($id) {
        $id = $this->validarId($id);
        return $this->incidenciaModel->getIncidencia($id);
    }

    public function filtrarEstado($estado) {
        $estado = $this->validarEstado($estado);
        return $this->incidenciaModel->filtrarEstado($estado);
    }

    public function filtrarTipo($tipo) {
        $tipo = $this->validarTipo($tipo);
        return $this->incidenciaModel->filtrarTipo($tipo);
    }

    public function filtrarPrioridad($prioridad) {
        $prioridad = $this->validarPrioridad($prioridad);
        return $this->incidenciaModel->filtrarPrioridad($prioridad);
    }

    public function listarUrgentes() {
        return $this->incidenciaModel->listarUrgentes();
    }

    public function getContenedoresVinculados($id) {
        $id = $this->validarId($id);
        $this->obtenerOFallar($id);
        return $this->incidenciaModel->getContenedoresVinculados($id);
    }

    // RF10: solo se asigna cuadrilla a una incidencia recién abierta.
    public function asignarCuadrilla($id, $idCuadrilla) {
        $id          = $this->validarId($id);
        $idCuadrilla = $this->validarId($idCuadrilla, 'Cuadrilla');

        $incidencia = $this->obtenerOFallar($id);
        if ($incidencia['estado'] !== 'abierta') {
            throw new InvalidArgumentException("Solo se puede asignar una cuadrilla a incidencias en estado 'abierta'. Estado actual: " . $incidencia['estado']);
        }
        if (!$this->incidenciaModel->cuadrillaExiste($idCuadrilla)) {
            throw new InvalidArgumentException("No existe una cuadrilla con el ID $idCuadrilla.");
        }

        return $this->incidenciaModel->asignarCuadrilla($id, $idCuadrilla);
    }

    // RF13: cierre de la cuadrilla, de 'en_curso' a 'resuelta'.
    public function cerrar($id) {
        $id = $this->validarId($id);
        $incidencia = $this->obtenerOFallar($id);
        if ($incidencia['estado'] !== 'en_curso') {
            throw new InvalidArgumentException("Solo se puede cerrar una incidencia en estado 'en_curso'. Estado actual: " . $incidencia['estado']);
        }
        return $this->incidenciaModel->cerrar($id);
    }
}
?>
