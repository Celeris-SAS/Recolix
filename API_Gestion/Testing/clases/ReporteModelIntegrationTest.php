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
        $incidencias->cerrar($resuelta, 10);
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
        $incidencias->cerrar($id, 10);

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

    private function insertarIncidencia(?int $cuadrillaId, string $estado, string $apertura, ?string $cierre): int
    {
        $conn = $this->conn();
        $stmt = $conn->prepare("INSERT INTO incidencia (id_cuadrilla, tipo, prioridad, estado, fecha_apertura, fecha_cierre) VALUES (?, 'rotura', 'baja', ?, ?, ?)");
        $stmt->bind_param('isss', $cuadrillaId, $estado, $apertura, $cierre);
        $stmt->execute();
        $id = $conn->insert_id;
        $stmt->close();
        return $id;
    }

    public function testGetTiemposResolucionCalculaPromedioMinimoYMaximoPorCuadrilla(): void
    {
        $model = new ReporteModel();
        $cuadrillaId = $this->crearCuadrilla();
        $this->insertarIncidencia($cuadrillaId, 'resuelta', date('Y-m-d H:i:s', strtotime('-5 hours')), date('Y-m-d H:i:s', strtotime('-3 hours')));
        $this->insertarIncidencia($cuadrillaId, 'resuelta', date('Y-m-d H:i:s', strtotime('-10 hours')), date('Y-m-d H:i:s', strtotime('-4 hours')));

        $filas = $model->getTiemposResolucion('siempre');

        $this->assertCount(1, $filas);
        $this->assertSame($cuadrillaId, $filas[0]['cuadrilla']);
        $this->assertSame(2, $filas[0]['incidenciasCerradas']);
        $this->assertEquals(4.0, $filas[0]['horasPromedio']);
        $this->assertEquals(2.0, $filas[0]['horasMinimo']);
        $this->assertEquals(6.0, $filas[0]['horasMaximo']);
    }

    public function testGetTiemposResolucionIgnoraIncidenciasNoResueltasOSinCuadrilla(): void
    {
        $model = new ReporteModel();
        $cuadrillaId = $this->crearCuadrilla();
        $this->insertarIncidencia($cuadrillaId, 'asignada', date('Y-m-d H:i:s', strtotime('-2 hours')), null);
        $this->insertarIncidencia(null, 'resuelta', date('Y-m-d H:i:s', strtotime('-2 hours')), date('Y-m-d H:i:s'));

        $this->assertSame([], $model->getTiemposResolucion('siempre'));
    }

    public function testGetTiemposResolucionRespetaElFiltroDeRango(): void
    {
        $model = new ReporteModel();
        $cuadrillaId = $this->crearCuadrilla();
        $this->insertarIncidencia($cuadrillaId, 'resuelta', date('Y-m-d H:i:s', strtotime('-40 days')), date('Y-m-d H:i:s', strtotime('-39 days')));

        $this->assertSame([], $model->getTiemposResolucion('mes'));
        $this->assertCount(1, $model->getTiemposResolucion('siempre'));
    }

    public function testGetIncidenciasDetalleFiltraAbiertasYResueltas(): void
    {
        $model = new ReporteModel();
        $cuadrillaId = $this->crearCuadrilla();
        $ahora = date('Y-m-d H:i:s');
        $this->insertarIncidencia($cuadrillaId, 'abierta', $ahora, null);
        $this->insertarIncidencia($cuadrillaId, 'asignada', $ahora, null);
        $this->insertarIncidencia($cuadrillaId, 'resuelta', $ahora, $ahora);

        $this->assertCount(2, $model->getIncidenciasDetalle('siempre', 'abiertas'));
        $this->assertCount(1, $model->getIncidenciasDetalle('siempre', 'resueltas'));
        $this->assertCount(3, $model->getIncidenciasDetalle('siempre'));
    }

    public function testGetVolumenPorTipoYPeriodoAgrupaPorDiaSemanaOMes(): void
    {
        $model = new ReporteModel();
        $tipoId = $this->crearTipoResiduo();
        $contenedorId = $this->crearContenedor(null, ['id_tipo' => $tipoId]);
        $ayer = date('Y-m-d', strtotime('-1 day'));
        $hoy = date('Y-m-d');
        $levanteUno = $this->crearLevante(null, ['hora_levante' => $ayer]);
        $levanteDos = $this->crearLevante(null, ['hora_levante' => $hoy]);
        $this->vincularLevanteAContenedor($levanteUno, $contenedorId, ['peso' => 40]);
        $this->vincularLevanteAContenedor($levanteDos, $contenedorId, ['peso' => 60]);

        $porDia = $model->getVolumenPorTipoYPeriodo('mes', 'dia');
        $periodosDia = array_column($porDia, 'periodo');
        $this->assertContains($ayer, $periodosDia);
        $this->assertContains($hoy, $periodosDia);

        $porMes = $model->getVolumenPorTipoYPeriodo('mes', 'mes');
        $totalMes = array_sum(array_column(array_filter($porMes, fn($f) => $f['periodo'] === date('Y-m')), 'totalKg'));
        $this->assertEquals(100.0, $totalMes);

        $porSemana = $model->getVolumenPorTipoYPeriodo('mes', 'semana');
        $this->assertMatchesRegularExpression('/^\d{4}-S\d{2}$/', $porSemana[0]['periodo']);
    }

    private function horaBdHace(int $horas): string
    {
        $fila = $this->conn()->query("SELECT DATE_FORMAT(NOW() - INTERVAL $horas HOUR, '%Y-%m-%d %H:%i:%s') AS h")->fetch_assoc();
        return $fila['h'];
    }

    public function testGetAlertasContenedoresEscalaACriticaA24HorasYMaximaA48(): void
    {
        $model = new ReporteModel();
        $levante = $this->crearLevante();
        $reciente = $this->crearContenedor();
        $critico = $this->crearContenedor();
        $maximo = $this->crearContenedor();
        $this->vincularLevanteAContenedor($levante, $reciente, ['hora_vaciado' => $this->horaBdHace(5)]);
        $this->vincularLevanteAContenedor($levante, $critico, ['hora_vaciado' => $this->horaBdHace(30)]);
        $this->vincularLevanteAContenedor($levante, $maximo, ['hora_vaciado' => $this->horaBdHace(50)]);

        $alertas = $model->getAlertasContenedores();
        $porContenedor = array_column($alertas, 'nivelAlerta', 'idContenedor');

        $this->assertArrayNotHasKey($reciente, $porContenedor);
        $this->assertSame('critica', $porContenedor[$critico]);
        $this->assertSame('maxima', $porContenedor[$maximo]);
        $this->assertSame($maximo, $alertas[0]['idContenedor'], 'El de más horas sin recolectar va primero');
    }

    public function testGetProyeccionesLlenadoDevuelveLaUltimaPorContenedorYMarcaSobrecarga(): void
    {
        $model = new ReporteModel();
        $contenedorId = $this->crearContenedor();
        $conn = $this->conn();
        $conn->query("INSERT INTO prediccion_llenado (id_contenedor, nivel_proyectado, fecha_calculo) VALUES ($contenedorId, 40, '2026-01-01 10:00:00'), ($contenedorId, 95, '2026-01-02 10:00:00')");

        $filas = $model->getProyeccionesLlenado();

        $this->assertCount(1, $filas);
        $this->assertSame(95, $filas[0]['nivelProyectado']);
        $this->assertTrue($filas[0]['sobrecarga']);
    }

    public function testGetTopZonasIncidenciasOrdenaPorCantidadDelUltimoMes(): void
    {
        $model = new ReporteModel();
        $municipioA = $this->crearMunicipio('Municipio A');
        $municipioB = $this->crearMunicipio('Municipio B');
        $contenedorA = $this->crearContenedor($this->crearCircuito($municipioA));
        $contenedorB = $this->crearContenedor($this->crearCircuito($municipioB));
        $conn = $this->conn();
        foreach ([$contenedorA, $contenedorB, $contenedorB] as $contenedorId) {
            $id = $this->insertarIncidencia(null, 'abierta', date('Y-m-d H:i:s'), null);
            $conn->query("INSERT INTO incidencia_contenedor (id_incidencia, id_contenedor) VALUES ($id, $contenedorId)");
        }

        $top = $model->getTopZonasIncidencias(5);

        $this->assertSame('Municipio B', $top[0]['municipio']);
        $this->assertSame(2, $top[0]['incidencias']);
        $this->assertSame(1, $top[1]['incidencias']);
    }

    public function testGetIncidenciasDetalleAceptaUnPeriodoPorFechas(): void
    {
        $model = new ReporteModel();
        $this->insertarIncidencia(null, 'abierta', '2026-09-10 10:00:00', null);
        $this->insertarIncidencia(null, 'abierta', '2026-09-30 23:00:00', null);
        $this->insertarIncidencia(null, 'abierta', '2026-10-01 00:30:00', null);

        $this->assertCount(2, $model->getIncidenciasDetalle('2026-09-01_2026-09-30'));
        $this->assertCount(3, $model->getIncidenciasDetalle('2026-09-01_2026-10-31'));
        $this->assertSame([], $model->getIncidenciasDetalle('2025-01-01_2025-01-31'));
    }

    public function testUnPeriodoConFinAnteriorAlInicioSeRechaza(): void
    {
        $model = new ReporteModel();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('anterior');
        $model->getIncidenciasDetalle('2026-09-30_2026-09-01');
    }

    public function testUnPeriodoConFechaInexistenteSeRechaza(): void
    {
        $model = new ReporteModel();

        $this->expectException(InvalidArgumentException::class);
        $model->getVolumenPorTipoYPeriodo('2026-02-31_2026-03-01');
    }

    public function testGetVolumenPorTipoYPeriodoFiltraPorFechas(): void
    {
        $model = new ReporteModel();
        $contenedorId = $this->crearContenedor(null, ['id_tipo' => $this->crearTipoResiduo()]);
        $this->vincularLevanteAContenedor($this->crearLevante(null, ['hora_levante' => '2026-09-15']), $contenedorId, ['peso' => 40]);
        $this->vincularLevanteAContenedor($this->crearLevante(null, ['hora_levante' => '2026-10-15']), $contenedorId, ['peso' => 60]);

        $septiembre = $model->getVolumenPorTipoYPeriodo('2026-09-01_2026-09-30', 'mes');

        $this->assertCount(1, $septiembre);
        $this->assertEquals(40.0, $septiembre[0]['totalKg']);
    }

    public function testGetClasificacionResiduosFiltraLosKgPorPeriodo(): void
    {
        $model = new ReporteModel();
        $tipoId = $this->crearTipoResiduo();
        $contenedorId = $this->crearContenedor(null, ['id_tipo' => $tipoId]);
        $this->vincularLevanteAContenedor($this->crearLevante(null, ['hora_levante' => '2026-09-15']), $contenedorId, ['peso' => 40]);
        $this->vincularLevanteAContenedor($this->crearLevante(null, ['hora_levante' => '2026-10-15']), $contenedorId, ['peso' => 60]);

        $totalKg = fn(string $rango) => array_sum(array_column($model->getClasificacionResiduos($rango), 'totalKg'));
        $this->assertEquals(100.0, $totalKg('siempre'));
        $this->assertEquals(40.0, $totalKg('2026-09-01_2026-09-30'));
    }

    public function testGetIngresosPorCentroIncluyeLasPlantasYFiltraPorPeriodo(): void
    {
        $model = new ReporteModel();
        $this->conn()->query('DELETE FROM camion_centro');
        $planta = $this->crearCentroAcopio(null, ['nombre' => 'Planta Test', 'tipo' => 'planta_clasificacion']);
        $this->conn()->query("INSERT INTO camion (matricula, tipo_carga, capacidad, anio, modelo, estado) VALUES ('ING1234', 'carga_lateral', 5000, 2020, 'M', 'disponible')");
        $camionId = $this->conn()->insert_id;
        $this->conn()->query("INSERT INTO camion_centro (id_camion, id_centro, fecha_ingreso, peso) VALUES ($camionId, $planta, '2026-09-05', 1500), ($camionId, $planta, '2026-09-20', 1700), ($camionId, $planta, '2026-10-02', 900)");

        $septiembre = array_values(array_filter($model->getIngresosPorCentro('2026-09-01_2026-09-30'), fn($f) => $f['nombre'] === 'Planta Test'));

        $this->assertCount(1, $septiembre, 'Las plantas de clasificación también figuran');
        $this->assertSame(2, $septiembre[0]['ingresos']);
        $this->assertEquals(3200.0, $septiembre[0]['totalKg']);
    }

    public function testGetZonasCriticasIncluyeMunicipiosConMuchosContenedoresEnAlerta(): void
    {
        $model = new ReporteModel();
        $municipioId = $this->crearMunicipio('Municipio Alerta');
        $circuitoId = $this->crearCircuito($municipioId);
        $levante = $this->crearLevante();
        foreach ([1, 2] as $_) {
            $contenedor = $this->crearContenedor($circuitoId);
            $this->vincularLevanteAContenedor($levante, $contenedor, ['hora_vaciado' => $this->horaBdHace(60)]);
        }

        $zonas = array_values(array_filter($model->getZonasCriticas('mes'), fn($z) => $z['municipio'] === 'Municipio Alerta'));

        $this->assertCount(1, $zonas);
        $this->assertSame('alertas', $zonas[0]['motivo']);
        $this->assertSame(2, $zonas[0]['contenedoresEnAlerta']);
    }
}
