"use strict";

/* ============================================================
   public/js/validaciones.js
   Validaciones del lado del cliente (JavaScript) de S.I.G.S.M.
   Pantallas que valida:
     1) Login                (#formLogin)
     2) Alta/edicion de documento (#formDocumento)
     3) Alta/edicion de usuario   (#formUsuario)
     4) Respuesta de encuesta     (#formEncuesta)
     5) Cambio de contrasena      (#formContrasena)

   Convenciones de catedra: const por defecto, === para comparar,
   patron guardian (si falla -> marcarError -> return false).
   IMPORTANTE: esto solo mejora la experiencia del usuario. PHP
   vuelve a validar todo del lado del servidor.
   ============================================================ */

// Patrones (Guia de expresiones regulares)
const PATRON_CEDULA = /^\d{7,8}$/;
const PATRON_EMAIL  = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
const PDF_MAX_BYTES = 5 * 1024 * 1024;

/* ---------- Helpers de mensajes ---------- */

// Marca el campo en rojo y muestra el texto debajo
function marcarError(campo, texto) {
    campo.classList.add("is-invalid");
    let aviso = campo.parentElement.querySelector(".invalid-feedback");
    if (!aviso) {
        aviso = document.createElement("div");
        aviso.className = "invalid-feedback";
        campo.parentElement.appendChild(aviso);
    }
    aviso.innerText = texto;
    campo.focus();
    return false;
}

function limpiarErrores(formulario) {
    formulario.querySelectorAll(".is-invalid").forEach(function (campo) {
        campo.classList.remove("is-invalid");
    });
}

// Limpia el error de un campo en cuanto el usuario lo corrige
function limpiarAlEscribir(formulario) {
    formulario.querySelectorAll("input, select, textarea").forEach(function (campo) {
        campo.addEventListener("input", function () {
            campo.classList.remove("is-invalid");
        });
    });
}

// Fecha real AAAA-MM-DD (2026-02-30 no existe)
function fechaReal(texto) {
    const partes = texto.split("-");
    if (partes.length !== 3) {
        return false;
    }
    const anio = Number(partes[0]);
    const mes  = Number(partes[1]);
    const dia  = Number(partes[2]);
    const fecha = new Date(anio, mes - 1, dia);
    return fecha.getFullYear() === anio && fecha.getMonth() === mes - 1 && fecha.getDate() === dia;
}

/* ============================================================
   1) LOGIN
   ============================================================ */
function validarLogin(formulario) {
    const cedula     = formulario.querySelector("#cedula");
    const contrasena = formulario.querySelector("#contrasena");
    const mensaje    = document.getElementById("mensajeLogin");

    cedula.classList.remove("campo-error");
    contrasena.classList.remove("campo-error");

    function errorLogin(campo, texto) {
        campo.classList.add("campo-error");
        mensaje.innerText = texto;
        mensaje.className = "login-mensaje error";
        campo.focus();
        return false;
    }

    // Guardian 1: cedula obligatoria
    if (cedula.value.trim() === "") {
        return errorLogin(cedula, "Ingrese su cédula.");
    }
    // Guardian 2: formato de cedula
    if (!PATRON_CEDULA.test(cedula.value.trim())) {
        return errorLogin(cedula, "La cédula debe tener 7 u 8 dígitos, sin puntos ni guion. Ej: 12345678");
    }
    // Guardian 3: contrasena obligatoria
    if (contrasena.value.trim() === "") {
        return errorLogin(contrasena, "Ingrese su contraseña.");
    }

    mensaje.innerText = "Verificando datos...";
    mensaje.className = "login-mensaje exito";
    return true;
}

/* ============================================================
   2) DOCUMENTO (alta y edicion)
   ============================================================ */
function validarDocumento(formulario) {
    limpiarErrores(formulario);
    const titulo   = formulario.querySelector("#titulo");
    const tipo     = formulario.querySelector("#tipo");
    const servicio = formulario.querySelector("#id_especialidad");
    const fecha    = formulario.querySelector("#fecha_emision");
    const archivo  = formulario.querySelector("#archivo");
    // En la edicion el PDF es opcional (solo si se quiere reemplazar)
    const pdfObligatorio = formulario.dataset.pdfObligatorio === "1";

    if (titulo.value.trim() === "") {
        return marcarError(titulo, "El título es obligatorio.");
    }
    if (titulo.value.trim().length > 150) {
        return marcarError(titulo, "El título no puede superar los 150 caracteres.");
    }
    if (tipo.value !== "indicacion" && tipo.value !== "informacion") {
        return marcarError(tipo, "Seleccione el tipo de documento.");
    }
    if (servicio.value === "") {
        return marcarError(servicio, "Seleccione el servicio.");
    }
    if (!fechaReal(fecha.value)) {
        return marcarError(fecha, "Ingrese una fecha de emisión válida.");
    }
    if (pdfObligatorio && archivo.files.length === 0) {
        return marcarError(archivo, "Seleccione el archivo PDF.");
    }
    if (archivo.files.length > 0) {
        const pdf = archivo.files[0];
        if (!pdf.name.toLowerCase().endsWith(".pdf")) {
            return marcarError(archivo, "El archivo debe ser un PDF.");
        }
        if (pdf.size > PDF_MAX_BYTES) {
            return marcarError(archivo, "El PDF no puede superar los 5 MB.");
        }
    }
    return true;
}

/* ============================================================
   3) USUARIO (alta y edicion)
   ============================================================ */
function validarUsuario(formulario) {
    limpiarErrores(formulario);
    const nombre     = formulario.querySelector("#nombre");
    const cedula     = formulario.querySelector("#cedula");
    const email      = formulario.querySelector("#email");
    const rol        = formulario.querySelector("#id_rol");
    const contrasena = formulario.querySelector("#contrasena");
    const repetir    = formulario.querySelector("#repetir");
    const esAlta     = formulario.dataset.alta === "1";

    if (nombre.value.trim().length < 3) {
        return marcarError(nombre, "Ingrese el nombre completo (mínimo 3 caracteres).");
    }
    if (!PATRON_CEDULA.test(cedula.value.trim())) {
        return marcarError(cedula, "La cédula debe tener 7 u 8 dígitos, sin puntos ni guion.");
    }
    if (!PATRON_EMAIL.test(email.value.trim())) {
        return marcarError(email, "Ingrese un email con formato válido.");
    }
    if (rol.value === "") {
        return marcarError(rol, "Seleccione un rol.");
    }
    // En la edicion la contrasena solo se valida si se escribio algo
    if (esAlta || contrasena.value !== "") {
        if (contrasena.value.length < 8) {
            return marcarError(contrasena, "La contraseña debe tener al menos 8 caracteres.");
        }
        if (contrasena.value !== repetir.value) {
            return marcarError(repetir, "Las contraseñas no coinciden.");
        }
    }
    return true;
}

/* ============================================================
   4) ENCUESTA (respuesta del paciente)
   ============================================================ */
function validarEncuesta(formulario) {
    limpiarErrores(formulario);
    const mensaje = document.getElementById("mensajeEncuesta");
    const bloques = formulario.querySelectorAll("[data-pregunta]");

    for (const bloque of bloques) {
        if (bloque.dataset.obligatoria !== "1") {
            continue;
        }
        const tipo = bloque.dataset.tipo;
        if (tipo === "texto_libre") {
            const texto = bloque.querySelector("textarea");
            if (texto.value.trim() === "") {
                return marcarError(texto, "Esta pregunta es obligatoria.");
            }
        } else if (!bloque.querySelector("input[type=radio]:checked")) {
            mensaje.innerText = "Responda la pregunta: " + bloque.dataset.texto;
            mensaje.className = "fw-bold text-danger";
            bloque.scrollIntoView({ behavior: "smooth", block: "center" });
            return false;
        }
    }
    const servicio = formulario.querySelector("#id_especialidad");
    if (servicio && servicio.value === "") {
        return marcarError(servicio, "Seleccione el servicio donde recibió asistencia.");
    }
    // Evita doble clic mientras se envia
    formulario.querySelector("button[type=submit]").disabled = true;
    return true;
}

/* ============================================================
   5) CAMBIO DE CONTRASENA
   ============================================================ */
function validarContrasena(formulario) {
    limpiarErrores(formulario);
    const actual  = formulario.querySelector("#actual");
    const nueva   = formulario.querySelector("#nueva");
    const repetir = formulario.querySelector("#repetir");

    if (actual.value === "") {
        return marcarError(actual, "Ingrese su contraseña actual.");
    }
    if (nueva.value.length < 8) {
        return marcarError(nueva, "La nueva contraseña debe tener al menos 8 caracteres.");
    }
    if (nueva.value === actual.value) {
        return marcarError(nueva, "La nueva contraseña debe ser distinta de la actual.");
    }
    if (nueva.value !== repetir.value) {
        return marcarError(repetir, "Las contraseñas no coinciden.");
    }
    return true;
}

/* ---------- Conexion de cada formulario con su validacion ---------- */
const VALIDADORES = {
    formLogin: validarLogin,
    formDocumento: validarDocumento,
    formUsuario: validarUsuario,
    formEncuesta: validarEncuesta,
    formContrasena: validarContrasena,
};

Object.keys(VALIDADORES).forEach(function (id) {
    const formulario = document.getElementById(id);
    if (!formulario) {
        return;
    }
    limpiarAlEscribir(formulario);
    formulario.addEventListener("submit", function (evento) {
        if (!VALIDADORES[id](formulario)) {
            evento.preventDefault();
        }
    });
});
