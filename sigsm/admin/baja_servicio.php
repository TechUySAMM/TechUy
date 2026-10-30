<?php
/* ============================================================
   admin/baja_servicio.php
   Baja logica o reactivacion de un servicio (solo POST + CSRF).
   Tratamiento de lo asociado: los documentos y encuestas NO se
   modifican; simplemente dejan de mostrarse en el portal porque
   todas las consultas publicas exigen especialidad.activo = 1.
   Al reactivar el servicio vuelven a verse tal como estaban.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
requerir_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('/admin/servicios.php');
}
csrf_verificar();

$id        = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
$reactivar = ($_POST['reactivar'] ?? '') === '1';

$stmt = $pdo->prepare('UPDATE especialidad SET activo = :nuevo WHERE id_especialidad = :id AND activo = :actual');
$stmt->execute([':nuevo' => $reactivar ? 1 : 0, ':actual' => $reactivar ? 0 : 1, ':id' => $id]);

if ($stmt->rowCount() === 1) {
    auditar($pdo, $reactivar ? 'reactivar' : 'baja', 'especialidad', $id, $reactivar ? 'Reactivación de servicio' : 'Baja lógica de servicio');
}
redirigir('/admin/servicios.php?ok=' . ($reactivar ? '4' : '3'));
