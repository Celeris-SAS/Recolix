<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: /recolix/LandingPage/login.html');
    exit;
}

$rol = $_SESSION['usuario']['rol'] ?? '';

if ($rol === 'vecino' || empty($rol)) {
    http_response_code(403);
    ?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso no autorizado — Recolix</title>
    <link rel="stylesheet" href="/recolix/Backoffice/styleBackoffice.css">
    <style>
        .denegado-wrap {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            gap: 1.25rem;
        }
        .denegado-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            justify-content: center;
            margin-bottom: 0.5rem;
        }
        .denegado-logo img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .denegado-logo span {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--moss);
        }
        .denegado-icono { font-size: 3rem; line-height: 1; }
        .denegado-titulo {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--red);
        }
        .denegado-desc {
            color: var(--muted);
            font-size: 0.95rem;
            max-width: 380px;
            line-height: 1.55;
        }
        .btn-volver {
            display: inline-block;
            padding: 0.7rem 1.5rem;
            background: var(--moss);
            color: #fff;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s;
            margin-top: 0.25rem;
        }
        .btn-volver:hover { background: var(--moss-dark); }
    </style>
</head>
<body>
<div class="denegado-wrap">
    <div class="denegado-logo">
        <img src="/recolix/API_Recoleccion/Vista/logoRecolix.png" alt="Recolix">
        <span>Recolix</span>
    </div>
    <div class="denegado-icono">⛔</div>
    <h1 class="denegado-titulo">Acceso no autorizado</h1>
    <p class="denegado-desc">
        Tu cuenta no tiene permisos para acceder al panel de administración de Recolix.
    </p>
    <a href="/recolix/LandingPage/index_LandingPage.html" class="btn-volver">Volver a la página principal</a>
</div>
</body>
</html><?php
    exit;
}
