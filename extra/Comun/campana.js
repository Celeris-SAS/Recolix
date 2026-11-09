function initCampanaBadge(opts) {
    const { ci, apiUsuarios, esAdmin, apiRecoleccion } = opts;
    const badge = document.getElementById('campanaBadge');
    if (!badge || !ci) {
        return;
    }

    async function generarAlertaVencidos() {
        if (!esAdmin) {
            return;
        }
        try {
            await fetch(`${apiRecoleccion}/levantes/generarAlertaVencidos`, { method: 'POST' });
        } catch {
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
        }
    }

    actualizar();
}
