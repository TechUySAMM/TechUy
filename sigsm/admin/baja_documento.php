<?php
/* ============================================================
   admin/baja_documento.php   (equivale a baja.php del ABM)
   Baja logica (activo = 0), solo por POST y con token CSRF.
   Prohibido usar DELETE: el usuario de la base ni siquiera
   tiene ese permiso.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
requerir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('/admin/documentos.php');
}
csrf_verificar();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

$stmt = $pdo->prepare('UPDATE documento SET activo = 0, fecha_modificacion = NOW() WHERE id_documento = :id AND activo = 1');
$stmt->execute([':id' => $id]);

if ($stmt->rowCount() === 1) {
    auditar($pdo, 'baja', 'documento', $id, 'Baja lógica de documento');
}
redirigir('/admin/documentos.php?ok=3');
