<?php
/* ============================================================
   includes/validar_servicio.php  (procesamiento)
   Validaciones del servidor para alta y edicion de servicios.
   El nombre duplicado lo controla la restriccion UNIQUE de la
   base (codigo 23000); la collation utf8mb4_spanish_ci hace que
   "Cardiologia" y "cardiología" cuenten como el mismo nombre.
   ============================================================ */

function leer_servicio_post(array &$errores): array
{
    $s = [
        'nombre'      => trim($_POST['nombre'] ?? ''),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
    ];
    if (mb_strlen($s['nombre']) < 3 || mb_strlen($s['nombre']) > 100) {
        $errores[] = 'El nombre del servicio debe tener entre 3 y 100 caracteres.';
    }
    if (mb_strlen($s['descripcion']) > 1000) {
        $errores[] = 'La descripción no puede superar los 1000 caracteres.';
    }
    return $s;
}
