<?php
/* ============================================================
   admin/alta_usuario.php
   Alta de usuario: validacion + password_hash() + INSERT.
   Cedula o email repetidos -> error amigable (codigo 23000).
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
require_once dirname(__DIR__) . '/includes/validar_usuario.php';
requerir_admin();

$errores = [];
$usuario = ['nombre' => '', 'cedula' => '', 'email' => '', 'id_rol' => ROL_USUARIO];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $usuario = leer_usuario_post($pdo, true, $errores);

    if (empty($errores)) {
        $sql = 'INSERT INTO usuario (nombre, cedula, email, contrasena_hash, id_rol, activo)
                VALUES (:nombre, :cedula, :email, :hash, :rol, 1)';
        try {
            $pdo->prepare($sql)->execute([
                ':nombre' => $usuario['nombre'],
                ':cedula' => $usuario['cedula'],
                ':email'  => $usuario['email'],
                ':hash'   => password_hash($usuario['contrasena'], PASSWORD_DEFAULT),
                ':rol'    => $usuario['id_rol'],
            ]);
            auditar($pdo, 'alta', 'usuario', (int) $pdo->lastInsertId(), 'Alta de usuario ' . $usuario['nombre']);
            redirigir('/admin/usuarios.php?ok=1');
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $errores[] = 'Ya existe un usuario con esa cédula o ese email.';
            } else {
                throw $ex;
            }
        }
    }
}

$roles  = $pdo->query('SELECT id_rol, nombre FROM rol ORDER BY id_rol')->fetchAll();
$esAlta = true;

$titulo  = 'Nuevo usuario';
$seccion = 'usuarios';
$scripts = ['validaciones.js'];
require dirname(__DIR__) . '/includes/encabezado.php';
?>

<h1 class="h3 mb-3">Nuevo usuario</h1>
<?= lista_errores($errores) ?>
<?php require dirname(__DIR__) . '/includes/form_usuario.php'; ?>

<?php require dirname(__DIR__) . '/includes/pie.php'; ?>
