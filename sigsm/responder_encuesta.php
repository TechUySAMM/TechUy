<?php
/* ============================================================
   responder_encuesta.php  (publico, anonimo)
   - Muestra las preguntas de una encuesta publicada.
   - Al enviar: valida cada respuesta segun su tipo, comprueba
     que las preguntas pertenezcan a la encuesta y guarda todo
     el envio en UNA transaccion (respuesta + detalles).
   - Token de un solo uso: recargar la pagina no duplica el envio.
   - Campo trampa + tiempo minimo contra envios automatizados.
   - No se guarda ningun dato personal: solo fecha y servicio.
   ============================================================ */

require_once __DIR__ . '/includes/inicio.php';

$idEncuesta = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;

$stmt = $pdo->prepare(
    "SELECT en.id_encuesta, en.nombre, en.descripcion, en.obligatoria, en.id_especialidad, es.nombre AS servicio
     FROM encuesta en
     LEFT JOIN especialidad es ON es.id_especialidad = en.id_especialidad
     WHERE en.id_encuesta = :id AND en.estado = 'publicada' AND en.activo = 1
       AND (en.id_especialidad IS NULL OR es.activo = 1)"
);
$stmt->execute([':id' => $idEncuesta]);
$encuesta = $stmt->fetch();

if (!$encuesta) {
    redirigir('/encuestas.php');
}

$stmt = $pdo->prepare('SELECT n_pregunta, texto, tipo_respuesta, obligatoria FROM pregunta WHERE id_encuesta = :id ORDER BY n_pregunta');
$stmt->execute([':id' => $idEncuesta]);
$preguntas = $stmt->fetchAll();

$errores   = [];
$valores   = $_POST['p'] ?? [];
$servicios = especialidades_activas($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $token = $_POST['token_envio'] ?? '';

    // 1) Proteccion anti-bots: campo trampa vacio y al menos 3 segundos en la pagina
    $inicio = $_SESSION['envios'][$token] ?? null;
    if (($_POST['sitio_web'] ?? '') !== '' || ($inicio !== null && time() - $inicio < 3)) {
        $errores[] = 'No se pudo procesar el envío. Intente nuevamente.';
    }

    // 2) Token de un solo uso (evita duplicar al recargar)
    if ($inicio === null) {
        $errores[] = 'Esta encuesta ya fue enviada o el formulario expiró. Vuelva a abrirla.';
    }

    // 3) Servicio: fijo si la encuesta es de un servicio, elegido si es general
    $idServicio = $encuesta['id_especialidad'];
    if ($idServicio === null) {
        $idServicio = filter_input(INPUT_POST, 'id_especialidad', FILTER_VALIDATE_INT) ?: null;
        $idsValidos = array_map('intval', array_column($servicios, 'id_especialidad'));
        if (!in_array($idServicio, $idsValidos, true)) {
            $errores[] = 'Seleccione el servicio donde recibió asistencia.';
        }
    }

    // 4) Solo se aceptan preguntas de ESTA encuesta, validadas segun su tipo
    $numerosValidos = array_column($preguntas, 'n_pregunta');
    foreach (array_keys($valores) as $n) {
        if (!in_array((int) $n, $numerosValidos, true)) {
            $errores[] = 'Se recibió una pregunta que no corresponde a esta encuesta.';
            break;
        }
    }
    $detalles = [];
    foreach ($preguntas as $p) {
        $v = trim((string) ($valores[$p['n_pregunta']] ?? ''));
        if ($v === '') {
            if ($p['obligatoria']) {
                $errores[] = 'Falta responder: ' . $p['texto'];
            }
            continue;
        }
        $valido = match ($p['tipo_respuesta']) {
            'escala_1_5'  => in_array($v, ['1', '2', '3', '4', '5'], true),
            'si_no'       => in_array($v, ['si', 'no'], true),
            'texto_libre' => mb_strlen($v) <= 500,
        };
        if (!$valido) {
            $errores[] = 'Respuesta no válida en: ' . $p['texto'];
            continue;
        }
        $detalles[(int) $p['n_pregunta']] = $v;
    }

    if (empty($errores)) {
        try {
            $pdo->beginTransaction();

            // Siguiente numero de envio dentro de la encuesta (bloqueando para evitar choques)
            $stmt = $pdo->prepare('SELECT COALESCE(MAX(n_respuesta), 0) + 1 FROM respuesta WHERE id_encuesta = :id FOR UPDATE');
            $stmt->execute([':id' => $idEncuesta]);
            $nRespuesta = (int) $stmt->fetchColumn();

            $pdo->prepare('INSERT INTO respuesta (id_encuesta, n_respuesta, id_especialidad, token_envio) VALUES (:e, :n, :s, :t)')
                ->execute([':e' => $idEncuesta, ':n' => $nRespuesta, ':s' => $idServicio, ':t' => $token]);

            $insDetalle = $pdo->prepare('INSERT INTO detalle (id_encuesta, n_respuesta, n_pregunta, valor) VALUES (:e, :n, :p, :v)');
            foreach ($detalles as $nPregunta => $valor) {
                $insDetalle->execute([':e' => $idEncuesta, ':n' => $nRespuesta, ':p' => $nPregunta, ':v' => $valor]);
            }

            $pdo->commit();
            unset($_SESSION['envios'][$token]);
            redirigir('/encuestas.php?ok=1');
        } catch (PDOException $ex) {
            $pdo->rollBack();
            if ($ex->getCode() === '23000') {
                // token_envio repetido: el mismo envio llego dos veces
                unset($_SESSION['envios'][$token]);
                redirigir('/encuestas.php?ok=1');
            }
            throw $ex;
        }
    }
}

// Token nuevo para este formulario (se guarda la hora para el control anti-bots)
if (empty($token) || !isset($_SESSION['envios'][$token])) {
    $token = bin2hex(random_bytes(16));
    $_SESSION['envios'][$token] = time();
}

$titulo  = $encuesta['nombre'];
$seccion = 'encuestas';
$publico = true;
$scripts = ['validaciones.js'];
require __DIR__ . '/includes/encabezado.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-9 col-lg-7">
        <h1 class="h3"><?= e($encuesta['nombre']) ?></h1>
        <p class="text-muted"><?= e($encuesta['descripcion']) ?></p>

        <div class="alert alert-info" role="note">
            Esta encuesta es <strong>anónima</strong>. Solo se registra la fecha y el servicio.
            <?php if ($encuesta['obligatoria']): ?><br><strong>Obligatoria</strong> para pacientes del programa.<?php endif; ?>
        </div>

        <?= lista_errores($errores) ?>

        <form id="formEncuesta" method="post" class="card card-body" novalidate>
            <?= csrf_campo() ?>
            <input type="hidden" name="token_envio" value="<?= e($token) ?>">
            <div class="campo-trampa" aria-hidden="true">
                <label for="sitio_web">No completar este campo</label>
                <input type="text" id="sitio_web" name="sitio_web" tabindex="-1" autocomplete="off">
            </div>

            <?php if ($encuesta['id_especialidad'] === null): ?>
                <div class="mb-4">
                    <label for="id_especialidad" class="form-label fw-semibold">Servicio donde recibió asistencia *</label>
                    <select id="id_especialidad" name="id_especialidad" class="form-select">
                        <option value="">Seleccione...</option>
                        <?php foreach ($servicios as $s): ?>
                            <option value="<?= (int) $s['id_especialidad'] ?>" <?= (int) ($_POST['id_especialidad'] ?? 0) === (int) $s['id_especialidad'] ? 'selected' : '' ?>><?= e($s['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <p><span class="badge bg-primary">Servicio: <?= e($encuesta['servicio']) ?></span></p>
            <?php endif; ?>

            <?php foreach ($preguntas as $p): $n = (int) $p['n_pregunta']; $actual = (string) ($valores[$n] ?? ''); ?>
                <fieldset class="mb-4" data-pregunta="<?= $n ?>" data-tipo="<?= e($p['tipo_respuesta']) ?>"
                          data-obligatoria="<?= (int) $p['obligatoria'] ?>" data-texto="<?= e($p['texto']) ?>">
                    <legend class="fs-6 fw-semibold"><?= $n ?>. <?= e($p['texto']) ?><?= $p['obligatoria'] ? ' *' : ' <small class="text-muted">(opcional)</small>' ?></legend>

                    <?php if ($p['tipo_respuesta'] === 'escala_1_5'): ?>
                        <div class="btn-group w-100" role="group">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" class="btn-check" name="p[<?= $n ?>]" id="p<?= $n ?>_<?= $i ?>" value="<?= $i ?>" <?= $actual === (string) $i ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary" for="p<?= $n ?>_<?= $i ?>"><?= $i ?></label>
                            <?php endfor; ?>
                        </div>
                        <small class="text-muted">1 = muy insatisfecho &middot; 5 = muy satisfecho</small>
                    <?php elseif ($p['tipo_respuesta'] === 'si_no'): ?>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="p[<?= $n ?>]" id="p<?= $n ?>_si" value="si" <?= $actual === 'si' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary px-4" for="p<?= $n ?>_si">Sí</label>
                            <input type="radio" class="btn-check" name="p[<?= $n ?>]" id="p<?= $n ?>_no" value="no" <?= $actual === 'no' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary px-4" for="p<?= $n ?>_no">No</label>
                        </div>
                    <?php else: ?>
                        <div>
                            <textarea name="p[<?= $n ?>]" class="form-control" rows="3" maxlength="500"><?= e($actual) ?></textarea>
                        </div>
                    <?php endif; ?>
                </fieldset>
            <?php endforeach; ?>

            <p id="mensajeEncuesta" role="status" aria-live="polite"></p>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Enviar encuesta</button>
                <a href="encuestas.php" class="btn btn-outline-secondary">Volver</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/pie.php'; ?>
