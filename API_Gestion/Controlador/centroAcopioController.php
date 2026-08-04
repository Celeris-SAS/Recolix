<?php

class CentroAcopioController {

    private $centroAcopioModel;
    private $tiposValidos = ['ecocentro', 'planta_clasificacion', 'relleno_sanitario', 'vertedero'];
    private $estadosValidos = ['operativo', 'saturado', 'cerrado'];

    public function __construct() {
        require_once __DIR__ . '/../Modelo/centroAcopioModel.php';
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

    private function validarEntero($valor, $nombre) {
        if (!is_numeric($valor) || floatval($valor) != intval($valor) || intval($valor) <= 0) {
            throw new InvalidArgumentException("$nombre debe ser un número entero positivo.");
        }
        return intval($valor);
    }

    // A diferencia de validarEntero, acá el 0 es un valor válido: un centro
    // recién asignado a un tipo de residuo empieza con ocupación 0.
    private function validarEnteroNoNegativo($valor, $nombre) {
        if (!is_numeric($valor) || floatval($valor) != intval($valor) || intval($valor) < 0) {
            throw new InvalidArgumentException("$nombre debe ser un número entero mayor o igual a 0.");
        }
        return intval($valor);
    }

    private function validarCoordenada($valor, $nombre) {
        if (!is_numeric($valor)) {
            throw new InvalidArgumentException("$nombre debe ser un número decimal válido.");
        }
        $valor = floatval($valor);
        $limite = ($nombre === 'Latitud') ? 90 : 180;
        if ($valor < -$limite || $valor > $limite) {
            throw new InvalidArgumentException("$nombre debe estar entre -$limite y $limite.");
        }
        return $valor;
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
            'idMunicipio' => ['tipo' => 'entero', 'min' => 1, 'label' => 'Municipio'],
            'nombre' => ['max' => 100, 'label' => 'Nombre'],
            'tipo' => ['valores' => $this->tiposValidos, 'label' => 'Tipo'],
            'calle' => ['max' => 100, 'label' => 'Calle'],
            'numCalle' => ['tipo' => 'entero', 'min' => 1, 'label' => 'Número de puerta'],
            'latitud' => ['tipo' => 'decimal', 'label' => 'Latitud'],
            'longitud' => ['tipo' => 'decimal', 'label' => 'Longitud'],
            'capacidadTotal' => ['tipo' => 'entero', 'min' => 1, 'label' => 'Capacidad total'],
            'estado' => ['valores' => $this->estadosValidos, 'label' => 'Estado']
        ];
    }

    // ==========================================
    // CRUD PRINCIPAL
    // ==========================================

    // RF74 — Alta de centro de acopio.
    public function insertCentro($idMunicipio, $nombre, $tipo, $calle, $numCalle, $latitud, $longitud, $capacidadTotal, $estado) {
        $idMunicipio = $this->validarId($idMunicipio);
        $nombre = $this->validarString($nombre, 'Nombre');
        $tipo = $this->validarTipo($tipo);
        $calle = $this->validarString($calle, 'Calle');
        $numCalle = $this->validarEntero($numCalle, 'Número de puerta');
        $latitud = $this->validarCoordenada($latitud, 'Latitud');
        $longitud = $this->validarCoordenada($longitud, 'Longitud');
        $capacidadTotal = $this->validarEntero($capacidadTotal, 'Capacidad total');
        $estado = $this->validarEstado($estado);

        if (!$this->centroAcopioModel->municipioExiste($idMunicipio)) {
            throw new InvalidArgumentException("No existe un municipio con el ID $idMunicipio.");
        }

        return $this->centroAcopioModel->insertCentro($idMunicipio, $nombre, $tipo, $calle, $numCalle, $latitud, $longitud, $capacidadTotal, $estado);
    }

    // RF75 — Modificación de centro de acopio.
    public function updateCentro($id, $idMunicipio, $nombre, $tipo, $calle, $numCalle, $latitud, $longitud, $capacidadTotal, $estado) {
        $id = $this->validarId($id);
        $idMunicipio = $this->validarId($idMunicipio);
        $nombre = $this->validarString($nombre, 'Nombre');
        $tipo = $this->validarTipo($tipo);
        $calle = $this->validarString($calle, 'Calle');
        $numCalle = $this->validarEntero($numCalle, 'Número de puerta');
        $latitud = $this->validarCoordenada($latitud, 'Latitud');
        $longitud = $this->validarCoordenada($longitud, 'Longitud');
        $capacidadTotal = $this->validarEntero($capacidadTotal, 'Capacidad total');
        $estado = $this->validarEstado($estado);

        $centro = $this->centroAcopioModel->getCentro($id);
        if (!$centro) {
            throw new InvalidArgumentException("No existe un centro de acopio con el ID $id.");
        }
        if (!$this->centroAcopioModel->municipioExiste($idMunicipio)) {
            throw new InvalidArgumentException("No existe un municipio con el ID $idMunicipio.");
        }

        return $this->centroAcopioModel->updateCentro($id, $idMunicipio, $nombre, $tipo, $calle, $numCalle, $latitud, $longitud, $capacidadTotal, $estado);
    }

    // RF76 — Baja de centro de acopio.
    public function bajaCentro($id) {
        $id = $this->validarId($id);

        $centro = $this->centroAcopioModel->getCentro($id);
        if (!$centro) {
            throw new InvalidArgumentException("No existe un centro de acopio con el ID $id.");
        }

        return $this->centroAcopioModel->bajaCentro($id);
    }

    // RF77 — Obtener un centro de acopio por id.
    public function getCentro($id) {
        $id = $this->validarId($id);
        return $this->centroAcopioModel->getCentro($id);
    }

    // RF77 — Listado de todos los centros de acopio.
    public function getAllCentros() {
        return $this->centroAcopioModel->getAllCentros();
    }

    // ==========================================
    // FILTROS (soporte de RF77)
    // ==========================================

    public function filtrarMunicipio($idMunicipio) {
        $idMunicipio = $this->validarId($idMunicipio);
        return $this->centroAcopioModel->filtrarMunicipio($idMunicipio);
    }

    public function filtrarTipo($tipo) {
        $tipo = $this->validarTipo($tipo);
        return $this->centroAcopioModel->filtrarTipo($tipo);
    }

    public function filtrarEstado($estado) {
        $estado = $this->validarEstado($estado);
        return $this->centroAcopioModel->filtrarEstado($estado);
    }

    // ==========================================
    // OPERACIONES DE NEGOCIO
    // ==========================================

    // RF78 — Consulta de ocupación del centro de acopio.
    // El controller calcula el porcentaje a partir de los datos crudos del modelo,
    // mismo criterio que getNivelLlenado en contenedorController.
    public function getOcupacion($idCentro) {
        $idCentro = $this->validarId($idCentro);

        $centro = $this->centroAcopioModel->getCentro($idCentro);
        if (!$centro) {
            throw new InvalidArgumentException("No existe un centro de acopio con el ID $idCentro.");
        }

        $dato = $this->centroAcopioModel->getOcupacion($idCentro);
        if (!$dato || $dato['capacidad'] === null) {
            return ['ocupado' => 0, 'capacidad' => 0, 'porcentaje' => null];
        }
        if ($dato['capacidad'] <= 0) {
            throw new InvalidArgumentException("La capacidad del centro no puede ser cero.");
        }

        return [
            'ocupado' => floatval($dato['ocupado']),
            'capacidad' => floatval($dato['capacidad']),
            'porcentaje' => round(($dato['ocupado'] / $dato['capacidad']) * 100, 2)
        ];
    }

    // Habilita/actualiza qué capacidad tiene el centro para un tipo de residuo.
    // No es un RF en sí mismo, es la base sobre la que se calcula RF78.
    public function asignarTipoResiduo($idCentro, $idResiduo, $capacidad) {
        $idCentro = $this->validarId($idCentro);
        $idResiduo = $this->validarId($idResiduo);
        $capacidad = $this->validarEntero($capacidad, 'Capacidad');

        $centro = $this->centroAcopioModel->getCentro($idCentro);
        if (!$centro) {
            throw new InvalidArgumentException("No existe un centro de acopio con el ID $idCentro.");
        }
        if (!$this->centroAcopioModel->tipoResiduoExiste($idResiduo)) {
            throw new InvalidArgumentException("No existe un tipo de residuo con el ID $idResiduo.");
        }

        return $this->centroAcopioModel->asignarTipoResiduo($idCentro, $idResiduo, $capacidad);
    }

    // Lista los tipos de residuo configurados para un centro, con su capacidad y ocupación.
    public function getResiduosPorCentro($idCentro) {
        $idCentro = $this->validarId($idCentro);

        $centro = $this->centroAcopioModel->getCentro($idCentro);
        if (!$centro) {
            throw new InvalidArgumentException("No existe un centro de acopio con el ID $idCentro.");
        }

        return $this->centroAcopioModel->getResiduosPorCentro($idCentro);
    }

    // Actualiza la ocupación de un tipo de residuo puntual dentro de un centro.
    // Reemplazo manual de lo que en el futuro debería derivarse del registro de
    // traslados (RF42), que todavía no está implementado.
    public function actualizarOcupacion($idCentro, $idResiduo, $ocupacionActual) {
        $idCentro = $this->validarId($idCentro);
        $idResiduo = $this->validarId($idResiduo);
        $ocupacionActual = $this->validarEnteroNoNegativo($ocupacionActual, 'Ocupación actual');

        $centro = $this->centroAcopioModel->getCentro($idCentro);
        if (!$centro) {
            throw new InvalidArgumentException("No existe un centro de acopio con el ID $idCentro.");
        }

        $asignacion = $this->centroAcopioModel->getCentroResiduo($idCentro, $idResiduo);
        if (!$asignacion) {
            throw new InvalidArgumentException("Este centro no tiene asignado ese tipo de residuo. Asignalo primero con una capacidad.");
        }
        if ($ocupacionActual > $asignacion['capacidad']) {
            throw new InvalidArgumentException("La ocupación no puede superar la capacidad asignada ({$asignacion['capacidad']}).");
        }

        return $this->centroAcopioModel->actualizarOcupacion($idCentro, $idResiduo, $ocupacionActual);
    }
}
?>
