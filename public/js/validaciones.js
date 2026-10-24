// public/js/validaciones.js
// Validaciones del lado del cliente exigidas en la 1ra entrega:
// - campos obligatorios
// - formatos (email)
// - reglas de contraseña: mínimo 8 caracteres, 1 mayúscula, 1 carácter especial, 1 número
//
// IMPORTANTE: esto NO sustituye la validación en servidor (Validator.php
// aplica las mismas reglas); es solo una primera barrera de UX.

document.addEventListener('DOMContentLoaded', function () {
  const REGEX_PASSWORD = /^(?=.*\p{Lu})(?=.*\p{N})(?=.*[^\p{L}\p{N}]).{8,}$/u;
  const REGEX_EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  function mostrarError(input, mensaje) {
    limpiarError(input);
    const small = document.createElement('small');
    small.className = 'field-error';
    small.style.color = '#dc2626';
    small.textContent = mensaje;
    input.insertAdjacentElement('afterend', small);
    input.style.borderColor = '#dc2626';
  }

  function limpiarError(input) {
    input.style.borderColor = '';
    const next = input.nextElementSibling;
    if (next && next.classList.contains('field-error')) {
      next.remove();
    }
  }

  function validarFormularioGenerico(form) {
    let valido = true;

    form.querySelectorAll('[required]').forEach(function (input) {
      limpiarError(input);
      if (!input.value || !input.value.trim()) {
        mostrarError(input, 'Este campo es obligatorio.');
        valido = false;
        return;
      }
      if (input.type === 'email' && !REGEX_EMAIL.test(input.value)) {
        mostrarError(input, 'Correo electrónico no válido.');
        valido = false;
      }
    });

    const passwordInput = form.querySelector('input[name="password"]');
    if (passwordInput && passwordInput.value) {
      if (!REGEX_PASSWORD.test(passwordInput.value)) {
        mostrarError(
          passwordInput,
          'Debe tener 8+ caracteres, una mayúscula, un número y un carácter especial.'
        );
        valido = false;
      }
    }

    return valido;
  }

  // Se aplica a todos los formularios del portal que se vayan agregando
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!validarFormularioGenerico(form)) {
        e.preventDefault();
      }
    });
  });

  // Confirmación antes de eliminar (requisito de "avisos" del proyecto)
  document.querySelectorAll('[data-confirm-eliminar]').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!confirm('¿Seguro que deseas eliminar este elemento?')) {
        e.preventDefault();
      }
    });
  });
});
