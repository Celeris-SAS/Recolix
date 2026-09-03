const RECOLIX_API_USUARIOS = '/recolix/API_Usuarios/api.php';
const RECOLIX_PAGINAS_USUARIOS = '/recolix/API_Usuarios';

document.addEventListener('DOMContentLoaded', async () => {
  const link = document.getElementById('loginNavLink');
  if (!link) return;

  try {
    const resp = await fetch(`${RECOLIX_PAGINAS_USUARIOS}/sesion.php`);    const data = await resp.json();

    if (data.loggedIn) {
      link.href = `${RECOLIX_PAGINAS_USUARIOS}/vecino.php`;
      link.title = 'Mi perfil';
      link.classList.remove('btn', 'btn-outline', 'btn-sm');
      link.classList.add('btn-user');
      link.innerHTML = `<img src="${RECOLIX_PAGINAS_USUARIOS}/Vista/img/user.svg" alt="Mi perfil" class="nav-user-icon">`;
    }
  } catch (e) {

  }
});
