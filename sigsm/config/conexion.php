<?php
/* ============================================================
   config/conexion.php
   Conexion PDO a MySQL/MariaDB (mismo patron que el ABM de
   usuarios). Si falla, se registra el error en logs/ y al
   usuario se le muestra un mensaje generico.
   ============================================================ */

require_once __DIR__ . '/config.php';
require_once dirname(__DIR__) . '/includes/errores.php';

$dsn = 'mysql:host=' . BD_HOST . ';dbname=' . BD_NOMBRE . ';charset=utf8mb4';

try {
    $pdo = new PDO($dsn, BD_USUARIO, BD_CLAVE, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,   // consultas preparadas reales
    ]);
} catch (PDOException $e) {
    registrar_error('Conexion BD: ' . $e->getMessage());
    mostrar_error_generico();
}
