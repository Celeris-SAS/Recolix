<?php

require_once __DIR__ . '/../../Modelo/camionModel.php';

final class CamionModelIntegrationTest extends DbTestCase
{
    public function testInsertYGetCamionRoundtrip(): void
    {
        $model = new CamionModel();
        $id = $model->insertCamion('ABC1234', 'carga_lateral', 5000, 2020, 'Fiat', 'disponible');

        $this->assertGreaterThan(0, $id);

        $camion = $model->getCamion($id);
        $this->assertSame('ABC1234', $camion['matricula']);
        $this->assertSame('disponible', $camion['estado']);
        $this->assertSame(5000, (int) $camion['capacidad']);
    }

    public function testGetMatriculaEncuentraDuplicado(): void
    {
        $model = new CamionModel();
        $model->insertCamion('XYZ9999', 'carga_trasera', 3000, 2019, 'Volvo', 'disponible');

        $this->assertNotEmpty($model->getMatricula('XYZ9999'));
        $this->assertNull($model->getMatricula('NOEXISTE1'));
    }

    public function testUpdateCamionModificaLosCampos(): void
    {
        $model = new CamionModel();
        $id = $model->insertCamion('ABC1234', 'carga_lateral', 5000, 2020, 'Fiat', 'disponible');

        $model->updateCamion($id, 'ABC1234', 'carga_trasera', 6000, 2021, 'Fiat Ducato', 'en_deposito');

        $camion = $model->getCamion($id);
        $this->assertSame('carga_trasera', $camion['tipo_carga']);
        $this->assertSame(6000, (int) $camion['capacidad']);
        $this->assertSame('en_deposito', $camion['estado']);
    }

    public function testBajaCamionCambiaEstadoADeBaja(): void
    {
        $model = new CamionModel();
        $id = $model->insertCamion('ABC1234', 'carga_lateral', 5000, 2020, 'Fiat', 'disponible');

        $model->bajaCamion($id);

        $this->assertSame('de_baja', $model->getCamion($id)['estado']);
    }

    public function testEsDisponibleFalseSiEstadoNoEsDisponible(): void
    {
        $model = new CamionModel();
        $id = $model->insertCamion('ABC1234', 'carga_lateral', 5000, 2020, 'Fiat', 'en_mantenimiento');

        $this->assertFalse($model->esDisponible($id));
    }

    public function testEsDisponibleTrueSinLevantesActivos(): void
    {
        $model = new CamionModel();
        $id = $model->insertCamion('ABC1234', 'carga_lateral', 5000, 2020, 'Fiat', 'disponible');

        $this->assertTrue($model->esDisponible($id));
    }

    public function testEsDisponibleFalseConLevantePendiente(): void
    {
        $model = new CamionModel();
        $camionId = $model->insertCamion('ABC1234', 'carga_lateral', 5000, 2020, 'Fiat', 'disponible');
        $cuadrillaId = $this->crearCuadrilla();
        $rutaId = $this->crearRuta();

        $conn = $this->conn();
        $stmt = $conn->prepare("INSERT INTO levante (id_camion, id_cuadrilla, id_ruta, hora_salida, hora_levante, estado) VALUES (?, ?, ?, NOW(), CURDATE(), 'pendiente')");
        $stmt->bind_param('iii', $camionId, $cuadrillaId, $rutaId);
        $stmt->execute();
        $stmt->close();

        $this->assertFalse($model->esDisponible($camionId));
    }

    public function testFiltrarEstadoDevuelveSoloCoincidencias(): void
    {
        $model = new CamionModel();
        $model->insertCamion('AAA1111', 'carga_lateral', 5000, 2020, 'Fiat', 'disponible');
        $model->insertCamion('BBB2222', 'carga_lateral', 5000, 2020, 'Fiat', 'de_baja');

        $resultado = $model->filtrarEstado('disponible');

        $this->assertCount(1, $resultado);
        $this->assertSame('AAA1111', $resultado[0]['matricula']);
    }

    public function testFinalizarMantenimientosVencidosVuelveADisponible(): void
    {
        $model = new CamionModel();
        $id = $model->insertCamion('ABC1234', 'carga_lateral', 5000, 2020, 'Fiat', 'en_mantenimiento');

        $conn = $this->conn();
        $stmt = $conn->prepare("INSERT INTO mantenimiento (id_camion, tipo, descripcion, costo, fecha_inicio, fecha_fin) VALUES (?, 'preventivo', 'service', 100, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY))");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        $model->finalizarMantenimientosVencidos();

        $this->assertSame('disponible', $model->getCamion($id)['estado']);
    }

    public function testFinalizarMantenimientosVencidosNoTocaMantenimientoVigente(): void
    {
        $model = new CamionModel();
        $id = $model->insertCamion('ABC1234', 'carga_lateral', 5000, 2020, 'Fiat', 'en_mantenimiento');

        $conn = $this->conn();
        $stmt = $conn->prepare("INSERT INTO mantenimiento (id_camion, tipo, descripcion, costo, fecha_inicio, fecha_fin) VALUES (?, 'preventivo', 'service', 100, NOW(), DATE_ADD(NOW(), INTERVAL 2 DAY))");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        $model->finalizarMantenimientosVencidos();

        $this->assertSame('en_mantenimiento', $model->getCamion($id)['estado']);
    }

    public function testObtenerHistorialCombinaLevanteMantenimientoYTraslado(): void
    {
        $model = new CamionModel();
        $id = $model->insertCamion('ABC1234', 'carga_lateral', 5000, 2020, 'Fiat', 'disponible');

        $conn = $this->conn();
        $stmt = $conn->prepare("INSERT INTO mantenimiento (id_camion, tipo, descripcion, costo, fecha_inicio) VALUES (?, 'preventivo', 'service', 100, NOW())");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO camion_centro (id_camion, id_centro, peso, fecha_ingreso) VALUES (?, 1, 500, CURDATE())");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        $historial = $model->obtenerHistorial($id);

        $this->assertCount(3, $historial);
        $tipos = array_column($historial, 'tipo_evento');
        sort($tipos);
        $this->assertSame(['estado', 'mantenimiento', 'traslado'], $tipos);
    }

    public function testObtenerEstadosRegistraAltaYCambiosDeEstado(): void
    {
        $model = new CamionModel();
        $id = $this->crearCamion(['estado' => 'disponible']);
        $conn = $this->conn();
        $conn->query("UPDATE camion SET estado = 'en_ruta' WHERE id_camion = $id");
        $conn->query("UPDATE camion SET estado = 'en_ruta' WHERE id_camion = $id");
        $conn->query("UPDATE camion SET estado = 'en_mantenimiento' WHERE id_camion = $id");

        $estados = $model->obtenerEstados($id);

        $this->assertCount(3, $estados, 'Actualizar al mismo estado no genera un registro');
        $this->assertSame('en_mantenimiento', $estados[0]['estado_nuevo']);
        $this->assertSame('en_ruta', $estados[0]['estado_anterior']);
        $this->assertNull($estados[2]['estado_anterior']);
        $this->assertSame('disponible', $estados[2]['estado_nuevo']);
    }

    public function testObtenerHistorialIncluyeLosCambiosDeEstado(): void
    {
        $model = new CamionModel();
        $id = $this->crearCamion(['estado' => 'disponible']);
        $this->conn()->query("UPDATE camion SET estado = 'de_baja' WHERE id_camion = $id");

        $eventos = array_filter($model->obtenerHistorial($id), fn($e) => $e['tipo_evento'] === 'estado');

        $this->assertCount(2, $eventos);
    }
}
