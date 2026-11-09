<?php

require_once __DIR__ . '/../../Controlador/contenedorController.php';

final class ContenedorControllerTest extends GestionTestCase
{
    private function controller(): contenedorController
    {
        return $this->newWithoutConstructor(contenedorController::class);
    }

    public function testValidarIdRechazaNoNumerico(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarId', ['abc']);
    }

    public function testValidarStringRechazaVacio(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarString', ['   ', 'Calle']);
    }

    public function testValidarDecimalRechazaCeroONegativo(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarDecimal', [0, 'Capacidad']);
    }

    /** @dataProvider coordenadasInvalidas */
    public function testValidarCoordenadaRechazaFueraDeRango(string $nombre, $valor): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarCoordenada', [$valor, $nombre]);
    }

    public static function coordenadasInvalidas(): array
    {
        return [
            'latitud fuera de rango' => ['Latitud', 91],
            'longitud fuera de rango' => ['Longitud', 181],
        ];
    }

    public function testValidarCoordenadaAceptaLimitesDistintosParaLatitudYLongitud(): void
    {
        $controller = $this->controller();
        $this->assertSame(90.0, $this->callPrivate($controller, 'validarCoordenada', [90, 'Latitud']));
        $this->assertSame(180.0, $this->callPrivate($controller, 'validarCoordenada', [180, 'Longitud']));
    }

    public function testValidarEstadoRechazaValorNoListado(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarEstado', ['flotando']);
    }

    public function testValidarEnteroRechazaDecimal(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarEntero', [3.5, 'Número de puerta']);
    }

    public function testInsertContenedorRechazaEstadoInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->insertContenedor(1, 1, 'Calle', 100, -34.9, -56.1, 100, 'flotando');
    }

    public function testInsertContenedorRechazaIdCircuitoInvalidoAntesQueElResto(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->insertContenedor(-1, 1, 'Calle', 100, -34.9, -56.1, 100, 'funcional');
    }

    public function testUpdateContenedorRechazaIdInexistente(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function getContenedor($id) { return null; }
        };
        $this->setPrivateProperty($controller, 'contenedorModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->updateContenedor(999, 1, 1, 'Calle', 100, -34.9, -56.1, 100);
    }

    public function testRegistrarReparacionRechazaSiElContenedorNoEstaRoto(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function getContenedor($id) { return ['id_contenedor' => $id, 'estado' => 'funcional']; }
        };
        $this->setPrivateProperty($controller, 'contenedorModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'roto'");
        $controller->registrarReparacion(1);
    }

    public function testGetNivelLlenadoRechazaCapacidadCero(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function consultarNivelLlenado($id) { return ['peso' => 50, 'capacidad' => 0]; }
        };
        $this->setPrivateProperty($controller, 'contenedorModel', $modelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->getNivelLlenado(1);
    }

    public function testGetNivelLlenadoCalculaElPorcentaje(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function consultarNivelLlenado($id) { return ['peso' => 50, 'capacidad' => 200]; }
        };
        $this->setPrivateProperty($controller, 'contenedorModel', $modelStub);

        $this->assertSame(25.0, $controller->getNivelLlenado(1));
    }

    public function testGetNivelLlenadoDevuelveNullSiNoHayDatos(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function consultarNivelLlenado($id) { return null; }
        };
        $this->setPrivateProperty($controller, 'contenedorModel', $modelStub);

        $this->assertNull($controller->getNivelLlenado(1));
    }

    public function testGetHistorialOrdenaDeMasNuevoAMasViejo(): void
    {
        $controller = $this->controller();
        $modelStub = new class {
            public function getHistorial($id) {
                return [
                    ['tipo_evento' => 'incidencia', 'fecha' => '2026-01-01'],
                    ['tipo_evento' => 'reparacion', 'fecha' => '2026-03-01'],
                    ['tipo_evento' => 'levante', 'fecha' => '2026-02-01'],
                ];
            }
        };
        $this->setPrivateProperty($controller, 'contenedorModel', $modelStub);

        $historial = $controller->getHistorial(1);

        $this->assertSame(['reparacion', 'levante', 'incidencia'], array_column($historial, 'tipo_evento'));
    }

    public function testReglasValidacionExponeValoresValidos(): void
    {
        $controller = $this->controller();
        $reglas = $controller->reglasValidacion();

        $this->assertSame(['funcional', 'roto', 'desbordado', 'inactivo', 'en_deposito'], $reglas['estado']['valores']);
    }
}
