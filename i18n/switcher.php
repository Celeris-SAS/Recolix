<?php
/**
 * Partial: selector de idioma. Requiere que i18n.php ya haya sido incluido.
 * Genera links ?lang=es|en|pt preservando la URL y el resto del query string actual.
 */

$recolixIdiomaActual = $RECOLIX_IDIOMA ?? recolix_idioma();

$rutaActual = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
parse_str(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY) ?: '', $queryActual);
?>
<div class="lang-switcher" role="group" aria-label="Idioma / Language / Idioma">
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
