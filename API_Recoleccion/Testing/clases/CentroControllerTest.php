<?php

require_once __DIR__ . '/../../Controlador/centroController.php';

final class CentroControllerTest extends RecoleccionTestCase
{
    private function controller(): CentroController
    {
        return $this->newWithoutConstructor(CentroController::class);
    }

    public function testGetCentroRechazaIdInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->getCentro(0);
    }

    public function testGetCentroValidaYDelegaAlModel(): void
    {
        $controller = $this->controller();
        $stub = new class {
            public $idRecibido;
            public function getCentro($id) {
                $this->idRecibido = $id;
                return ['id_centro' => $id];
            }
        };
        $this->setPrivateProperty($controller, 'centroModel', $stub);

        $resultado = $controller->getCentro('7');

        $this->assertSame(7, $stub->idRecibido);
        $this->assertSame(['id_centro' => 7], $resultado);
    }

    public function testFiltrarOperativosDelegaAlModel(): void
    {
        $controller = $this->controller();
        $stub = new class {
            public function filtrarOperativos() {
                return [['id_centro' => 1, 'estado' => 'operativo']];
            }
        };
        $this->setPrivateProperty($controller, 'centroModel', $stub);

        $resultado = $controller->filtrarOperativos();

        $this->assertSame([['id_centro' => 1, 'estado' => 'operativo']], $resultado);
    }
}
