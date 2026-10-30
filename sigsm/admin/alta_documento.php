<?php
/* ============================================================
   admin/alta_documento.php   (equivale a alta.php del ABM)
   Formulario + validacion + subida del PDF + INSERT.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
require_once dirname(__DIR__) . '/includes/validar_documento.php';
requerir_login();

$errores = [];
$doc = ['titulo' => '', 'descripcion' => '', 'tipo' => '', 'id_especialidad' => 0,
        'fecha_emision' => date('Y-m-d'), 'publicado' => 1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $doc = leer_documento_post($pdo, $errores);

    // El PDF se guarda solo si el resto de los datos es correcto
    $ruta = empty($errores) ? guardar_pdf($_FILES['archivo'] ?? [], $doc['tipo'], $errores) : null;

    if ($ruta !== null && !ruta_pdf_valida($ruta)) {
        $errores[] = 'La ruta del archivo debe empezar con documentos/ y terminar en .pdf.';
    }

    if (empty($errores)) {
        $sql = 'INSERT INTO documento (titulo, descripcion, tipo, fecha_emision, ruta_archivo, publicado, activo, id_especialidad, id_usuario_carga)
                VALUES (:titulo, :descripcion, :tipo, :fecha, :ruta, :publicado, 1, :esp, :usuario)';
        try {
            $pdo->prepare($sql)->execute([
                ':titulo'      => $doc['titulo'],
                ':descripcion' => $doc['descripcion'] !== '' ? $doc['descripcion'] : null,
                ':tipo'        => $doc['tipo'],
                ':fecha'       => $doc['fecha_emision'],
                ':ruta'        => $ruta,
                ':publicado'   => $doc['publicado'],
                ':esp'         => $doc['id_especialidad'],
                ':usuario'     => $_SESSION['id_usuario'],
            ]);
            $id = (int) $pdo->lastInsertId();
            auditar($pdo, 'alta', 'documento', $id, 'Alta de documento: ' . $doc['titulo']);
            redirigir('/admin/documentos.php?ok=1');
        } catch (PDOException $ex) {
            @unlink(RUTA_DOCUMENTOS . '/' . basename($ruta)); // no dejar archivos huerfanos
            if ($ex->getCode() === '23000') {
                $errores[] = 'Ya existe otro documento con esa ruta de archivo.';
            } else {
                throw $ex;
            }
        }
    }
}

$servicios = especialidades_activas($pdo);
$esAlta    = true;

$titulo  = 'Nuevo documento';
$seccion = 'documentos';
$scripts = ['validaciones.js'];
require dirname(__DIR__) . '/includes/encabezado.php';
?>

<h1 class="h3 mb-3">Nuevo documento</h1>
<?= lista_errores($errores) ?>
<?php require dirname(__DIR__) . '/includes/form_documento.php'; ?>

<?php require dirname(__DIR__) . '/includes/pie.php'; ?>
