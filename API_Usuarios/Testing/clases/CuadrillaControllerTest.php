<?php

require_once __DIR__ . '/../../Controlador/cuadrillaController.php';

final class CuadrillaControllerTest extends UsuarioTestCase
{
    private function controller(): CuadrillaController
    {
        return $this->newWithoutConstructor(CuadrillaController::class);
    }


    public function testValidarIdRechazaCero(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarId', [0]);
    }

    public function testValidarIdAceptaEnteroPositivo(): void
    {
        $controller = $this->controller();
        $this->assertSame(5, $this->callPrivate($controller, 'validarId', ['5']));
    }

    public function testValidarStringRechazaVacio(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarString', ['   ', 'Estado']);
    }

    public function testValidarStringRechazaMasDe255Caracteres(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarString', [str_repeat('a', 256), 'Estado']);
    }

    public function testValidarEnteroRechazaDecimal(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarEntero', [1.5, 'Municipio']);
    }


    public function testValidarIdMunicipioRechazaMenorAUno(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarIdMunicipio', [0]);
    }

    public function testValidarIdMunicipioRechazaMunicipioInexistente(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function existeMunicipio($id) { return false; }
        };
        $this->setPrivateProperty($controller, 'cuadrillaModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarIdMunicipio', [999]);
    }

    public function testValidarIdMunicipioAceptaMunicipioExistente(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function existeMunicipio($id) { return true; }
        };
        $this->setPrivateProperty($controller, 'cuadrillaModel', $modelStub);

        $this->assertSame(1, $this->callPrivate($controller, 'validarIdMunicipio', [1]));
    }


    public function testReglasValidacionExponeValoresValidos(): void
    {
        $controller = $this->controller();
        $reglas = $controller->reglasValidacion();

        $this->assertSame(['activa', 'inactiva'], $reglas['estado']['valores']);
    }


    public function testInsertCuadrillaRechazaEstadoInvalido(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function existeMunicipio($id) { return true; }
        };
        $this->setPrivateProperty($controller, 'cuadrillaModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->insertCuadrilla(1, 'rota');
    }

    public function testInsertCuadrillaRechazaMunicipioInexistente(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function existeMunicipio($id) { return false; }
        };
        $this->setPrivateProperty($controller, 'cuadrillaModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->insertCuadrilla(999, 'activa');
    }

    public function testUpdateCuadrillaRechazaEstadoInvalido(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function existeMunicipio($id) { return true; }
        };
        $this->setPrivateProperty($controller, 'cuadrillaModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->updateCuadrilla(1, 1, 'rota');
    }


    public function testFiltrarEstadoRechazaValorNoListado(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->filtrarEstado('rota');
    }


    public function testGetCuadrillaDelegaEnElModelTrasValidar(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function getCuadrilla($id) { return ['id_cuadrilla' => $id, 'estado' => 'activa']; }
        };
        $this->setPrivateProperty($controller, 'cuadrillaModel', $modelStub);

        $this->assertSame(5, $controller->getCuadrilla('5')['id_cuadrilla']);
    }

    public function testGetCuadrillaRechazaIdInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->getCuadrilla(0);
    }

    public function testEsDisponibleRechazaIdInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->esDisponible(-1);
    }

    public function testObtenerHistorialRechazaIdInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->obtenerHistorial(0);
    }
}
