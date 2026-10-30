<?php
/* ============================================================
   admin/editar_servicio.php
   Edicion de nombre y descripcion de un servicio activo.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
require_once dirname(__DIR__) . '/includes/validar_servicio.php';
requerir_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$stmt = $pdo->prepare('SELECT nombre, descripcion FROM especialidad WHERE id_especialidad = :id AND activo = 1');
$stmt->execute([':id' => $id]);
$servicio = $stmt->fetch();
if (!$servicio) {
    redirigir('/admin/servicios.php');
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $servicio = leer_servicio_post($errores);

    if (empty($errores)) {
        try {
            $pdo->prepare('UPDATE especialidad SET nombre = :n, descripcion = :d WHERE id_especialidad = :id')
                ->execute([':n' => $servicio['nombre'], ':d' => $servicio['descripcion'] !== '' ? $servicio['descripcion'] : null, ':id' => $id]);
            auditar($pdo, 'edicion', 'especialidad', $id, 'Edición de servicio ' . $servicio['nombre']);
            redirigir('/admin/servicios.php?ok=2');
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $errores[] = 'Ya existe otro servicio con ese nombre.';
            } else {
                throw $ex;
            }
        }
    }
}

$titulo  = 'Editar servicio';
$seccion = 'servicios';
require dirname(__DIR__) . '/includes/encabezado.php';
?>

<h1 class="h3 mb-3">Editar servicio</h1>
<?= lista_errores($errores) ?>
<?php require dirname(__DIR__) . '/includes/form_servicio.php'; ?>

<?php require dirname(__DIR__) . '/includes/pie.php'; ?>
