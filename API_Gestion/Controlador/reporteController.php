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

    public function getZonasCriticas() {
        return $this->reporteModel->getZonasCriticas();
    }

    public function getVolumenPorTipoYPeriodo($rango = 'siempre') {
        return $this->reporteModel->getVolumenPorTipoYPeriodo($rango);
    }

    public function getReciclaje() {
        return $this->reporteModel->getReciclaje();
    }

    public function getIncidenciasDetalle($rango = 'siempre') {
        return $this->reporteModel->getIncidenciasDetalle($rango);
    }

    public function getTiemposResolucion($rango = 'siempre') {
        return $this->reporteModel->getTiemposResolucion($rango);
    }

    public function getFrecuenciaRecoleccion($turno = null, $dia = null) {
        return $this->reporteModel->getFrecuenciaRecoleccion($turno, $dia);
    }

    public function getCapacidadVertederos() {
        return $this->reporteModel->getCapacidadVertederos();
    }

    public function getClasificacionResiduos() {
        return $this->reporteModel->getClasificacionResiduos();
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
