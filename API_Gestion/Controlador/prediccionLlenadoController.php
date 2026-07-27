<?php

class prediccionLlenadoController {

    private $prediccionModel;
    private $contenedorModel;

    public function __construct() {
        require_once __DIR__ . '/../Modelo/prediccionLlenadoModel.php';
        require_once __DIR__ . '/../Modelo/contenedorModel.php';
        $this->prediccionModel = new prediccionLlenadoModel();
        $this->contenedorModel = new contenedorModel();
    }
 
 
    private function validarId($id) {
        if (!is_numeric($id) || intval($id) <= 0) {
            throw new InvalidArgumentException("El ID debe ser un número entero positivo.");
        }
        return intval($id);
    }
 
 
    public function calcularPrediccion($idContenedor) {
        $idContenedor = $this->validarId($idContenedor);
 
        $contenedor = $this->contenedorModel->getContenedor($idContenedor);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $idContenedor.");
        }
        if ($contenedor['capacidad'] <= 0) {
            throw new InvalidArgumentException("La capacidad del contenedor no puede ser cero.");
        }
 
        $levantes = $this->prediccionModel->getDatosLevantes($idContenedor);
        if (empty($levantes)) {
            throw new RuntimeException("No hay levantes registrados para el contenedor $idContenedor.");
        }
 
        $hace7dias = strtotime('-7 days');
        $levantesRecientes = array_filter($levantes, function ($l) use ($hace7dias) {
            return strtotime($l['hora_levante']) >= $hace7dias;
        });
 
        if (empty($levantesRecientes)) {
            throw new RuntimeException("No hay levantes en los últimos 7 días para calcular la predicción.");
        }
 
        $totalPeso = array_sum(array_column($levantesRecientes, 'peso'));
        $promedio  = $totalPeso / count($levantesRecientes);
 
        $nivelProyectado = intval(round(($promedio / $contenedor['capacidad']) * 100));
 
        return $this->prediccionModel->insertPrediccion($idContenedor, $nivelProyectado);
    }
 
    public function getUltimaPrediccion($idContenedor) {
        $idContenedor = $this->validarId($idContenedor);
 
        $contenedor = $this->contenedorModel->getContenedor($idContenedor);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $idContenedor.");
        }
 
        return $this->prediccionModel->getUltimaPrediccion($idContenedor);
    }
 
    public function getPrediccion($id) {
        $id = $this->validarId($id);
        return $this->prediccionModel->getPrediccion($id);
    }
 
    public function getAllPredicciones($idContenedor) {
        $idContenedor = $this->validarId($idContenedor);
 
        $contenedor = $this->contenedorModel->getContenedor($idContenedor);
        if (!$contenedor) {
            throw new InvalidArgumentException("No existe un contenedor con el ID $idContenedor.");
        }
 
        return $this->prediccionModel->getAllPredicciones($idContenedor);
    }
}
?>
