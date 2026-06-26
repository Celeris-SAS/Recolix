<?php

    class ContenedorController {

        private $contenedorModel;

        public function __construct() {
            require_once __DIR__ . '/ContenedorModel.php';
            $this->contenedorModel = new ContenedorModel();
        }

        public function getAllContenedores() {
            return $this->contenedorModel->listarContenedores();
        }

        public function insertContenedor($id, $idCircuito, $idTipoResiduo, $calle, $numCalle, $latitud, $longitud, $capacidad, $estado) {
            return $this->contenedorModel->altaContenedor($id, $idCircuito, $idTipoResiduo, $calle, $numCalle, $latitud, $longitud, $capacidad, $estado);
        }

        public function updateContenedor($id, $calle, $numCalle, $latitud, $longitud, $capacidad) {
            return $this->contenedorModel->modificarContenedor($id, $calle, $numCalle, $latitud, $longitud, $capacidad);
        }

        public function deleteContenedor($id) {
            return $this->contenedorModel->bajaContenedor($id);
        }

        // ==========================================
        // Operaciones específicas (instanciando por ID)
        // ==========================================

        public function cambiarEstado($id, $nuevoEstado) {
            $contenedor = new ContenedorModel($id);
            return $contenedor->cambiarEstado($nuevoEstado);
        }

        public function registrarReparacion($id) {
            $contenedor = new ContenedorModel($id);
            return $contenedor->registrarReparacion();
        }

        public function clasificarPorTipoResiduo($id, $idTipoResiduo) {
            $contenedor = new ContenedorModel($id);
            return $contenedor->clasificarPorTipoResiduo($idTipoResiduo);
        }

        public function asignarACircuito($id, $idCircuito) {
            $contenedor = new ContenedorModel($id);
            return $contenedor->asignarACircuito($idCircuito);
        }

        public function consultarNivelLlenado($id) {
            $contenedor = new ContenedorModel($id);
            return $contenedor->consultarNivelLlenado();
        }

        public function obtenerHistorial($id) {
            $contenedor = new ContenedorModel($id);
            return $contenedor->obtenerHistorial();
        }

    }
?>
