<?php
session_start();
header('Content-Type: application/json');

// Incluir tu controlador de usuarios
require_once __DIR__ . '/Controlador/UsuarioController.php';

// Leer datos enviados por fetch (JSON)
$input = json_decode(file_get_contents('php://input'), true);
$ci = $input['ci'] ?? '';
$contrasenia = $input['contrasenia'] ?? '';

$usuarioController = new UsuarioController();

try {
    // Llamar al método login del controlador
    $usuario = $usuarioController->login($ci, $contrasenia);

    if ($usuario) {
        // Guardar datos en sesión
        $_SESSION['ci_usuario'] = $usuario['ci_usuario'];
        $_SESSION['rol'] = $usuario['rol'];

        // Responder al frontend
        echo json_encode([
            "success" => true,
            "rol" => $usuario['rol']
        ]);
    } else {
        echo json_encode([
            "success" => false
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
