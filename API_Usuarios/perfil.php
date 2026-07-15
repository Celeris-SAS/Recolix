<?php
session_start();

// Esta página es exclusiva del staff que entra por el Portal de Empleados
// (Backoffice/login_backoffice.php, guarda $_SESSION['usuario'] como array
// anidado). La Landing Page no debe poder llegar acá: esa sesión (login.php,
// $_SESSION['ci_usuario']/$_SESSION['rol'] planos) siempre va a vecino.php.
$ci  = $_SESSION['usuario']['ci_usuario'] ?? null;
$rol = $_SESSION['usuario']['rol'] ?? null;

if (!$ci) {
    header("Location: /recolix/Backoffice/login_backoffice.php");
    exit();
}

// Solo administrador, cuadrilla u operario pueden entrar
$rolesPermitidos = ['administrador', 'cuadrilla', 'operario'];
if (!in_array($rol, $rolesPermitidos)) {
    header("Location: /recolix/Backoffice/login_backoffice.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recolix — Mi panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../Backoffice/styleBackoffice.css">
    <link rel="stylesheet" href="Vista/styles_vecino.css">
</head>

<body>

    <header class="navbar">
        <div class="nav-inner">
            <div class="nav-left">
                <div class="brand">
                    <img src="../API_Recoleccion/Vista/logoRecolix.png" alt="Recolix" class="brand-logo">
                    <span class="brand-name">Recolix</span>
                </div>
                <nav class="nav-links">
                    <a href="../Backoffice/indexBackoffice.php">Inicio</a>
                    <a href="../API_Recoleccion/Vista/camiones.php">Camiones</a>
                    <a href="#">Cuadrillas</a>
                    <a href="../API_Gestion/Vista/contenedores.php">Contenedores</a>
                    <a href="#">Vertederos</a>
                    <a href="#">Centros de Acopio</a>
                    <a href="../Backoffice/mapa.php">Mapa</a>
                    <a href="#">Reportes</a>
                    <a href="#">Incidencias</a>
                    <a href="Vista/usuarios.php">Usuarios</a>
                </nav>
            </div>
            <div class="nav-right">
                <a href="perfil.php" class="btn-user" title="Perfil de usuario">
                    <img src="../API_Recoleccion/Vista/img/user.svg" alt="Usuario" class="nav-user-icon">
                </a>
                <button class="nav-hamburger" id="navHamburger" onclick="toggleMobileMenu()" aria-label="Menú">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
        <nav class="nav-mobile" id="navMobile">
            <a href="../Backoffice/indexBackoffice.php">Inicio</a>
            <a href="../API_Recoleccion/Vista/camiones.php">Camiones</a>
            <a href="#">Cuadrillas</a>
            <a href="../API_Gestion/Vista/contenedores.php">Contenedores</a>
            <a href="#">Vertederos</a>
            <a href="#">Centros de Acopio</a>
            <a href="../Backoffice/mapa.php">Mapa</a>
            <a href="#">Reportes</a>
            <a href="#">Incidencias</a>
            <a href="Vista/usuarios.php">Usuarios</a>
        </nav>
        <div class="nav-hr"></div>
    </header>

    <main class="container dash-main">
        <section class="dash-section" id="seccionPerfil">
            <div class="section-header">
                <div>
                    <p class="section-eyebrow">Mi cuenta</p>
                    <h2>Mis datos</h2>
                </div>
                <button class="btn btn-outline btn-sm" id="editarPerfilBtn">Editar datos</button>
            </div>

            <div class="panel-card" id="perfilVista">
                <div class="perfil-grid">
                    <div class="perfil-campo">
                        <p class="campo-label">Nombre completo</p>
                        <p class="campo-valor" id="perfilNombre">—</p>
                    </div>
                    <div class="perfil-campo">
                        <p class="campo-label">Cédula de identidad</p>
                        <p class="campo-valor" id="perfilCI">—</p>
                    </div>
                    <div class="perfil-campo">
                        <p class="campo-label">Correo electrónico</p>
                        <p class="campo-valor" id="perfilEmail">—</p>
                    </div>
                    <div class="perfil-campo">
                        <p class="campo-label">Teléfono</p>
                        <p class="campo-valor" id="perfilTel">—</p>
                    </div>
                </div>
                <button class="btn btn-outline btn-sm" id="cambiarContraseniaBtn" style="margin-top:20px;">Cambiar
                    contraseña</button>
            </div>

            <div class="panel-card" id="perfilForm" hidden>
                <h3>Editar datos de contacto</h3>
                <p class="form-nota">Solo podés modificar tu correo y teléfono. Para otros cambios contactá al
                    administrador.</p>
                <div class="form-row">
                    <label for="editEmail">Correo electrónico</label>
                    <input type="email" id="editEmail" name="email" placeholder="nombre@correo.com">
                    <p class="error-msg" id="editEmailError"></p>
                </div>
                <div class="form-row">
                    <label for="editTel">Teléfono</label>
                    <input type="tel" id="editTel" name="tel" placeholder="Ej: 099123456">
                    <p class="error-msg" id="editTelError"></p>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" id="guardarPerfilBtn">Guardar cambios</button>
                    <button class="btn btn-outline btn-sm" id="cancelarPerfilBtn">Cancelar</button>
                </div>
                <p class="form-feedback" id="perfilFeedback"></p>
            </div>

            <div class="panel-card" id="contraseniaForm" hidden>
                <h3>Cambiar contraseña</h3>
                <div class="form-row">
                    <label for="contraActual">Contraseña actual</label>
                    <input type="password" id="contraActual" placeholder="Tu contraseña actual">
                    <p class="error-msg" id="contraActualError"></p>
                </div>
                <div class="form-row">
                    <label for="contraNueva">Nueva contraseña</label>
                    <input type="password" id="contraNueva" placeholder="Mínimo 8 caracteres">
                    <p class="error-msg" id="contraNuevaError"></p>
                </div>
                <div class="form-actions">
                    <button class="btn btn-primary" id="guardarContraBtn">Cambiar contraseña</button>
                    <button class="btn btn-outline btn-sm" id="cancelarContraBtn">Cancelar</button>
                </div>
                <p class="form-feedback" id="contraFeedback"></p>
            </div>

            <div class="logout">
                <button class="btn btn-outline btn-sm" id="logoutBtn">Cerrar sesión</button>
            </div>
        </section>
    </main>

    <script>const CI = <?= json_encode($ci) ?>;</script>
    <script src="perfil.js"></script>
    <script>
function toggleMobileMenu() {
    document.getElementById('navMobile').classList.toggle('nav-mobile-open');
}
</script>
</body>
</html>
