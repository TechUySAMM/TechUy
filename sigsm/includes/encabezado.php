<?php
/* ============================================================
   includes/encabezado.php  (presentacion)
   Cabecera comun. Antes de incluirlo cada pagina define:
     $titulo  -> texto de la pestana
     $seccion -> opcion del menu a resaltar
     $publico -> true en las paginas del portal de pacientes
   ============================================================ */

$publico = $publico ?? false;
$seccion = $seccion ?? '';

function item_menu(string $clave, string $ruta, string $texto, string $seccion): string
{
    $activa = $clave === $seccion ? ' active" aria-current="page' : '';
    return '<li class="nav-item"><a class="nav-link' . $activa . '" href="' . URL_BASE . $ruta . '">' . $texto . '</a></li>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo ?? 'S.I.G.S.M.') ?> - S.I.G.S.M.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/estilos.css">
</head>
<body class="<?= $publico ? 'portal-publico' : 'panel-admin' ?>">

<nav class="navbar navbar-expand-lg navbar-dark navbar-sigsm shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= URL_BASE ?><?= $publico ? '/index.php' : '/admin/panel.php' ?>">
            <img src="<?= URL_BASE ?>/public/img/logo.jpg" alt="Logo TechUY">
            <span><strong>S.I.G.S.M.</strong> <small class="d-none d-sm-inline">Hospital de Clínicas</small></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menú">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav me-auto">
                <?php if ($publico || !usuario_logueado()): ?>
                    <?= item_menu('inicio', '/index.php', 'Inicio', $seccion) ?>
                    <?= item_menu('documentos', '/documentos.php', 'Documentos', $seccion) ?>
                    <?= item_menu('encuestas', '/encuestas.php', 'Encuestas', $seccion) ?>
                <?php else: ?>
                    <?= item_menu('panel', '/admin/panel.php', 'Panel', $seccion) ?>
                    <?= item_menu('documentos', '/admin/documentos.php', 'Documentos', $seccion) ?>
                    <?php if (es_admin()): ?>
                        <?= item_menu('encuestas', '/admin/encuestas.php', 'Encuestas', $seccion) ?>
                        <?= item_menu('servicios', '/admin/servicios.php', 'Servicios', $seccion) ?>
                        <?= item_menu('usuarios', '/admin/usuarios.php', 'Usuarios', $seccion) ?>
                        <?= item_menu('auditoria', '/admin/auditoria.php', 'Auditoría', $seccion) ?>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if (usuario_logueado()): ?>
                    <?php if ($publico): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= URL_BASE ?>/admin/panel.php">Ir al panel</a></li>
                    <?php endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= e($_SESSION['nombre']) ?> (<?= es_admin() ? 'Administrador' : 'Usuario' ?>)
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= URL_BASE ?>/admin/cambiar_contrasena.php">Cambiar contraseña</a></li>
                            <li><a class="dropdown-item" href="<?= URL_BASE ?>/index.php">Ver portal público</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="post" action="<?= URL_BASE ?>/logout.php" class="px-3">
                                    <?= csrf_campo() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">Cerrar sesión</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= URL_BASE ?>/login.php">Ingreso de personal</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
<?php if (($_GET['error'] ?? '') === 'permiso'): ?>
    <div class="alert alert-warning" role="alert">No tiene permisos para acceder a esa sección.</div>
<?php endif; ?>
