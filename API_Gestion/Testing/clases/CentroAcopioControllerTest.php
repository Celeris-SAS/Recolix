<?php

require_once __DIR__ . '/../../Controlador/centroAcopioController.php';

final class CentroAcopioControllerTest extends GestionTestCase
{
    private function controller(): CentroAcopioController
    {
        return $this->newWithoutConstructor(CentroAcopioController::class);
    }

    public function testValidarStringRechazaMasDe100Caracteres(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarString', [str_repeat('a', 101), 'Nombre']);
    }

    public function testValidarEnteroNoNegativoAceptaCero(): void
    {
        $controller = $this->controller();
        $this->assertSame(0, $this->callPrivate($controller, 'validarEnteroNoNegativo', [0, 'Ocupación actual']));
    }

    public function testValidarEnteroNoNegativoRechazaNegativo(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarEnteroNoNegativo', [-1, 'Ocupación actual']);
    }

    public function testValidarCoordenadaUsaLimiteDeLatitud(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarCoordenada', [90.1, 'Latitud']);
    }

    public function testValidarTipoRechazaValorNoListado(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarTipo', ['basural_a_cielo_abierto']);
    }

    public function testInsertCentroRechazaMunicipioInexistente(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function municipioExiste($id) { return false; }
        };
        $this->setPrivateProperty($controller, 'centroAcopioModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->insertCentro(999, 'Centro Test', 'ecocentro', 'Calle', 100, -34.9, -56.1, 1000, 'operativo');
    }

    public function testGetOcupacionRechazaCentroInexistente(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function getCentro($id) { return null; }
        };
        $this->setPrivateProperty($controller, 'centroAcopioModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->getOcupacion(999);
    }

    public function testGetOcupacionRechazaCapacidadCero(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function getCentro($id) { return ['id_centro' => $id]; }
            public function getOcupacion($id) { return ['ocupado' => 10, 'capacidad' => 0]; }
        };
        $this->setPrivateProperty($controller, 'centroAcopioModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->getOcupacion(1);
    }

    public function testGetOcupacionCalculaElPorcentaje(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function getCentro($id) { return ['id_centro' => $id]; }
            public function getOcupacion($id) { return ['ocupado' => 50, 'capacidad' => 200]; }
        };
        $this->setPrivateProperty($controller, 'centroAcopioModel', $modelStub);

        $resultado = $controller->getOcupacion(1);

        $this->assertSame(25.0, $resultado['porcentaje']);
        $this->assertSame(50.0, $resultado['ocupado']);
    }

    public function testGetOcupacionSinDatosDevuelveCerosSinPorcentaje(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function getCentro($id) { return ['id_centro' => $id]; }
            public function getOcupacion($id) { return ['ocupado' => 0, 'capacidad' => null]; }
        };
        $this->setPrivateProperty($controller, 'centroAcopioModel', $modelStub);

        $resultado = $controller->getOcupacion(1);

        $this->assertSame(['ocupado' => 0, 'capacidad' => 0, 'porcentaje' => null], $resultado);
    }

    public function testActualizarOcupacionRechazaSiSuperaLaCapacidadAsignada(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function getCentro($id) { return ['id_centro' => $id]; }
            public function getCentroResiduo($idCentro, $idResiduo) { return ['capacidad' => 100]; }
        };
        $this->setPrivateProperty($controller, 'centroAcopioModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no puede superar la capacidad');
        $controller->actualizarOcupacion(1, 1, 150);
    }

    public function testActualizarOcupacionRechazaSiElTipoNoEstaAsignadoAlCentro(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function getCentro($id) { return ['id_centro' => $id]; }
            public function getCentroResiduo($idCentro, $idResiduo) { return null; }
        };
        $this->setPrivateProperty($controller, 'centroAcopioModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no tiene asignado');
        $controller->actualizarOcupacion(1, 1, 50);
    }

    public function testReglasValidacionExponeValoresValidos(): void
    {
        $controller = $this->controller();
        $reglas = $controller->reglasValidacion();

        $this->assertSame(['ecocentro', 'planta_clasificacion', 'relleno_sanitario', 'vertedero'], $reglas['tipo']['valores']);
        $this->assertSame(['operativo', 'saturado', 'cerrado'], $reglas['estado']['valores']);
    }
}
