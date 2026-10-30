<?php
// La carpeta admin/ siempre lleva al panel (que exige login)
require_once dirname(__DIR__) . '/config/config.php';
header('Location: ' . URL_BASE . '/admin/panel.php');
exit;
