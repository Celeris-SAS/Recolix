<?php

require_once __DIR__ . '/../../Modelo/reporteModel.php';

final class ReporteModelTest extends GestionTestCase
{
    private function model(): ReporteModel
    {
        return $this->newWithoutConstructor(ReporteModel::class);
    }

    /** @dataProvider rangosDeFecha */
    public function testCondicionRangoFechaGeneraElFragmentoEsperado(string $rango, string $esperadoParcial): void
    {
        $model = $this->model();
        $sql = $this->callPrivate($model, 'condicionRangoFecha', ['l.hora_levante', $rango]);
        $this->assertStringContainsString($esperadoParcial, $sql);
    }

    public static function rangosDeFecha(): array
    {
        return [
            'hoy' => ['hoy', 'l.hora_levante = CURDATE()'],
            'semana' => ['semana', 'INTERVAL 7 DAY'],
            'mes' => ['mes', 'INTERVAL 1 MONTH'],
            'siempre' => ['siempre', '1=1'],
            'valor desconocido cae en el default (hoy)' => ['inventado', 'l.hora_levante = CURDATE()'],
        ];
    }

    /** @dataProvider rangosDeFechaHora */
    public function testCondicionRangoFechaHoraGeneraElFragmentoEsperado(string $rango, string $esperadoParcial): void
    {
        $model = $this->model();
        $sql = $this->callPrivate($model, 'condicionRangoFechaHora', ['fecha_apertura', $rango]);
        $this->assertStringContainsString($esperadoParcial, $sql);
    }

    public static function rangosDeFechaHora(): array
    {
        return [
            'hoy' => ['hoy', 'DATE(fecha_apertura) = CURDATE()'],
            'semana' => ['semana', 'INTERVAL 7 DAY'],
            'mes' => ['mes', 'INTERVAL 1 MONTH'],
            'siempre' => ['siempre', '1=1'],
        ];
    }
}
