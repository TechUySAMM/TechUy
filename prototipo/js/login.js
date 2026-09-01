"use strict";

/* ============================================================
   js/login.js
   Validaciones de la pantalla de ingreso de personal (S.I.G.S.M.)

   Esto SOLO valida el formato de los datos antes de "enviarlos".
   La verificacion real del usuario y la contraseña contra la
   base de datos la hace el backend en PHP, mas adelante en el
   proyecto (esta pantalla todavia es un prototipo sin logica).

   Convenciones de catedra: const por defecto, === para comparar,
   patron guardian (if falla -> mostrarError -> return).
   ============================================================ */

// 1) Referencias a los elementos del HTML
const inputCedula     = document.getElementById("cedula");
const inputContrasena = document.getElementById("contrasena");
const mensajeLogin    = document.getElementById("mensajeLogin");
const btnIngresar     = document.getElementById("btnIngresar");

// 2) Patron de cedula, tomado de la Guia de expresiones regulares:
//    solo digitos, 7 u 8 (hay cedulas de las dos longitudes)
const patronCedula = /^\d{7,8}$/;

// 3) Mensajes de error / exito (mismo patron que en encuesta.js)
function mostrarError(texto) {
    mensajeLogin.innerText = texto;
    mensajeLogin.classList.add("text-danger");
    mensajeLogin.classList.remove("text-success");
}

function mostrarExito(texto) {
    mensajeLogin.innerText = texto;
    mensajeLogin.classList.add("text-success");
    mensajeLogin.classList.remove("text-danger");
}

// 4) Validacion del formulario (patron guardian)
function validarLogin() {
    const cedula     = inputCedula.value.trim();
    const contrasena = inputContrasena.value;

    // Guardian 1: cedula obligatoria
    if (cedula === "") {
        mostrarError("Ingrese su usuario o cedula.");
        inputCedula.focus();
        return;
    }

    // Guardian 2: formato de cedula (solo digitos, 7 u 8, sin puntos ni guion)
    if (!patronCedula.test(cedula)) {
        mostrarError("La cedula debe tener 7 u 8 digitos, sin puntos ni guion. Ej: 12345678");
        inputCedula.focus();
        return;
    }

    // Guardian 3: contraseña obligatoria
    if (contrasena.trim() === "") {
        mostrarError("Ingrese su contraseña.");
        inputContrasena.focus();
        return;
    }

    // Si llegamos hasta aca, el formato es correcto: recien ahi se
    // avanza al panel (la validacion contra la base de datos la
    // hace el backend, no esta pantalla)
    mostrarExito("Formato correcto. Ingresando...");
    window.location.href = "panel.html";
}
