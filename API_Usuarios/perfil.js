document.addEventListener('DOMContentLoaded', cargarPerfil);

let perfilDatos = {};

async function cargarPerfil() {
    try {
        const res = await fetch(`${API_BASE}/usuarios/${CI}`);
        const json = await res.json();
        if (json.status === 'success' && json.data) {
            const u = json.data;
            perfilDatos = u;
            document.getElementById('perfilNombre').textContent = `${u.primer_nombre} ${u.p_apellido}`;
            document.getElementById('perfilCI').textContent = u.ci_usuario;
            document.getElementById('perfilEmail').textContent = u.email;
            document.getElementById('perfilTel').textContent = u.tel;
        } else {
            mostrarFeedback(document.getElementById('perfilFeedback'), json.message || I18N.msgNoPudoGuardar, false);
        }
    } catch (err) {
        console.error(err);
    }
}

const perfilVista = document.getElementById('perfilVista');
const perfilForm = document.getElementById('perfilForm');
const contraseniaForm = document.getElementById('contraseniaForm');

document.getElementById('editarPerfilBtn').addEventListener('click', () => {
    document.getElementById('editNombre').value = perfilDatos.primer_nombre || '';
    document.getElementById('editApellido').value = perfilDatos.p_apellido || '';
    document.getElementById('editEmail').value = document.getElementById('perfilEmail').textContent;
    document.getElementById('editTel').value = document.getElementById('perfilTel').textContent;
    perfilVista.hidden = true;
    perfilForm.hidden = false;
    contraseniaForm.hidden = true;
});

document.getElementById('cambiarContraseniaBtn').addEventListener('click', () => {
    perfilVista.hidden = true;
    perfilForm.hidden = true;
    contraseniaForm.hidden = false;
});

document.getElementById('cancelarPerfilBtn').addEventListener('click', mostrarVista);
document.getElementById('cancelarContraBtn').addEventListener('click', mostrarVista);

function mostrarVista() {
    perfilVista.hidden = false;
    perfilForm.hidden = true;
    contraseniaForm.hidden = true;
}

document.getElementById('guardarPerfilBtn').addEventListener('click', async () => {
    const primerNombre = document.getElementById('editNombre').value.trim();
    const pApellido = document.getElementById('editApellido').value.trim();
    const email = document.getElementById('editEmail').value.trim();
    const tel = document.getElementById('editTel').value.trim();
    const feedback = document.getElementById('perfilFeedback');

    const regexNombre = /^[A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]{2,50}$/;
    const nombreOk = regexNombre.test(primerNombre);
    const apellidoOk = regexNombre.test(pApellido);
    document.getElementById('editNombreError').textContent = nombreOk ? '' : I18N.msgNombreInvalido;
    document.getElementById('editApellidoError').textContent = apellidoOk ? '' : I18N.msgNombreInvalido;
    if (!nombreOk || !apellidoOk) return;

    try {
        const res = await fetch(`${API_BASE}/usuarios/${CI}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, tel, primerNombre, pApellido })
        });
        const json = await res.json();
        if (json.status === 'success') {
            perfilDatos = { ...perfilDatos, primer_nombre: primerNombre, p_apellido: pApellido };
            document.getElementById('perfilNombre').textContent = `${primerNombre} ${pApellido}`;
            document.getElementById('perfilEmail').textContent = email;
            document.getElementById('perfilTel').textContent = tel;
            mostrarFeedback(feedback, I18N.msgDatosActualizados, true);
            setTimeout(mostrarVista, 900);
        } else {
            mostrarFeedback(feedback, json.message || I18N.msgNoPudoGuardar, false);
        }
    } catch (err) {
        mostrarFeedback(feedback, I18N.msgNoPudoGuardar, false);
    }
});

document.getElementById('guardarContraBtn').addEventListener('click', async () => {
    const actual = document.getElementById('contraActual').value;
    const nueva = document.getElementById('contraNueva').value;
    const feedback = document.getElementById('contraFeedback');

    if (!actual) {
        document.getElementById('contraActualError').textContent = I18N.msgContraActualRequerida;
        return;
    }
    if (nueva.length < 8) {
        document.getElementById('contraNuevaError').textContent = I18N.msgContraNuevaMin;
        return;
    }

    try {
        const res = await fetch(`${API_BASE}/usuarios/cambiarContrasenia`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ci: CI, contraseniaActual: actual, nuevaContrasenia: nueva })
        });
        const json = await res.json();
        if (json.status === 'success') {
            mostrarFeedback(feedback, I18N.msgContraActualizada, true);
            setTimeout(mostrarVista, 900);
        } else {
            mostrarFeedback(feedback, json.message || I18N.msgNoPudoCambiarContra, false);
        }
    } catch (err) {
        mostrarFeedback(feedback, I18N.msgNoPudoCambiarContra, false);
    }
});

document.getElementById('logoutBtn').addEventListener('click', () => {
    window.location.href = '/recolix/Backoffice/logout_backoffice.php';
});

function mostrarFeedback(el, msg, exito) {
    el.textContent = msg;
    el.classList.remove('is-success', 'is-error');
    el.classList.add(exito ? 'is-success' : 'is-error');
}