<?php
/* ============================================================
   includes/sesion.php
   Manejo de sesiones PHP y control de permisos por rol.
   Cada pagina privada llama a requerir_login() o
   requerir_admin() ANTES de procesar nada, asi aunque alguien
   escriba la URL directamente no puede entrar.
   ============================================================ */

require_once dirname(__DIR__) . '/config/config.php';

const ROL_ADMIN   = 1;
const ROL_USUARIO = 2;

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,     // la cookie no se puede leer desde JavaScript
        'samesite' => 'Strict',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_name('SIGSMSESION');
    session_start();
}

function usuario_logueado(): bool
{
    return isset($_SESSION['id_usuario']);
}

function es_admin(): bool
{
    return ($_SESSION['id_rol'] ?? 0) === ROL_ADMIN;
}

function requerir_login(): void
{
    if (!usuario_logueado()) {
        header('Location: ' . URL_BASE . '/login.php?error=sesion');
        exit;
    }
}

function requerir_admin(): void
{
    requerir_login();
    if (!es_admin()) {
        http_response_code(403);
        header('Location: ' . URL_BASE . '/admin/panel.php?error=permiso');
        exit;
    }
}
