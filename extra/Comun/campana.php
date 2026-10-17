<?php
// Campana de notificaciones compartida por los cuatro portales (vecino, admin, cuadrilla,
// operario). Se incluye una vez en cada navbar, justo antes del link al perfil, con un
// include de $_SERVER['DOCUMENT_ROOT'] más '/recolix/extra/Comun/campana.php' envuelto en PHP.
//
// Es solo un link a notificaciones.php con un contador de no leídas: la lista completa,
// con marcar leída y borrar, vive en esa página.
//
// Requiere que la página ya haya iniciado sesión y cargado i18n (t()); todas las que la usan
// ya cumplen eso porque la usan para su propio navbar. El CI se lee con el mismo fallback que
// usa el resto del código ($_SESSION['usuario']['ci_usuario'] en Backoffice/cuadrilla/operario,
// $_SESSION['ci_usuario'] en el portal de vecinos).
$campanaCiUsuario = (int) ($_SESSION['usuario']['ci_usuario'] ?? $_SESSION['ci_usuario'] ?? 0);
// RF53: la alerta de levantes vencidos se genera acá (no solo en el dashboard del Backoffice),
// para que le llegue al admin sin importar en qué página esté cuando abre la campana.
$campanaEsAdmin = ($_SESSION['usuario']['rol'] ?? '') === 'administrador';
?>
<a href="/recolix/extra/Comun/notificaciones.php" class="campana-btn" id="campanaBtn" aria-label="<?= htmlspecialchars(t('common.campana_titulo')) ?>">
    <img src="/recolix/extra/Comun/campana-icono.jpg" alt="" class="campana-icono">
    <span class="campana-badge" id="campanaBadge" hidden>0</span>
</a>
<style>
    .campana-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border: none;
        background: transparent;
        padding: 0;
        cursor: pointer;
    }
    .campana-icono {
        width: 20px;
        height: 20px;
        object-fit: contain;
    }
    .campana-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 17px;
        height: 17px;
        padding: 0 3px;
        border-radius: 999px;
        background: var(--status-crit, #c0392b);
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        line-height: 17px;
        text-align: center;
    }

    /* Navbar en mobile: solo logo, idioma (dropdown), campana y hamburguesa. El acceso al perfil
       pasa al menú hamburguesa (ver el script de abajo / el <li> del portal de vecinos). */
    .nav-perfil-movil { display: none; }
    @media (max-width: 768px) {
        .nav-right .btn-user { display: none; }
        .nav-right { gap: 8px; }
        .nav-perfil-movil { display: block; }
    }
</style>
<script src="/recolix/extra/Comun/campana.js"></script>
<script>
    // En los portales con menú hamburguesa (#navMobile) se agrega el link al perfil, que en mobile
    // deja de verse como ícono en la barra. Reusa el href y el título del ícono de usuario.
    document.addEventListener('DOMContentLoaded', function () {
        var usuario = document.querySelector('.nav-right .btn-user');
        var menu = document.getElementById('navMobile');
        if (!usuario || !menu || menu.querySelector('.nav-perfil-movil')) return;
        var enlace = document.createElement('a');
        enlace.href = usuario.getAttribute('href');
        enlace.className = 'nav-perfil-movil';
        enlace.textContent = usuario.getAttribute('title') || 'Perfil';
        menu.appendChild(enlace);
    });
</script>
<script>
    initCampanaBadge({
        ci: <?= json_encode($campanaCiUsuario) ?>,
        apiUsuarios: '/recolix/API_Usuarios/api.php',
        esAdmin: <?= json_encode($campanaEsAdmin) ?>,
        apiRecoleccion: '/recolix/API_Recoleccion'
    });
</script>
