<?php
/* ============================================================
   includes/validar_usuario.php  (procesamiento)
   Validaciones del servidor para alta y edicion de usuarios.
   ============================================================ */

function leer_usuario_post(PDO $pdo, bool $esAlta, array &$errores): array
{
    $u = [
        'nombre'     => trim($_POST['nombre'] ?? ''),
        'cedula'     => trim($_POST['cedula'] ?? ''),
        'email'      => trim($_POST['email'] ?? ''),
        'id_rol'     => filter_input(INPUT_POST, 'id_rol', FILTER_VALIDATE_INT) ?: 0,
        'contrasena' => $_POST['contrasena'] ?? '',
    ];

    if (mb_strlen($u['nombre']) < 3 || mb_strlen($u['nombre']) > 100) {
        $errores[] = 'El nombre debe tener entre 3 y 100 caracteres.';
    }
    if (!cedula_valida($u['cedula'])) {
        $errores[] = 'La cédula debe tener 7 u 8 dígitos, sin puntos ni guion.';
    }
    if (!filter_var($u['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Error de formato en el email.';
    }
    if ($u['id_rol'] !== ROL_ADMIN && $u['id_rol'] !== ROL_USUARIO) {
        $errores[] = 'Debe seleccionar un rol válido.';
    }
    if ($esAlta || $u['contrasena'] !== '') {
        if (strlen($u['contrasena']) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif ($u['contrasena'] !== ($_POST['repetir'] ?? '')) {
            $errores[] = 'Las contraseñas no coinciden.';
        }
    }
    return $u;
}
