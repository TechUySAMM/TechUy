<?php
/* ============================================================
   admin/documentos.php   (equivale a usuarios.php del ABM)
   Lista los documentos activos con botones Editar y Baja.
   Busqueda por titulo, filtro por servicio y estado, paginado.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
requerir_login();

$busqueda = trim($_GET['q'] ?? '');
$servicio = filter_input(INPUT_GET, 'servicio', FILTER_VALIDATE_INT) ?: null;
$estado   = $_GET['estado'] ?? '';
$pagina   = pagina_actual();

$where  = 'd.activo = 1';
$params = [];
if ($busqueda !== '') {
    $where .= ' AND d.titulo LIKE :q';
    $params[':q'] = '%' . $busqueda . '%';
}
if ($servicio) {
    $where .= ' AND d.id_especialidad = :s';
    $params[':s'] = $servicio;
}
if ($estado === 'publicado') {
    $where .= ' AND d.publicado = 1';
} elseif ($estado === 'oculto') {
    $where .= ' AND d.publicado = 0';
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM documento d WHERE $where");
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT d.id_documento, d.titulo, d.tipo, d.fecha_emision, d.ruta_archivo, d.publicado,
            e.nombre AS servicio, e.activo AS servicio_activo, u.nombre AS cargado_por
     FROM documento d
     JOIN especialidad e ON e.id_especialidad = d.id_especialidad
     JOIN usuario u ON u.id_usuario = d.id_usuario_carga
     WHERE $where
     ORDER BY d.fecha_carga DESC
     LIMIT " . POR_PAGINA . ' OFFSET ' . (($pagina - 1) * POR_PAGINA)
);
$stmt->execute($params);
$documentos = $stmt->fetchAll();

// Para el filtro se listan todos los servicios (incluso dados de baja)
$servicios = $pdo->query('SELECT id_especialidad, nombre, activo FROM especialidad ORDER BY nombre')->fetchAll();

$titulo  = 'Documentos';
$seccion = 'documentos';
require dirname(__DIR__) . '/includes/encabezado.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="h3 mb-0">Documentos</h1>
    <a href="alta_documento.php" class="btn btn-primary">+ Nuevo documento</a>
</div>

<?= mensaje_ok([
    '1' => 'Documento creado correctamente.',
    '2' => 'Documento actualizado correctamente.',
    '3' => 'Documento dado de baja correctamente.',
]) ?>

<form method="get" class="card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label for="q" class="form-label">Buscar por título</label>
            <input type="search" id="q" name="q" class="form-control" value="<?= e($busqueda) ?>">
        </div>
        <div class="col-6 col-md-3">
            <label for="servicio" class="form-label">Servicio</label>
            <select id="servicio" name="servicio" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($servicios as $s): ?>
                    <option value="<?= (int) $s['id_especialidad'] ?>" <?= $servicio === (int) $s['id_especialidad'] ? 'selected' : '' ?>>
                        <?= e($s['nombre']) ?><?= $s['activo'] ? '' : ' (baja)' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label for="estado" class="form-label">Estado</label>
            <select id="estado" name="estado" class="form-select">
                <option value="">Todos</option>
                <option value="publicado" <?= $estado === 'publicado' ? 'selected' : '' ?>>Publicados</option>
                <option value="oculto" <?= $estado === 'oculto' ? 'selected' : '' ?>>Ocultos</option>
            </select>
        </div>
        <div class="col-12 col-md-2 d-grid">
            <button class="btn btn-primary" type="submit">Filtrar</button>
        </div>
    </div>
</form>

<p class="text-muted small mb-2"><?= $total ?> documento(s).</p>

<div class="table-responsive card">
    <table class="table table-striped table-hover align-middle mb-0">
        <thead>
            <tr><th>Título</th><th>Tipo</th><th>Servicio</th><th>Emisión</th><th>Archivo</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($documentos as $d): ?>
            <tr>
                <td><?= e($d['titulo']) ?><br><small class="text-muted">Cargó: <?= e($d['cargado_por']) ?></small></td>
                <td><?= e(texto_tipo($d['tipo'])) ?></td>
                <td>
                    <?= e($d['servicio']) ?>
                    <?php if (!$d['servicio_activo']): ?><br><span class="badge bg-danger">Servicio de baja: no visible</span><?php endif; ?>
                </td>
                <td><?= e(date('d/m/Y', strtotime($d['fecha_emision']))) ?></td>
                <td><a href="<?= URL_BASE ?>/ver_documento.php?id=<?= (int) $d['id_documento'] ?>" target="_blank" rel="noopener"><small><?= e($d['ruta_archivo']) ?></small></a></td>
                <td><?= $d['publicado'] ? '<span class="badge bg-success">Publicado</span>' : '<span class="badge bg-secondary">Oculto</span>' ?></td>
                <td class="text-nowrap">
                    <a href="editar_documento.php?id=<?= (int) $d['id_documento'] ?>" class="btn btn-outline-primary btn-sm">Editar</a>
                    <form method="post" action="baja_documento.php" class="d-inline"
                          data-confirmar="¿Dar de baja el documento &quot;<?= e($d['titulo']) ?>&quot;? Los pacientes ya no podrán abrirlo.">
                        <?= csrf_campo() ?>
                        <input type="hidden" name="id" value="<?= (int) $d['id_documento'] ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm">Baja</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$documentos): ?>
            <tr><td colspan="7" class="text-center text-muted py-4">No hay documentos con esos filtros.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="mt-3"><?= paginacion($total, $pagina) ?></div>

<?php require dirname(__DIR__) . '/includes/pie.php'; ?>
