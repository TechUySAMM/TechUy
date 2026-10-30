<?php
/* ============================================================
   admin/cambiar_contrasena.php
   Cualquier usuario logueado cambia su propia contrasena.
   Se verifica la actual con password_verify().
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
requerir_login();

$errores = [];
$ok = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $actual  = $_POST['actual'] ?? '';
    $nueva   = $_POST['nueva'] ?? '';
    $repetir = $_POST['repetir'] ?? '';

    $stmt = $pdo->prepare('SELECT contrasena_hash FROM usuario WHERE id_usuario = :id AND activo = 1');
    $stmt->execute([':id' => $_SESSION['id_usuario']]);
    $hash = $stmt->fetchColumn();

    if (!$hash || !password_verify($actual, $hash)) {
        $errores[] = 'La contraseña actual no es correcta.';
    }
    if (strlen($nueva) < 8) {
        $errores[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
    }
    if ($nueva === $actual) {
        $errores[] = 'La nueva contraseña debe ser distinta de la actual.';
    }
    if ($nueva !== $repetir) {
        $errores[] = 'Las contraseñas nuevas no coinciden.';
    }

    if (empty($errores)) {
        $pdo->prepare('UPDATE usuario SET contrasena_hash = :h WHERE id_usuario = :id')
            ->execute([':h' => password_hash($nueva, PASSWORD_DEFAULT), ':id' => $_SESSION['id_usuario']]);
        auditar($pdo, 'cambio_contrasena', 'usuario', $_SESSION['id_usuario'], 'Cambio de contraseña propia');
        session_regenerate_id(true);
        $ok = true;
    }
}

$titulo  = 'Cambiar contraseña';
$seccion = '';
$scripts = ['validaciones.js'];
require dirname(__DIR__) . '/includes/encabezado.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-7 col-lg-5">
        <h1 class="h3 mb-3">Cambiar contraseña</h1>
        <?php if ($ok): ?><div class="alert alert-success">La contraseña se cambió correctamente.</div><?php endif; ?>
        <?= lista_errores($errores) ?>
        <form id="formContrasena" method="post" class="card card-body" novalidate>
            <?= csrf_campo() ?>
            <div class="mb-3">
                <label for="actual" class="form-label">Contraseña actual</label>
                <input type="password" id="actual" name="actual" class="form-control" autocomplete="current-password">
            </div>
            <div class="mb-3">
                <label for="nueva" class="form-label">Nueva contraseña</label>
                <input type="password" id="nueva" name="nueva" class="form-control" autocomplete="new-password">
                <div class="form-text">Mínimo 8 caracteres.</div>
            </div>
            <div class="mb-3">
                <label for="repetir" class="form-label">Repetir nueva contraseña</label>
                <input type="password" id="repetir" name="repetir" class="form-control" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary">Cambiar contraseña</button>
        </form>
    </div>
</div>

<?php require dirname(__DIR__) . '/includes/pie.php'; ?>
