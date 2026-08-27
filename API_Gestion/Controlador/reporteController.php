<?php

class reporteController {

    private $reporteModel;

    public function __construct() {
        require_once __DIR__ . '/../Modelo/reporteModel.php';
        $this->reporteModel = new ReporteModel();
    }

    public function getResumenGeneral() {
        return [
            'contenedoresRecolectados' => $this->reporteModel->getContenedoresRecolectados(),
            'usuariosRegistrados'      => $this->reporteModel->getUsuariosRegistrados(),
            'residuosRecolectados'     => $this->reporteModel->getResiduosRecolectados(),
        ];
    }

    public function getDistribucionLlenado() {
        $niveles = $this->reporteModel->getNivelesLlenado();
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

    public function getIndicadores() {
        return [
            'contenedoresFuncionales'    => $this->reporteModel->getPorcentajeContenedoresFuncionales(),
            'contenedoresSinRecolectar'  => $this->reporteModel->getPorcentajeContenedoresSinRecolectar(),
            'incidenciasResueltas'       => $this->reporteModel->getPorcentajeIncidenciasResueltas(),
            'levantesCompletados'        => $this->reporteModel->getPorcentajeLevantesCompletados(),
        ];
    }

    public function getZonasCriticas() {
        return $this->reporteModel->getZonasCriticas();
    }
}
