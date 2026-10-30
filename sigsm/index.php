<?php
/* ============================================================
   index.php
   Portada publica del portal. Es la pagina que abre el UNICO
   codigo QR del sistema. Desde aca el paciente va a Documentos
   o a Encuestas sin iniciar sesion.
   ============================================================ */

require_once __DIR__ . '/includes/inicio.php';

// Servicios activos que tienen al menos un documento visible
$servicios = $pdo->query(
    'SELECT e.id_especialidad, e.nombre, COUNT(d.id_documento) AS cantidad
     FROM especialidad e
     JOIN documento d ON d.id_especialidad = e.id_especialidad
                     AND d.activo = 1 AND d.publicado = 1
     WHERE e.activo = 1
     GROUP BY e.id_especialidad, e.nombre
     ORDER BY e.nombre'
)->fetchAll();

$titulo  = 'Portal de pacientes';
$seccion = 'inicio';
$publico = true;
require __DIR__ . '/includes/encabezado.php';
?>

<section class="hero-portal p-4 p-md-5 mb-4 shadow">
    <h1 class="h2 fw-bold">Portal de pacientes</h1>
    <p class="lead mb-0">Consulte las indicaciones y la información de su servicio desde el celular,
        sin necesidad de copias impresas.</p>
</section>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <a href="documentos.php" class="btn btn-primary w-100 acceso-grande d-flex flex-column justify-content-center">
            <span class="fs-3">📄</span>
            Documentos e indicaciones
        </a>
    </div>
    <div class="col-12 col-md-6">
        <a href="encuestas.php" class="btn btn-outline-primary bg-white w-100 acceso-grande d-flex flex-column justify-content-center">
            <span class="fs-3">⭐</span>
            Encuestas de satisfacción
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Buscar por servicio</div>
    <div class="list-group list-group-flush">
        <?php foreach ($servicios as $s): ?>
            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
               href="documentos.php?servicio=<?= (int) $s['id_especialidad'] ?>">
                <?= e($s['nombre']) ?>
                <span class="badge bg-primary rounded-pill"><?= (int) $s['cantidad'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="alert alert-info" role="alert">
    Las encuestas son <strong>anónimas</strong>: no se le pedirá nombre, cédula ni ningún dato personal.
    <a href="qr.php" class="alert-link ms-1">Ver el código QR del portal</a>.
</div>

<?php require __DIR__ . '/includes/pie.php'; ?>
