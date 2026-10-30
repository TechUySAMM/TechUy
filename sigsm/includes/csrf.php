<?php
/* ============================================================
   includes/csrf.php
   Token CSRF para todos los formularios que modifican datos.
   ============================================================ */

require_once __DIR__ . '/sesion.php';

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

// Campo oculto listo para pegar dentro de un <form>
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

// Corta la ejecucion si el token no coincide
function csrf_verificar(): void
{
    $enviado = $_POST['csrf'] ?? '';
    if (!is_string($enviado) || !hash_equals(csrf_token(), $enviado)) {
        http_response_code(400);
        exit('Solicitud no válida (token CSRF). Vuelva atrás y recargue la página.');
    }
}
