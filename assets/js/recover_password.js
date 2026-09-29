document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-password-toggle]').forEach(toggle => {
        const input = document.getElementById(toggle.dataset.passwordToggle);
        if (!input) {
            return;
        }

        toggle.addEventListener('click', () => {
            const mostrar = input.type === 'password';
            input.type = mostrar ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
            toggle.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    });

    const resetForm = document.getElementById('reset-form');
    const nuevaContrasena = document.getElementById('nueva_contrasena');
    const confirmarContrasena = document.getElementById('confirmar_contrasena');

    const validarConfirmacion = () => {
        if (!nuevaContrasena || !confirmarContrasena) {
            return;
        }

        const noCoinciden = confirmarContrasena.value !== ''
            && nuevaContrasena.value !== confirmarContrasena.value;
        confirmarContrasena.setCustomValidity(noCoinciden ? 'Las contraseñas no coinciden.' : '');
    };

    nuevaContrasena?.addEventListener('input', validarConfirmacion);
    confirmarContrasena?.addEventListener('input', validarConfirmacion);
    resetForm?.addEventListener('submit', validarConfirmacion);
});
