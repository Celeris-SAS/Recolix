<?php

require_once __DIR__ . '/../../Controlador/camionController.php';

/**
 * CamionController valida todos sus argumentos antes de tocar el Model,
 * así que se puede instanciar sin constructor (sin CamionModel real,
 * sin conexión a MySQL) y ejercitar las validaciones directamente: las
 * llamadas inválidas lanzan InvalidArgumentException antes de llegar a
 * $this->camionModel.
 */
final class CamionControllerTest extends RecoleccionTestCase
{
    private function controller(): CamionController
    {
        return $this->newWithoutConstructor(CamionController::class);
    }

    public function testValidarIdAceptaEnteroPositivo(): void
    {
        $controller = $this->controller();
        $this->assertSame(5, $this->callPrivate($controller, 'validarId', ['5']));
    }

    public function testValidarIdRechazaCero(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarId', [0]);
    }

    public function testValidarIdRechazaNoNumerico(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarId', ['abc']);
    }

    public function testValidarStringRecortaEspacios(): void
    {
        $controller = $this->controller();
        $this->assertSame('Fiat', $this->callPrivate($controller, 'validarString', ['  Fiat  ', 'Modelo']));
    }

    public function testValidarStringRechazaVacio(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarString', ['   ', 'Modelo']);
    }

    public function testValidarStringRechazaMasDe255Caracteres(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarString', [str_repeat('a', 256), 'Modelo']);
    }

    public function testValidarEnteroRechazaDecimal(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarEntero', [3.5, 'Capacidad']);
    }

    /** @dataProvider capacidadesInvalidas */
    public function testValidarCapacidadRechazaFueraDeRango($valor): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarCapacidad', [$valor]);
    }

    public static function capacidadesInvalidas(): array
    {
        return [
            'cero' => [0],
            'excede el maximo' => [100001],
        ];
    }

    public function testValidarCapacidadAceptaLimitesInclusive(): void
    {
        $controller = $this->controller();
        $this->assertSame(1, $this->callPrivate($controller, 'validarCapacidad', [1]));
        $this->assertSame(100000, $this->callPrivate($controller, 'validarCapacidad', [100000]));
    }

    /** @dataProvider aniosInvalidos */
    public function testValidarAnioRechazaFueraDeRango($valor): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarAnio', [$valor]);
    }

    public static function aniosInvalidos(): array
    {
        return [
            'antes de 1990' => [1989],
            'despues de 2030' => [2031],
        ];
    }

    public function testReglasValidacionExponeValoresValidos(): void
    {
        $controller = $this->controller();
        $reglas = $controller->reglasValidacion();

        $this->assertSame(['carga_lateral', 'carga_trasera', 'puerta_a_puerta'], $reglas['tipoCarga']['valores']);
        $this->assertSame(1990, $reglas['anio']['min']);
        $this->assertSame(2030, $reglas['anio']['max']);
    }

    public function testInsertCamionRechazaMatriculaConFormatoInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Matrícula debe tener formato');
        $controller->insertCamion('ABCD123', 'carga_lateral', 100, 2020, 'Fiat', 'disponible');
    }

    public function testInsertCamionRechazaTipoCargaInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->insertCamion('ABC1234', 'carga_voladora', 100, 2020, 'Fiat', 'disponible');
    }

    public function testInsertCamionRechazaEstadoInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->insertCamion('ABC1234', 'carga_lateral', 100, 2020, 'Fiat', 'flotando');
    }

    public function testUpdateCamionValidaIdAntesQueElResto(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->updateCamion(-1, 'ABC1234', 'carga_lateral', 100, 2020, 'Fiat', 'disponible');
    }

    public function testDeleteCamionRechazaIdInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->deleteCamion('no-es-un-id');
    }

    public function testFiltrarEstadoRechazaValorNoListado(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->filtrarEstado('inventado');
    }

    public function testFiltrarTipoCargaRechazaValorNoListado(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->filtrarTipoCarga('inventado');
    }

    public function testFiltrarAnioRechazaFueraDeRango(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->filtrarAnio(1800);
    }

    public function testFiltrarCapacidadRechazaFueraDeRango(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->filtrarCapacidad(0);
    }

    public function testObtenerHistorialRechazaIdInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->obtenerHistorial(0);
    }
}
