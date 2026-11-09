<?php
?>
<div class="filtro-periodo">
    <div class="filtro-rango" id="filtroRango">
        <?php foreach ($opcionesPeriodo as [$valor, $clave]): ?>
            <button type="button" class="filtro-btn<?= $valor === $rangoInicial ? ' filtro-btn-active' : '' ?>" data-rango="<?= htmlspecialchars($valor) ?>"><?= t($clave) ?></button>
        <?php endforeach; ?>
    </div>
    <div class="filtro-fechas" id="filtroFechas"
         data-msg-incompleto="<?= htmlspecialchars(t('reportes.periodo_incompleto')) ?>"
         data-msg-invalido="<?= htmlspecialchars(t('reportes.periodo_invalido')) ?>">
        <label for="fechaDesde"><?= t('reportes.periodo_desde') ?></label>
        <input type="date" id="fechaDesde" class="filtro-select">
        <label for="fechaHasta"><?= t('reportes.periodo_hasta') ?></label>
        <input type="date" id="fechaHasta" class="filtro-select">
        <button type="button" class="filtro-btn" id="btnAplicarFechas"><?= t('reportes.periodo_aplicar') ?></button>
        <span class="filtro-error" id="errorFechas" role="alert" hidden></span>
    </div>
    <?= $filtrosExtra ?? '' ?>
</div>
