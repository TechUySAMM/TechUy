<?php
/* ============================================================
   login.php
   Ingreso del personal. Procesamiento arriba, presentacion abajo.
   - password_verify() contra el hash guardado
   - limite de intentos fallidos por cedula e IP
   - session_regenerate_id() al ingresar (evita fijacion de sesion)
   ============================================================ */

require_once __DIR__ . '/includes/inicio.php';

if (usuario_logueado()) {
    redirigir('/admin/panel.php');
}

$errores = [];
$cedula  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    $cedula     = trim($_POST['cedula'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if (!cedula_valida($cedula) || $contrasena === '') {
        $errores[] = 'Ingrese una cédula de 7 u 8 dígitos y su contraseña.';
    }

    // Cuantos intentos fallidos hubo en los ultimos minutos, por cedula y por IP
    // (la IP tiene un limite mas alto porque en la intranet varios equipos la comparten)
    if (empty($errores)) {
        $stmt = $pdo->prepare(
            'SELECT SUM(cedula = :c) AS por_cedula, COUNT(*) AS por_ip FROM intento_login
             WHERE exitoso = 0 AND (cedula = :c2 OR ip = :ip)
               AND fecha > (NOW() - INTERVAL ' . LOGIN_MINUTOS_BLOQUEO . ' MINUTE)'
        );
        $stmt->execute([':c' => $cedula, ':c2' => $cedula, ':ip' => ip_cliente()]);
        $fallos = $stmt->fetch();
        if ((int) $fallos['por_cedula'] >= LOGIN_MAX_INTENTOS || (int) $fallos['por_ip'] >= LOGIN_MAX_INTENTOS_IP) {
            $errores[] = 'Demasiados intentos fallidos. Espere ' . LOGIN_MINUTOS_BLOQUEO . ' minutos e intente de nuevo.';
        }
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare(
            'SELECT id_usuario, nombre, contrasena_hash, id_rol
             FROM usuario WHERE cedula = :c AND activo = 1'
        );
        $stmt->execute([':c' => $cedula]);
        $usuario = $stmt->fetch();

        $exitoso = $usuario && password_verify($contrasena, $usuario['contrasena_hash']);

        $pdo->prepare('INSERT INTO intento_login (cedula, ip, exitoso) VALUES (:c, :ip, :e)')
            ->execute([':c' => $cedula, ':ip' => ip_cliente(), ':e' => $exitoso ? 1 : 0]);

        if ($exitoso) {
            session_regenerate_id(true);
            $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
            $_SESSION['nombre']     = $usuario['nombre'];
            $_SESSION['id_rol']     = (int) $usuario['id_rol'];
            unset($_SESSION['csrf']); // token nuevo para la sesion autenticada

            $pdo->prepare('UPDATE usuario SET ultimo_acceso = NOW() WHERE id_usuario = :id')
                ->execute([':id' => $usuario['id_usuario']]);
            auditar($pdo, 'login', 'usuario', (int) $usuario['id_usuario'], 'Inicio de sesión');

            redirigir('/admin/panel.php');
        }
        // Mensaje generico: no se revela si la cedula existe
        $errores[] = 'Cédula o contraseña incorrecta.';
    }
}

$avisos = [
    'sesion'  => 'Debe iniciar sesión para acceder a esa página.',
    'salida'  => 'La sesión se cerró correctamente.',
];
$aviso = $avisos[$_GET['error'] ?? ($_GET['ok'] ?? '')] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso de personal - S.I.G.S.M.</title>
    <link rel="stylesheet" href="<?= URL_BASE ?>/public/css/login_sistema.css">
</head>
<body class="pagina-login">

<div class="login-wrapper">
    <div class="login-left">
        <div class="login-overlay">
            <div class="login-brand">
                <h1>S.I.G.S.M.</h1>
                <h3>Sistema Informático de Gestión de Servicios Médicos</h3>
                <p>Hospital de Clínicas. Gestión digital de documentación para pacientes
                   y encuestas de satisfacción, accesibles mediante un código QR.</p>
            </div>
        </div>
    </div>

    <div class="login-right">
        <div class="login-box">
            <img src="<?= URL_BASE ?>/public/img/logo.jpg" alt="Logo TechUY" class="login-logo">
            <h2>Ingreso de personal</h2>
            <p class="login-sub">Ingrese con su cédula y contraseña</p>

            <form id="formLogin" method="post" action="login.php" novalidate>
                <?= csrf_campo() ?>
                <label for="cedula">Cédula</label>
                <input type="text" id="cedula" name="cedula" inputmode="numeric" maxlength="8"
                       placeholder="Ej: 12345678" value="<?= e($cedula) ?>" autocomplete="username" required>

                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" placeholder="********"
                       autocomplete="current-password" required>

                <p id="mensajeLogin" class="login-mensaje <?= $errores ? 'error' : 'exito' ?>" role="status" aria-live="polite"><?php
                    echo $errores ? e($errores[0]) : e($aviso);
                ?></p>

                <button type="submit">Ingresar</button>
            </form>

            <div class="login-links">
                ¿Es paciente? <a href="<?= URL_BASE ?>/index.php">Ir al portal de pacientes</a>
            </div>
        </div>
    </div>
</div>

<script src="<?= URL_BASE ?>/public/js/validaciones.js"></script>
</body>
</html>
