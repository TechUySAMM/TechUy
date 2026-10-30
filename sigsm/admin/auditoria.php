<?php
/* ============================================================
   admin/auditoria.php
   Registro de acciones importantes: quien, que y cuando.
   Filtro por tabla y paginacion. Solo administrador.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
requerir_admin();

$tablas = ['documento' => 'Documentos', 'usuario' => 'Usuarios', 'especialidad' => 'Servicios', 'encuesta' => 'Encuestas'];
$tabla  = isset($tablas[$_GET['tabla'] ?? '']) ? $_GET['tabla'] : '';
$pagina = pagina_actual();

$where  = '1 = 1';
$params = [];
if ($tabla !== '') {
    $where .= ' AND a.tabla_afectada = :t';
    $params[':t'] = $tabla;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM auditoria a WHERE $where");
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT a.fecha, a.accion, a.tabla_afectada, a.id_registro, a.detalle, a.ip, u.nombre
     FROM auditoria a LEFT JOIN usuario u ON u.id_usuario = a.id_usuario
     WHERE $where
     ORDER BY a.fecha DESC, a.id_auditoria DESC
     LIMIT " . POR_PAGINA . ' OFFSET ' . (($pagina - 1) * POR_PAGINA)
);
$stmt->execute($params);
$registros = $stmt->fetchAll();

$titulo  = 'Auditoría';
$seccion = 'auditoria';
require dirname(__DIR__) . '/includes/encabezado.php';
?>

<h1 class="h3 mb-3">Registro de acciones (auditoría)</h1>

<form method="get" class="d-flex flex-wrap gap-2 mb-3">
    <label for="tabla" class="visually-hidden">Filtrar por sección</label>
    <select id="tabla" name="tabla" class="form-select w-auto">
        <option value="">Todas las secciones</option>
        <?php foreach ($tablas as $valor => $texto): ?>
            <option value="<?= $valor ?>" <?= $tabla === $valor ? 'selected' : '' ?>><?= $texto ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-primary">Filtrar</button>
</form>

<div class="table-responsive card">
    <table class="table table-striped align-middle mb-0 small">
        <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Sección</th><th>Registro</th><th>Detalle</th><th>IP</th></tr></thead>
        <tbody>
        <?php foreach ($registros as $r): ?>
            <tr>
                <td class="text-nowrap"><?= e(date('d/m/Y H:i', strtotime($r['fecha']))) ?></td>
                <td><?= e($r['nombre'] ?? 'Visitante anónimo') ?></td>
                <td><span class="badge text-bg-light"><?= e($r['accion']) ?></span></td>
                <td><?= e($tablas[$r['tabla_afectada']] ?? $r['tabla_afectada']) ?></td>
                <td><?= e($r['id_registro'] ?? '-') ?></td>
                <td><?= e($r['detalle']) ?></td>
                <td><?= e($r['ip']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p class="text-muted small mt-2"><?= $total ?> registro(s).</p>
<?= paginacion($total, $pagina) ?>

<?php require dirname(__DIR__) . '/includes/pie.php'; ?>
