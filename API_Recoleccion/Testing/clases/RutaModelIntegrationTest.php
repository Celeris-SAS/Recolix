<?php

require_once __DIR__ . '/../../Modelo/rutaModel.php';

final class RutaModelIntegrationTest extends DbTestCase
{
    public function testInsertYGetRutaRoundtrip(): void
    {
        $model = new RutaModel();
        $circuitoId = $this->crearCircuito();

        $id = $model->insertRuta($circuitoId, 'Ruta Centro', 'barrido');

        $this->assertGreaterThan(0, $id);
        $ruta = $model->getRuta($id);
        $this->assertSame('Ruta Centro', $ruta['nombre']);
        $this->assertSame(0, (int) $ruta['levantes_count']);
    }

    public function testUpdateRutaModificaCampos(): void
    {
        $model = new RutaModel();
        $circuitoId = $this->crearCircuito();
        $id = $model->insertRuta($circuitoId, 'Ruta Centro', 'barrido');

        $actualizado = $model->updateRuta($id, $circuitoId, 'Ruta Renombrada', 'poda');

        $this->assertTrue($actualizado);
        $this->assertSame('Ruta Renombrada', $model->getRuta($id)['nombre']);
    }

    public function testDeleteRutaLaElimina(): void
    {
        $model = new RutaModel();
        $circuitoId = $this->crearCircuito();
        $id = $model->insertRuta($circuitoId, 'Ruta Centro', 'barrido');

        $model->deleteRuta($id);

        $this->assertNull($model->getRuta($id));
    }

    public function testObtenerPorCircuitoFiltraCorrectamente(): void
    {
        $model = new RutaModel();
        $circuitoA = $this->crearCircuito();
        $circuitoB = $this->crearCircuito();
        $model->insertRuta($circuitoA, 'Ruta A', 'barrido');
        $model->insertRuta($circuitoB, 'Ruta B', 'barrido');

        $resultado = $model->obtenerPorCircuito($circuitoA);

        $this->assertCount(1, $resultado);
        $this->assertSame('Ruta A', $resultado[0]['nombre']);
    }

    public function testFiltrarPorNombreUsaLike(): void
    {
        $model = new RutaModel();
        $circuitoId = $this->crearCircuito();
        $model->insertRuta($circuitoId, 'Ruta Centro Norte', 'barrido');
        $model->insertRuta($circuitoId, 'Ruta Sur', 'barrido');

        $resultado = $model->filtrarPorNombre('Centro');

        $this->assertCount(1, $resultado);
        $this->assertSame('Ruta Centro Norte', $resultado[0]['nombre']);
    }

    public function testAgregarYQuitarContenedor(): void
    {
        $model = new RutaModel();
        $circuitoId = $this->crearCircuito();
        $rutaId = $model->insertRuta($circuitoId, 'Ruta Centro', 'barrido');
        $contenedorId = $this->crearContenedor($circuitoId);

        $model->agregarContenedor($rutaId, $contenedorId, 1);
        $this->assertCount(1, $model->obtenerContenedores($rutaId));

        $model->quitarContenedor($rutaId, $contenedorId);
        $this->assertCount(0, $model->obtenerContenedores($rutaId));
    }

    public function testAsignarYQuitarCuadrilla(): void
    {
        $model = new RutaModel();
        $circuitoId = $this->crearCircuito();
        $rutaId = $model->insertRuta($circuitoId, 'Ruta Centro', 'barrido');
        $cuadrillaId = $this->crearCuadrilla();

        $model->asignarCuadrilla($rutaId, $cuadrillaId);
        $this->assertCount(1, $model->obtenerCuadrillas($rutaId));

        $model->quitarCuadrilla($rutaId, $cuadrillaId);
        $this->assertCount(0, $model->obtenerCuadrillas($rutaId));
    }

    public function testObtenerLevantesDeLaRuta(): void
    {
        $model = new RutaModel();
        $circuitoId = $this->crearCircuito();
        $rutaId = $model->insertRuta($circuitoId, 'Ruta Centro', 'barrido');
        $camionId = $this->crearCamion();
        $cuadrillaId = $this->crearCuadrilla();

        $conn = $this->conn();
        $stmt = $conn->prepare("INSERT INTO levante (id_camion, id_cuadrilla, id_ruta, hora_salida, hora_levante, estado) VALUES (?, ?, ?, NOW(), CURDATE(), 'pendiente')");
        $stmt->bind_param('iii', $camionId, $cuadrillaId, $rutaId);
        $stmt->execute();
        $stmt->close();

        $this->assertCount(1, $model->obtenerLevantes($rutaId));
    }

    public function testObtenerResumenCalculaLosContadoresDesdeLaBase(): void
    {
        $model = new RutaModel();
        $circuitoId = $this->crearCircuito();
        $rutaId = $model->insertRuta($circuitoId, 'Ruta Centro', 'barrido');
        $contenedorId = $this->crearContenedor($circuitoId, ['nivel_llenado' => 80]);
        $this->agregarContenedorARuta($rutaId, $contenedorId);

        $camionId = $this->crearCamion();
        $cuadrillaId = $this->crearCuadrilla();
        $conn = $this->conn();
        $stmt = $conn->prepare("INSERT INTO levante (id_camion, id_cuadrilla, id_ruta, hora_salida, hora_levante, estado) VALUES (?, ?, ?, NOW(), CURDATE(), 'en_curso')");
        $stmt->bind_param('iii', $camionId, $cuadrillaId, $rutaId);
        $stmt->execute();
        $stmt->close();

        $resumen = $model->obtenerResumen();
        $fila = current(array_filter($resumen, fn($r) => (int) $r['id_ruta'] === $rutaId));

        $this->assertNotFalse($fila);
        $this->assertSame(1, (int) $fila['en_curso_count']);
        $this->assertSame(1, (int) $fila['contenedores_count']);
        $this->assertEqualsWithDelta(80.0, (float) $fila['llenado_promedio'], 0.001);
    }
}
