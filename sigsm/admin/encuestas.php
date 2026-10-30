<?php
/* ============================================================
   admin/encuestas.php
   Listado de encuestas con su estado y cantidad de respuestas.
   Acciones: ver resultados, publicar, cerrar. Solo admin.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
requerir_admin();

$busqueda = trim($_GET['q'] ?? '');
$servicio = filter_input(INPUT_GET, 'servicio', FILTER_VALIDATE_INT) ?: null;

$where  = 'en.activo = 1';
$params = [];
if ($busqueda !== '') {
    $where .= ' AND en.nombre LIKE :q';
    $params[':q'] = '%' . $busqueda . '%';
}
if ($servicio) {
    $where .= ' AND en.id_especialidad = :s';
    $params[':s'] = $servicio;
}

$stmt = $pdo->prepare(
    "SELECT en.id_encuesta, en.nombre, en.estado, en.obligatoria, en.fecha_creacion,
            es.nombre AS servicio, es.activo AS servicio_activo,
            (SELECT COUNT(*) FROM respuesta r WHERE r.id_encuesta = en.id_encuesta) AS respuestas,
            (SELECT COUNT(*) FROM pregunta p WHERE p.id_encuesta = en.id_encuesta) AS preguntas
     FROM encuesta en
     LEFT JOIN especialidad es ON es.id_especialidad = en.id_especialidad
     WHERE $where
     ORDER BY FIELD(en.estado, 'publicada', 'borrador', 'cerrada'), en.nombre"
);
$stmt->execute($params);
$encuestas = $stmt->fetchAll();
$servicios = especialidades_activas($pdo);

$colores = ['borrador' => 'secondary', 'publicada' => 'success', 'cerrada' => 'dark'];

$titulo  = 'Encuestas';
$seccion = 'encuestas';
require dirname(__DIR__) . '/includes/encabezado.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="h3 mb-0">Encuestas</h1>
    <div class="d-flex gap-2">
        <a href="resultados.php" class="btn btn-outline-primary">Ver resultados</a>
        <a href="alta_encuesta.php" class="btn btn-primary">+ Nueva encuesta</a>
    </div>
</div>

<?= mensaje_ok([
    '1' => 'Encuesta creada como borrador. Publíquela para que los pacientes puedan responderla.',
    '2' => 'Estado de la encuesta actualizado.',
]) ?>

<form method="get" class="card card-body mb-3">
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
        <div class="col-12 col-md-2 d-grid"><button class="btn btn-primary">Filtrar</button></div>
    </div>
</form>

<div class="table-responsive card">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead><tr><th>Encuesta</th><th>Servicio</th><th>Preguntas</th><th>Respuestas</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php foreach ($encuestas as $en): ?>
            <tr>
                <td><?= e($en['nombre']) ?><?= $en['obligatoria'] ? ' <span class="badge bg-warning text-dark">Obligatoria</span>' : '' ?></td>
                <td><?= e($en['servicio'] ?? 'General') ?><?= ($en['servicio'] && !$en['servicio_activo']) ? ' <span class="badge bg-danger">baja</span>' : '' ?></td>
                <td><?= (int) $en['preguntas'] ?></td>
                <td><?= (int) $en['respuestas'] ?></td>
                <td><span class="badge bg-<?= $colores[$en['estado']] ?>"><?= e(texto_estado_encuesta($en['estado'])) ?></span></td>
                <td class="text-nowrap">
                    <a href="resultados.php?encuesta=<?= (int) $en['id_encuesta'] ?>" class="btn btn-outline-primary btn-sm">Resultados</a>
                    <?php
                    // Transiciones permitidas: borrador -> publicada -> cerrada
                    $siguiente = ['borrador' => ['publicada', 'Publicar', '¿Publicar la encuesta? Los pacientes podrán responderla.'],
                                  'publicada' => ['cerrada', 'Cerrar', '¿Cerrar la encuesta? No se podrán recibir más respuestas.']][$en['estado']] ?? null;
                    if ($siguiente): ?>
                        <form method="post" action="estado_encuesta.php" class="d-inline" data-confirmar="<?= e($siguiente[2]) ?>">
                            <?= csrf_campo() ?>
                            <input type="hidden" name="id" value="<?= (int) $en['id_encuesta'] ?>">
                            <input type="hidden" name="estado" value="<?= $siguiente[0] ?>">
                            <button type="submit" class="btn btn-sm <?= $siguiente[0] === 'cerrada' ? 'btn-outline-danger' : 'btn-outline-success' ?>"><?= $siguiente[1] ?></button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require dirname(__DIR__) . '/includes/pie.php'; ?>
