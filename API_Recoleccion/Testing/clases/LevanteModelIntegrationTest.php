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

    public function testRegistrarContenedorTodoOkRequierePeso(): void
    {
        [$model, $idLevante, , $contenedorId] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('peso es obligatorio');
        $model->registrarContenedor($idLevante, $contenedorId, 'todo_ok', null, null);
    }

    public function testRegistrarContenedorNoRecolectadoRequiereMotivo(): void
    {
        [$model, $idLevante, , $contenedorId] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('motivo es obligatorio');
        $model->registrarContenedor($idLevante, $contenedorId, 'contenedor_ausente', null, null);
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

    public function testCompletarLevanteRechazaSiHayContenedoresSinResolver(): void
    {
        // La precarga de contenedores (al planificar) los marca 'todo_ok' por
        // defecto, así que la única forma real de dejar uno "sin resolver"
        // (estado distinto de todo_ok y sin motivo) es vía una escritura
        // directa -- registrarContenedor() no lo permite. Este test ejercita
        // esa guarda defensiva de completarLevante() igual.
        [$model, $idLevante, , $contenedorId] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);
        $conn = $this->conn();
        $stmt = $conn->prepare("UPDATE contenedor_levante SET estado_encontrado = 'contenedor_ausente', motivo = NULL WHERE id_levante = ? AND id_contenedor = ?");
        $stmt->bind_param('ii', $idLevante, $contenedorId);
        $stmt->execute();
        $stmt->close();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('requiere estado y motivo');
        $model->completarLevante($idLevante);
    }

    public function testCompletarLevanteCalculaTotalRecolectadoYLiberaElCamion(): void
    {
        [$model, $idLevante, $camionId, $contenedorId] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);
        $model->registrarContenedor($idLevante, $contenedorId, 'todo_ok', 42, null);

        $levante = $model->completarLevante($idLevante);

        $this->assertSame('completado', $levante['estado']);
        $this->assertEqualsWithDelta(42.0, (float) $levante['total_recolectado'], 0.001);
        $this->assertSame(1, $this->contarFilas('camion', "id_camion = $camionId AND estado = 'disponible'"));
    }

    public function testCancelarLevanteLiberaElCamion(): void
    {
        [$model, $idLevante, $camionId] = $this->levantePendiente();

        $levante = $model->cancelarLevante($idLevante);

        $this->assertSame('cancelado', $levante['estado']);
        $this->assertSame(1, $this->contarFilas('camion', "id_camion = $camionId AND estado = 'disponible'"));
    }

    public function testCancelarLevanteRechazaSiYaEstaCompletado(): void
    {
        [$model, $idLevante, , $contenedorId] = $this->levantePendiente();
        $model->iniciarLevante($idLevante);
        $model->registrarContenedor($idLevante, $contenedorId, 'todo_ok', 10, null);
        $model->completarLevante($idLevante);

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
}
