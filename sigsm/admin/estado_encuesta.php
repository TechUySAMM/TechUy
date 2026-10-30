<?php
/* ============================================================
   admin/estado_encuesta.php
   Cambia el estado de una encuesta: borrador -> publicada ->
   cerrada. Solo POST + CSRF. Una encuesta cerrada conserva su
   historial de respuestas.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
requerir_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirigir('/admin/encuestas.php');
}
csrf_verificar();

$id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;
$estado = $_POST['estado'] ?? '';

// Estado anterior que exige cada transicion
$anterior = ['publicada' => 'borrador', 'cerrada' => 'publicada'][$estado] ?? null;

if ($anterior !== null) {
    $stmt = $pdo->prepare('UPDATE encuesta SET estado = :nuevo WHERE id_encuesta = :id AND estado = :anterior AND activo = 1');
    $stmt->execute([':nuevo' => $estado, ':anterior' => $anterior, ':id' => $id]);
    if ($stmt->rowCount() === 1) {
        auditar($pdo, $estado === 'publicada' ? 'publicar' : 'cerrar', 'encuesta', $id, 'Encuesta ' . $estado);
    }
}
redirigir('/admin/encuestas.php?ok=2');
