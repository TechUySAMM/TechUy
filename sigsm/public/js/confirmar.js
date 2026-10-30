"use strict";

/* ============================================================
   public/js/confirmar.js
   Pide confirmacion antes de acciones delicadas (dar de baja,
   cerrar una encuesta, etc.). Cualquier <form> que tenga el
   atributo data-confirmar="mensaje" pasa por aca.
   La baja real la sigue haciendo PHP por POST con token CSRF.
   ============================================================ */

document.querySelectorAll("form[data-confirmar]").forEach(function (formulario) {
    formulario.addEventListener("submit", function (evento) {
        const mensaje = formulario.getAttribute("data-confirmar");
        if (!window.confirm(mensaje)) {
            evento.preventDefault();
        }
    });
});
