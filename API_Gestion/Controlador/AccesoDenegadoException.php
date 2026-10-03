<?php

// Se lanza cuando la sesión no tiene permiso para la operación. api.php la traduce a HTTP 403.
class AccesoDenegadoException extends RuntimeException {
}
