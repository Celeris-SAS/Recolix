<?php
session_start();

// Sin sesión → login de vecinos
if (!isset($_SESSION['usuario'])) {
    header('Location: /recolix/LandingPage/login.html');
    exit;
}

$rol = $_SESSION['usuario']['rol'] ?? '';

// Rol vecino o cualquier caso inesperado → acceso denegado
if ($rol === 'vecino' || empty($rol)) {
    http_response_code(403);
    ?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso no autorizado — Recolix</title>
    <link rel="stylesheet" href="styleBackoffice.css">
    <link rel="stylesheet" href="styleAccesoDenegado.css">
</head>
<body>
<div class="denegado-wrap">
    <div class="denegado-logo">
        <img src="../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix">
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

$usuario        = $_SESSION['usuario'];
$primerNombre   = htmlspecialchars($usuario['primer_nombre'] ?? '');
$apellido       = htmlspecialchars($usuario['p_apellido'] ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice — Recolix</title>
    <link rel="stylesheet" href="styleBackoffice.css">
</head>
<body>

<!-- ── BARRA DE NAVEGACIÓN ─────────────────────────────────────────── -->
<header class="navbar">
    <div class="nav-inner">
        <div class="nav-left">
            <div class="brand">
                <img src="../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="brand-logo">
                <span class="brand-name">Recolix</span>
            </div>
            <nav class="nav-links">
                <a href="#" class="nav-active">Inicio</a>
                <a href="../API_Recoleccion/Vista/camiones.php">Camiones</a>
                <a href="#">Cuadrillas</a>
                <a href="../API_Gestion/Vista/contenedores.php">Contenedores</a>
                <a href="#">Vertederos</a>
                <a href="#">Centros de Acopio</a>
                <a href="mapa.php">Mapa</a>
                <a href="#">Reportes</a>
                <a href="#">Incidencias</a>
            </nav>
        </div>
        <div class="nav-right">
            <a href="../API_Usuarios/Vista/perfil.html" class="btn-user" title="Perfil de usuario">
                <img src="../API_Recoleccion/Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
            </a>
            <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="Menú">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>
    <nav class="nav-mobile" id="navMobile">
        <a href="#" class="nav-active">Inicio</a>
        <a href="../API_Recoleccion/Vista/camiones.php">Camiones</a>
        <a href="#">Cuadrillas</a>
        <a href="../API_Gestion/Vista/contenedores.php">Contenedores</a>
        <a href="#">Vertederos</a>
        <a href="#">Centros de Acopio</a>
        <a href="mapa.php">Mapa</a>
        <a href="#">Reportes</a>
        <a href="#">Incidencias</a>
    </nav>
    <div class="nav-hr"></div>
</header>

<!-- ── HERO ────────────────────────────────────────────────────────── -->
<section class="hero">
    <h1 class="hero-title">Bienvenido, <?= $primerNombre ?></h1>
    <p class="hero-sub">Panel de administración</p>
</section>

<!-- ── NOVEDADES ───────────────────────────────────────────────────── -->
<section class="novedades">
    <div class="novedades-header">
        <h2 class="novedades-title">Novedades</h2>
        <span class="novedades-count" id="novedadesCount"></span>
    </div>
    <div class="novedades-grid" id="novedadesGrid">
        <a href="#" class="novedad-card">
            <div class="novedad-icon novedad-icon-incidencias">
                <img src="img/exclamacion.png" alt="" class="card-svg-icon">
            </div>
            <div class="novedad-body">
                <h3>Incidencias Nuevas</h3>
                <p class="novedad-resumen">Reportes de vecinos sin revisar</p>
                <span class="novedad-badge" id="badgeIncidencias">0</span>
            </div>
        </a>
        <a href="indexSolicitudRegistro.php" class="novedad-card">
            <div class="novedad-icon novedad-icon-usuarios">
                <img src="../API_Recoleccion/Vista/img/user.svg" alt="" class="card-svg-icon">
            </div>
            <div class="novedad-body">
                <h3>Usuarios Nuevos</h3>
                <p class="novedad-resumen">Solicitudes de registro pendientes de aprobación</p>
                <span class="novedad-badge" id="badgeUsuarios">0</span>
            </div>
        </a>
        <a href="../API_Gestion/Vista/contenedores.php" class="novedad-card">
            <div class="novedad-icon novedad-icon-contenedores">
                <img src="img/contenedor.png" alt="" class="card-svg-icon">
            </div>
            <div class="novedad-body">
                <h3>Contenedores Críticos</h3>
                <p class="novedad-resumen">Contenedores desbordados o fuera de servicio</p>
                <span class="novedad-badge" id="badgeContenedores">0</span>
            </div>
        </a>
    </div>
</section>

<!-- ── ACCESOS RÁPIDOS ────────────────────────────────────────────── -->
<section class="cards-section">
    <div class="cards-grid">

        <a href="../API_Recoleccion/Vista/camiones.php" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="../API_Recoleccion/Vista/img/truck-svgrepo-com.svg" alt="" class="card-svg-icon"></div>
                <h3>Gestión de Camiones</h3>
                <p>Administrar flota de recolección</p>
            </div>
        </a>

        <a href="../API_Gestion/Vista/contenedores.php" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="img/contenedor.png" alt="" class="card-svg-icon"></div>
                <h3>Gestión de Contenedores</h3>
                <p>Administrar inventario de contenedores</p>
            </div>
        </a>

        <a href="../API_Gestion/Vista/reportes.html" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="img/reportes.png" alt="" class="card-svg-icon"></div>
                <h3>Reportes</h3>
                <p>Ver estadísticas y reportes del sistema</p>
            </div>
        </a>

        <a href="../API_Gestion/Vista/incidencias.html" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="img/exclamacion.png" alt="" class="card-svg-icon"></div>
                <h3>Incidencias</h3>
                <p>Gestionar reportes de vecinos</p>
            </div>
        </a>

        <a href="#" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="img/cuadrilla.svg" alt="" class="card-svg-icon"></div>
                <h3>Cuadrillas</h3>
                <p>Administrar equipos de trabajo</p>
            </div>
        </a>

        <a href="mapa.php" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="img/mapa.png" alt="" class="card-svg-icon"></div>
                <h3>Mapa</h3>
                <p>Visualizar rutas en el mapa</p>
            </div>
        </a>

        <a href="#" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="img/vertedero.svg" alt="" class="card-svg-icon"></div>
                <h3>Vertederos</h3>
                <p>Gestionar puntos de descarga</p>
            </div>
        </a>

        <a href="#" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="img/centroAcopio.png" alt="" class="card-svg-icon"></div>
                <h3>Centros de Acopio</h3>
                <p>Administrar centros de acopio</p>
            </div>
        </a>
        <a href="#" class="card-operar">
            <div class="card-content">
                <div class="card-icon"><img src="../API_Usuarios/Vista/img/user.svg" alt="" class="card-svg-icon"></div>
                <h3>Usuarios</h3>
                <p>Administrar usuarios del sistema</p>
            </div>
        </a>

    </div>
</section>

<!-- ── PIE DE PÁGINA ────────────────────────────────────────────────── -->
<footer class="footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <div class="footer-logo">
                <img src="../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="footer-logo-img">
                <span class="footer-logo-name">Recolix</span>
            </div>
            <p>Plataforma de gestión de residuos urbanos para Montevideo.</p>
        </div>
        <div class="footer-col">
            <h3>Navegación</h3>
            <ul>
                <li><a href="../LandingPage/index_LandingPage.html">Página principal</a></li>
                <li><a href="../LandingPage/mapa.html">Mapa público</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-hr"></div>
    <p class="footer-copy">&copy; 2026 Recolix. Todos los derechos reservados.</p>
</footer>

<script>
const API_GESTION      = '/recolix/API_Gestion';
const API_RECOLECCION  = '/recolix/API_Recoleccion';

async function cargarNovedades() {
    const badges = {
        incidencias:  document.getElementById('badgeIncidencias'),
        usuarios:     document.getElementById('badgeUsuarios'),
        contenedores: document.getElementById('badgeContenedores')
    };
    const countEl = document.getElementById('novedadesCount');
    let total = 0;

    try {
        const res  = await fetch(`${API_GESTION}/incidencias?estado=Abierta`);
        const json = await res.json();
        if (json.status === 'success' && Array.isArray(json.data)) {
            badges.incidencias.textContent = json.data.length;
            total += json.data.length;
        }
    } catch {
        badges.incidencias.textContent = '0';
    }

    try {
        const res  = await fetch(`/recolix/API_Usuarios/usuarios?acceso=pendiente`);
        const json = await res.json();
        if (json.status === 'success' && Array.isArray(json.data)) {
            badges.usuarios.textContent = json.data.length;
            total += json.data.length;
        }
    } catch {
        badges.usuarios.textContent = '0';
    }

    let criticos = 0;
    try {
        const res  = await fetch(`${API_GESTION}/contenedores?estado=desbordado`);
        const json = await res.json();
        if (json.status === 'success' && Array.isArray(json.data)) criticos += json.data.length;
    } catch {}
    try {
        const res  = await fetch(`${API_GESTION}/contenedores?estado=roto`);
        const json = await res.json();
        if (json.status === 'success' && Array.isArray(json.data)) criticos += json.data.length;
    } catch {}
    badges.contenedores.textContent = criticos;
    total += criticos;

    if (countEl) {
        countEl.textContent = total > 0 ? `${total} pendiente${total !== 1 ? 's' : ''}` : '';
    }
}

document.addEventListener('DOMContentLoaded', cargarNovedades);

function toggleMobileMenu() {
    document.getElementById('navMobile').classList.toggle('nav-mobile-open');
}
</script>

</body>
</html>
