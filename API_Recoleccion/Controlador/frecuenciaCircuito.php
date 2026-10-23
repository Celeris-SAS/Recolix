<?php
class FrecuenciaCircuito {
    const DIAS = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];

    public static function normalizar($texto) {
        $texto = mb_strtolower((string) $texto, 'UTF-8');
        return strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);
    }

    public static function dias($frecuencia) {
        $texto = self::normalizar($frecuencia);
        $encontrados = [];

        foreach (self::DIAS as $dia) {
            if (mb_strpos($texto, $dia) !== false) {
                $encontrados[$dia] = true;
            }
        }

        $patronDias = implode('|', self::DIAS);
        if (preg_match('/(' . $patronDias . ')\s+a\s+(' . $patronDias . ')/', $texto, $m)) {
            $inicio = array_search($m[1], self::DIAS, true);
            $fin = array_search($m[2], self::DIAS, true);
            if ($inicio !== false && $fin !== false) {
                $total = count(self::DIAS);
                $i = $inicio;
                while (true) {
                    $encontrados[self::DIAS[$i]] = true;
                    if ($i === $fin) break;
                    $i = ($i + 1) % $total;
                }
            }
        }

        return array_keys($encontrados);
    }
}
