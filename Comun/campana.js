// Contador de la campana de notificaciones, compartido por los cuatro portales. Solo calcula
// el badge (no leídas) y lo deja al día; la lista completa, con marcar leída y borrar, vive en
// notificaciones.php (ver notificaciones.js), a donde la campana lleva como un link normal.
function initCampanaBadge(opts) {
    const { ci, apiUsuarios, esAdmin, apiRecoleccion } = opts;
    const badge = document.getElementById('campanaBadge');
    if (!badge || !ci) {
        return;
    }

    // RF53: para el admin, antes de contar las no leídas se generan (si hay alguna nueva) las
    // alertas de levantes vencidos. Corre acá y no solo en el dashboard, para que le llegue al
    // admin sin importar en qué página del Backoffice esté.
    async function generarAlertaVencidos() {
        if (!esAdmin) {
            return;
        }
        try {
            await fetch(`${apiRecoleccion}/levantes/generarAlertaVencidos`, { method: 'POST' });
        } catch {
            // Si falla, no bloquea el resto: el badge se actualiza con lo que ya había.
        }
    }

    async function actualizar() {
        await generarAlertaVencidos();
        try {
            const resp = await fetch(`${apiUsuarios}/usuarios/getNotificaciones?ci=${ci}`);
            const json = await resp.json();
            const datos = Array.isArray(json.data) ? json.data : [];
            const noLeidas = datos.filter(n => n.estado !== 'leida').length;
            if (noLeidas <= 0) {
                badge.hidden = true;
                return;
            }
            badge.hidden = false;
            badge.textContent = noLeidas > 9 ? '+9' : String(noLeidas);
        } catch {
            // Si falla, el badge se queda como estaba; no es crítico para el resto de la página.
        }
    }

    actualizar();
}
