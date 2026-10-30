<?php
/* ============================================================
   ver_documento.php
   Abre el PDF de un documento. La carpeta /documentos esta
   bloqueada por .htaccess: la UNICA forma de ver un PDF es esta
   pagina, que controla que el documento este activo, publicado
   y que su servicio no este dado de baja.
   El personal logueado puede previsualizar documentos ocultos.
   ============================================================ */

require_once __DIR__ . '/includes/inicio.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$stmt = $pdo->prepare(
    'SELECT d.titulo, d.ruta_archivo, d.activo, d.publicado, e.activo AS servicio_activo
     FROM documento d
     JOIN especialidad e ON e.id_especialidad = d.id_especialidad
     WHERE d.id_documento = :id'
);
$stmt->execute([':id' => $id ?: 0]);
$doc = $stmt->fetch();

$visiblePublico = $doc && $doc['activo'] && $doc['publicado'] && $doc['servicio_activo'];
$previewPersonal = $doc && $doc['activo'] && usuario_logueado();

if (!$visiblePublico && !$previewPersonal) {
    http_response_code(404);
    $titulo  = 'Documento no disponible';
    $publico = true;
    require __DIR__ . '/includes/encabezado.php';
    echo '<div class="alert alert-warning">El documento solicitado no existe o ya no está disponible.</div>'
       . '<a href="documentos.php" class="btn btn-primary">Volver a documentos</a>';
    require __DIR__ . '/includes/pie.php';
    exit;
}

// basename() evita que una ruta manipulada salga de /documentos
$archivo = RUTA_DOCUMENTOS . '/' . basename($doc['ruta_archivo']);
if (!is_file($archivo)) {
    registrar_error('Falta el PDF ' . $doc['ruta_archivo']);
    mostrar_error_generico();
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($archivo) . '"');
header('Content-Length: ' . filesize($archivo));
header('X-Content-Type-Options: nosniff');
readfile($archivo);
