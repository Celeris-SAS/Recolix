// Lista completa de notificaciones (recolix/Comun/notificaciones.php), para los cuatro
// portales. Misma API que usaba la campana: getNotificaciones, marcarNotificacionLeida y
// borrarNotificacion, todas filtradas por el CI de la sesión.
function initListaNotificaciones(opts) {
    const { ci, apiUsuarios, i18n } = opts;
    const lista = document.getElementById('notifLista');
    if (!lista || !ci) {
        return;
    }

    function escapar(texto) {
        const div = document.createElement('div');
        div.textContent = texto ?? '';
        return div.innerHTML;
    }

    function formatearFecha(fecha) {
        return new Date(String(fecha).replace(' ', 'T')).toLocaleString();
    }

    function renderizar(datos) {
        if (!datos.length) {
            lista.innerHTML = `<p class="estado-vacio">${escapar(i18n.vacio)}</p>`;
            return;
        }
        lista.innerHTML = datos.map(n => `
            <div class="campana-item ${n.estado === 'leida' ? 'leida' : 'no-leida'}" data-id="${Number(n.id_notificacion)}">
                <span class="campana-punto"></span>
                <div class="campana-cuerpo">
                    <p class="campana-mensaje">${escapar(n.mensaje)}</p>
                    <div class="campana-meta">
                        <span>${escapar(n.prioridad)}</span>
                        ${n.tipo_origen ? `<span>${escapar(n.tipo_origen)}</span>` : ''}
                        <span>${escapar(formatearFecha(n.fecha_creacion))}</span>
                    </div>
                </div>
                <div class="campana-acciones">
                    ${n.estado !== 'leida' ? `<button type="button" class="campana-accion campana-marcar" title="${escapar(i18n.marcarLeida)}" aria-label="${escapar(i18n.marcarLeida)}">&#10003;</button>` : ''}
                    <button type="button" class="campana-accion campana-borrar" title="${escapar(i18n.borrar)}" aria-label="${escapar(i18n.borrar)}">&#10005;</button>
                </div>
            </div>
        `).join('');

        lista.querySelectorAll('.campana-marcar').forEach(boton => {
            boton.addEventListener('click', () => marcarLeida(boton.closest('.campana-item').dataset.id));
        });
        lista.querySelectorAll('.campana-borrar').forEach(boton => {
            boton.addEventListener('click', () => borrar(boton.closest('.campana-item').dataset.id));
        });
    }

    async function cargar() {
        try {
            const resp = await fetch(`${apiUsuarios}/usuarios/getNotificaciones?ci=${ci}`);
            const json = await resp.json();
            renderizar(Array.isArray(json.data) ? json.data : []);
        } catch {
            lista.innerHTML = `<p class="estado-vacio">${escapar(i18n.error)}</p>`;
        }
    }

    async function marcarLeida(idNotificacion) {
        try {
            await fetch(`${apiUsuarios}/usuarios/marcarNotificacionLeida`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idNotificacion: Number(idNotificacion) })
            });
        } catch {}
        cargar();
    }

    async function borrar(idNotificacion) {
        try {
            await fetch(`${apiUsuarios}/usuarios/borrarNotificacion`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idNotificacion: Number(idNotificacion) })
            });
        } catch {}
        cargar();
    }

    cargar();
}
