<?php

require_once __DIR__ . '/../../../extra/Comun/Etiquetas.php';

use PHPUnit\Framework\TestCase;

final class EtiquetasTest extends TestCase
{
    public function testReemplazaGuionesBajosPorEspacios(): void
    {
        $this->assertSame('acceso bloqueado', Etiquetas::legible('acceso_bloqueado'));
        $this->assertSame('contenedor ausente', Etiquetas::legible('contenedor_ausente'));
        $this->assertSame('en curso', Etiquetas::legible('en_curso'));
    }

    public function testUsaTextoEspecialDondeElReemplazoNoAlcanza(): void
    {
        $this->assertSame('falta de recolección', Etiquetas::legible('falta_recoleccion'));
        $this->assertSame('riesgo de incendio', Etiquetas::legible('riesgo_de_incendio'));
    }

    public function testDejaIgualLosValoresSimples(): void
    {
        $this->assertSame('sobrepeso', Etiquetas::legible('sobrepeso'));
        $this->assertSame('', Etiquetas::legible(null));
    }
}
