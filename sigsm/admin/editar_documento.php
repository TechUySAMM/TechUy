<?php
/* ============================================================
   admin/editar_documento.php   (equivale a editar.php del ABM)
   Busca por id, precarga, valida y hace UPDATE.
   Permite reemplazar el PDF y publicar / ocultar.
   Cada cambio queda registrado en la auditoria.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
require_once dirname(__DIR__) . '/includes/validar_documento.php';
requerir_login();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

$stmt = $pdo->prepare('SELECT * FROM documento WHERE id_documento = :id AND activo = 1');
$stmt->execute([':id' => $id]);
$original = $stmt->fetch();
if (!$original) {
    redirigir('/admin/documentos.php');
}

$errores = [];
$doc = $original;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $doc = array_merge($original, leer_documento_post($pdo, $errores));

    // Reemplazo del PDF: solo si se eligio un archivo nuevo
    $rutaNueva = null;
    $hayArchivo = ($_FILES['archivo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    if (empty($errores) && $hayArchivo) {
        $rutaNueva = guardar_pdf($_FILES['archivo'], $doc['tipo'], $errores);
    }

    if (empty($errores)) {
        $sql = 'UPDATE documento
                SET titulo = :titulo, descripcion = :descripcion, tipo = :tipo, fecha_emision = :fecha,
                    ruta_archivo = :ruta, publicado = :publicado, id_especialidad = :esp,
                    fecha_modificacion = NOW()
                WHERE id_documento = :id AND activo = 1';
        try {
            $pdo->prepare($sql)->execute([
                ':titulo'      => $doc['titulo'],
                ':descripcion' => $doc['descripcion'] !== '' ? $doc['descripcion'] : null,
                ':tipo'        => $doc['tipo'],
                ':fecha'       => $doc['fecha_emision'],
                ':ruta'        => $rutaNueva ?? $original['ruta_archivo'],
                ':publicado'   => $doc['publicado'],
                ':esp'         => $doc['id_especialidad'],
                ':id'          => $id,
            ]);

            // Que campos cambiaron, para el registro de modificaciones
            $cambios = [];
            foreach (['titulo' => 'título', 'tipo' => 'tipo', 'id_especialidad' => 'servicio', 'fecha_emision' => 'fecha', 'descripcion' => 'descripción'] as $campo => $texto) {
                if ((string) $original[$campo] !== (string) $doc[$campo]) {
                    $cambios[] = $texto;
                }
            }
            if ($cambios) {
                auditar($pdo, 'edicion', 'documento', $id, 'Cambió: ' . implode(', ', $cambios));
            }
            if ((int) $original['publicado'] !== $doc['publicado']) {
                auditar($pdo, $doc['publicado'] ? 'publicar' : 'ocultar', 'documento', $id, $doc['publicado'] ? 'Documento publicado' : 'Documento ocultado');
            }
            if ($rutaNueva) {
                // El PDF anterior se conserva en el servidor como historial
                auditar($pdo, 'reemplazo_pdf', 'documento', $id, 'PDF anterior: ' . $original['ruta_archivo']);
            }
            redirigir('/admin/documentos.php?ok=2');
        } catch (PDOException $ex) {
            if ($rutaNueva) {
                @unlink(RUTA_DOCUMENTOS . '/' . basename($rutaNueva));
            }
            if ($ex->getCode() === '23000') {
                $errores[] = 'Ya existe otro documento con esa ruta de archivo.';
            } else {
                throw $ex;
            }
        }
    }
}

// Historial de modificaciones de este documento
$stmt = $pdo->prepare(
    "SELECT a.fecha, a.accion, a.detalle, u.nombre
     FROM auditoria a LEFT JOIN usuario u ON u.id_usuario = a.id_usuario
     WHERE a.tabla_afectada = 'documento' AND a.id_registro = :id
     ORDER BY a.fecha DESC"
);
$stmt->execute([':id' => $id]);
$historial = $stmt->fetchAll();

$stmt = $pdo->prepare('SELECT nombre FROM usuario WHERE id_usuario = :id');
$stmt->execute([':id' => $original['id_usuario_carga']]);
$cargadoPor = $stmt->fetchColumn();

$servicios = especialidades_activas($pdo);
$esAlta    = false;

$titulo  = 'Editar documento';
$seccion = 'documentos';
$scripts = ['validaciones.js'];
require dirname(__DIR__) . '/includes/encabezado.php';
?>

<h1 class="h3 mb-1">Editar documento</h1>
<p class="text-muted small">Cargado por <?= e($cargadoPor) ?> el <?= e(date('d/m/Y H:i', strtotime($original['fecha_carga']))) ?>
    <?= $original['fecha_modificacion'] ? ' &middot; Última modificación: ' . e(date('d/m/Y H:i', strtotime($original['fecha_modificacion']))) : '' ?></p>

<?= lista_errores($errores) ?>
<?php require dirname(__DIR__) . '/includes/form_documento.php'; ?>

<div class="card mt-4">
    <div class="card-header">Historial de modificaciones</div>
    <ul class="list-group list-group-flush">
        <?php foreach ($historial as $h): ?>
            <li class="list-group-item small">
                <strong><?= e(date('d/m/Y H:i', strtotime($h['fecha']))) ?></strong> &middot;
                <?= e($h['nombre'] ?? 'Sistema') ?> &middot; <?= e($h['accion']) ?>: <?= e($h['detalle']) ?>
            </li>
        <?php endforeach; ?>
        <?php if (!$historial): ?><li class="list-group-item small text-muted">Sin modificaciones registradas.</li><?php endif; ?>
    </ul>
</div>

<?php require dirname(__DIR__) . '/includes/pie.php'; ?>
