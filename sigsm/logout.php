<?php
/* ============================================================
   logout.php
   Cierra la sesion. Solo por POST y con token CSRF, para que un
   enlace externo no pueda cerrar la sesion del usuario.
   ============================================================ */

require_once __DIR__ . '/includes/inicio.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('/admin/panel.php');
}
csrf_verificar();

if (usuario_logueado()) {
    auditar($pdo, 'logout', 'usuario', $_SESSION['id_usuario'], 'Cierre de sesión');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

redirigir('/login.php?ok=salida');
