<?php
/* ============================================================
   admin/panel.php
   Panel principal con indicadores generales del sistema.
   Acceso: cualquier usuario logueado.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
requerir_login();

// Indicadores (consultas sin datos del usuario: no necesitan parametros)
$ind = $pdo->query(
    "SELECT
       (SELECT COUNT(*) FROM documento d JOIN especialidad e ON e.id_especialidad = d.id_especialidad
          WHERE d.activo = 1 AND d.publicado = 1 AND e.activo = 1)          AS docs_publicados,
       (SELECT COUNT(*) FROM documento WHERE activo = 1 AND publicado = 0) AS docs_ocultos,
       (SELECT COUNT(*) FROM especialidad WHERE activo = 1)                AS servicios,
       (SELECT COUNT(*) FROM encuesta WHERE estado = 'publicada' AND activo = 1) AS encuestas,
       (SELECT COUNT(*) FROM respuesta)                                    AS respuestas,
       (SELECT COUNT(*) FROM usuario WHERE activo = 1)                     AS usuarios,
       (SELECT ROUND(AVG(d.valor), 2) FROM detalle d
          JOIN pregunta p ON p.id_encuesta = d.id_encuesta AND p.n_pregunta = d.n_pregunta
          WHERE p.tipo_respuesta = 'escala_1_5')                            AS promedio"
)->fetch();

$ultimos = $pdo->query(
    'SELECT d.id_documento, d.titulo, d.publicado, e.nombre AS servicio, d.fecha_carga
     FROM documento d JOIN especialidad e ON e.id_especialidad = d.id_especialidad
     WHERE d.activo = 1 ORDER BY d.fecha_carga DESC LIMIT 5'
)->fetchAll();

$tarjetas = [
    ['Documentos publicados', $ind['docs_publicados'], 'documentos.php'],
    ['Documentos ocultos', $ind['docs_ocultos'], 'documentos.php?estado=oculto'],
    ['Servicios activos', $ind['servicios'], es_admin() ? 'servicios.php' : null],
    ['Encuestas publicadas', $ind['encuestas'], es_admin() ? 'encuestas.php' : null],
    ['Respuestas recibidas', $ind['respuestas'], es_admin() ? 'resultados.php' : null],
    ['Satisfacción promedio (1-5)', $ind['promedio'] ?? '-', es_admin() ? 'resultados.php' : null],
];

$titulo  = 'Panel';
$seccion = 'panel';
require dirname(__DIR__) . '/includes/encabezado.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h3 mb-0">Hola, <?= e($_SESSION['nombre']) ?></h1>
        <small class="text-muted">Panel de <?= es_admin() ? 'administración' : 'gestión de documentos' ?></small>
    </div>
    <a href="alta_documento.php" class="btn btn-primary">+ Nuevo documento</a>
</div>

<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 mb-4">
    <?php foreach ($tarjetas as [$texto, $valor, $enlace]): ?>
        <div class="col">
            <div class="card indicador h-100">
                <div class="card-body">
                    <div class="numero"><?= e($valor) ?></div>
                    <div class="text-muted"><?= e($texto) ?></div>
                    <?php if ($enlace): ?><a href="<?= e($enlace) ?>" class="stretched-link" aria-label="Ver <?= e($texto) ?>"></a><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card">
    <div class="card-header">Últimos documentos cargados</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>Título</th><th>Servicio</th><th>Estado</th><th>Cargado</th></tr></thead>
            <tbody>
            <?php foreach ($ultimos as $d): ?>
                <tr>
                    <td><a href="editar_documento.php?id=<?= (int) $d['id_documento'] ?>"><?= e($d['titulo']) ?></a></td>
                    <td><?= e($d['servicio']) ?></td>
                    <td><?= $d['publicado'] ? '<span class="badge bg-success">Publicado</span>' : '<span class="badge bg-secondary">Oculto</span>' ?></td>
                    <td><?= e(date('d/m/Y', strtotime($d['fecha_carga']))) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require dirname(__DIR__) . '/includes/pie.php'; ?>
