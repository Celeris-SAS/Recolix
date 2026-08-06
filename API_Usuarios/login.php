<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/Controlador/UsuarioController.php';

$input = json_decode(file_get_contents('php://input'), true);
$ci = $input['ci'] ?? '';
$contrasenia = $input['contrasenia'] ?? '';

$usuarioController = new UsuarioController();

try {
    $usuario = $usuarioController->login($ci, $contrasenia);

    if ($usuario) {
        $_SESSION['ci_usuario'] = $usuario['ci_usuario'];
        $_SESSION['rol'] = $usuario['rol'];

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
