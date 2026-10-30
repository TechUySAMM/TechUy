<?php
/* ============================================================
   encuestas.php  (publico)
   Lista las encuestas publicadas. Buscar por titulo y filtrar
   por servicio. No requiere iniciar sesion.
   ============================================================ */

require_once __DIR__ . '/includes/inicio.php';

$busqueda = trim($_GET['q'] ?? '');
$servicio = filter_input(INPUT_GET, 'servicio', FILTER_VALIDATE_INT) ?: null;

$where  = "en.estado = 'publicada' AND en.activo = 1 AND (en.id_especialidad IS NULL OR es.activo = 1)";
$params = [];
if ($busqueda !== '') {
    $where .= ' AND en.nombre LIKE :q';
    $params[':q'] = '%' . $busqueda . '%';
}
if ($servicio) {
    // Las encuestas generales tambien aplican a cualquier servicio
    $where .= ' AND (en.id_especialidad = :s OR en.id_especialidad IS NULL)';
    $params[':s'] = $servicio;
}

$stmt = $pdo->prepare(
    "SELECT en.id_encuesta, en.nombre, en.descripcion, en.obligatoria, es.nombre AS servicio
     FROM encuesta en
     LEFT JOIN especialidad es ON es.id_especialidad = en.id_especialidad
     WHERE $where
     ORDER BY en.nombre"
);
$stmt->execute($params);
$encuestas = $stmt->fetchAll();
$servicios = especialidades_activas($pdo);

$titulo  = 'Encuestas de satisfacción';
$seccion = 'encuestas';
$publico = true;
require __DIR__ . '/includes/encabezado.php';
?>

<h1 class="h3 mb-3">Encuestas de satisfacción</h1>
<?= mensaje_ok(['1' => '¡Gracias! Su respuesta fue registrada de forma anónima.']) ?>

<form method="get" class="card card-body mb-4" role="search">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-6">
            <label for="q" class="form-label">Buscar por título</label>
            <input type="search" id="q" name="q" class="form-control" value="<?= e($busqueda) ?>">
        </div>
        <div class="col-12 col-md-4">
            <label for="servicio" class="form-label">Servicio</label>
            <select id="servicio" name="servicio" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($servicios as $s): ?>
                    <option value="<?= (int) $s['id_especialidad'] ?>" <?= $servicio === (int) $s['id_especialidad'] ? 'selected' : '' ?>><?= e($s['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-2 d-grid">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
    </div>
</form>

<?php if (!$encuestas): ?>
    <div class="alert alert-warning">No hay encuestas disponibles con esos filtros.</div>
<?php endif; ?>

<div class="list-group">
    <?php foreach ($encuestas as $en): ?>
        <a href="responder_encuesta.php?id=<?= (int) $en['id_encuesta'] ?>" class="list-group-item list-group-item-action p-3">
            <div class="d-flex justify-content-between flex-wrap gap-2">
                <strong><?= e($en['nombre']) ?></strong>
                <span>
                    <span class="badge text-bg-light"><?= e($en['servicio'] ?? 'General') ?></span>
                    <?php if ($en['obligatoria']): ?><span class="badge bg-warning text-dark">Obligatoria</span><?php endif; ?>
                </span>
            </div>
            <small class="text-muted"><?= e($en['descripcion']) ?></small>
        </a>
    <?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/pie.php'; ?>
