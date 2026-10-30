<?php
/* ============================================================
   admin/usuarios.php
   Listado de usuarios activos (ABM de usuarios). Solo admin.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
requerir_admin();

$verBajas = ($_GET['bajas'] ?? '') === '1';

$stmt = $pdo->prepare(
    'SELECT u.id_usuario, u.nombre, u.cedula, u.email, u.activo, u.ultimo_acceso, r.nombre AS rol
     FROM usuario u JOIN rol r ON r.id_rol = u.id_rol
     WHERE u.activo = :activo
     ORDER BY u.nombre'
);
$stmt->execute([':activo' => $verBajas ? 0 : 1]);
$usuarios = $stmt->fetchAll();

$titulo  = 'Usuarios';
$seccion = 'usuarios';
require dirname(__DIR__) . '/includes/encabezado.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="h3 mb-0">Usuarios <?= $verBajas ? '<small class="text-muted">(dados de baja)</small>' : '' ?></h1>
    <div class="d-flex gap-2">
        <a href="usuarios.php<?= $verBajas ? '' : '?bajas=1' ?>" class="btn btn-outline-secondary"><?= $verBajas ? 'Ver activos' : 'Ver dados de baja' ?></a>
        <a href="alta_usuario.php" class="btn btn-primary">+ Nuevo usuario</a>
    </div>
</div>

<?= mensaje_ok([
    '1' => 'Usuario creado correctamente.',
    '2' => 'Usuario actualizado correctamente.',
    '3' => 'Usuario dado de baja correctamente.',
    '4' => 'Usuario reactivado correctamente.',
]) ?>
<?php if (($_GET['error'] ?? '') === 'propio'): ?>
    <div class="alert alert-warning">No puede darse de baja a sí mismo.</div>
<?php endif; ?>

<div class="table-responsive card">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead><tr><th>Nombre</th><th>Cédula</th><th>Email</th><th>Rol</th><th>Último acceso</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= e($u['nombre']) ?></td>
                <td><?= e($u['cedula']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><?= e($u['rol']) ?></td>
                <td><?= $u['ultimo_acceso'] ? e(date('d/m/Y H:i', strtotime($u['ultimo_acceso']))) : '<span class="text-muted">Nunca</span>' ?></td>
                <td class="text-nowrap">
                    <?php if ($u['activo']): ?>
                        <a href="editar_usuario.php?id=<?= (int) $u['id_usuario'] ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                        <?php if ((int) $u['id_usuario'] !== $_SESSION['id_usuario']): ?>
                            <form method="post" action="baja_usuario.php" class="d-inline"
                                  data-confirmar="¿Dar de baja a <?= e($u['nombre']) ?>? No podrá volver a ingresar al sistema.">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="id" value="<?= (int) $u['id_usuario'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Baja</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <form method="post" action="baja_usuario.php" class="d-inline" data-confirmar="¿Reactivar a <?= e($u['nombre']) ?>?">
                            <?= csrf_campo() ?>
                            <input type="hidden" name="id" value="<?= (int) $u['id_usuario'] ?>">
                            <input type="hidden" name="reactivar" value="1">
                            <button type="submit" class="btn btn-outline-primary btn-sm">Reactivar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$usuarios): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No hay usuarios en esta lista.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require dirname(__DIR__) . '/includes/pie.php'; ?>
