<?php

require_once __DIR__ . '/../Controlador/tipoResiduoController.php';

final class TipoResiduoControllerTest extends GestionTestCase
{
    private function controller(): tipoResiduoController
    {
        return $this->newWithoutConstructor(tipoResiduoController::class);
    }

    public function testValidarStringRechazaMasDe255CaracteresPorDefecto(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarString', [str_repeat('a', 256), 'Descripción']);
    }

    /** @dataProvider valoresBooleanosValidos */
    public function testValidarBooleanoAceptaFormatosSoportados($entrada, bool $esperado): void
    {
        $controller = $this->controller();
        $this->assertSame($esperado, $this->callPrivate($controller, 'validarBooleano', [$entrada, 'Reciclable']));
    }

    public static function valoresBooleanosValidos(): array
    {
        return [
            'true nativo' => [true, true],
            'false nativo' => [false, false],
            'entero 1' => [1, true],
            'entero 0' => [0, false],
            'string 1' => ['1', true],
            'string 0' => ['0', false],
        ];
    }

    public function testValidarBooleanoRechazaValorNoBooleano(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarBooleano', ['tal-vez', 'Reciclable']);
    }

    public function testUpdateTipoResiduoRechazaIdInexistente(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function getTipoResiduo($id) { return null; }
        };
        $this->setPrivateProperty($controller, 'tipoResiduoModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->updateTipoResiduo(999, 'Verde', 'Reciclables', true);
    }

    public function testBajaTipoResiduoRechazaSiTieneContenedoresAsociados(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function getTipoResiduo($id) { return ['id_tipo' => $id]; }
            public function tieneContenedoresAsociados($id) { return true; }
        };
        $this->setPrivateProperty($controller, 'tipoResiduoModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('contenedores asignados');
        $controller->bajaTipoResiduo(1);
    }

    public function testBajaTipoResiduoRechazaIdInexistente(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function getTipoResiduo($id) { return null; }
        };
        $this->setPrivateProperty($controller, 'tipoResiduoModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->bajaTipoResiduo(999);
    }

    public function testReglasValidacionExponeLimitesDeLosCampos(): void
    {
        $controller = $this->controller();
        $reglas = $controller->reglasValidacion();

        $this->assertSame(30, $reglas['color']['max']);
        $this->assertSame(150, $reglas['descripcion']['max']);
    }
}
