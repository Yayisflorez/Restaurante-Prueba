document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function (e) {
      // Limpiar errores previos
        const errorFields = [
            'tipoDocError', 'numDocError', 'nameError', 'lastnameError',
            'telefonoError', 'emailError', 'passwordError', 'passwordConfirmError'
        ];
        errorFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                    el.textContent = '';
                el.classList.add('hidden');
            }
        });

        let valid = true;

      // Tipo de documento
        const tipoDoc = document.getElementById('tipo_documento').value;
        if (!tipoDoc) {
            document.getElementById('tipoDocError').textContent = 'El tipo de documento es obligatorio';
            document.getElementById('tipoDocError').classList.remove('hidden');
            valid = false;
        }

      // Número de documento
        const numDoc = document.getElementById('numero_documento').value.trim();
        if (!numDoc) {
            document.getElementById('numDocError').textContent = 'El número de documento es obligatorio';
            document.getElementById('numDocError').classList.remove('hidden');
            valid = false;
        }

      // Nombre
        const name = document.getElementById('name').value.trim();
        if (!name) {
            document.getElementById('nameError').textContent = 'El nombre es obligatorio';
            document.getElementById('nameError').classList.remove('hidden');
            valid = false;
        }

      // Apellido
        const lastname = document.getElementById('lastname').value.trim();
        if (!lastname) {
            document.getElementById('lastnameError').textContent = 'El apellido es obligatorio';
            document.getElementById('lastnameError').classList.remove('hidden');
            valid = false;
        }

      // Teléfono
        const telefono = document.getElementById('telefono').value.trim();
        if (!telefono) {
            document.getElementById('telefonoError').textContent = 'El teléfono es obligatorio';
            document.getElementById('telefonoError').classList.remove('hidden');
            valid = false;
        }

      // Email
        const email = document.getElementById('email').value.trim();
        if (!email) {
            document.getElementById('emailError').textContent = 'El email es obligatorio';
            document.getElementById('emailError').classList.remove('hidden');
            valid = false;
        } else if (!/^\S+@\S+\.\S+$/.test(email)) {
            document.getElementById('emailError').textContent = 'Email no válido';
            document.getElementById('emailError').classList.remove('hidden');
            valid = false;
        }

      // Contraseña
        const password = document.getElementById('password').value;
        if (!password) {
            document.getElementById('passwordError').textContent = 'La contraseña es obligatoria';
            document.getElementById('passwordError').classList.remove('hidden');
            valid = false;
        } else if (password.length < 8) {
            document.getElementById('passwordError').textContent = 'Debe tener al menos 8 caracteres, 1 mayúscula y 1 número.';
            document.getElementById('passwordError').classList.remove('hidden');
            valid = false;
        } else if (!/[A-Z]/.test(password)) {
            document.getElementById('passwordError').textContent = 'Debe tener al menos 8 caracteres, 1 mayúscula y 1 número.';
            document.getElementById('passwordError').classList.remove('hidden');
            valid = false;
        } else if (!/[0-9]/.test(password)) {
            document.getElementById('passwordError').textContent = 'Debe tener al menos 8 caracteres, 1 mayúscula y 1 número.';
            document.getElementById('passwordError').classList.remove('hidden');
            valid = false;
        }

      // Confirmar contraseña
        const passwordConfirm = document.getElementById('password_confirm').value;
        if (!passwordConfirm) {
            document.getElementById('passwordConfirmError').textContent = 'Confirma la contraseña';
            document.getElementById('passwordConfirmError').classList.remove('hidden');
            valid = false;
        } else if (password !== passwordConfirm) {
            document.getElementById('passwordConfirmError').textContent = 'Las contraseñas no coinciden';
            document.getElementById('passwordConfirmError').classList.remove('hidden');
            valid = false;
        }

        if (!valid) {
            e.preventDefault();
        }
    });
    }
});
