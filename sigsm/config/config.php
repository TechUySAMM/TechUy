<?php
/* ============================================================
   config/config.php
   Constantes de configuracion de S.I.G.S.M.
   Para instalar en otra computadora solo se cambia este archivo
   (ver README.md, seccion "Configuracion").
   ============================================================ */

// Base de datos: se usa un usuario con permisos minimos, no root
define('BD_HOST',    'localhost');
define('BD_NOMBRE',  'hc_gestion_documental');
define('BD_USUARIO', 'sigsm_app');
define('BD_CLAVE',   'Sigsm.App.2026');

// Carpeta del proyecto dentro de htdocs (http://localhost/sigsm)
define('URL_BASE', '/sigsm');

// Rutas fisicas
define('RUTA_RAIZ',       dirname(__DIR__));
define('RUTA_DOCUMENTOS', RUTA_RAIZ . '/documentos');
define('RUTA_LOGS',       RUTA_RAIZ . '/logs');

// Subida de PDF: tamano maximo 5 MB
define('PDF_TAMANO_MAX', 5 * 1024 * 1024);

// Login: maximo de intentos fallidos y ventana de bloqueo (minutos)
define('LOGIN_MAX_INTENTOS', 5);      // por cedula
define('LOGIN_MAX_INTENTOS_IP', 20);  // por direccion IP
define('LOGIN_MINUTOS_BLOQUEO', 15);

// Paginacion de listados
define('POR_PAGINA', 10);

// Zona horaria de Uruguay
date_default_timezone_set('America/Montevideo');
