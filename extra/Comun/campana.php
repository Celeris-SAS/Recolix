<?php
$campanaCiUsuario = (int) ($_SESSION['usuario']['ci_usuario'] ?? $_SESSION['ci_usuario'] ?? 0);
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

    .nav-perfil-movil { display: none; }
    @media (max-width: 768px) {
        .nav-right .btn-user { display: none; }
        .nav-right { gap: 8px; }
        .nav-perfil-movil { display: block; }
    }
</style>
<script src="/recolix/extra/Comun/campana.js"></script>
<script>
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
