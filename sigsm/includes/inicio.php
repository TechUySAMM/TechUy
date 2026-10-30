<?php
/* ============================================================
   includes/inicio.php
   Se incluye al principio de cada pagina: carga configuracion,
   manejo de errores, conexion, sesion, CSRF y funciones.
   ============================================================ */

require_once dirname(__DIR__) . '/config/conexion.php';
require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/funciones.php';
