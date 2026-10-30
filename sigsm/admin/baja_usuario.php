<?php
/* ============================================================
   admin/baja_usuario.php
   Baja logica (activo = 0) o reactivacion. Solo POST + CSRF.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
requerir_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('/admin/usuarios.php');
}
csrf_verificar();

$id        = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
$reactivar = ($_POST['reactivar'] ?? '') === '1';

if ($id === $_SESSION['id_usuario']) {
    redirigir('/admin/usuarios.php?error=propio');
}

$stmt = $pdo->prepare('UPDATE usuario SET activo = :nuevo WHERE id_usuario = :id AND activo = :actual');
$stmt->execute([':nuevo' => $reactivar ? 1 : 0, ':actual' => $reactivar ? 0 : 1, ':id' => $id]);

if ($stmt->rowCount() === 1) {
    auditar($pdo, $reactivar ? 'reactivar' : 'baja', 'usuario', $id, $reactivar ? 'Reactivación de usuario' : 'Baja lógica de usuario');
}
redirigir('/admin/usuarios.php?ok=' . ($reactivar ? '4' : '3'));
