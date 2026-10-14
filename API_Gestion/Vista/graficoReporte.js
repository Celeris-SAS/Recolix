// Gráfico individual de cada informe. renderGraficoReporte(tipo, filas, ayudas) dibuja el gráfico
// que corresponde al informe a partir de las mismas filas que muestra la tabla.
//   ayudas.titulos: { tipo: título }, ayudas.colorTipo(nombre), ayudas.etiquetaTipoIncidencia(valor),
//   ayudas.etiquetaNivelAlerta(valor), ayudas.etiquetaEstado(valor)
const PALETA_GRAFICO = ['#3f5a33', '#8bbd3f', '#e3b23c', '#4a90a4', '#c0392b', '#7d5ba6', '#d98e04', '#2e7d32', '#999999'];

function agruparConteo(filas, clave, etiquetar) {
    const conteo = {};
    filas.forEach(f => {
        const k = f[clave] ?? '—';
        conteo[k] = (conteo[k] ?? 0) + 1;
    });
    const claves = Object.keys(conteo);
    return { labels: claves.map(k => etiquetar ? etiquetar(k) : k), valores: claves.map(k => conteo[k]) };
}

function recortar(texto, max = 28) {
    texto = String(texto ?? '');
    return texto.length > max ? texto.slice(0, max - 1) + '…' : texto;
}

function configuracionGrafico(tipo, filas, a) {
    const tope = lista => lista.slice(0, 15);
    switch (tipo) {
        case 'volumen': {
            const periodos = [...new Set(filas.map(f => f.periodo))].sort();
            const tipos = [...new Set(filas.map(f => f.tipoResiduo))];
            return {
                tipo: 'bar', apilado: true, unidad: 'kg', labels: periodos,
                datasets: tipos.map(t => ({
                    label: recortar(t, 40),
                    backgroundColor: a.colorTipo(filas.find(f => f.tipoResiduo === t).color),
                    data: periodos.map(p => (filas.find(f => f.periodo === p && f.tipoResiduo === t) || { totalKg: 0 }).totalKg)
                }))
            };
        }
        case 'incidencias': {
            const g = agruparConteo(filas, 'tipo', a.etiquetaTipoIncidencia);
            return { tipo: 'bar', labels: g.labels, valores: g.valores, unidad: '' };
        }
        case 'tiempos_resolucion':
            return { tipo: 'bar', labels: filas.map(f => '#' + f.cuadrilla), valores: filas.map(f => f.horasPromedio), unidad: 'h' };
        case 'zonas_criticas':
            return { tipo: 'bar', horizontal: true, labels: tope(filas).map(f => f.municipio), valores: tope(filas).map(f => f.incidenciasMes), unidad: '' };
        case 'frecuencia': {
            const f15 = tope([...filas].sort((x, y) => y.levantesCompletados - x.levantesCompletados));
            return { tipo: 'bar', horizontal: true, labels: f15.map(f => recortar(f.circuito)), valores: f15.map(f => f.levantesCompletados), unidad: '' };
        }
        case 'vertederos':
            return { tipo: 'bar', horizontal: true, labels: filas.map(f => recortar(f.nombre)), valores: filas.map(f => f.porcentaje), unidad: '%' };
        case 'ingresos':
            return { tipo: 'bar', horizontal: true, labels: filas.map(f => recortar(f.nombre)), valores: filas.map(f => f.totalKg), unidad: 'kg' };
        case 'clasificacion':
            return { tipo: 'doughnut', labels: filas.map(f => recortar(f.tipoResiduo, 40)), valores: filas.map(f => f.totalKg), colores: filas.map(f => a.colorTipo(f.color)), unidad: 'kg' };
        case 'reciclaje': {
            const sinTotal = filas.filter(f => f.concepto !== 'total');
            return { tipo: 'doughnut', labels: sinTotal.map(f => a.etiquetaConcepto(f.concepto)), valores: sinTotal.map(f => f.totalKg), colores: ['#8bbd3f', '#999999'], unidad: 'kg' };
        }
        case 'usuarios': {
            const ordenadas = [...filas].reverse();
            return { tipo: 'bar', labels: ordenadas.map(f => f.periodo), valores: ordenadas.map(f => f.nuevosUsuarios), unidad: '' };
        }
        case 'camiones':
            return { tipo: 'bar', labels: filas.map(f => f.matricula), valores: filas.map(f => f.kgRecolectados), unidad: 'kg' };
        case 'maquinaria': {
            const g = agruparConteo(filas, 'estado', a.etiquetaEstado);
            return { tipo: 'doughnut', labels: g.labels, valores: g.valores, unidad: '' };
        }
        case 'alertas': {
            const g = agruparConteo(filas, 'nivelAlerta', a.etiquetaNivelAlerta);
            return { tipo: 'doughnut', labels: g.labels, valores: g.valores, colores: ['#e3b23c', '#c0392b'], unidad: '' };
        }
        case 'proyecciones':
            return {
                tipo: 'bar', labels: tope(filas).map(f => '#' + f.idContenedor),
                datasets: [
                    { label: a.etiquetaActual, backgroundColor: '#8bbd3f', data: tope(filas).map(f => f.nivelActual) },
                    { label: a.etiquetaProyectado, backgroundColor: '#3f5a33', data: tope(filas).map(f => f.nivelProyectado) }
                ], unidad: '%'
            };
        default:
            return null;
    }
}

function renderGraficoReporte(tipo, filas, ayudas) {
    const wrap = document.getElementById('chartReporteWrap');
    if (!wrap || typeof Chart === 'undefined') return;
    const canvas = document.getElementById('chartReporte');
    const vacio = document.getElementById('chartReporteVacio');
    document.getElementById('chartReporteTitulo').textContent = (ayudas.titulos || {})[tipo] || '';

    const existente = Chart.getChart('chartReporte');
    if (existente) existente.destroy();

    const cfg = filas.length ? configuracionGrafico(tipo, filas, ayudas) : null;
    canvas.parentElement.hidden = !cfg;
    vacio.hidden = !!cfg;
    if (!cfg) return;

    const datasets = cfg.datasets || [{
        data: cfg.valores,
        backgroundColor: cfg.colores || (cfg.tipo === 'doughnut' ? PALETA_GRAFICO : '#3f5a33'),
        borderRadius: cfg.tipo === 'bar' ? 4 : 0,
        maxBarThickness: 36
    }];
    const esDona = cfg.tipo === 'doughnut';
    const opciones = {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: cfg.horizontal ? 'y' : 'x',
        plugins: {
            legend: { display: esDona || datasets.length > 1, position: esDona ? 'right' : 'bottom' },
            tooltip: { callbacks: { label: ctx => `${ctx.dataset.label ? ctx.dataset.label + ': ' : ''}${ctx.formattedValue}${cfg.unidad ? ' ' + cfg.unidad : ''}` } }
        }
    };
    if (!esDona) {
        const ejeValores = { beginAtZero: true, stacked: !!cfg.apilado, ticks: cfg.unidad ? {} : { precision: 0 }, title: { display: !!cfg.unidad, text: cfg.unidad } };
        const ejeCategorias = { stacked: !!cfg.apilado, grid: { display: false } };
        opciones.scales = cfg.horizontal ? { x: ejeValores, y: ejeCategorias } : { x: ejeCategorias, y: ejeValores };
    }
    new Chart(canvas, { type: cfg.tipo, data: { labels: cfg.labels, datasets }, options: opciones });
}
