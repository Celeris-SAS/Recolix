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
        return ['usuario' => null, 'error' => 'credenciales_invalidas'];
    }
    if ($row['acceso'] !== 'habilitado') {
        return ['usuario' => null, 'error' => 'acceso_no_habilitado'];
    }
    if ($row['rol'] === 'vecino') {
        return ['usuario' => null, 'error' => 'sin_permiso_vecino'];
    }

    unset($row['contrasenia']);
    return ['usuario' => $row, 'error' => null];
}