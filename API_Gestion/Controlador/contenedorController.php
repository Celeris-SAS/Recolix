<?php
 
class contenedorController {

    private $contenedorModel;
    private $estadosValidos = ['funcional', 'roto', 'desbordado', 'inactivo', 'en_deposito'];

    public function __construct() {
        require_once __DIR__ . '/../Modelo/contenedorModel.php';
        require_once __DIR__ . '/../Modelo/conexion.php';
        $this->contenedorModel = new class extends ContenedorModel {
            public function __construct() {
                $this->conn = new conexion();
            }
        };
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
 
    private function validarDecimal($valor, $nombre) {
        if (!is_numeric($valor) || floatval($valor) <= 0) {
            throw new InvalidArgumentException("$nombre debe ser un número decimal positivo.");
        }
        return floatval($valor);
    }
 
    private function validarCoordenada($valor, $nombre) {
        if (!is_numeric($valor)) {
            throw new InvalidArgumentException("$nombre debe ser un número decimal válido.");
        }
        return floatval($valor);
    }
 
    private function validarEstado($estado) {
        if (!in_array($estado, $this->estadosValidos)) {
            throw new InvalidArgumentException("Estado no válido. Valores permitidos: " . implode(', ', $this->estadosValidos));
        }
        return $estado;
    }
 
    public function reglasValidacion() {
        return [
            'idCircuito' => [
                'tipo'   => 'entero',
                'min'    => 1,
                'label'  => 'Circuito'
            ],
            'idTipo' => [
                'tipo'   => 'entero',
                'min'    => 1,
                'label'  => 'Tipo de residuo'
            ],
            'calle' => [
                'tipo'   => 'string',
                'max'    => 100,
                'label'  => 'Calle'
            ],
            'numCalle' => [
                'tipo'   => 'string',
                'max'    => 20,
                'label'  => 'Número de puerta'
            ],
            'latitud' => [
                'tipo'   => 'decimal',
                'label'  => 'Latitud'
            ],
            'longitud' => [
                'tipo'   => 'decimal',
                'label'  => 'Longitud'
            ],
            'capacidad' => [
                'tipo'   => 'decimal',
                'min'    => 1,
                'label'  => 'Capacidad'
            ],
            'estado' => [
                'valores' => $this->estadosValidos,
                'label'   => 'Estado'
            ]
        ];
    }
 
    // ==========================================
    // CRUD PRINCIPAL
    // ==========================================
 
    // RF14 — Alta de contenedor.
    public function insertContenedor($idCircuito, $idTipo, $calle, $numCalle, $latitud, $longitud, $capacidad, $estado) {
        $idCircuito = $this->validarId($idCircuito);
        $idTipo     = $this->validarId($idTipo);
        $calle      = $this->validarString($calle, 'Calle', 100);
        $numCalle   = $this->validarString($numCalle, 'Número de puerta', 20);
        $latitud    = $this->validarCoordenada($latitud, 'Latitud');
        $longitud   = $this->validarCoordenada($longitud, 'Longitud');
        $capacidad  = $this->validarDecimal($capacidad, 'Capacidad');
        $estado     = $this->validarEstado($estado);
 
        return $this->contenedorModel->insertContenedor($idCircuito, $idTipo, $calle, $numCalle, $latitud, $longitud, $capacidad, $estado);
    }
 
    // RF15 — Modificación de contenedor.
    public function updateContenedor($id, $idCircuito, $idTipo, $calle, $numCalle, $latitud, $longitud, $capacidad) {
        $id         = $this->validarId($id);
        $idCircuito = $this->validarId($idCircuito);
        $idTipo     = $this->validarId($idTipo);
        $calle      = $this->validarString($calle, 'Calle', 100);
        $numCalle   = $this->validarString($numCalle, 'Número de puerta', 20);
        $latitud    = $this->validarCoordenada($latitud, 'Latitud');
        $longitud   = $this->validarCoordenada($longitud, 'Longitud');
        $capacidad  = $this->validarDecimal($capacidad, 'Capacidad');

        $contenedor = $this->contenedorModel->getContenedor($id);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $id.");
        }

        return $this->contenedorModel->updateContenedor($id, $idCircuito, $idTipo, $calle, $numCalle, $latitud, $longitud, $capacidad);
    }
 
    // RF16 — Baja lógica de contenedor.
    public function bajaContenedor($id) {
        $id = $this->validarId($id);
 
        $contenedor = $this->contenedorModel->getContenedor($id);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $id.");
        }
 
        return $this->contenedorModel->bajaContenedor($id);
    }
 
    // RF17 — Obtener un contenedor por id.
    public function getContenedor($id) {
        $id = $this->validarId($id);
        return $this->contenedorModel->getContenedor($id);
    }
 
    // RF17 — Listado de todos los contenedores.
    public function getAllContenedores() {
        return $this->contenedorModel->getAllContenedores();
    }
 
    // ==========================================
    // OPERACIONES DE NEGOCIO
    // ==========================================
 
    // RF19, RF20 — Cambio de estado.
    public function cambiarEstado($id, $nuevoEstado) {
        $id         = $this->validarId($id);
        $nuevoEstado = $this->validarEstado($nuevoEstado);
 
        $contenedor = $this->contenedorModel->getContenedor($id);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $id.");
        }
 
        return $this->contenedorModel->cambiarEstado($id, $nuevoEstado);
    }
 
    // RF21 — Clasificación por tipo de residuo.
    public function clasificarPorTipoResiduo($id, $idTipo) {
        $id     = $this->validarId($id);
        $idTipo = $this->validarId($idTipo);
 
        $contenedor = $this->contenedorModel->getContenedor($id);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $id.");
        }
 
        return $this->contenedorModel->clasificarPorTipoResiduo($id, $idTipo);
    }
 
    // RF22 — Asignación a circuito.
    public function asignarACircuito($id, $idCircuito) {
        $id         = $this->validarId($id);
        $idCircuito = $this->validarId($idCircuito);
 
        $contenedor = $this->contenedorModel->getContenedor($id);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $id.");
        }
 
        return $this->contenedorModel->asignarACircuito($id, $idCircuito);
    }
 
    // RF26 — Registro de reparación.
    // Valida que el contenedor esté en estado 'roto' antes de reparar.
    public function registrarReparacion($id) {
        $id = $this->validarId($id);
 
        $contenedor = $this->contenedorModel->getContenedor($id);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $id.");
        }
        if ($contenedor['estado'] !== 'roto') {
            throw new InvalidArgumentException("El contenedor no está en estado 'roto'. Estado actual: " . $contenedor['estado']);
        }
 
        return $this->contenedorModel->registrarReparacion($id);
    }
 
    // RF24, RF25 — Consulta del nivel de llenado.
    // El controlador calcula el porcentaje a partir de los datos crudos del modelo.
    public function getNivelLlenado($id) {
        $id   = $this->validarId($id);
        $dato = $this->contenedorModel->consultarNivelLlenado($id);
 
        if (!$dato) {
            return null;
        }
        if ($dato['capacidad'] <= 0) {
            throw new InvalidArgumentException("La capacidad del contenedor no puede ser cero.");
        }
 
        return round(($dato['peso'] / $dato['capacidad']) * 100, 2);
    }
 
    // RF28 — Historial del contenedor.
    // El controlador ordena los eventos por fecha descendente.
    public function getHistorial($id) {
        $id       = $this->validarId($id);
        $historial = $this->contenedorModel->getHistorial($id);
 
        usort($historial, function ($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });
 
        return $historial;
    }

 
    // RF18 — Filtrar por municipio.
    public function filtrarMunicipio($idMunicipio) {
        $idMunicipio = $this->validarId($idMunicipio);
        return $this->contenedorModel->filtrarMunicipio($idMunicipio);
    }
 
    // RF18 — Filtrar por tipo de residuo.
    public function filtrarTipoResiduo($idTipo) {
        $idTipo = $this->validarId($idTipo);
        return $this->contenedorModel->filtrarTipoResiduo($idTipo);
    }
 
    // RF18 — Filtrar por estado.
    // RF27 — Inventario de contenedores en depósito: filtrarEstado('en_deposito').
    public function filtrarEstado($estado) {
        $estado = $this->validarEstado($estado);
        return $this->contenedorModel->filtrarEstado($estado);
    }
}
?>
