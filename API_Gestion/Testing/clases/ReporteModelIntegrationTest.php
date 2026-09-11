<?php

require_once __DIR__ . '/../../Modelo/reporteModel.php';
require_once __DIR__ . '/../../Modelo/incidenciaModel.php';

final class ReporteModelIntegrationTest extends DbTestCase
{
    private function vincularLevanteAContenedor(int $levanteId, int $contenedorId, array $overrides = []): void
    {
        $datos = array_merge([
            'estado_encontrado' => 'todo_ok',
            'hora_vaciado' => date('Y-m-d H:i:s'),
            'peso' => 50,
        ], $overrides);

        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO contenedor_levante (id_levante, id_contenedor, estado_encontrado, hora_vaciado, peso) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('iissi', $levanteId, $contenedorId, $datos['estado_encontrado'], $datos['hora_vaciado'], $datos['peso']);
        $stmt->execute();
        $stmt->close();
    }

    public function testGetContenedoresRecolectadosCuentaContenedoresDistintosHoy(): void
    {
        $model = new ReporteModel();
        $levanteId = $this->crearLevante();
        $contenedorUno = $this->crearContenedor();
        $contenedorDos = $this->crearContenedor();
        $this->vincularLevanteAContenedor($levanteId, $contenedorUno);
        $this->vincularLevanteAContenedor($levanteId, $contenedorDos);

        $this->assertSame(2, $model->getContenedoresRecolectados('hoy'));
    }

    public function testGetContenedoresRecolectadosEsCeroSiempreConRangoHoySinDatos(): void
    {
        $model = new ReporteModel();

        $this->assertSame(0, $model->getContenedoresRecolectados('hoy'));
    }

    public function testGetUsuariosRegistradosCuentaLosDeHoy(): void
    {
        $model = new ReporteModel();
        $this->crearUsuario();
        $this->crearUsuario();

        $this->assertSame(2, $model->getUsuariosRegistrados('hoy'));
    }

    public function testGetResiduosRecolectadosSumaElTotalRecolectado(): void
    {
        $model = new ReporteModel();
        $this->crearLevante(null, ['total_recolectado' => 120]);
        $this->crearLevante(null, ['total_recolectado' => 80]);

        $this->assertSame(200.0, $model->getResiduosRecolectados('hoy'));
    }

    public function testGetNivelesLlenadoDevuelveLosNivelesDeContenedoresConLevanteHoy(): void
    {
        $model = new ReporteModel();
        $levanteId = $this->crearLevante();
        $contenedorId = $this->crearContenedor(null, ['nivel_llenado' => 75]);
        $this->vincularLevanteAContenedor($levanteId, $contenedorId);

        $this->assertSame([75], $model->getNivelesLlenado('hoy'));
    }

    public function testGetPorcentajeContenedoresFuncionalesCalculaSobreLosRecolectadosEnElRango(): void
    {
        $model = new ReporteModel();
        $levanteId = $this->crearLevante();
        $funcional = $this->crearContenedor(null, ['estado' => 'funcional']);
        $desbordado = $this->crearContenedor(null, ['estado' => 'desbordado']);
        $this->vincularLevanteAContenedor($levanteId, $funcional);
        $this->vincularLevanteAContenedor($levanteId, $desbordado);

        $this->assertSame(50.0, $model->getPorcentajeContenedoresFuncionales('hoy'));
    }

    public function testGetPorcentajeContenedoresFuncionalesEsCeroSinDatosEnElRango(): void
    {
        $model = new ReporteModel();

        $this->assertSame(0.0, $model->getPorcentajeContenedoresFuncionales('hoy'));
    }

    public function testGetPorcentajeContenedoresSinRecolectarUsaEstadoDesbordado(): void
    {
        $model = new ReporteModel();
        $levanteId = $this->crearLevante();
        $funcional = $this->crearContenedor(null, ['estado' => 'funcional']);
        $desbordado = $this->crearContenedor(null, ['estado' => 'desbordado']);
        $this->vincularLevanteAContenedor($levanteId, $funcional);
        $this->vincularLevanteAContenedor($levanteId, $desbordado);

        $this->assertSame(50.0, $model->getPorcentajeContenedoresSinRecolectar('hoy'));
    }

    public function testGetPorcentajeIncidenciasResueltasCalculaSobreLasDelRango(): void
    {
        $model = new ReporteModel();
        $incidencias = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $resuelta = $incidencias->crearIncidencia($ci, 'rotura', null, null, 0);
        $incidencias->cerrar($resuelta);
        $incidencias->crearIncidencia($ci, 'desborde', null, null, 0);

        $this->assertSame(50.0, $model->getPorcentajeIncidenciasResueltas('hoy'));
    }

    public function testGetPorcentajeIncidenciasResueltasEsCeroSinIncidenciasEnElRango(): void
    {
        $model = new ReporteModel();

        $this->assertSame(0.0, $model->getPorcentajeIncidenciasResueltas('hoy'));
    }

    public function testGetPorcentajeLevantesCompletadosCalculaSobreLosDelRango(): void
    {
        $model = new ReporteModel();
        $this->crearLevante(null, ['estado' => 'completado']);
        $this->crearLevante(null, ['estado' => 'cancelado']);

        $this->assertSame(50.0, $model->getPorcentajeLevantesCompletados('hoy'));
    }

    public function testGetZonasCriticasDetectaMunicipioConMuchosDesbordados(): void
    {
        $model = new ReporteModel();
        $municipioId = $this->crearMunicipio('Municipio Saturado');
        $circuitoId = $this->crearCircuito($municipioId);
        $this->crearContenedor($circuitoId, ['estado' => 'desbordado']);
        $this->crearContenedor($circuitoId, ['estado' => 'funcional']);

        $otroMunicipioId = $this->crearMunicipio('Municipio Tranquilo');
        $otroCircuitoId = $this->crearCircuito($otroMunicipioId);
        $this->crearContenedor($otroCircuitoId, ['estado' => 'funcional']);

        $zonas = $model->getZonasCriticas();

        $this->assertCount(1, $zonas);
        $this->assertSame('Municipio Saturado', $zonas[0]['municipio']);
        $this->assertSame('desbordados', $zonas[0]['motivo']);
        $this->assertSame(50.0, $zonas[0]['porcentaje']);
    }

    public function testGetZonasCriticasDetectaMunicipioConMuchasIncidenciasEsteMes(): void
    {
        $model = new ReporteModel();
        $incidencias = new IncidenciaModel();
        $municipioId = $this->crearMunicipio('Municipio Conflictivo');
        $circuitoId = $this->crearCircuito($municipioId);
        $contenedorId = $this->crearContenedor($circuitoId, ['estado' => 'funcional']);
        $ci = $this->crearUsuario();

        for ($i = 0; $i < ReporteModel::UMBRAL_INCIDENCIAS_MES + 1; $i++) {
            $idIncidencia = $incidencias->crearIncidencia($ci, 'rotura', null, null, 0);
            $incidencias->vincularContenedor($idIncidencia, $contenedorId);
        }

        $zonas = $model->getZonasCriticas();

        $this->assertCount(1, $zonas);
        $this->assertSame('Municipio Conflictivo', $zonas[0]['municipio']);
        $this->assertSame('incidencias', $zonas[0]['motivo']);
    }

    public function testGetZonasCriticasNoIncluyeMunicipiosPorDebajoDeLosUmbrales(): void
    {
        $model = new ReporteModel();
        $municipioId = $this->crearMunicipio();
        $circuitoId = $this->crearCircuito($municipioId);
        $this->crearContenedor($circuitoId, ['estado' => 'funcional']);

        $this->assertSame([], $model->getZonasCriticas());
    }

    public function testGetVolumenPorTipoYPeriodoAgrupaPorTipoYSumaKg(): void
    {
        $model = new ReporteModel();
        $tipoId = $this->crearTipoResiduo(['descripcion' => 'Plásticos', 'color' => 'Amarillo']);
        $contenedorId = $this->crearContenedor(null, ['id_tipo' => $tipoId]);
        $levanteId = $this->crearLevante(null, ['estado' => 'completado']);
        $this->vincularLevanteAContenedor($levanteId, $contenedorId, ['peso' => 60]);

        $resultado = $model->getVolumenPorTipoYPeriodo('siempre');

        $this->assertCount(1, $resultado);
        $this->assertSame('Plásticos', $resultado[0]['tipoResiduo']);
        $this->assertSame(60.0, $resultado[0]['totalKg']);
        $this->assertSame(1, $resultado[0]['totalRecolecciones']);
    }

    public function testGetVolumenPorTipoYPeriodoIgnoraLevantesNoCompletados(): void
    {
        $model = new ReporteModel();
        $tipoId = $this->crearTipoResiduo();
        $contenedorId = $this->crearContenedor(null, ['id_tipo' => $tipoId]);
        $levanteId = $this->crearLevante(null, ['estado' => 'cancelado']);
        $this->vincularLevanteAContenedor($levanteId, $contenedorId);

        $this->assertSame([], $model->getVolumenPorTipoYPeriodo('siempre'));
    }

    public function testGetIncidenciasDetalleIncluyeHorasResolucionParaLasResueltas(): void
    {
        $model = new ReporteModel();
        $incidencias = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $id = $incidencias->crearIncidencia($ci, 'rotura', null, null, 0);
        $incidencias->cerrar($id);

        $detalle = $model->getIncidenciasDetalle('hoy');

        $this->assertCount(1, $detalle);
        $this->assertSame('resuelta', $detalle[0]['estado']);
        $this->assertNotNull($detalle[0]['horasResolucion']);
    }

    public function testGetIncidenciasDetalleDejaHorasResolucionNulaSiSigueAbierta(): void
    {
        $model = new ReporteModel();
        $incidencias = new IncidenciaModel();
        $ci = $this->crearUsuario();
        $incidencias->crearIncidencia($ci, 'desborde', null, null, 0);

        $detalle = $model->getIncidenciasDetalle('hoy');

        $this->assertCount(1, $detalle);
        $this->assertNull($detalle[0]['horasResolucion']);
    }

    public function testGetFrecuenciaRecoleccionCuentaLevantesCompletadosPorCircuito(): void
    {
        $model = new ReporteModel();
        $circuitoId = $this->crearCircuito(null, ['turno' => 'mañana', 'frecuencia' => 'diaria (lunes a viernes)']);
        $rutaId = $this->crearRuta($circuitoId);
        $this->crearLevante($rutaId, ['estado' => 'completado']);
        $this->crearLevante($rutaId, ['estado' => 'pendiente']);

        $resultado = $model->getFrecuenciaRecoleccion('mañana');

        $this->assertCount(1, $resultado);
        $this->assertSame(1, $resultado[0]['levantesCompletados']);
    }

    public function testGetFrecuenciaRecoleccionFiltraPorTurno(): void
    {
        $model = new ReporteModel();
        $this->crearCircuito(null, ['turno' => 'mañana']);
        $this->crearCircuito(null, ['turno' => 'tarde']);

        $this->assertCount(1, $model->getFrecuenciaRecoleccion('tarde'));
    }

    public function testGetCapacidadVertederosSoloIncluyeVertederosYRellenos(): void
    {
        $model = new ReporteModel();
        $municipioId = $this->crearMunicipio();
        $vertederoId = $this->crearCentroAcopio($municipioId, ['tipo' => 'vertedero', 'capacidad_total' => 1000]);
        $this->crearCentroAcopio($municipioId, ['tipo' => 'ecocentro']);
        $tipoId = $this->crearTipoResiduo();
        $conn = $this->conn();
        $stmt = $conn->prepare('INSERT INTO centro_residuo (id_centro, id_residuo, ocupacion_actual, capacidad) VALUES (?, ?, 250, 1000)');
        $stmt->bind_param('ii', $vertederoId, $tipoId);
        $stmt->execute();
        $stmt->close();

        $resultado = $model->getCapacidadVertederos();

        $this->assertCount(1, $resultado);
        $this->assertSame(250, $resultado[0]['ocupacionActual']);
        $this->assertSame(25.0, $resultado[0]['porcentaje']);
    }

    public function testGetClasificacionResiduosCalculaPorcentajeDeKgPorTipo(): void
    {
        $model = new ReporteModel();
        $tipoId = $this->crearTipoResiduo(['descripcion' => 'Vidrio']);
        $contenedorId = $this->crearContenedor(null, ['id_tipo' => $tipoId, 'estado' => 'funcional']);
        $levanteId = $this->crearLevante();
        $this->vincularLevanteAContenedor($levanteId, $contenedorId, ['peso' => 40]);

        $resultado = $model->getClasificacionResiduos();

        $this->assertCount(1, $resultado);
        $this->assertSame('Vidrio', $resultado[0]['tipoResiduo']);
        $this->assertSame(1, $resultado[0]['totalContenedores']);
        $this->assertSame(1, $resultado[0]['funcionales']);
        $this->assertSame(40.0, $resultado[0]['totalKg']);
        $this->assertSame(100.0, $resultado[0]['porcentaje']);
    }

    public function testGetEvolucionUsuariosCuentaPorRolEnRangoSiempre(): void
    {
        $model = new ReporteModel();
        $this->crearUsuario(null, ['rol' => 'vecino']);
        $this->crearUsuario(null, ['rol' => 'vecino']);
        $this->crearUsuario(null, ['rol' => 'operario']);

        $resultado = $model->getEvolucionUsuarios('siempre');

        $this->assertCount(1, $resultado);
        $this->assertSame(3, $resultado[0]['nuevosUsuarios']);
        $this->assertSame(2, $resultado[0]['vecinos']);
        $this->assertSame(1, $resultado[0]['operarios']);
    }
}
