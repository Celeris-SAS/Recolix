// Botón "Borrar cuenta" compartido por los perfiles (vecino.php, perfil.php, perfilCuadrilla.php, perfilOperario.php).
// Si la sesión es de un vecino pide confirmación y borra la cuenta; si es personal, solo muestra el aviso
// de que tiene que pedirle a un administrador que la dé de baja (la deshabilita, no la borra). El backend vuelve a validar el rol.
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('borrarCuentaBtn');
    if (!btn) return;

    const confirmCard = document.getElementById('borrarCuentaConfirm');
    const avisoTrabajador = document.getElementById('borrarCuentaTrabajador');
    const feedback = document.getElementById('borrarFeedback');
    const confirmarBtn = document.getElementById('confirmarBorrarBtn');

    function abrir(card) {
        confirmCard.hidden = card !== confirmCard;
        avisoTrabajador.hidden = card !== avisoTrabajador;
        feedback.textContent = '';
        feedback.className = 'form-feedback';
        card.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function cerrar() {
        confirmCard.hidden = true;
        avisoTrabajador.hidden = true;
    }

    btn.addEventListener('click', () => {
        abrir(btn.dataset.esVecino === '1' ? confirmCard : avisoTrabajador);
    });

    document.getElementById('cancelarBorrarBtn').addEventListener('click', cerrar);
    document.getElementById('cerrarAvisoTrabajadorBtn').addEventListener('click', cerrar);

    confirmarBtn.addEventListener('click', async () => {
        confirmarBtn.disabled = true;
        try {
            const res = await fetch(`/recolix/API_Usuarios/api.php/usuarios/${btn.dataset.ci}`, { method: 'DELETE' });
            const json = await res.json();
            if (json.status === 'success') {
                window.location.href = '/recolix/LandingPage/index_LandingPage.html';
                return;
            }
            feedback.textContent = json.message || btn.dataset.msgError;
        } catch (err) {
            feedback.textContent = btn.dataset.msgError;
        }
        feedback.className = 'form-feedback is-error';
        confirmarBtn.disabled = false;
    });
});
