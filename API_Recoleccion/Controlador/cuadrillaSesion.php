<?php
require_once __DIR__ . '/AccesoDenegadoException.php';
require_once __DIR__ . '/../../conexion.php';

// Una cuadrilla solo opera sobre sus propios levantes y rutas (la cuadrilla real del usuario se lee
// de la base, no de la sesión, que puede quedar vieja). El administrador no tiene esta restricción.
class CuadrillaSesion {

    public static function aplica() {
        return ($_SESSION['usuario']['rol'] ?? '') === 'cuadrilla';
    }

    public static function id() {
        $ci = $_SESSION['usuario']['ci_usuario'] ?? $_SESSION['ci_usuario'] ?? null;
        if ($ci === null) {
            return null;
        }
        $conn = (new conexion())->getConexion();
        $stmt = mysqli_prepare($conn, "SELECT id_cuadrilla FROM USUARIO WHERE ci_usuario = ?");
        mysqli_stmt_bind_param($stmt, "i", $ci);
        mysqli_stmt_execute($stmt);
        $fila = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return isset($fila['id_cuadrilla']) ? (int) $fila['id_cuadrilla'] : null;
    }

    public static function exigir($idCuadrilla) {
        if (!self::aplica()) {
            return;
        }
        $propia = self::id();
        if ($propia === null || $idCuadrilla === null || $propia !== (int) $idCuadrilla) {
            throw new AccesoDenegadoException('Solo podés operar sobre levantes de tu cuadrilla.');
        }
    }

    // Filas de levante (con id_cuadrilla): para una cuadrilla, solo las propias.
    public static function soloPropios(array $levantes) {
        if (!self::aplica()) {
            return $levantes;
        }
        $propia = self::id();
        return array_values(array_filter($levantes, function ($l) use ($propia) {
            return $propia !== null && (int) ($l['id_cuadrilla'] ?? 0) === $propia;
        }));
    }
}
