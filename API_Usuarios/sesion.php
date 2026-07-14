<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['ci_usuario'])) {
    echo json_encode([
        'loggedIn'   => true,
        'ci_usuario' => $_SESSION['ci_usuario'],
        'rol'        => $_SESSION['rol'] ?? null
    ]);
} else {
    echo json_encode(['loggedIn' => false]);
}
