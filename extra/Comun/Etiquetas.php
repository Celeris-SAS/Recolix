<?php
class Etiquetas {
    private static $especiales = [
        'falta_recoleccion' => 'falta de recolección',
        'riesgo_de_incendio' => 'riesgo de incendio',
    ];

    public static function legible($valor) {
        $valor = (string) $valor;
        return self::$especiales[$valor] ?? str_replace('_', ' ', $valor);
    }
}
