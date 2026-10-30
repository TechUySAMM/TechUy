<?php
/* ============================================================
   includes/errores.php
   Registro interno de errores: el detalle tecnico va a
   logs/errores.log y el usuario solo ve un mensaje amigable.
   ============================================================ */

require_once dirname(__DIR__) . '/config/config.php';

// En produccion no se muestran errores en pantalla
ini_set('display_errors', '0');
error_reporting(E_ALL);

function registrar_error(string $mensaje): void
{
    $linea = '[' . date('Y-m-d H:i:s') . '] ' . ($_SERVER['REQUEST_URI'] ?? 'cli') . ' - ' . $mensaje . PHP_EOL;
    @file_put_contents(RUTA_LOGS . '/errores.log', $linea, FILE_APPEND | LOCK_EX);
}

function mostrar_error_generico(): void
{
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Error</title>'
       . '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>'
       . '<body class="container py-5"><div class="alert alert-danger">'
       . 'Ocurrió un problema interno. Intente nuevamente en unos minutos.'
       . '</div><a href="' . URL_BASE . '/index.php" class="btn btn-secondary">Volver al inicio</a></body></html>';
    exit;
}

// Cualquier excepcion no atrapada termina aca
set_exception_handler(function (Throwable $e) {
    registrar_error(get_class($e) . ': ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
    mostrar_error_generico();
});
