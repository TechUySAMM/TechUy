"use strict";

/* ============================================================
   public/js/encuesta_admin.js
   Constructor de encuestas: agrega filas de preguntas y valida
   que haya titulo y al menos una pregunta antes de enviar.
   ============================================================ */

const listaPreguntas = document.getElementById("listaPreguntas");
const btnAgregar     = document.getElementById("btnAgregarPregunta");
const formAlta       = document.getElementById("formAltaEncuesta");

// Clona la primera fila, la vacia y le pone indices nuevos
btnAgregar.addEventListener("click", function () {
    const filas = listaPreguntas.querySelectorAll(".fila-pregunta");
    const indice = filas.length;
    const nueva = filas[0].cloneNode(true);

    nueva.querySelectorAll("input, select, label").forEach(function (elemento) {
        ["name", "id", "for"].forEach(function (atributo) {
            const valor = elemento.getAttribute(atributo);
            if (valor) {
                elemento.setAttribute(atributo, valor.replace(/\[\d+\]/, "[" + indice + "]").replace(/^ob\d+$/, "ob" + indice));
            }
        });
        if (elemento.type === "text") {
            elemento.value = "";
        }
        if (elemento.type === "checkbox") {
            elemento.checked = true;
        }
    });
    listaPreguntas.appendChild(nueva);
    nueva.querySelector("input[type=text]").focus();
});

formAlta.addEventListener("submit", function (evento) {
    const titulo = document.getElementById("nombre");
    titulo.classList.remove("is-invalid");

    // Guardian 1: titulo
    if (titulo.value.trim().length < 3) {
        titulo.classList.add("is-invalid");
        titulo.focus();
        alert("El título de la encuesta debe tener al menos 3 caracteres.");
        evento.preventDefault();
        return;
    }
    // Guardian 2: al menos una pregunta con texto
    const conTexto = Array.from(listaPreguntas.querySelectorAll("input[type=text]"))
        .filter(function (campo) { return campo.value.trim() !== ""; });
    if (conTexto.length === 0) {
        alert("Agregue al menos una pregunta.");
        evento.preventDefault();
    }
});
