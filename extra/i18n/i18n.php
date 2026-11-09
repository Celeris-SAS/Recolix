<?php

const RECOLIX_IDIOMAS = ['es', 'en', 'pt'];
const RECOLIX_IDIOMA_DEFECTO = 'es';
const RECOLIX_COOKIE = 'recolix_lang';
const RECOLIX_COOKIE_PATH = '/';

function recolix_idioma(): string
{
    static $idioma = null;
    if ($idioma !== null) {
        return $idioma;
    }

    $solicitado = $_GET['lang'] ?? null;

    if ($solicitado !== null && in_array($solicitado, RECOLIX_IDIOMAS, true)) {
        $idioma = $solicitado;
        if (!headers_sent()) {
            setcookie(RECOLIX_COOKIE, $idioma, [
                'expires'  => time() + 60 * 60 * 24 * 365,
                'path'     => RECOLIX_COOKIE_PATH,
                'samesite' => 'Lax',
            ]);
        }
        return $idioma;
    }

    $deCookie = $_COOKIE[RECOLIX_COOKIE] ?? null;
    if ($deCookie !== null && in_array($deCookie, RECOLIX_IDIOMAS, true)) {
        $idioma = $deCookie;
        return $idioma;
    }

    $idioma = RECOLIX_IDIOMA_DEFECTO;
    return $idioma;
}

function recolix_cargar_traducciones(string $idioma): array
{
    static $cache = [];
    if (isset($cache[$idioma])) {
        return $cache[$idioma];
    }

    $ruta = __DIR__ . "/{$idioma}.json";
    $datos = is_file($ruta) ? json_decode(file_get_contents($ruta), true) : null;
    $cache[$idioma] = is_array($datos) ? $datos : [];
    return $cache[$idioma];
}

function t(string $clave): string
{
    static $traducciones = null;
    static $fallback = null;

    if ($traducciones === null) {
        $traducciones = recolix_cargar_traducciones(recolix_idioma());
        $fallback = recolix_cargar_traducciones(RECOLIX_IDIOMA_DEFECTO);
    }

    [$seccion, $llave] = array_pad(explode('.', $clave, 2), 2, null);

    if ($llave !== null && isset($traducciones[$seccion][$llave])) {
        return $traducciones[$seccion][$llave];
    }
    if ($llave !== null && isset($fallback[$seccion][$llave])) {
        return $fallback[$seccion][$llave];
    }

    return $clave;
}

function t_array(array $claves): array
{
    $resultado = [];
    foreach ($claves as $alias => $clave) {
        $resultado[$alias] = t($clave);
    }
    return $resultado;
}

function t_locale(): string
{
    return t('mapa.js_locale');
}

$RECOLIX_IDIOMA = recolix_idioma();
