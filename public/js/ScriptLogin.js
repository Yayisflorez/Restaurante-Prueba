document.addEventListener('DOMContentLoaded', function() {
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
      // Limpiar errores previos
      const errorFields = ['emailError', 'passwordError'];
      errorFields.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
          el.textContent = '';
          el.classList.add('hidden');
        }
      });

      let valid = true;
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;

      if (!email) {
        document.getElementById('emailError').textContent = 'El email es obligatorio';
        document.getElementById('emailError').classList.remove('hidden');
        valid = false;
      } else if (!/^\S+@\S+\.\S+$/.test(email)) {
        document.getElementById('emailError').textContent = 'Email no válido';
        document.getElementById('emailError').classList.remove('hidden');
        valid = false;
      }
      
      if (!password) {
        document.getElementById('passwordError').textContent = 'La contraseña es obligatoria';
        document.getElementById('passwordError').classList.remove('hidden');
        valid = false;
      } else if (password.length < 8) {
        document.getElementById('passwordError').textContent = 'Mínimo 8 caracteres';
        document.getElementById('passwordError').classList.remove('hidden');
        valid = false;
      }
      
      if (!valid) {
        e.preventDefault();
      } else {
        if (typeof mostrarCarga === 'function') mostrarCarga('Iniciando sesión...'); else Swal.fire({ title: 'Iniciando sesión...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
      }
    });
  }
});
