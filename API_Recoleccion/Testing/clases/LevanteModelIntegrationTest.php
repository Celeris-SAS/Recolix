<?php

require_once __DIR__ . '/../../Modelo/levanteModel.php';

final class LevanteModelIntegrationTest extends DbTestCase
{
    private function rutaActiva(): int
    {
        $circuitoId = $this->crearCircuito();
        return $this->crearRuta($circuitoId, ['activa' => 1]);
    }

    public function testPlanificarLevanteRoundtrip(): void
    {
        $model = new LevanteModel();
        $rutaId = $this->rutaActiva();
        $camionId = $this->crearCamion(['estado' => 'disponible']);
        $cuadrillaId = $this->crearCuadrilla();

        $levante = $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');

        $this->assertSame('pendiente', $levante['estado']);
        $this->assertSame($camionId, (int) $levante['id_camion']);
    }

    public function testPlanificarLevantePoneElCamionEnDeposito(): void
    {
        $model = new LevanteModel();
        $rutaId = $this->rutaActiva();
        $camionId = $this->crearCamion(['estado' => 'disponible']);
        $cuadrillaId = $this->crearCuadrilla();

        $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');

        $this->assertSame(1, $this->contarFilas('camion', "id_camion = $camionId AND estado = 'en_deposito'"));
    }

    public function testPlanificarLevanteRechazaRutaInactiva(): void
    {
        $model = new LevanteModel();
        $circuitoId = $this->crearCircuito();
        $rutaId = $this->crearRuta($circuitoId, ['activa' => 0]);
        $camionId = $this->crearCamion();
        $cuadrillaId = $this->crearCuadrilla();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ruta activa');
        $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');
    }

    public function testPlanificarLevanteRechazaSiLaRutaYaTieneUnoPendiente(): void
    {
        $model = new LevanteModel();
        $rutaId = $this->rutaActiva();
        $camion1 = $this->crearCamion(['matricula' => 'AAA1111']);
        $camion2 = $this->crearCamion(['matricula' => 'BBB2222']);
        $cuadrillaId = $this->crearCuadrilla();
        $model->planificarLevante($rutaId, $camion1, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('levante pendiente o en curso');
        $model->planificarLevante($rutaId, $camion2, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');
    }

    public function testPlanificarLevanteRechazaCamionNoDisponible(): void
    {
        $model = new LevanteModel();
        $rutaId = $this->rutaActiva();
        $camionId = $this->crearCamion(['estado' => 'en_mantenimiento']);
        $cuadrillaId = $this->crearCuadrilla();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('camión con estado disponible');
        $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');
    }

    public function testPlanificarLevanteRechazaCuadrillaInactiva(): void
    {
        $model = new LevanteModel();
        $rutaId = $this->rutaActiva();
        $camionId = $this->crearCamion();
        $cuadrillaId = $this->crearCuadrilla(null, 'inactiva');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cuadrilla no encontrada o inactiva');
        $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');
    }

    public function testPlanificarLevanteGuardaElDestinoFinalEnCentroLevante(): void
    {
        $model = new LevanteModel();
        $rutaId = $this->rutaActiva();
        $camionId = $this->crearCamion(['estado' => 'disponible']);
        $cuadrillaId = $this->crearCuadrilla();
        $centroId = $this->crearCentro();

        $levante = $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10', $centroId);
        $idLevante = (int) $levante['id_levante'];

        $destino = $this->conn()->query("SELECT * FROM centro_levante WHERE id_levante = $idLevante")->fetch_assoc();
        $this->assertNotNull($destino);
        $this->assertSame($centroId, (int) $destino['id_centro']);
    }

    public function testPlanificarLevanteRechazaCentroInexistente(): void
    {
        $model = new LevanteModel();
        $rutaId = $this->rutaActiva();
        $camionId = $this->crearCamion(['estado' => 'disponible']);
        $cuadrillaId = $this->crearCuadrilla();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Sitio de disposición final no encontrado');
        $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10', 999999);
    }

    public function testPlanificarLevanteRechazaCentroNoOperativo(): void
    {
        $model = new LevanteModel();
        $rutaId = $this->rutaActiva();
        $camionId = $this->crearCamion(['estado' => 'disponible']);
        $cuadrillaId = $this->crearCuadrilla();
        $centroId = $this->crearCentro(['estado' => 'cerrado']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no está operativo');
        $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10', $centroId);
    }

    public function testPlanificarLevanteSinCentroNoCreaDestino(): void
    {
        $model = new LevanteModel();
        $rutaId = $this->rutaActiva();
        $camionId = $this->crearCamion(['estado' => 'disponible']);
        $cuadrillaId = $this->crearCuadrilla();

        $levante = $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');

        $this->assertSame(0, $this->contarFilas('centro_levante', "id_levante = {$levante['id_levante']}"));
    }

    public function testPlanificarLevantePrecargaLosContenedoresDeLaRuta(): void
    {
        $model = new LevanteModel();
        $circuitoId = $this->crearCircuito();
        $rutaId = $this->crearRuta($circuitoId, ['activa' => 1]);
        $contenedorId = $this->crearContenedor($circuitoId);
        $this->agregarContenedorARuta($rutaId, $contenedorId);
        $camionId = $this->crearCamion();
        $cuadrillaId = $this->crearCuadrilla();

        $levante = $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');

        $this->assertCount(1, $levante['contenedores']);
        $this->assertSame($contenedorId, $levante['contenedores'][0]['id_contenedor']);
    }

    private function levantePendiente(): array
    {
        $model = new LevanteModel();
        $circuitoId = $this->crearCircuito();
        $rutaId = $this->crearRuta($circuitoId, ['activa' => 1]);
        $contenedorId = $this->crearContenedor($circuitoId, ['capacidad' => 100]);
        $this->agregarContenedorARuta($rutaId, $contenedorId);
        $camionId = $this->crearCamion();
        $cuadrillaId = $this->crearCuadrilla();
        $levante = $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');
        return [$model, (int) $levante['id_levante'], $camionId, $contenedorId];
    }

    public function testIniciarLevanteCambiaEstadosDeLevanteYCamion(): void
    {
        [$model, $idLevante, $camionId] = $this->levantePendiente();

        $levante = $model->iniciarLevante($idLevante);

        $this->assertSame('en_curso', $levante['estado']);
        $this->assertSame(1, $this->contarFilas('camion', "id_camion = $camionId AND estado = 'en_ruta'"));
    }

    public function testIniciarLevanteRechazaSiNoEstaPendiente(): void
    {
        [$model, $idLevante] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('estado pendiente');
        $model->iniciarLevante($idLevante);
    }

    public function testIniciarLevantePasaAEnCursoLasIncidenciasAsignadasDeLaRuta(): void
    {
        [$model, $idLevante, , $contenedorId] = $this->levantePendiente();
        $ciUsuario = $this->crearUsuario();
        $idIncidencia = $this->crearIncidencia($ciUsuario, ['estado' => 'asignada']);
        $this->vincularIncidenciaContenedor($idIncidencia, $contenedorId);

        $model->iniciarLevante($idLevante);

        $this->assertSame(1, $this->contarFilas('incidencia', "id_incidencia = $idIncidencia AND estado = 'en_curso'"));
    }

    public function testIniciarLevanteNoTocaIncidenciasQueNoEstanAsignadas(): void
    {
        [$model, $idLevante, , $contenedorId] = $this->levantePendiente();
        $ciUsuario = $this->crearUsuario();
        $idIncidencia = $this->crearIncidencia($ciUsuario, ['estado' => 'abierta']);
        $this->vincularIncidenciaContenedor($idIncidencia, $contenedorId);

        $model->iniciarLevante($idLevante);

        $this->assertSame(1, $this->contarFilas('incidencia', "id_incidencia = $idIncidencia AND estado = 'abierta'"));
    }

    public function testRegistrarContenedorTodoOkRequierePeso(): void
    {
        [$model, $idLevante, , $contenedorId] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('peso es obligatorio');
        $model->registrarContenedor($idLevante, $contenedorId, 'todo_ok', null, null);
    }

    public function testRegistrarContenedorNoRecolectadoAceptaMotivoVacio(): void
    {
        // El motivo es una nota libre y opcional: lo único obligatorio es el estado encontrado.
        [$model, $idLevante, , $contenedorId] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);

        $contenedor = $model->registrarContenedor($idLevante, $contenedorId, 'contenedor_ausente', null, null);

        $this->assertSame('contenedor_ausente', $contenedor['estado_encontrado']);
        $this->assertNull($contenedor['motivo']);
    }

    public function testRegistrarContenedorTodoOkActualizaNivelDeLlenado(): void
    {
        [$model, $idLevante, , $contenedorId] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);

        $model->registrarContenedor($idLevante, $contenedorId, 'todo_ok', 50, null);

        $nivel = $this->conn()->query("SELECT nivel_llenado FROM contenedor WHERE id_contenedor = $contenedorId")->fetch_assoc()['nivel_llenado'];
        // capacidad=100, peso=50 -> 50%
        $this->assertSame(50, (int) $nivel);
    }

    public function testRegistrarContenedorRechazaEstadoDesconocido(): void
    {
        [$model, $idLevante, , $contenedorId] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Estado encontrado inválido');
        $model->registrarContenedor($idLevante, $contenedorId, 'no_existe', 10, null);
    }

    public function testCompletarLevanteRechazaSiHayContenedoresPendientes(): void
    {
        // La precarga marca 'todo_ok' a cada contenedor de la ruta, pero un contenedor
        // al que la cuadrilla todavía no pasó (sin hora_vaciado ni motivo) sigue pendiente.
        [$model, $idLevante] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('faltan contenedores por registrar');
        $model->completarLevante($idLevante);
    }

    public function testRegistrarUltimoContenedorCierraElLevanteSoloYLiberaElCamion(): void
    {
        [$model, $idLevante, $camionId, $contenedorId] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);

        $model->registrarContenedor($idLevante, $contenedorId, 'todo_ok', 42, null);

        $levante = $model->getLevante($idLevante);
        $this->assertSame('completado', $levante['estado']);
        $this->assertEqualsWithDelta(42.0, (float) $levante['total_recolectado'], 0.001);
        $this->assertSame(1, $this->contarFilas('camion', "id_camion = $camionId AND estado = 'disponible'"));
    }

    public function testContenedorNoRecolectadoConMotivoTambienCierraElLevante(): void
    {
        [$model, $idLevante, , $contenedorId] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);

        $model->registrarContenedor($idLevante, $contenedorId, 'acceso_bloqueado', null, 'Calle cortada');

        $levante = $model->getLevante($idLevante);
        $this->assertSame('completado', $levante['estado']);
        $this->assertEqualsWithDelta(0.0, (float) $levante['total_recolectado'], 0.001);
    }

    public function testElLevanteSoloSeCierraAlResolverTodosLosContenedoresYQuitaLaRutaDeLaCuadrilla(): void
    {
        $model = new LevanteModel();
        $circuitoId = $this->crearCircuito();
        $rutaId = $this->crearRuta($circuitoId, ['activa' => 1]);
        $primero = $this->crearContenedor($circuitoId, ['capacidad' => 100]);
        $segundo = $this->crearContenedor($circuitoId, ['capacidad' => 100]);
        $this->agregarContenedorARuta($rutaId, $primero, 1);
        $this->agregarContenedorARuta($rutaId, $segundo, 2);
        $cuadrillaId = $this->crearCuadrilla();
        $levante = $model->planificarLevante($rutaId, $this->crearCamion(), $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10');
        $idLevante = (int) $levante['id_levante'];
        $vinculo = "id_ruta = $rutaId AND id_cuadrilla = $cuadrillaId";
        $this->assertSame(1, $this->contarFilas('ruta_cuadrilla', $vinculo));
        $model->iniciarLevante($idLevante);

        $model->registrarContenedor($idLevante, $primero, 'todo_ok', 10, null);
        $this->assertSame('en_curso', $model->getLevante($idLevante)['estado']);
        $this->assertSame(1, $this->contarFilas('ruta_cuadrilla', $vinculo));

        $model->registrarContenedor($idLevante, $segundo, 'otro', null, 'No estaba');
        $this->assertSame('completado', $model->getLevante($idLevante)['estado']);
        $this->assertSame(0, $this->contarFilas('ruta_cuadrilla', $vinculo));
    }

    public function testRegistrarContenedorFueraDeLaRutaSeRechaza(): void
    {
        [$model, $idLevante] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);
        $ajeno = $this->crearContenedor(null, ['capacidad' => 100]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no pertenece a la ruta');
        $model->registrarContenedor($idLevante, $ajeno, 'todo_ok', 10, null);
    }

    public function testCancelarLevanteLiberaElCamion(): void
    {
        [$model, $idLevante, $camionId] = $this->levantePendiente();

        $levante = $model->cancelarLevante($idLevante);

        $this->assertSame('cancelado', $levante['estado']);
        $this->assertSame(1, $this->contarFilas('camion', "id_camion = $camionId AND estado = 'disponible'"));
    }

    public function testCompletarLevanteMantieneElDestinoFinalElegido(): void
    {
        $model = new LevanteModel();
        $circuitoId = $this->crearCircuito();
        $rutaId = $this->crearRuta($circuitoId, ['activa' => 1]);
        $contenedorId = $this->crearContenedor($circuitoId, ['capacidad' => 100]);
        $this->agregarContenedorARuta($rutaId, $contenedorId);
        $camionId = $this->crearCamion();
        $cuadrillaId = $this->crearCuadrilla();
        $centroId = $this->crearCentro();
        $levante = $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10', $centroId);
        $idLevante = (int) $levante['id_levante'];

        $model->iniciarLevante($idLevante);
        // Registrar el último contenedor cierra el levante solo (no hay que llamar a completarLevante).
        $model->registrarContenedor($idLevante, $contenedorId, 'todo_ok', 42, null);

        $this->assertSame('completado', $model->getLevante($idLevante)['estado']);
        $this->assertSame(1, $this->contarFilas('centro_levante', "id_levante = $idLevante AND id_centro = $centroId"));
    }

    public function testCancelarLevanteMantieneElDestinoFinalElegido(): void
    {
        $model = new LevanteModel();
        $rutaId = $this->rutaActiva();
        $camionId = $this->crearCamion(['estado' => 'disponible']);
        $cuadrillaId = $this->crearCuadrilla();
        $centroId = $this->crearCentro();
        $levante = $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10', $centroId);
        $idLevante = (int) $levante['id_levante'];

        $model->cancelarLevante($idLevante);

        $this->assertSame(1, $this->contarFilas('centro_levante', "id_levante = $idLevante AND id_centro = $centroId"));
    }

    public function testCancelarLevanteRechazaSiYaEstaCompletado(): void
    {
        [$model, $idLevante, , $contenedorId] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);
        $model->registrarContenedor($idLevante, $contenedorId, 'todo_ok', 10, null);

        $this->expectException(InvalidArgumentException::class);
        $model->cancelarLevante($idLevante);
    }

    public function testObtenerResumenCuentaPorEstado(): void
    {
        $model = new LevanteModel();
        [, $idLevante1] = $this->levantePendiente();
        [, $idLevante2] = $this->levantePendiente();
        $model->cancelarLevante($idLevante2);

        $resumen = $model->obtenerResumen();

        $this->assertSame(1, $resumen['pendiente']);
        $this->assertSame(1, $resumen['cancelado']);
        $this->assertSame(2, $resumen['total']);
    }

    public function testObtenerPorRutaDevuelveLosLevantesDeEsaRuta(): void
    {
        [$model, $idLevante] = $this->levantePendiente();
        $idRuta = $this->conn()->query("SELECT id_ruta FROM levante WHERE id_levante = $idLevante")->fetch_assoc()['id_ruta'];

        $resultado = $model->obtenerPorRuta((int) $idRuta);

        $this->assertCount(1, $resultado);
        $this->assertSame($idLevante, (int) $resultado[0]['id_levante']);
    }

    public function testObtenerPorRutaIncluyeElNombreDelCentroDeDestino(): void
    {
        $model = new LevanteModel();
        $rutaId = $this->rutaActiva();
        $camionId = $this->crearCamion(['estado' => 'disponible']);
        $cuadrillaId = $this->crearCuadrilla();
        $centroId = $this->crearCentro(['nombre' => 'Relleno Test']);
        $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2026-01-10 08:00:00', '2026-01-10', $centroId);

        $resultado = $model->obtenerPorRuta($rutaId);

        $this->assertSame('Relleno Test', $resultado[0]['centro_nombre']);
    }

    public function testObtenerPendientesVencidosDetectaFechaPasada(): void
    {
        $model = new LevanteModel();
        $circuitoId = $this->crearCircuito();
        $rutaId = $this->crearRuta($circuitoId, ['activa' => 1, 'actividad' => 'barrido']);
        $camionId = $this->crearCamion();
        $cuadrillaId = $this->crearCuadrilla();

        $conn = $this->conn();
        $stmt = $conn->prepare("INSERT INTO levante (id_camion, id_cuadrilla, id_ruta, hora_salida, hora_levante, estado) VALUES (?, ?, ?, NOW(), DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'pendiente')");
        $stmt->bind_param('iii', $camionId, $cuadrillaId, $rutaId);
        $stmt->execute();
        $stmt->close();

        $vencidos = $model->obtenerPendientesVencidos();

        $this->assertCount(1, $vencidos);
    }

    // RF53: alerta de levante no completado al vencer el turno.
    public function testGenerarAlertaVencidosAvisaAlAdministradorUnaSolaVez(): void
    {
        $admin = $this->crearUsuario(null, ['rol' => 'administrador', 'acceso' => 'habilitado']);
        [$model, $idLevante] = $this->levantePendiente(); // hora_levante en el pasado: ya está vencido

        $primera = $model->generarAlertaVencidos();
        $segunda = $model->generarAlertaVencidos();

        $this->assertSame(1, $primera);
        $this->assertSame(0, $segunda);

        $stmt = $this->conn()->prepare(
            "SELECT n.mensaje FROM NOTIFICACION n
             JOIN NOTIFICACION_USUARIO nu ON nu.id_notificacion = n.id_notificacion
             WHERE nu.ci_usuario = ?"
        );
        $stmt->bind_param('i', $admin);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $this->assertNotNull($fila);
        $this->assertStringContainsString("levante #$idLevante", $fila['mensaje']);
    }

    public function testGenerarAlertaVencidosNoAvisaSiElLevanteNoEstaVencido(): void
    {
        $this->crearUsuario(null, ['rol' => 'administrador', 'acceso' => 'habilitado']);
        $circuitoId = $this->crearCircuito();
        $rutaId = $this->crearRuta($circuitoId, ['activa' => 1, 'actividad' => 'barrido']);
        $camionId = $this->crearCamion();
        $cuadrillaId = $this->crearCuadrilla();
        $model = new LevanteModel();
        // Turno a futuro: no debería generar ninguna alerta.
        $model->planificarLevante($rutaId, $camionId, $cuadrillaId, '2099-01-10 08:00:00', '2099-01-10');

        $avisados = $model->generarAlertaVencidos();

        $this->assertSame(0, $avisados);
    }

    // Si se reportó un desperfecto a mitad de ruta (el camión queda "en_mantenimiento"),
    // terminar o cancelar el levante no debe devolverlo a "disponible" como si nada.
    public function testCompletarLevanteNoLiberaUnCamionEnMantenimiento(): void
    {
        [$model, $idLevante, $camionId, $contenedorId] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);
        $this->conn()->query("UPDATE camion SET estado = 'en_mantenimiento' WHERE id_camion = $camionId");

        $model->registrarContenedor($idLevante, $contenedorId, 'todo_ok', 42, null);

        $this->assertSame(1, $this->contarFilas('camion', "id_camion = $camionId AND estado = 'en_mantenimiento'"));
    }

    public function testCancelarLevanteNoLiberaUnCamionEnMantenimiento(): void
    {
        [$model, $idLevante, $camionId] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);
        $this->conn()->query("UPDATE camion SET estado = 'en_mantenimiento' WHERE id_camion = $camionId");

        $model->cancelarLevante($idLevante);

        $this->assertSame(1, $this->contarFilas('camion', "id_camion = $camionId AND estado = 'en_mantenimiento'"));
    }
}
