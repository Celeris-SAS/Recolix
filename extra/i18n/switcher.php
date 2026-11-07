<?php

$recolixIdiomaActual = $RECOLIX_IDIOMA ?? recolix_idioma();

$rutaActual = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
parse_str(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?: '', $queryActual);
?>
<div class="lang-switcher" role="group" aria-label="Idioma / Language / Idioma">
    <button type="button" class="lang-switcher-toggle" aria-haspopup="true" aria-expanded="false"><?= strtoupper($recolixIdiomaActual) ?></button>
    <div class="lang-switcher-menu">
    <?php foreach (RECOLIX_IDIOMAS as $idiomaOpcion):
        $queryOpcion = array_merge($queryActual, ['lang' => $idiomaOpcion]);
        $href = $rutaActual . '?' . http_build_query($queryOpcion);
        $activo = $idiomaOpcion === $recolixIdiomaActual;
    ?>
        <a href="<?= htmlspecialchars($href) ?>"
           class="lang-switcher-item<?= $activo ? ' is-active' : '' ?>"
           aria-current="<?= $activo ? 'true' : 'false' ?>"
        ><?= strtoupper($idiomaOpcion) ?></a>
    <?php endforeach; ?>
    </div>
</div>
<style>
    .lang-switcher-toggle { display: none; }
    .lang-switcher-menu { display: contents; }

    @media (max-width: 768px) {
        .lang-switcher { position: relative; display: block; }
        .lang-switcher-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 34px;
            padding: 0 10px;
            border: 1px solid rgba(128, 128, 128, 0.45);
            border-radius: 8px;
            background: transparent;
            color: inherit;
            font: inherit;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.03em;
            cursor: pointer;
        }
        .lang-switcher-toggle::after {
            content: '';
            width: 6px;
            height: 6px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: translateY(-2px) rotate(45deg);
        }
        .lang-switcher.is-open .lang-switcher-toggle::after { transform: translateY(1px) rotate(-135deg); }
        .lang-switcher-menu {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            min-width: 64px;
            flex-direction: column;
            gap: 2px;
            padding: 6px;
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.12);
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            z-index: 300;
        }
        .lang-switcher.is-open .lang-switcher-menu { display: flex; }
        .lang-switcher-menu .lang-switcher-item {
            display: block;
            padding: 8px 12px;
            color: #222 !important;
            background: transparent !important;
            text-align: center;
        }
        .lang-switcher-menu .lang-switcher-item.is-active { background: #eceae5 !important; }
    }
</style>
<script>
    (function () {
        document.querySelectorAll('.lang-switcher').forEach(function (box) {
            if (box.dataset.dropdown) return;
            box.dataset.dropdown = '1';
            var toggle = box.querySelector('.lang-switcher-toggle');
            if (!toggle) return;
            toggle.addEventListener('click', function (e) {
                e.stopPropagation();
                var abierto = box.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', String(abierto));
            });
            document.addEventListener('click', function (e) {
                if (!box.contains(e.target)) {
                    box.classList.remove('is-open');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        });
    })();
</script>
