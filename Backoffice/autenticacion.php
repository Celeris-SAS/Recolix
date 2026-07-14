<?php

function autenticarUsuarioBackoffice(mysqli $conn, string $ci, string $contrasenia): array {
    $stmt = $conn->prepare(
        "SELECT ci_usuario, primer_nombre, p_apellido, rol, acceso, contrasenia
         FROM USUARIO WHERE ci_usuario = ?"
    );
    $stmt->bind_param('i', $ci);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row || !password_verify($contrasenia, $row['contrasenia'])) {
        return ['usuario' => null, 'error' => 'Cédula o contraseña incorrectos.'];
    }
    if (!$row['acceso']) {
        return ['usuario' => null, 'error' => 'Tu acceso aún no está habilitado. Contactá al administrador.'];
    }
    if ($row['rol'] === 'vecino') {
        return ['usuario' => null, 'error' => 'No tenés permiso para acceder al panel de administración.'];
    }

    unset($row['contrasenia']);
    return ['usuario' => $row, 'error' => null];
}
