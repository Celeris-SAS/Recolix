<?php
$camposValidacion = ['municipioId', 'idMunicipio', 'nombre', 'codigo', 'actividad', 'turno', 'frecuencia', 'matricula', 'tipoCarga',
    'capacidad', 'anio', 'modelo', 'estado', 'calle', 'numCalle', 'latitud', 'longitud', 'idTipo', 'idCircuito', 'tipo',
    'capacidadTotal', 'email', 'tel', 'rol', 'acceso', 'circuitoId', 'ci', 'primerNombre', 'pApellido', 'contrasenia'];
$mensajesValidacion = ['noValido' => 'msg_no_valido', 'entero' => 'msg_entero', 'numero' => 'msg_numero', 'menor' => 'msg_menor',
    'mayor' => 'msg_mayor', 'maxCaracteres' => 'msg_max_caracteres', 'nueveDigitos' => 'msg_nueve_digitos',
    'formato' => 'msg_formato', 'requerido' => 'msg_requerido'];
$v18n = ['campos' => [], 'mensajes' => [], 'matricula' => t('validacion.mensaje_matricula')];
foreach ($camposValidacion as $c) {
    $v18n['campos'][$c] = t('validacion.campo_' . $c);
}
foreach ($mensajesValidacion as $alias => $clave) {
    $v18n['mensajes'][$alias] = t('validacion.' . $clave);
}
?>
<script>
    const V18N = <?= json_encode($v18n, JSON_UNESCAPED_UNICODE) ?>;
    function etiquetaCampo(campo, regla) {
        return V18N.campos[campo] || (regla && regla.label) || campo;
    }
    function msgValidacion(clave, campo, regla) {
        const texto = (V18N.mensajes[clave] || '')
            .replace('{min}', regla ? regla.min : '')
            .replace('{max}', regla ? regla.max : '');
        return etiquetaCampo(campo, regla) + ' ' + texto;
    }
</script>
