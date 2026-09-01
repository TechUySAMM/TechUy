"use strict";

/* ============================================================
   js/encuesta.js
   Logica de la pantalla de encuesta de satisfaccion (S.I.G.S.M.)

   1) Adapta la pantalla segun el parametro ?tipo= que llega en la URL
      (lo arma documento.html: general / servicio / trasplante)
   2) Valida el formulario antes de "enviarlo" (campos obligatorios)
   Convenciones de catedra: const por defecto, === para comparar,
   patron guardian (if falla -> mostrarError -> return).
   ============================================================ */

// 1) Leer el parametro ?tipo= de la URL
const parametros    = new URLSearchParams(window.location.search);
const tipoEncuesta  = parametros.get("tipo");

// 2) Referencias a los elementos del HTML (const: no van a cambiar)
const tituloEncuesta  = document.getElementById("tituloEncuesta");
const grupoServicio   = document.getElementById("grupoServicio");
const selectServicio  = document.getElementById("servicio");
const avisoTrasplante = document.getElementById("avisoTrasplante");
const mensajeEncuesta = document.getElementById("mensajeEncuesta");
const selectTrato     = document.getElementById("trato");
const selectTiempos   = document.getElementById("tiempos");
const btnEnviar       = document.getElementById("btnEnviar");

// 3) Adaptar la pantalla segun el tipo de encuesta
function configurarSegunTipo() {
    if (tipoEncuesta === "trasplante") {
        tituloEncuesta.innerText = "Encuesta para pacientes trasplantados";
        selectServicio.value = "Centro de Trasplante";
        selectServicio.disabled = true; // ya se sabe el servicio, no se pide
        avisoTrasplante.classList.remove("d-none");
    } else if (tipoEncuesta === "general") {
        tituloEncuesta.innerText = "Encuesta de satisfaccion general";
        grupoServicio.classList.add("d-none"); // aca no corresponde elegir servicio
        avisoTrasplante.classList.add("d-none");
    } else {
        // "servicio" o sin parametro: comportamiento por defecto
        tituloEncuesta.innerText = "Encuesta de satisfaccion";
        avisoTrasplante.classList.add("d-none");
    }
}

configurarSegunTipo();

/* ============================================================
   4) Validacion del formulario (campos obligatorios)
      Patron guardian: cada "if" revisa una sola cosa, muestra
      el error con mostrarError() y corta con return.
   ============================================================ */
function mostrarError(texto) {
    mensajeEncuesta.innerText = texto;
    mensajeEncuesta.classList.add("text-danger");
    mensajeEncuesta.classList.remove("text-success");
}

function mostrarExito(texto) {
    mensajeEncuesta.innerText = texto;
    mensajeEncuesta.classList.add("text-success");
    mensajeEncuesta.classList.remove("text-danger");
}

function enviarEncuesta() {
    // Guardian 1: servicio obligatorio, salvo que el bloque este
    // oculto (tipo=general) o ya venga precargado (tipo=trasplante)
    const servicioOculto = grupoServicio.classList.contains("d-none");
    if (!servicioOculto && !selectServicio.disabled && selectServicio.value === "") {
        mostrarError("Seleccione el servicio donde recibio asistencia.");
        selectServicio.focus();
        return;
    }

    // Guardian 2: nivel de satisfaccion (grupo de radios)
    const satisfaccionMarcada = document.querySelector('input[name="satisfaccion"]:checked');
    if (!satisfaccionMarcada) {
        mostrarError("Seleccione un nivel de satisfaccion general.");
        return;
    }

    // Guardian 3: trato del personal
    if (selectTrato.value === "") {
        mostrarError("Indique si el trato del personal fue adecuado.");
        selectTrato.focus();
        return;
    }

    // Guardian 4: tiempos de espera
    if (selectTiempos.value === "") {
        mostrarError("Indique si los tiempos de espera fueron razonables.");
        selectTiempos.focus();
        return;
    }

    // Si llegamos hasta aca, el formulario esta completo
    mostrarExito("Gracias, la encuesta fue enviada correctamente.");
    btnEnviar.disabled = true;
}
