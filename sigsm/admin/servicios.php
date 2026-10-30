<?php
/* ============================================================
   admin/servicios.php
   Listado de servicios (especialidades) con su cantidad de
   documentos y encuestas. Solo administrador.
   Si un servicio se da de baja, sus documentos y encuestas
   dejan de mostrarse en el portal publico (no se borran).
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
requerir_admin();

$servicios = $pdo->query(
    'SELECT e.id_especialidad, e.nombre, e.descripcion, e.activo,
            (SELECT COUNT(*) FROM documento d WHERE d.id_especialidad = e.id_especialidad AND d.activo = 1) AS documentos,
            (SELECT COUNT(*) FROM encuesta en WHERE en.id_especialidad = e.id_especialidad AND en.activo = 1) AS encuestas
     FROM especialidad e
     ORDER BY e.activo DESC, e.nombre'
)->fetchAll();

$titulo  = 'Servicios';
$seccion = 'servicios';
require dirname(__DIR__) . '/includes/encabezado.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="h3 mb-0">Servicios</h1>
    <a href="alta_servicio.php" class="btn btn-primary">+ Nuevo servicio</a>
</div>

<?= mensaje_ok([
    '1' => 'Servicio creado correctamente.',
    '2' => 'Servicio actualizado correctamente.',
    '3' => 'Servicio dado de baja. Sus documentos y encuestas ya no se muestran al público.',
    '4' => 'Servicio reactivado.',
]) ?>

<div class="table-responsive card">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead><tr><th>Nombre</th><th>Descripción</th><th>Documentos</th><th>Encuestas</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($servicios as $s): ?>
            <tr class="<?= $s['activo'] ? '' : 'table-secondary' ?>">
                <td><?= e($s['nombre']) ?></td>
                <td><small><?= e($s['descripcion']) ?></small></td>
                <td><?= (int) $s['documentos'] ?></td>
                <td><?= (int) $s['encuestas'] ?></td>
                <td><?= $s['activo'] ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-secondary">De baja</span>' ?></td>
                <td class="text-nowrap">
                    <?php if ($s['activo']): ?>
                        <a href="editar_servicio.php?id=<?= (int) $s['id_especialidad'] ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                    <?php endif; ?>
                    <form method="post" action="baja_servicio.php" class="d-inline"
                          data-confirmar="<?= $s['activo']
                              ? '¿Dar de baja el servicio ' . e($s['nombre']) . '? Sus ' . (int) $s['documentos'] . ' documento(s) y ' . (int) $s['encuestas'] . ' encuesta(s) dejarán de verse en el portal.'
                              : '¿Reactivar el servicio ' . e($s['nombre']) . '?' ?>">
                        <?= csrf_campo() ?>
                        <input type="hidden" name="id" value="<?= (int) $s['id_especialidad'] ?>">
                        <?php if ($s['activo']): ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm">Baja</button>
                        <?php else: ?>
                            <input type="hidden" name="reactivar" value="1">
                            <button type="submit" class="btn btn-outline-primary btn-sm">Reactivar</button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require dirname(__DIR__) . '/includes/pie.php'; ?>
