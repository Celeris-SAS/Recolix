<?php
 
class contenedorController {

    private $contenedorModel;
    private $estadosValidos = ['funcional', 'roto', 'desbordado', 'inactivo', 'en_deposito'];

    public function __construct() {
        require_once __DIR__ . '/../Modelo/contenedorModel.php';
        $this->contenedorModel = new contenedorModel();
    }
 
 
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
        $valor  = floatval($valor);
        if ($nombre === 'Latitud') {
              $limite = 90;
                  } else {
                        $limite = 180;
        }
        if ($valor < -$limite || $valor > $limite) {
            throw new InvalidArgumentException("$nombre debe estar entre -$limite y $limite.");
        }
        return $valor;
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
                'tipo'   => 'entero',
                'min'    => 1,
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
                'tipo'   => 'entero',
                'min'    => 1,
                'label'  => 'Capacidad'
            ],
            'estado' => [
                'valores' => $this->estadosValidos,
                'label'   => 'Estado'
            ]
        ];
    }
 

    private function validarEntero($valor, $nombre) {
    if (!is_numeric($valor) || floatval($valor) != intval($valor) || intval($valor) <= 0) {
        throw new InvalidArgumentException("$nombre debe ser un número entero positivo.");
    }
    return intval($valor);
    }
 
    public function insertContenedor($idCircuito, $idTipo, $calle, $numCalle, $latitud, $longitud, $capacidad, $estado) {
    $idCircuito = $this->validarId($idCircuito);
    $idTipo     = $this->validarId($idTipo);
    $estado     = $this->validarEstado($estado);

    if ($estado === 'en_deposito') {
        $calle    = null;
        $numCalle = null;
        $latitud  = null;
        $longitud = null;
    } else {
        $calle    = $this->validarString($calle, 'Calle', 100);
        $numCalle = $this->validarEntero($numCalle, 'Número de puerta');
        $latitud  = $this->validarCoordenada($latitud, 'Latitud');
        $longitud = $this->validarCoordenada($longitud, 'Longitud');
    }
    $capacidad  = $this->validarEntero($capacidad, 'Capacidad');

    return $this->contenedorModel->insertContenedor($idCircuito, $idTipo, $calle, $numCalle, $latitud, $longitud, $capacidad, $estado);
}
 
    public function updateContenedor($id, $idCircuito, $idTipo, $calle, $numCalle, $latitud, $longitud, $capacidad) {
        $id         = $this->validarId($id);
        $idCircuito = $this->validarId($idCircuito);
        $idTipo     = $this->validarId($idTipo);

        $contenedor = $this->contenedorModel->getContenedor($id);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $id.");
        }

        if ($contenedor['estado'] === 'en_deposito') {
            $calle    = null;
            $numCalle = null;
            $latitud  = null;
            $longitud = null;
        } else {
            $calle    = $this->validarString($calle, 'Calle', 100);
            $numCalle = $this->validarEntero($numCalle, 'Número de puerta');
            $latitud  = $this->validarCoordenada($latitud, 'Latitud');
            $longitud = $this->validarCoordenada($longitud, 'Longitud');
        }
        $capacidad  = $this->validarEntero($capacidad, 'Capacidad');

        return $this->contenedorModel->updateContenedor($id, $idCircuito, $idTipo, $calle, $numCalle, $latitud, $longitud, $capacidad);
    }
 
    public function bajaContenedor($id) {
        $id = $this->validarId($id);
 
        $contenedor = $this->contenedorModel->getContenedor($id);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $id.");
        }
 
        return $this->contenedorModel->bajaContenedor($id);
    }
 
    public function getContenedor($id) {
        $id = $this->validarId($id);
        return $this->contenedorModel->getContenedor($id);
    }
 
    public function getAllContenedores() {
        return $this->contenedorModel->getAllContenedores();
    }
 
 
    public function cambiarEstado($id, $nuevoEstado) {
        $id         = $this->validarId($id);
        $nuevoEstado = $this->validarEstado($nuevoEstado);
 
        $contenedor = $this->contenedorModel->getContenedor($id);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $id.");
        }
 
        return $this->contenedorModel->cambiarEstado($id, $nuevoEstado);
    }
    public function filtrarCriticos() {
    return $this->contenedorModel->filtrarCriticos();
}
 
    public function clasificarPorTipoResiduo($id, $idTipo) {
    $id     = $this->validarId($id);
    $idTipo = $this->validarId($idTipo);

    $contenedor = $this->contenedorModel->getContenedor($id);
    if (!$contenedor) {
        throw new InvalidArgumentException("No existe un contenedor con el ID $id.");
    }
    if (!$this->contenedorModel->tipoResiduoExiste($idTipo)) {
        throw new InvalidArgumentException("No existe un tipo de residuo con el ID $idTipo.");
    }

    return $this->contenedorModel->clasificarPorTipoResiduo($id, $idTipo);
}
 
    public function asignarACircuito($id, $idCircuito) {
        $id         = $this->validarId($id);
        $idCircuito = $this->validarId($idCircuito);

        $contenedor = $this->contenedorModel->getContenedor($id);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $id.");
        }
        if (!$this->contenedorModel->circuitoExiste($idCircuito)) {
            throw new InvalidArgumentException("No existe un circuito con el ID $idCircuito.");
        }

        return $this->contenedorModel->asignarACircuito($id, $idCircuito);
    }
 
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
 
    public function getHistorial($id) {
        $id       = $this->validarId($id);
        $historial = $this->contenedorModel->getHistorial($id);
 
        usort($historial, function ($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });
 
        return $historial;
    }

 
    public function filtrarMunicipio($idMunicipio) {
        $idMunicipio = $this->validarId($idMunicipio);
        return $this->contenedorModel->filtrarMunicipio($idMunicipio);
    }
 
    public function filtrarTipoResiduo($idTipo) {
        $idTipo = $this->validarId($idTipo);
        return $this->contenedorModel->filtrarTipoResiduo($idTipo);
    }
 
    public function filtrarEstado($estado) {
        $estado = $this->validarEstado($estado);
        return $this->contenedorModel->filtrarEstado($estado);
    }
}
?>
