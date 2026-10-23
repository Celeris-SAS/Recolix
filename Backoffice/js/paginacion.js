(function () {
    'use strict';

    const TAMANOS = [10, 25, 50, 100];
    const TEXTOS = {
        es: { mostrar: 'Mostrar', filas: 'filas', anterior: 'Anterior', siguiente: 'Siguiente', mostrando: (d, h, t) => `Mostrando ${d}–${h} de ${t}`, nav: 'Paginación', pagina: n => `Página ${n}` },
        en: { mostrar: 'Show', filas: 'rows', anterior: 'Previous', siguiente: 'Next', mostrando: (d, h, t) => `Showing ${d}–${h} of ${t}`, nav: 'Pagination', pagina: n => `Page ${n}` },
        pt: { mostrar: 'Mostrar', filas: 'linhas', anterior: 'Anterior', siguiente: 'Próxima', mostrando: (d, h, t) => `Mostrando ${d}–${h} de ${t}`, nav: 'Paginação', pagina: n => `Página ${n}` }
    };
    const idioma = (document.documentElement.lang || 'es').slice(0, 2);
    const T = TEXTOS[idioma] || TEXTOS.es;

    function inyectarEstilos() {
        if (document.getElementById('paginacion-estilos')) return;
        const css = document.createElement('style');
        css.id = 'paginacion-estilos';
        css.textContent = `
        .pag-barra { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px 20px; margin: 12px 0; font-size: 0.85rem; color: var(--muted, #6b6b66); }
        .pag-barra[hidden] { display: none; }
        .pag-tamano { display: flex; align-items: center; gap: 8px; }
        .pag-tamano select { height: 32px; padding: 0 8px; border: 1px solid var(--border, #e4e2de); border-radius: 8px; background: #fff; color: var(--text, #222); font: inherit; }
        .pag-info { font-weight: 600; }
        .pag-nav { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; }
        .pag-btn { min-width: 34px; height: 32px; padding: 0 10px; border: 1px solid var(--border, #e4e2de); border-radius: 8px; background: #fff; color: var(--text, #222); font: inherit; font-weight: 600; cursor: pointer; transition: background .15s, color .15s; }
        .pag-btn:hover:not(:disabled):not(.pag-activa) { background: var(--bg2, #eeece7); }
        .pag-btn:disabled { opacity: .45; cursor: not-allowed; }
        .pag-btn.pag-activa { background: var(--green, #3f5a33); border-color: var(--green, #3f5a33); color: #fff; cursor: default; }
        .pag-puntos { padding: 0 4px; }
        @media (max-width: 640px) { .pag-barra { justify-content: center; } }`;
        document.head.appendChild(css);
    }

    function numerosVisibles(actual, total) {
        const set = new Set([1, total, actual, actual - 1, actual + 1]);
        if (actual <= 3) { set.add(2); set.add(3); set.add(4); }
        if (actual >= total - 2) { set.add(total - 1); set.add(total - 2); set.add(total - 3); }
        const orden = [...set].filter(n => n >= 1 && n <= total).sort((a, b) => a - b);
        const salida = [];
        orden.forEach((n, i) => {
            if (i > 0 && n - orden[i - 1] > 1) salida.push('…');
            salida.push(n);
        });
        return salida;
    }

    const NO_CUENTA = '.estado-vacio, .estado-carga, .resumen-empty, .maquinaria-meta, [data-no-paginar]';

    class TablaPaginada {
        constructor(contenedor, compacta = false) {
            this.contenedor = contenedor;
            this.esTabla = contenedor.tagName === 'TBODY';
            this.compacta = compacta;
            this.pagina = 1;
            this.tamano = TAMANOS[0];
            this.totalPrevio = -1;
            this.pendiente = false;

            this.barraArriba = compacta ? null : this.crearBarra(true);
            this.barraAbajo = this.crearBarra(compacta);
            let ancla = contenedor;
            if (this.esTabla) {
                const tabla = contenedor.closest('table');
                ancla = tabla.closest('.table-wrapper') || tabla;
            }
            if (this.barraArriba) ancla.parentNode.insertBefore(this.barraArriba, ancla);
            ancla.parentNode.insertBefore(this.barraAbajo, ancla.nextSibling);

            this.observador = new MutationObserver(() => this.programar());
            this.observador.observe(contenedor, { childList: true });
            this.programar();
        }

        destruir() {
            this.observador.disconnect();
            [this.barraArriba, this.barraAbajo].forEach(b => b && b.remove());
        }

        crearBarra(conSelector) {
            const barra = document.createElement('div');
            barra.className = 'pag-barra';
            barra.hidden = true;
            const izquierda = document.createElement('div');
            izquierda.className = 'pag-tamano';
            if (conSelector) {
                const etiqueta = document.createElement('label');
                etiqueta.textContent = T.mostrar;
                const select = document.createElement('select');
                TAMANOS.forEach(n => {
                    const op = document.createElement('option');
                    op.value = n;
                    op.textContent = `${n} ${T.filas}`;
                    select.appendChild(op);
                });
                select.addEventListener('change', () => {
                    this.tamano = parseInt(select.value, 10);
                    this.pagina = 1;
                    this.render();
                });
                etiqueta.appendChild(document.createTextNode(' '));
                izquierda.append(etiqueta, select);
                barra.selector = select;
            }
            const info = document.createElement('span');
            info.className = 'pag-info';
            izquierda.appendChild(info);
            const nav = document.createElement('nav');
            nav.className = 'pag-nav';
            nav.setAttribute('aria-label', T.nav);
            barra.append(izquierda, nav);
            barra.info = info;
            barra.navegacion = nav;
            return barra;
        }

        programar() {
            if (this.pendiente) return;
            this.pendiente = true;
            requestAnimationFrame(() => { this.pendiente = false; this.render(); });
        }

        filas() {
            return [...this.contenedor.children].filter(el =>
                this.esTabla ? el.tagName === 'TR' : !el.matches(NO_CUENTA));
        }

        irA(n) {
            this.pagina = n;
            this.render();
        }

        render() {
            const filas = this.filas();
            const total = filas.length;
            if (total !== this.totalPrevio) {
                this.pagina = 1;
                this.totalPrevio = total;
            }
            const paginas = Math.max(1, Math.ceil(total / this.tamano));
            this.pagina = Math.min(Math.max(1, this.pagina), paginas);
            const desde = (this.pagina - 1) * this.tamano;
            const hasta = Math.min(desde + this.tamano, total);

            filas.forEach((tr, i) => { tr.style.display = (i >= desde && i < hasta) ? '' : 'none'; });

            const visible = total > TAMANOS[0];
            [this.barraArriba, this.barraAbajo].forEach(barra => {
                if (!barra) return;
                barra.hidden = !visible;
                if (!visible) return;
                if (barra.selector) barra.selector.value = String(this.tamano);
                barra.info.textContent = T.mostrando(desde + 1, hasta, total);
                this.dibujarNavegacion(barra.navegacion, paginas);
            });
        }

        boton(texto, deshabilitado, alClick, activa = false, etiquetaAria = '') {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'pag-btn' + (activa ? ' pag-activa' : '');
            b.textContent = texto;
            b.disabled = deshabilitado;
            if (activa) b.setAttribute('aria-current', 'page');
            if (etiquetaAria) b.setAttribute('aria-label', etiquetaAria);
            if (!deshabilitado && !activa) b.addEventListener('click', alClick);
            return b;
        }

        dibujarNavegacion(nav, paginas) {
            nav.textContent = '';
            nav.appendChild(this.boton('‹ ' + T.anterior, this.pagina === 1, () => this.irA(this.pagina - 1)));
            numerosVisibles(this.pagina, paginas).forEach(n => {
                if (n === '…') {
                    const p = document.createElement('span');
                    p.className = 'pag-puntos';
                    p.textContent = '…';
                    nav.appendChild(p);
                } else {
                    nav.appendChild(this.boton(String(n), false, () => this.irA(n), n === this.pagina, T.pagina(n)));
                }
            });
            nav.appendChild(this.boton(T.siguiente + ' ›', this.pagina === paginas, () => this.irA(this.pagina + 1)));
        }
    }

    const registrados = new Set();

    function iniciar() {
        inyectarEstilos();
        registrados.forEach(p => {
            if (!p.contenedor.isConnected) {
                p.destruir();
                registrados.delete(p);
            }
        });
        document.querySelectorAll('tbody[id], [data-paginar-lista]').forEach(el => {
            if (el.dataset.paginada) return;
            if (el.tagName === 'TBODY' && !el.closest('table')) return;
            el.dataset.paginada = '1';
            const compacta = el.dataset.paginarLista === 'compacta';
            const p = new TablaPaginada(el, compacta);
            el.paginador = p;
            registrados.add(p);
        });
    }

    let revisionPendiente = false;
    function revisarMasTarde() {
        if (revisionPendiente) return;
        revisionPendiente = true;
        requestAnimationFrame(() => { revisionPendiente = false; iniciar(); });
    }

    function arrancar() {
        iniciar();
        new MutationObserver(revisarMasTarde).observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', arrancar);
    } else {
        arrancar();
    }
    window.TablaPaginada = TablaPaginada;
})();
