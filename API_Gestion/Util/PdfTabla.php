<?php

// Generador mínimo de PDF con una tabla (sin dependencias externas).
// Usa las fuentes estándar Helvetica / Helvetica-Bold, A4 apaisado, con encabezado repetido
// en cada página y pie "Página X de Y".
class PdfTabla {
    const ANCHO = 841.89;
    const ALTO = 595.28;
    const MARGEN = 36;
    const TAM = 8;
    const INTERLINEA = 10;
    const PADDING = 3;

    // Anchos de Helvetica (AFM) para los caracteres 32..126, en milésimas de em.
    private static $anchos = [
        278,278,355,556,556,889,667,191,333,333,389,584,278,333,278,278,
        556,556,556,556,556,556,556,556,556,556,
        278,278,584,584,584,556,1015,
        667,667,722,722,667,611,778,722,278,500,667,556,833,722,778,667,778,722,667,611,722,667,944,667,667,611,
        278,278,278,469,556,333,
        556,556,500,556,556,278,556,556,222,222,500,222,833,556,556,556,556,333,500,278,556,500,722,500,500,500,
        334,260,334,584,
    ];

    private $titulo;
    private $subtitulo;
    private $encabezados;
    private $filas;
    private $textos;

    // $textos: ['pagina' => 'Página {n} de {total}', 'sinDatos' => 'Sin datos.']
    public function __construct($titulo, $subtitulo, array $encabezados, array $filas, array $textos = []) {
        $this->textos = $textos + ['pagina' => 'Página {n} de {total}', 'sinDatos' => 'Sin datos.'];
        $this->titulo = $titulo;
        $this->subtitulo = $subtitulo;
        $this->encabezados = $encabezados;
        $this->filas = $filas;
    }

    private static function aLatin1($texto) {
        return mb_convert_encoding((string) $texto, 'Windows-1252', 'UTF-8');
    }

    private static function ancho($texto, $negrita = false) {
        $suma = 0;
        $texto = self::aLatin1($texto);
        for ($i = 0, $n = strlen($texto); $i < $n; $i++) {
            $c = ord($texto[$i]);
            $suma += ($c >= 32 && $c <= 126) ? self::$anchos[$c - 32] : 556;
        }
        $ancho = $suma * self::TAM / 1000;
        return $negrita ? $ancho * 1.07 : $ancho;
    }

    private static function escapar($texto) {
        return str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', '', ' '], self::aLatin1($texto));
    }

    // Parte el texto en líneas que entren en $maximo puntos.
    private static function envolver($texto, $maximo, $negrita = false) {
        $lineas = [];
        $actual = '';
        foreach (preg_split('/\s+/u', trim((string) $texto)) as $palabra) {
            while (self::ancho($palabra, $negrita) > $maximo && mb_strlen($palabra) > 1) {
                $corte = mb_strlen($palabra) - 1;
                while ($corte > 1 && self::ancho(mb_substr($palabra, 0, $corte), $negrita) > $maximo) {
                    $corte--;
                }
                if ($actual !== '') { $lineas[] = $actual; $actual = ''; }
                $lineas[] = mb_substr($palabra, 0, $corte);
                $palabra = mb_substr($palabra, $corte);
            }
            $prueba = $actual === '' ? $palabra : $actual . ' ' . $palabra;
            if ($actual !== '' && self::ancho($prueba, $negrita) > $maximo) {
                $lineas[] = $actual;
                $actual = $palabra;
            } else {
                $actual = $prueba;
            }
        }
        $lineas[] = $actual;
        return $lineas;
    }

    private function anchosColumnas() {
        $disponible = self::ANCHO - 2 * self::MARGEN;
        $naturales = [];
        foreach ($this->encabezados as $i => $enc) {
            $max = self::ancho($enc, true);
            foreach ($this->filas as $fila) {
                $max = max($max, self::ancho($fila[$i] ?? '', false));
            }
            $naturales[$i] = $max + 2 * self::PADDING + 2;
        }
        $total = array_sum($naturales);
        if ($total <= 0) {
            return $naturales;
        }
        if ($total <= $disponible) {
            $factor = $disponible / $total;
            return array_map(function ($a) use ($factor) { return $a * $factor; }, $naturales);
        }
        // No entra: las columnas angostas conservan su ancho y las anchas se reparten el resto.
        $minimo = $disponible / count($naturales) * 0.6;
        $fijas = array_filter($naturales, function ($a) use ($minimo) { return $a <= $minimo; });
        $resto = $disponible - array_sum($fijas);
        $flexTotal = $total - array_sum($fijas);
        $res = [];
        foreach ($naturales as $i => $a) {
            $res[$i] = isset($fijas[$i]) ? $a : $a / $flexTotal * $resto;
        }
        return $res;
    }

    private function rgb($hex) {
        return sprintf('%.3f %.3f %.3f', hexdec(substr($hex, 0, 2)) / 255, hexdec(substr($hex, 2, 2)) / 255, hexdec(substr($hex, 4, 2)) / 255);
    }

    private function filaPdf(array $celdas, array $anchos, $y, $alto, $negrita, $fondo, $texto) {
        $out = '';
        $x = self::MARGEN;
        if ($fondo !== null) {
            $out .= $this->rgb($fondo) . " rg " . sprintf('%.2f %.2f %.2f %.2f re f ', $x, $y - $alto, array_sum($anchos), $alto);
        }
        $out .= '0.800 0.800 0.800 RG 0.5 w ';
        $font = $negrita ? '/F2' : '/F1';
        foreach ($celdas as $i => $lineas) {
            $out .= sprintf('%.2f %.2f %.2f %.2f re S ', $x, $y - $alto, $anchos[$i], $alto);
            $out .= $this->rgb($texto) . " rg BT $font " . self::TAM . " Tf ";
            $ty = $y - self::PADDING - self::TAM;
            foreach ($lineas as $linea) {
                $out .= sprintf('1 0 0 1 %.2f %.2f Tm (%s) Tj ', $x + self::PADDING, $ty, self::escapar($linea));
                $ty -= self::INTERLINEA;
            }
            $out .= 'ET ';
            $x += $anchos[$i];
        }
        return $out;
    }

    public function generar() {
        $anchos = $this->anchosColumnas();
        $paginas = [];
        $contenido = '';
        $y = 0;
        $limite = self::MARGEN + 14;

        $nuevaPagina = function () use (&$paginas, &$contenido, &$y) {
            if ($contenido !== '') {
                $paginas[] = $contenido;
            }
            $contenido = '';
            $y = self::ALTO - self::MARGEN;
            $contenido .= '0.247 0.353 0.200 rg BT /F2 14 Tf ' . sprintf('1 0 0 1 %.2f %.2f Tm (%s) Tj ET ', self::MARGEN, $y - 12, self::escapar($this->titulo));
            $contenido .= '0.4 0.4 0.4 rg BT /F1 8 Tf ' . sprintf('1 0 0 1 %.2f %.2f Tm (%s) Tj ET ', self::MARGEN, $y - 26, self::escapar($this->subtitulo));
            $y -= 38;
        };

        $encabezadoLineas = [];
        $altoEnc = 0;
        foreach ($this->encabezados as $i => $enc) {
            $encabezadoLineas[$i] = self::envolver($enc, $anchos[$i] - 2 * self::PADDING, true);
            $altoEnc = max($altoEnc, count($encabezadoLineas[$i]));
        }
        $altoEnc = $altoEnc * self::INTERLINEA + 2 * self::PADDING;

        $nuevaPagina();
        $contenido .= $this->filaPdf($encabezadoLineas, $anchos, $y, $altoEnc, true, '3F5A33', 'FFFFFF');
        $y -= $altoEnc;

        foreach ($this->filas as $n => $fila) {
            $celdas = [];
            $altoFila = 1;
            foreach ($this->encabezados as $i => $_) {
                $celdas[$i] = self::envolver($fila[$i] ?? '', $anchos[$i] - 2 * self::PADDING);
                $altoFila = max($altoFila, count($celdas[$i]));
            }
            $altoFila = $altoFila * self::INTERLINEA + 2 * self::PADDING;
            if ($y - $altoFila < $limite) {
                $nuevaPagina();
                $contenido .= $this->filaPdf($encabezadoLineas, $anchos, $y, $altoEnc, true, '3F5A33', 'FFFFFF');
                $y -= $altoEnc;
            }
            $fondo = $n % 2 === 1 ? 'F4F3F0' : null;
            $contenido .= $this->filaPdf($celdas, $anchos, $y, $altoFila, false, $fondo, '222222');
            $y -= $altoFila;
        }
        if (count($this->filas) === 0) {
            $contenido .= '0.4 0.4 0.4 rg BT /F1 9 Tf ' . sprintf('1 0 0 1 %.2f %.2f Tm (%s) Tj ET ', self::MARGEN, $y - 14, self::escapar($this->textos['sinDatos']));
        }
        $paginas[] = $contenido;

        $total = count($paginas);
        foreach ($paginas as $k => $c) {
            $pie = str_replace(['{n}', '{total}'], [$k + 1, $total], $this->textos['pagina']);
            $x = self::ANCHO - self::MARGEN - self::ancho($pie);
            $paginas[$k] .= '0.4 0.4 0.4 rg BT /F1 8 Tf ' . sprintf('1 0 0 1 %.2f %.2f Tm (%s) Tj ET', $x, self::MARGEN - 10, self::escapar($pie));
        }

        // Objetos: 1 catálogo, 2 páginas, 3 y 4 fuentes, luego (página, contenido) por cada página.
        $objetos = [];
        $kids = [];
        for ($k = 0; $k < $total; $k++) {
            $idPagina = 5 + 2 * $k;
            $kids[] = "$idPagina 0 R";
        }
        $objetos[1] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objetos[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . "] /Count $total >>";
        $objetos[3] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';
        $objetos[4] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>';
        foreach ($paginas as $k => $c) {
            $idPagina = 5 + 2 * $k;
            $objetos[$idPagina] = sprintf('<< /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2f %.2f] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents %d 0 R >>', self::ANCHO, self::ALTO, $idPagina + 1);
            $objetos[$idPagina + 1] = '<< /Length ' . strlen($c) . " >>\nstream\n" . $c . "\nendstream";
        }

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objetos as $id => $cuerpo) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "$id 0 obj\n$cuerpo\nendobj\n";
        }
        $xref = strlen($pdf);
        $n = count($objetos) + 1;
        $pdf .= "xref\n0 $n\n0000000000 65535 f \n";
        foreach ($offsets as $o) {
            $pdf .= sprintf("%010d 00000 n \n", $o);
        }
        $pdf .= "trailer\n<< /Size $n /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
        return $pdf;
    }
}
