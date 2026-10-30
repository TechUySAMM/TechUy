<?php
/* ============================================================
   admin/alta_servicio.php
   Alta de servicio con control de nombre duplicado.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
require_once dirname(__DIR__) . '/includes/validar_servicio.php';
requerir_admin();

$errores  = [];
$servicio = ['nombre' => '', 'descripcion' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $servicio = leer_servicio_post($errores);

    if (empty($errores)) {
        try {
            $pdo->prepare('INSERT INTO especialidad (nombre, descripcion, activo) VALUES (:n, :d, 1)')
                ->execute([':n' => $servicio['nombre'], ':d' => $servicio['descripcion'] !== '' ? $servicio['descripcion'] : null]);
            auditar($pdo, 'alta', 'especialidad', (int) $pdo->lastInsertId(), 'Alta de servicio ' . $servicio['nombre']);
            redirigir('/admin/servicios.php?ok=1');
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $errores[] = 'Ya existe un servicio con ese nombre (puede estar dado de baja: reactívelo desde el listado).';
            } else {
                throw $ex;
            }
        }
    }
}

$titulo  = 'Nuevo servicio';
$seccion = 'servicios';
require dirname(__DIR__) . '/includes/encabezado.php';
?>

<h1 class="h3 mb-3">Nuevo servicio</h1>
<?= lista_errores($errores) ?>
<?php require dirname(__DIR__) . '/includes/form_servicio.php'; ?>

<?php require dirname(__DIR__) . '/includes/pie.php'; ?>
