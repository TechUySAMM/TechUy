<?php
/* ============================================================
   admin/editar_usuario.php
   Busca por id, precarga, valida y hace UPDATE. La contrasena
   solo se cambia si se completa el campo.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
require_once dirname(__DIR__) . '/includes/validar_usuario.php';
requerir_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$stmt = $pdo->prepare('SELECT id_usuario, nombre, cedula, email, id_rol FROM usuario WHERE id_usuario = :id AND activo = 1');
$stmt->execute([':id' => $id]);
$usuario = $stmt->fetch();
if (!$usuario) {
    redirigir('/admin/usuarios.php');
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $usuario = array_merge($usuario, leer_usuario_post($pdo, false, $errores));

    // Un administrador no puede quitarse a si mismo el rol (quedaria sin acceso)
    if ($id === $_SESSION['id_usuario'] && $usuario['id_rol'] !== ROL_ADMIN) {
        $errores[] = 'No puede quitarse a sí mismo el rol de Administrador.';
    }

    if (empty($errores)) {
        $sql = 'UPDATE usuario SET nombre = :nombre, cedula = :cedula, email = :email, id_rol = :rol';
        $params = [
            ':nombre' => $usuario['nombre'],
            ':cedula' => $usuario['cedula'],
            ':email'  => $usuario['email'],
            ':rol'    => $usuario['id_rol'],
            ':id'     => $id,
        ];
        if ($usuario['contrasena'] !== '') {
            $sql .= ', contrasena_hash = :hash';
            $params[':hash'] = password_hash($usuario['contrasena'], PASSWORD_DEFAULT);
        }
        $sql .= ' WHERE id_usuario = :id AND activo = 1';

        try {
            $pdo->prepare($sql)->execute($params);
            auditar($pdo, 'edicion', 'usuario', $id, 'Edición de usuario ' . $usuario['nombre'] . ($usuario['contrasena'] !== '' ? ' (con cambio de contraseña)' : ''));
            redirigir('/admin/usuarios.php?ok=2');
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $errores[] = 'Ya existe otro usuario con esa cédula o ese email.';
            } else {
                throw $ex;
            }
        }
    }
}

$roles  = $pdo->query('SELECT id_rol, nombre FROM rol ORDER BY id_rol')->fetchAll();
$esAlta = false;

$titulo  = 'Editar usuario';
$seccion = 'usuarios';
$scripts = ['validaciones.js'];
require dirname(__DIR__) . '/includes/encabezado.php';
?>

<h1 class="h3 mb-3">Editar usuario</h1>
<?= lista_errores($errores) ?>
<?php require dirname(__DIR__) . '/includes/form_usuario.php'; ?>

<?php require dirname(__DIR__) . '/includes/pie.php'; ?>
