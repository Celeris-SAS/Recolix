<?php

require_once __DIR__ . '/../../Controlador/trasladoController.php';

// Ingreso de residuos del operario: el camión se identifica por matrícula y debe existir; el centro
// sale del usuario de la sesión. Se prueba contra la base de test.
final class TrasladoControllerIntegrationTest extends DbTestCase
{
    protected function tearDown(): void
    {
        unset($_SESSION['usuario']);
        parent::tearDown();
    }

    private function operarioConCentro(): array
    {
        $centroId = $this->crearCentro();
        $ci = $this->crearUsuario(null, ['rol' => 'operario']);
        $this->conn()->query("UPDATE usuario SET id_centro = $centroId WHERE ci_usuario = $ci");
        $_SESSION['usuario'] = ['ci_usuario' => $ci, 'rol' => 'operario'];
        return [$ci, $centroId];
    }

    public function testRegistrarIngresoGuardaElIngresoEnElCentroDelOperario(): void
    {
        [, $centroId] = $this->operarioConCentro();
        $camionId = $this->crearCamion(['matricula' => 'ABC1234']);

        $id = (new TrasladoController())->registrarIngreso('abc1234', 250, '2026-03-05');

        $this->assertGreaterThan(0, $id);
        $this->assertSame(1, $this->contarFilas('camion_centro', "id = $id AND id_camion = $camionId AND id_centro = $centroId AND peso = 250"));
    }

    public function testRegistrarIngresoRechazaUnCamionQueNoExiste(): void
    {
        $this->operarioConCentro();

        try {
            (new TrasladoController())->registrarIngreso('ZZZ9999', 100, '2026-03-05');
            $this->fail('Debía rechazar la matrícula inexistente');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('No existe un camión con la matrícula ZZZ9999', $e->getMessage());
        }
        $this->assertSame(0, $this->contarFilas('camion_centro'));
    }

    public function testRegistrarIngresoRechazaAlOperarioSinCentro(): void
    {
        $ci = $this->crearUsuario(null, ['rol' => 'operario']);
        $_SESSION['usuario'] = ['ci_usuario' => $ci, 'rol' => 'operario'];
        $this->crearCamion(['matricula' => 'ABC1234']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('no tiene un centro de acopio asignado');
        (new TrasladoController())->registrarIngreso('ABC1234', 100, '2026-03-05');
    }

    public function testRegistrarIngresoRechazaPesoCero(): void
    {
        $this->operarioConCentro();
        $this->crearCamion(['matricula' => 'ABC1234']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('mayor que 0');
        (new TrasladoController())->registrarIngreso('ABC1234', 0, '2026-03-05');
    }

    public function testInsertTrasladoResiduoRechazaUnCamionInexistente(): void
    {
        $centroId = $this->crearCentro();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No existe un camión');
        (new TrasladoController())->insertTrasladoResiduo(999999, $centroId, 100, '2026-03-05');
    }

    public function testInsertTrasladoResiduoRechazaUnCentroInexistente(): void
    {
        $camionId = $this->crearCamion();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No existe un centro de acopio');
        (new TrasladoController())->insertTrasladoResiduo($camionId, 999999, 100, '2026-03-05');
    }
}
