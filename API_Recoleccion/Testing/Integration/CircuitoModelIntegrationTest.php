<?php

require_once __DIR__ . '/../../Modelo/circuitoModel.php';

final class CircuitoModelIntegrationTest extends DbTestCase
{
    public function testInsertYGetCircuitoRoundtrip(): void
    {
        $model = new CircuitoModel();
        $municipioId = $this->crearMunicipio();

        $id = $model->insertCircuito($municipioId, 'Circuito Centro', 'C-01', 'barrido', 'mañana', 'diaria (lunes a viernes)');

        $this->assertGreaterThan(0, $id);
        $circuito = $model->getCircuito($id);
        $this->assertSame('Circuito Centro', $circuito['nombre']);
        $this->assertSame('C-01', $circuito['codigo']);
        $this->assertSame($municipioId, (int) $circuito['id_municipio']);
    }

    public function testInsertCircuitoConPoligonoLoPersiste(): void
    {
        $model = new CircuitoModel();
        $municipioId = $this->crearMunicipio();
        $poligono = json_encode([[0, 0], [0, 1], [1, 0]]);

        $id = $model->insertCircuito($municipioId, 'Circuito Poligono', 'C-02', 'barrido', 'tarde', 'diaria', $poligono);

        $this->assertSame($poligono, $model->getCircuito($id)['poligono']);
    }

    public function testUpdateCircuitoModificaCampos(): void
    {
        $model = new CircuitoModel();
        $municipioId = $this->crearMunicipio();
        $id = $model->insertCircuito($municipioId, 'Circuito Centro', 'C-01', 'barrido', 'mañana', 'diaria');

        $actualizado = $model->updateCircuito($id, $municipioId, 'Circuito Renombrado', 'C-01', 'poda', 'noche', 'semanal');

        $this->assertTrue($actualizado);
        $circuito = $model->getCircuito($id);
        $this->assertSame('Circuito Renombrado', $circuito['nombre']);
        $this->assertSame('noche', $circuito['turno']);
    }

    public function testUpdateCircuitoInexistenteDevuelveFalse(): void
    {
        $model = new CircuitoModel();
        $municipioId = $this->crearMunicipio();

        $actualizado = $model->updateCircuito(999999, $municipioId, 'X', 'X', 'X', 'mañana', 'X');

        $this->assertFalse($actualizado);
    }

    public function testActualizarPoligono(): void
    {
        $model = new CircuitoModel();
        $municipioId = $this->crearMunicipio();
        $id = $model->insertCircuito($municipioId, 'Circuito Centro', 'C-01', 'barrido', 'mañana', 'diaria');

        $poligono = json_encode([[0, 0], [0, 1], [1, 0]]);
        $model->actualizarPoligono($id, $poligono);

        $this->assertSame($poligono, $model->getCircuito($id)['poligono']);
    }

    public function testDeleteCircuitoLoElimina(): void
    {
        $model = new CircuitoModel();
        $municipioId = $this->crearMunicipio();
        $id = $model->insertCircuito($municipioId, 'Circuito Centro', 'C-01', 'barrido', 'mañana', 'diaria');

        $model->deleteCircuito($id);

        $this->assertNull($model->getCircuito($id));
    }

    public function testGetCodigoEncuentraDuplicado(): void
    {
        $model = new CircuitoModel();
        $municipioId = $this->crearMunicipio();
        $model->insertCircuito($municipioId, 'Circuito Centro', 'C-01', 'barrido', 'mañana', 'diaria');

        $this->assertNotEmpty($model->getCodigo('C-01'));
        $this->assertNull($model->getCodigo('NO-EXISTE'));
    }

    public function testObtenerPorMunicipioFiltraCorrectamente(): void
    {
        $model = new CircuitoModel();
        $municipioA = $this->crearMunicipio('Municipio A');
        $municipioB = $this->crearMunicipio('Municipio B');
        $model->insertCircuito($municipioA, 'Circuito A', 'C-A', 'barrido', 'mañana', 'diaria');
        $model->insertCircuito($municipioB, 'Circuito B', 'C-B', 'barrido', 'mañana', 'diaria');

        $resultado = $model->obtenerPorMunicipio($municipioA);

        $this->assertCount(1, $resultado);
        $this->assertSame('Circuito A', $resultado[0]['nombre']);
    }

    public function testFiltrarPorTurno(): void
    {
        $model = new CircuitoModel();
        $municipioId = $this->crearMunicipio();
        $model->insertCircuito($municipioId, 'Circuito Mañana', 'C-01', 'barrido', 'mañana', 'diaria');
        $model->insertCircuito($municipioId, 'Circuito Noche', 'C-02', 'barrido', 'noche', 'diaria');

        $resultado = $model->filtrarPorTurno('noche');

        $this->assertCount(1, $resultado);
        $this->assertSame('Circuito Noche', $resultado[0]['nombre']);
    }

    public function testObtenerRutasYContenedoresDelCircuito(): void
    {
        $model = new CircuitoModel();
        $municipioId = $this->crearMunicipio();
        $circuitoId = $model->insertCircuito($municipioId, 'Circuito Centro', 'C-01', 'barrido', 'mañana', 'diaria');
        $this->crearRuta($circuitoId, ['nombre' => 'Ruta 1']);
        $this->crearContenedor($circuitoId);

        $this->assertCount(1, $model->obtenerRutas($circuitoId));
        $this->assertCount(1, $model->obtenerContenedores($circuitoId));
    }
}
