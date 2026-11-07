<?php

require_once __DIR__ . '/../../Modelo/tipoResiduoModel.php';

final class TipoResiduoModelIntegrationTest extends DbTestCase
{
    public function testInsertYGetTipoResiduoRoundtrip(): void
    {
        $model = new tipoResiduoModel();

        $id = $model->insertTipoResiduo('Azul', 'Papel y cartón', 1);

        $this->assertGreaterThan(0, $id);
        $tipo = $model->getTipoResiduo($id);
        $this->assertSame('Azul', $tipo['color']);
        $this->assertSame('Papel y cartón', $tipo['descripcion']);
        $this->assertSame(1, (int) $tipo['reciclable']);
    }

    public function testUpdateTipoResiduoModificaLosCampos(): void
    {
        $model = new tipoResiduoModel();
        $id = $this->crearTipoResiduo(['color' => 'Verde', 'descripcion' => 'Original', 'reciclable' => 1]);

        $model->updateTipoResiduo($id, 'Amarillo', 'Renombrado', 0);

        $tipo = $model->getTipoResiduo($id);
        $this->assertSame('Amarillo', $tipo['color']);
        $this->assertSame('Renombrado', $tipo['descripcion']);
        $this->assertSame(0, (int) $tipo['reciclable']);
    }

    public function testBajaTipoResiduoEliminaLaFilaCuandoNoTieneContenedores(): void
    {
        $model = new tipoResiduoModel();
        $id = $this->crearTipoResiduo();

        $resultado = $model->bajaTipoResiduo($id);

        $this->assertTrue($resultado);
        $this->assertNull($model->getTipoResiduo($id));
    }

    public function testBajaTipoResiduoDevuelveFalseSiNoExiste(): void
    {
        $model = new tipoResiduoModel();

        $this->assertFalse($model->bajaTipoResiduo(999999));
    }

    public function testTieneContenedoresAsociadosTrueCuandoHayUno(): void
    {
        $model = new tipoResiduoModel();
        $tipoId = $this->crearTipoResiduo();
        $this->crearContenedor(null, ['id_tipo' => $tipoId]);

        $this->assertTrue($model->tieneContenedoresAsociados($tipoId));
    }

    public function testTieneContenedoresAsociadosFalseSinContenedores(): void
    {
        $model = new tipoResiduoModel();
        $tipoId = $this->crearTipoResiduo();

        $this->assertFalse($model->tieneContenedoresAsociados($tipoId));
    }

    public function testGetAllTiposResiduoTraeTodos(): void
    {
        $model = new tipoResiduoModel();
        $this->crearTipoResiduo(['descripcion' => 'Uno']);
        $this->crearTipoResiduo(['descripcion' => 'Dos']);

        $this->assertCount(2, $model->getAllTiposResiduo());
    }

    public function testFiltrarReciclableDevuelveSoloCoincidencias(): void
    {
        $model = new tipoResiduoModel();
        $this->crearTipoResiduo(['reciclable' => 1]);
        $this->crearTipoResiduo(['reciclable' => 1]);
        $this->crearTipoResiduo(['reciclable' => 0]);

        $this->assertCount(2, $model->filtrarReciclable(1));
        $this->assertCount(1, $model->filtrarReciclable(0));
    }
}
