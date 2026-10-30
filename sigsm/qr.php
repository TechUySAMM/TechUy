<?php
/* ============================================================
   qr.php
   Muestra el UNICO codigo QR del sistema, que apunta a la
   portada publica (index.php). Se genera en el navegador con la
   libreria qrcodejs, a partir de la direccion actual del
   servidor, asi funciona igual en localhost o en la IP de la red.
   Esta pagina se imprime y se coloca en las pantallas del hospital.
   ============================================================ */

require_once __DIR__ . '/includes/inicio.php';

$esquema = !empty($_SERVER['HTTPS']) ? 'https' : 'http';
$urlPortal = $esquema . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . URL_BASE . '/index.php';

$titulo  = 'Código QR del portal';
$seccion = 'inicio';
$publico = true;
require __DIR__ . '/includes/encabezado.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-7 col-lg-5">
        <div class="card text-center">
            <div class="card-body p-4">
                <h1 class="h4">Escanee para ingresar al portal de pacientes</h1>
                <p class="text-muted">Documentos e indicaciones &middot; Encuestas de satisfacción</p>
                <div id="codigoQR" class="d-inline-block p-3 bg-white border rounded" aria-label="Código QR del portal"></div>
                <p class="mt-3 mb-1 small text-break"><strong>Dirección:</strong> <a href="<?= e($urlPortal) ?>"><?= e($urlPortal) ?></a></p>
                <p class="small text-muted mb-3">QR real generado a partir de la dirección del servidor.</p>
                <button type="button" class="btn btn-outline-primary" onclick="window.print()">Imprimir cartel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    "use strict";
    new QRCode(document.getElementById("codigoQR"), {
        text: <?= json_encode($urlPortal) ?>,
        width: 240,
        height: 240,
        colorDark: "#0f172a",
        colorLight: "#ffffff",
    });
</script>

<?php require __DIR__ . '/includes/pie.php'; ?>
