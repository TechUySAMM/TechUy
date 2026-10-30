<?php
/* ============================================================
   includes/funciones.php
   Funciones de apoyo reutilizadas en todo el sistema:
   escape de HTML, validaciones del servidor, auditoria y
   paginacion.
   ============================================================ */

// Escapa cualquier texto antes de mostrarlo en HTML (evita XSS)
function e($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

function redirigir(string $ruta): void
{
    header('Location: ' . URL_BASE . $ruta);
    exit;
}

function ip_cliente(): string
{
    return substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45);
}

/* ---------- Validaciones del servidor (practico ABM) ---------- */

// Cedula uruguaya: 7 u 8 digitos, sin puntos ni guion
function cedula_valida(string $cedula): bool
{
    return preg_match('/^\d{7,8}$/', $cedula) === 1;
}

// Fecha real en formato AAAA-MM-DD (2026-02-30 no existe)
function fecha_valida(string $fecha): bool
{
    $partes = explode('-', $fecha);
    if (count($partes) !== 3 || !ctype_digit(implode('', $partes))) {
        return false;
    }
    [$anio, $mes, $dia] = array_map('intval', $partes);
    return checkdate($mes, $dia, $anio);
}

// La ruta debe empezar con documentos/ y terminar en .pdf
function ruta_pdf_valida(string $ruta): bool
{
    return preg_match('#^documentos/[a-z0-9_\-]+\.pdf$#', $ruta) === 1;
}

function texto_tipo(string $tipo): string
{
    return $tipo === 'indicacion' ? 'Indicación médica' : 'Información general';
}

function texto_estado_encuesta(string $estado): string
{
    return ['borrador' => 'Borrador', 'publicada' => 'Publicada', 'cerrada' => 'Cerrada'][$estado] ?? $estado;
}

/* ---------- Subida de PDF ---------- */

/**
 * Valida el archivo subido (tipo real y tamano) y lo guarda en
 * /documentos con un nombre unico. Devuelve la ruta relativa
 * (documentos/xxx.pdf) o agrega el error al arreglo $errores.
 */
function guardar_pdf(array $archivo, string $tipo, array &$errores): ?string
{
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        $errores[] = 'Debe seleccionar un archivo PDF.';
        return null;
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        $errores[] = 'No se pudo subir el archivo (código ' . (int) $archivo['error'] . ').';
        return null;
    }
    if ($archivo['size'] > PDF_TAMANO_MAX) {
        $errores[] = 'El PDF no puede superar los 5 MB.';
        return null;
    }
    // Se revisa el tipo real del contenido, no solo la extension
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
    if ($mime !== 'application/pdf' || $extension !== 'pdf') {
        $errores[] = 'El archivo debe ser un PDF válido.';
        return null;
    }

    $nombre = $tipo . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.pdf';
    if (!move_uploaded_file($archivo['tmp_name'], RUTA_DOCUMENTOS . '/' . $nombre)) {
        registrar_error('No se pudo mover el PDF a ' . RUTA_DOCUMENTOS);
        $errores[] = 'No se pudo guardar el archivo en el servidor.';
        return null;
    }
    return 'documentos/' . $nombre;
}

/* ---------- Auditoria ---------- */

function auditar(PDO $pdo, string $accion, string $tabla, ?int $idRegistro, string $detalle = ''): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO auditoria (id_usuario, accion, tabla_afectada, id_registro, detalle, ip)
         VALUES (:u, :a, :t, :r, :d, :ip)'
    );
    $stmt->execute([
        ':u'  => $_SESSION['id_usuario'] ?? null,
        ':a'  => $accion,
        ':t'  => $tabla,
        ':r'  => $idRegistro,
        ':d'  => mb_substr($detalle, 0, 255),
        ':ip' => ip_cliente(),
    ]);
}

/* ---------- Datos de uso comun ---------- */

function especialidades_activas(PDO $pdo): array
{
    return $pdo->query('SELECT id_especialidad, nombre FROM especialidad WHERE activo = 1 ORDER BY nombre')->fetchAll();
}

/* ---------- Paginacion ---------- */

function pagina_actual(): int
{
    $p = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
    return ($p && $p > 0) ? $p : 1;
}

// Dibuja los botones de paginacion conservando los filtros del GET
function paginacion(int $total, int $pagina): string
{
    $paginas = (int) ceil($total / POR_PAGINA);
    if ($paginas <= 1) {
        return '';
    }
    $html = '<nav aria-label="Paginación"><ul class="pagination justify-content-center flex-wrap">';
    for ($i = 1; $i <= $paginas; $i++) {
        $query = http_build_query(array_merge($_GET, ['pagina' => $i]));
        $activa = $i === $pagina ? ' active" aria-current="page' : '';
        $html .= '<li class="page-item' . $activa . '"><a class="page-link" href="?' . e($query) . '">' . $i . '</a></li>';
    }
    return $html . '</ul></nav>';
}

// Mensaje de exito segun ?ok=N (patron del ABM)
function mensaje_ok(array $mensajes): string
{
    $ok = $_GET['ok'] ?? '';
    if (!isset($mensajes[$ok])) {
        return '';
    }
    return '<div class="alert alert-success alert-dismissible fade show" role="alert">' . e($mensajes[$ok])
         . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>';
}

function lista_errores(array $errores): string
{
    if (empty($errores)) {
        return '';
    }
    $html = '<div class="alert alert-danger" role="alert"><ul class="mb-0">';
    foreach ($errores as $error) {
        $html .= '<li>' . e($error) . '</li>';
    }
    return $html . '</ul></div>';
}
