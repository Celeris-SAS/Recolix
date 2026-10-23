<?php

require_once __DIR__ . '/../../Util/PdfTabla.php';

final class PdfTablaTest extends GestionTestCase
{
    public function testGeneraUnPdfValidoConElTitulo(): void
    {
        $pdf = (new PdfTabla('Tiempos de Resolución', 'Generado', ['A', 'B'], [['1', 'dos'], ['3', 'cuatro']]))->generar();

        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertStringEndsWith('%%EOF', $pdf);
        $this->assertStringContainsString('/Count 1', $pdf);
        $this->assertStringContainsString("Tiempos de Resoluci\xF3n", $pdf, 'Los acentos van en WinAnsi');
    }

    public function testEscapaParentesisYBarras(): void
    {
        $pdf = (new PdfTabla('T', 'S', ['A'], [['a(b)c\\d']]))->generar();

        $this->assertStringContainsString('a\\(b\\)c\\\\d', $pdf);
    }

    public function testPaginaCuandoHayMuchasFilas(): void
    {
        $filas = [];
        for ($i = 0; $i < 120; $i++) {
            $filas[] = ['fila ' . $i, 'x'];
        }
        $pdf = (new PdfTabla('T', 'S', ['A', 'B'], $filas))->generar();

        $this->assertDoesNotMatchRegularExpression('/\/Count 1\b/', $pdf);
        $this->assertStringContainsString("P\xE1gina 1 de", $pdf);
    }

    public function testSinFilasMuestraSinDatos(): void
    {
        $pdf = (new PdfTabla('T', 'S', ['A'], []))->generar();

        $this->assertStringContainsString('Sin datos.', $pdf);
    }

    public function testElXrefApuntaALosObjetos(): void
    {
        $pdf = (new PdfTabla('T', 'S', ['A'], [['x']]))->generar();

        preg_match('/startxref\n(\d+)\n/', $pdf, $m);
        $this->assertSame('xref', substr($pdf, (int) $m[1], 4));
    }
}
