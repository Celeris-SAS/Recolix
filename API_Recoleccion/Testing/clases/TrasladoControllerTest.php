<?php

require_once __DIR__ . '/../../Controlador/trasladoController.php';

final class TrasladoControllerTest extends RecoleccionTestCase
{
    private function controller(): TrasladoController
    {
        return $this->newWithoutConstructor(TrasladoController::class);
    }

    public function testValidarFechaAceptaFormatoEstricto(): void
    {
        $controller = $this->controller();
        $this->assertSame('2026-03-05', $this->callPrivate($controller, 'validarFecha', ['2026-03-05', 'Fecha']));
    }

    public function testValidarFechaRechazaFormatoConHora(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarFecha', ['2026-03-05 10:00:00', 'Fecha']);
    }

    public function testValidarFechaRechazaFechaInexistente(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        // DateTime normaliza 2026-02-30 a marzo, así que el round-trip
        // contra el string original falla y debe rechazarse.
        $this->callPrivate($controller, 'validarFecha', ['2026-02-30', 'Fecha']);
    }

    public function testValidarDecimalRechazaNegativo(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $this->callPrivate($controller, 'validarDecimal', [-1, 'Peso']);
    }

    public function testValidarDecimalAceptaCeroPeseAQueReglasValidacionPidaMinimoPositivo(): void
    {
        // reglasValidacion() documenta min=0.001 para 'peso', pero validarDecimal()
        // usa min=0 en la práctica: este test fija el comportamiento real del código.
        $controller = $this->controller();
        $this->assertSame(0.0, $this->callPrivate($controller, 'validarDecimal', [0, 'Peso']));
    }

    public function testInsertTrasladoResiduoRechazaFechaInvalida(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->insertTrasladoResiduo(1, 2, 500, '05-03-2026');
    }

    public function testUpdateTrasladoResiduoValidaIdPrimero(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->updateTrasladoResiduo(0, 1, 2, 500, '2026-03-05');
    }

    public function testDeleteTrasladoResiduoRechazaIdInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->deleteTrasladoResiduo('x');
    }

    public function testFiltrarPorFechaRechazaFormatoInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->filtrarPorFecha('marzo 2026');
    }

    public function testObtenerPesoTotalRechazaIdInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->obtenerPesoTotal(-5);
    }

    public function testReglasValidacionExponeCamposEsperados(): void
    {
        $controller = $this->controller();
        $reglas = $controller->reglasValidacion();
        $this->assertArrayHasKey('camionId', $reglas);
        $this->assertArrayHasKey('centroId', $reglas);
        $this->assertArrayHasKey('peso', $reglas);
        $this->assertArrayHasKey('fechaIngreso', $reglas);
    }
}
