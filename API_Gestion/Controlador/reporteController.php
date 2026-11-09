<?php

class reporteController {

    private $reporteModel;

    public function __construct() {
        require_once __DIR__ . '/../Modelo/reporteModel.php';
        $this->reporteModel = new ReporteModel();
    }

    public function getResumenGeneral($rango = 'hoy') {
        return [
            'contenedoresRecolectados' => $this->reporteModel->getContenedoresRecolectados($rango),
            'usuariosRegistrados'      => $this->reporteModel->getUsuariosRegistrados($rango),
            'residuosRecolectados'     => $this->reporteModel->getResiduosRecolectados($rango),
        ];
    }

    public function getResumenPublico() {
        return $this->reporteModel->getResumenPublico();
    }

    public function getDistribucionLlenado($rango = 'hoy') {
        $niveles = $this->reporteModel->getNivelesLlenado($rango);
        $buckets = array_fill(0, 10, 0);

        foreach ($niveles as $nivel) {
            $indice = intdiv(min(max($nivel, 0), 100), 10);
            if ($indice > 9) $indice = 9;
            $buckets[$indice]++;
        }

        $etiquetas = [];
        for ($i = 0; $i < 10; $i++) {
            $etiquetas[] = ($i * 10) . '-' . ($i * 10 + 10);
        }

        return ['etiquetas' => $etiquetas, 'valores' => array_values($buckets)];
    }

    public function getIndicadores($rango = 'hoy') {
        return [
            'contenedoresFuncionales'    => $this->reporteModel->getPorcentajeContenedoresFuncionales($rango),
            'contenedoresSinRecolectar'  => $this->reporteModel->getPorcentajeContenedoresSinRecolectar($rango),
            'incidenciasResueltas'       => $this->reporteModel->getPorcentajeIncidenciasResueltas($rango),
            'levantesCompletados'        => $this->reporteModel->getPorcentajeLevantesCompletados($rango),
        ];
    }

    public function getZonasCriticas($rango = 'mes') {
        return $this->reporteModel->getZonasCriticas($rango);
    }

    public function getVolumenPorTipoYPeriodo($rango = 'siempre', $agrupacion = 'mes') {
        return $this->reporteModel->getVolumenPorTipoYPeriodo($rango, $agrupacion);
    }

    public function getTopZonasIncidencias($limite = 5, $rango = 'mes') {
        return $this->reporteModel->getTopZonasIncidencias($limite, $rango);
    }

    public function getAlertasContenedores() {
        return $this->reporteModel->getAlertasContenedores();
    }

    public function getProyeccionesLlenado() {
        return $this->reporteModel->getProyeccionesLlenado();
    }

    public function getReciclaje($rango = 'siempre') {
        return $this->reporteModel->getReciclaje($rango);
    }

    public function getIncidenciasDetalle($rango = 'siempre', $estado = null) {
        return $this->reporteModel->getIncidenciasDetalle($rango, $estado);
    }

    public function getTiemposResolucion($rango = 'siempre') {
        return $this->reporteModel->getTiemposResolucion($rango);
    }

    public function getFrecuenciaRecoleccion($turno = null, $dia = null, $rango = 'siempre') {
        return $this->reporteModel->getFrecuenciaRecoleccion($turno, $dia, $rango);
    }

    public function getCapacidadVertederos() {
        return $this->reporteModel->getCapacidadVertederos();
    }

    public function getIngresosPorCentro($rango = 'siempre') {
        return $this->reporteModel->getIngresosPorCentro($rango);
    }

    public function getClasificacionResiduos($rango = 'siempre') {
        return $this->reporteModel->getClasificacionResiduos($rango);
    }

    public function getEvolucionUsuarios($rango = 'siempre') {
        return $this->reporteModel->getEvolucionUsuarios($rango);
    }

    public function getDisponibilidadCamiones($rango = 'siempre') {
        return $this->reporteModel->getDisponibilidadCamiones($rango);
    }

    public function getDisponibilidadMaquinaria() {
        return $this->reporteModel->getDisponibilidadMaquinaria();
    }
}
