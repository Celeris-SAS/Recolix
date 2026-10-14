// Filtro de período de los informes (ver filtroPeriodo.php). Llama a alCambiar(rango) con un rango
// fijo ("hoy", "semana", ...) o con un período por fechas "AAAA-MM-DD_AAAA-MM-DD".
function initFiltroPeriodo(alCambiar) {
    const botones = document.querySelectorAll('#filtroRango .filtro-btn');
    const contenedor = document.getElementById('filtroFechas');
    const desde = document.getElementById('fechaDesde');
    const hasta = document.getElementById('fechaHasta');
    const error = document.getElementById('errorFechas');

    function mostrarError(mensaje) {
        error.textContent = mensaje;
        error.hidden = !mensaje;
    }

    botones.forEach(btn => {
        btn.addEventListener('click', () => {
            if (btn.classList.contains('filtro-btn-active')) return;
            botones.forEach(b => b.classList.remove('filtro-btn-active'));
            btn.classList.add('filtro-btn-active');
            desde.value = '';
            hasta.value = '';
            mostrarError('');
            alCambiar(btn.dataset.rango);
        });
    });

    document.getElementById('btnAplicarFechas').addEventListener('click', () => {
        if (!desde.value || !hasta.value) {
            mostrarError(contenedor.dataset.msgIncompleto);
            return;
        }
        if (desde.value > hasta.value) {
            mostrarError(contenedor.dataset.msgInvalido);
            return;
        }
        mostrarError('');
        botones.forEach(b => b.classList.remove('filtro-btn-active'));
        alCambiar(`${desde.value}_${hasta.value}`);
    });
}
