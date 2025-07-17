const container = document.querySelector(".container");
const btnSignIn = document.getElementById("btn-sign-in");
const btnSignUp = document.getElementById("btn-sign-up");

btnSignIn.addEventListener("click",()=>{
    container.classList.remove("toggle");
});
btnSignUp.addEventListener("click",()=>{
    container.classList.add("toggle");
});

document.addEventListener("DOMContentLoaded", function () {
  const validaciones = {
    usuario: /^([a-zA-Z0-9_.+-]{4,20}|[^\s@]+@[^\s@]+\.[^\s@]{2,})$/, // Acepta usuario o correo
    password: /^.{6,}$/,
    nombre: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{2,40}$/,
    correo: /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/
  };

  const mensajesError = {
    usuario: "Ingrese un usuario (4-20 caracteres) o un correo válido.",
    password: "Mínimo 6 caracteres.",
    nombre: "Solo letras y espacios (mínimo 2).",
    correo: "Ingrese un correo electrónico válido."
  };

  function validarCampo(nombreCampo, valor) {
    const regex = validaciones[nombreCampo];
    return regex ? regex.test(valor.trim()) : true;
  }

  function agregarValidacionEnVivo(formulario) {
    const campos = formulario.querySelectorAll('.container-input input');

    campos.forEach(input => {
      const nombreCampo = input.name;
      const icon = input.previousElementSibling;

      // Crea el contenedor de error si no existe
      let errorDiv = input.parentElement.querySelector('.error-message');
      if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.classList.add('error-message');
        input.parentElement.appendChild(errorDiv);
      }

      input.addEventListener('input', () => {
        const valor = input.value;
        const esValido = validarCampo(nombreCampo, valor);

        if (esValido) {
          input.classList.remove('input-error');
          icon?.classList.remove('error-icon');
          errorDiv.textContent = "";
        } else {
          input.classList.add('input-error');
          icon?.classList.add('error-icon');
          errorDiv.textContent = mensajesError[nombreCampo] || "Campo inválido.";
        }
      });
    });
  }

  // Validación al enviar formularios
  document.querySelectorAll("form").forEach(form => {
    form.addEventListener("submit", function (e) {
      let errores = false;
      const inputs = form.querySelectorAll(".container-input input");

      inputs.forEach(input => {
        const nombreCampo = input.name;
        const valor = input.value;
        const icon = input.previousElementSibling;
        let errorDiv = input.parentElement.querySelector(".error-message");

        if (!errorDiv) {
          errorDiv = document.createElement("div");
          errorDiv.classList.add("error-message");
          input.parentElement.appendChild(errorDiv);
        }

        if (!validarCampo(nombreCampo, valor)) {
          input.classList.add("input-error");
          icon?.classList.add("error-icon");
          errorDiv.textContent = mensajesError[nombreCampo] || "Campo inválido.";
          errores = true;
        } else {
          input.classList.remove("input-error");
          icon?.classList.remove("error-icon");
          errorDiv.textContent = "";
        }
      });

      if (errores) {
        e.preventDefault();
      }
    });

    agregarValidacionEnVivo(form);
  });
});
