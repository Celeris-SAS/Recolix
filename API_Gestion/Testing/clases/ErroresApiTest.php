<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../extra/Comun/ErroresApi.php';

final class ErroresApiTest extends TestCase
{
    public function testArgumentCountErrorNoFiltraDetallesInternos(): void
    {
        $mensaje = $this->mensajeDe(function ($a, $b) {}, []);

        $this->assertSame('Faltan datos obligatorios para esta operación.', $mensaje);
        $this->assertStringNotContainsString('Too few arguments', $mensaje);
    }

    public function testTypeErrorSeTraduceAUnMensajeDeNegocio(): void
    {
        $mensaje = $this->mensajeDe(function (int $n) {}, ['no es un número']);

        $this->assertSame('Algún dato enviado no tiene el formato esperado.', $mensaje);
    }

    /** @dataProvider restriccionesDeLaBase */
    public function testRestriccionesDeLaBaseSeTraducen(int $codigo, string $esperado): void
    {
        $e = new mysqli_sql_exception('Cannot add or update a child row...', $codigo);

        $this->assertSame($esperado, recolix_mensaje_error($e));
    }

    public static function restriccionesDeLaBase(): array
    {
        return [
            'clave foránea inexistente' => [1452, 'El registro referenciado no existe. Verificá los datos ingresados.'],
            'registros asociados' => [1451, 'No se puede eliminar o modificar porque tiene registros asociados.'],
            'duplicado' => [1062, 'Ya existe un registro con esos datos.'],
        ];
    }

    public function testErrorDeBaseSinTraduccionConservaSuMensaje(): void
    {
        $this->assertSame('otro error', recolix_mensaje_error(new mysqli_sql_exception('otro error', 1146)));
    }

    public function testLosErroresDeNegocioConservanSuMensaje(): void
    {
        $this->assertSame('El peso es obligatorio', recolix_mensaje_error(new InvalidArgumentException('El peso es obligatorio')));
    }

    private function mensajeDe(callable $funcion, array $argumentos): string
    {
        try {
            $funcion(...$argumentos);
        } catch (Throwable $e) {
            return recolix_mensaje_error($e);
        }
        $this->fail('Se esperaba un error de argumentos');
    }
}
