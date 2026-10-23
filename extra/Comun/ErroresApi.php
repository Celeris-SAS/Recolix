<?php

function recolix_mensaje_error(Throwable $e): string
{
    if ($e instanceof mysqli_sql_exception) {
        switch ((int) $e->getCode()) {
            case 1452:
                return 'El registro referenciado no existe. Verificá los datos ingresados.';
            case 1451:
                return 'No se puede eliminar o modificar porque tiene registros asociados.';
            case 1062:
                return 'Ya existe un registro con esos datos.';
        }
    }
    if ($e instanceof ArgumentCountError) {
        return 'Faltan datos obligatorios para esta operación.';
    }
    if ($e instanceof TypeError) {
        return 'Algún dato enviado no tiene el formato esperado.';
    }
    return $e->getMessage();
}
