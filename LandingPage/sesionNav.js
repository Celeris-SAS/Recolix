document.addEventListener('DOMContentLoaded', async () => {
  const link = document.getElementById('loginNavLink');
  if (!link) return;

  try {
    const resp = await fetch('/recolix/API_Usuarios/sesion.php');
    const data = await resp.json();

    if (data.loggedIn) {
      link.href = '/recolix/API_Usuarios/vecino.php';
      link.title = 'Mi perfil';
      link.classList.remove('btn', 'btn-outline', 'btn-sm');
      link.classList.add('btn-user');
      link.innerHTML = '<img src="/recolix/API_Usuarios/Vista/img/user.svg" alt="Mi perfil" class="nav-user-icon">';
    }
  } catch (e) {
    // Sin conexión con la API: se deja el link de "Iniciar Sesión" por defecto.
  }
});
