<?php
/* ============================================================
   documentos.php  (publico)
   Lista los documentos activos y publicados de servicios
   activos. Permite buscar por titulo y filtrar por servicio.
   ============================================================ */

require_once __DIR__ . '/includes/inicio.php';

$busqueda = trim($_GET['q'] ?? '');
$servicio = filter_input(INPUT_GET, 'servicio', FILTER_VALIDATE_INT) ?: null;
$pagina   = pagina_actual();

// Filtros armados con parametros (nunca concatenando texto del usuario)
$where  = 'd.activo = 1 AND d.publicado = 1 AND e.activo = 1';
$params = [];
if ($busqueda !== '') {
    $where .= ' AND d.titulo LIKE :q';
    $params[':q'] = '%' . $busqueda . '%';
}
if ($servicio) {
    $where .= ' AND d.id_especialidad = :s';
    $params[':s'] = $servicio;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM documento d JOIN especialidad e ON e.id_especialidad = d.id_especialidad WHERE $where");
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT d.id_documento, d.titulo, d.descripcion, d.tipo, d.fecha_emision, e.nombre AS servicio
     FROM documento d
     JOIN especialidad e ON e.id_especialidad = d.id_especialidad
     WHERE $where
     ORDER BY e.nombre, d.titulo
     LIMIT " . POR_PAGINA . ' OFFSET ' . (($pagina - 1) * POR_PAGINA)
);
$stmt->execute($params);
$documentos = $stmt->fetchAll();

$servicios = especialidades_activas($pdo);

$titulo  = 'Documentos para pacientes';
$seccion = 'documentos';
$publico = true;
require __DIR__ . '/includes/encabezado.php';
?>

<h1 class="h3 mb-3">Documentos e indicaciones</h1>

<form method="get" class="card card-body mb-4" role="search">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-6">
            <label for="q" class="form-label">Buscar por título</label>
            <input type="search" id="q" name="q" class="form-control" value="<?= e($busqueda) ?>" placeholder="Ej: warfarina">
        </div>
        <div class="col-12 col-md-4">
            <label for="servicio" class="form-label">Servicio</label>
            <select id="servicio" name="servicio" class="form-select">
                <option value="">Todos los servicios</option>
                <?php foreach ($servicios as $s): ?>
                    <option value="<?= (int) $s['id_especialidad'] ?>" <?= $servicio === (int) $s['id_especialidad'] ? 'selected' : '' ?>>
                        <?= e($s['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-2 d-grid">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
    </div>
</form>

<p class="text-muted"><?= $total ?> documento(s) encontrado(s).</p>

<?php if (!$documentos): ?>
    <div class="alert alert-warning">No hay documentos que coincidan con la búsqueda.</div>
<?php endif; ?>

<div class="row row-cols-1 row-cols-md-2 g-3">
    <?php foreach ($documentos as $d): ?>
        <div class="col">
            <div class="card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="mb-2">
                        <span class="badge badge-tipo-<?= e($d['tipo']) ?>"><?= e(texto_tipo($d['tipo'])) ?></span>
                        <span class="badge text-bg-light"><?= e($d['servicio']) ?></span>
                    </div>
                    <h2 class="h5"><?= e($d['titulo']) ?></h2>
                    <p class="text-muted small flex-grow-1"><?= e($d['descripcion']) ?></p>
                    <a href="ver_documento.php?id=<?= (int) $d['id_documento'] ?>" target="_blank" rel="noopener"
                       class="btn btn-primary">Abrir documento (PDF)</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="mt-4"><?= paginacion($total, $pagina) ?></div>

<?php require __DIR__ . '/includes/pie.php'; ?>
