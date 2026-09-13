<?php

require_once __DIR__ . '/../../Controlador/maquinariaController.php';

final class MaquinariaControllerTest extends GestionTestCase
{
    private function controller(): MaquinariaController
    {
        return $this->newWithoutConstructor(MaquinariaController::class);
    }

    public function testValidarFechaAceptaFormatoYyyyMmDd(): void
    {
        $controller = $this->controller();
        $this->assertSame('2024-01-15', $this->callPrivate($controller, 'validarFecha', ['2024-01-15', 'Fecha de adquisición']));
    }

    public function testValidarFechaRechazaFormatoConHora(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarFecha', ['2024-01-15 10:00:00', 'Fecha de adquisición']);
    }

    public function testValidarFechaRechazaTextoInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarFecha', ['no-es-una-fecha', 'Fecha de adquisición']);
    }

    public function testValidarFechaRechazaVacio(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarFecha', ['', 'Fecha de adquisición']);
    }

    public function testValidarTipoRechazaValorNoListado(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarTipo', ['dron']);
    }

    public function testValidarEstadoRechazaValorNoListado(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarEstado', ['rota']);
    }

    public function testInsertMaquinariaRechazaCentroInexistente(): void
    {
        $controller = $this->controller();
        $centroModelStub = new class {
            public function getCentro($id) { return null; }
        };
        $this->setPrivateProperty($controller, 'centroAcopioModel', $centroModelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->insertMaquinaria(999, 'Pala Test', 'pala', '2024-01-01', 'operativa');
    }

    public function testUpdateMaquinariaRechazaIdInexistente(): void
    {
        $controller = $this->controller();
        $maquinariaModelStub = new class {
            public function getMaquinaria($id) { return null; }
        };
        $this->setPrivateProperty($controller, 'maquinariaModel', $maquinariaModelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->updateMaquinaria(999, 'Pala Test', 'pala', '2024-01-01', 'operativa');
    }

    public function testCambiarEstadoRechazaIdInexistente(): void
    {
        $controller = $this->controller();
        $maquinariaModelStub = new class {
            public function getMaquinaria($id) { return null; }
        };
        $this->setPrivateProperty($controller, 'maquinariaModel', $maquinariaModelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->cambiarEstado(999, 'fuera_servicio');
    }

    public function testCambiarEstadoRechazaEstadoInvalidoAntesQueElResto(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->cambiarEstado(1, 'derretida');
    }

    public function testReglasValidacionExponeValoresValidos(): void
    {
        $controller = $this->controller();
        $reglas = $controller->reglasValidacion();

        $this->assertSame(['compactadora', 'cinta', 'pala', 'bascula', 'herramienta', 'otro'], $reglas['tipo']['valores']);
        $this->assertSame(['operativa', 'en_mantenimiento', 'fuera_servicio'], $reglas['estado']['valores']);
    }
}
