<?php

require_once __DIR__ . '/../../Controlador/prediccionLlenadoController.php';

final class PrediccionLlenadoControllerTest extends GestionTestCase
{
    private function controller(): prediccionLlenadoController
    {
        return $this->newWithoutConstructor(prediccionLlenadoController::class);
    }

    public function testCalcularPrediccionRechazaContenedorInexistente(): void
    {
        $controller = $this->controller();
        $contenedorModelStub = new class {
            public function getContenedor($id) { return null; }
        };
        $this->setPrivateProperty($controller, 'contenedorModel', $contenedorModelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->calcularPrediccion(999);
    }

    public function testCalcularPrediccionRechazaCapacidadCero(): void
    {
        $controller = $this->controller();
        $contenedorModelStub = new class {
            public function getContenedor($id) { return ['id_contenedor' => $id, 'capacidad' => 0]; }
        };
        $this->setPrivateProperty($controller, 'contenedorModel', $contenedorModelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->calcularPrediccion(1);
    }

    public function testCalcularPrediccionRechazaSinLevantesRegistrados(): void
    {
        $controller = $this->controller();
        $contenedorModelStub = new class {
            public function getContenedor($id) { return ['id_contenedor' => $id, 'capacidad' => 100]; }
        };
        $prediccionModelStub = new class {
            public function getDatosLevantes($id) { return []; }
        };
        $this->setPrivateProperty($controller, 'contenedorModel', $contenedorModelStub);
        $this->setPrivateProperty($controller, 'prediccionModel', $prediccionModelStub);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No hay levantes registrados');
        $controller->calcularPrediccion(1);
    }

    public function testCalcularPrediccionRechazaSiTodosLosLevantesSonAntiguos(): void
    {
        $controller = $this->controller();
        $contenedorModelStub = new class {
            public function getContenedor($id) { return ['id_contenedor' => $id, 'capacidad' => 100]; }
        };
        $prediccionModelStub = new class {
            public function getDatosLevantes($id) {
                return [
                    ['peso' => 50, 'hora_levante' => date('Y-m-d', strtotime('-30 days'))],
                ];
            }
        };
        $this->setPrivateProperty($controller, 'contenedorModel', $contenedorModelStub);
        $this->setPrivateProperty($controller, 'prediccionModel', $prediccionModelStub);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('últimos 7 días');
        $controller->calcularPrediccion(1);
    }

    public function testCalcularPrediccionPromediaSoloLosLevantesRecientes(): void
    {
        $controller = $this->controller();
        $contenedorModelStub = new class {
            public function getContenedor($id) { return ['id_contenedor' => $id, 'capacidad' => 200]; }
        };
        $prediccionModelStub = new class {
            public $idRecibido = null;
            public $nivelRecibido = null;
            public function getDatosLevantes($id) {
                return [
                    ['peso' => 1000, 'hora_levante' => date('Y-m-d', strtotime('-30 days'))],
                    ['peso' => 60, 'hora_levante' => date('Y-m-d', strtotime('-1 day'))],
                    ['peso' => 80, 'hora_levante' => date('Y-m-d', strtotime('-2 days'))],
                ];
            }
            public function insertPrediccion($idContenedor, $nivelProyectado) {
                $this->idRecibido = $idContenedor;
                $this->nivelRecibido = $nivelProyectado;
                return 123;
            }
        };
        $this->setPrivateProperty($controller, 'contenedorModel', $contenedorModelStub);
        $this->setPrivateProperty($controller, 'prediccionModel', $prediccionModelStub);

        $resultado = $controller->calcularPrediccion(1);

        $this->assertSame(1, $prediccionModelStub->idRecibido);
        $this->assertSame(35, $prediccionModelStub->nivelRecibido);
        $this->assertSame(123, $resultado);
    }

    public function testGetUltimaPrediccionRechazaContenedorInexistente(): void
    {
        $controller = $this->controller();
        $contenedorModelStub = new class {
            public function getContenedor($id) { return null; }
        };
        $this->setPrivateProperty($controller, 'contenedorModel', $contenedorModelStub);

        $this->expectException(InvalidArgumentException::class);
        $controller->getUltimaPrediccion(999);
    }

    public function testGetPrediccionRechazaIdInvalido(): void
    {
        $controller = $this->controller();
        $this->expectException(InvalidArgumentException::class);
        $controller->getPrediccion('no-es-un-id');
    }
}
