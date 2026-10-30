<?php
/* ============================================================
   admin/alta_encuesta.php
   Crea una encuesta (queda en borrador) con sus preguntas.
   Encuesta + preguntas se guardan en una sola transaccion.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
requerir_admin();

const TIPOS_PREGUNTA = ['escala_1_5' => 'Valoración 1 a 5', 'si_no' => 'Sí / No', 'texto_libre' => 'Texto breve'];

$errores  = [];
$encuesta = ['nombre' => '', 'descripcion' => '', 'id_especialidad' => 0, 'obligatoria' => 0];
$preguntas = [['texto' => '', 'tipo' => 'escala_1_5', 'obligatoria' => 1]];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $encuesta = [
        'nombre'          => trim($_POST['nombre'] ?? ''),
        'descripcion'     => trim($_POST['descripcion'] ?? ''),
        'id_especialidad' => filter_input(INPUT_POST, 'id_especialidad', FILTER_VALIDATE_INT) ?: 0,
        'obligatoria'     => isset($_POST['obligatoria']) ? 1 : 0,
    ];
    if (mb_strlen($encuesta['nombre']) < 3 || mb_strlen($encuesta['nombre']) > 150) {
        $errores[] = 'El título debe tener entre 3 y 150 caracteres.';
    }
    if ($encuesta['id_especialidad'] !== 0) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM especialidad WHERE id_especialidad = :id AND activo = 1');
        $stmt->execute([':id' => $encuesta['id_especialidad']]);
        if ((int) $stmt->fetchColumn() === 0) {
            $errores[] = 'El servicio seleccionado no es válido.';
        }
    }

    // Preguntas: se descartan las filas vacias
    $preguntas = [];
    $textos = $_POST['pregunta_texto'] ?? [];
    foreach ((array) $textos as $i => $texto) {
        $texto = trim((string) $texto);
        if ($texto === '') {
            continue;
        }
        $tipo = $_POST['pregunta_tipo'][$i] ?? '';
        if (!isset(TIPOS_PREGUNTA[$tipo])) {
            $errores[] = 'Tipo de pregunta no válido.';
            break;
        }
        $preguntas[] = ['texto' => mb_substr($texto, 0, 255), 'tipo' => $tipo, 'obligatoria' => isset($_POST['pregunta_obligatoria'][$i]) ? 1 : 0];
    }
    if (count($preguntas) === 0) {
        $errores[] = 'Agregue al menos una pregunta.';
        $preguntas = [['texto' => '', 'tipo' => 'escala_1_5', 'obligatoria' => 1]];
    }

    if (empty($errores)) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO encuesta (nombre, descripcion, obligatoria, estado, id_especialidad) VALUES (:n, :d, :o, \'borrador\', :e)')
                ->execute([
                    ':n' => $encuesta['nombre'],
                    ':d' => $encuesta['descripcion'] !== '' ? $encuesta['descripcion'] : null,
                    ':o' => $encuesta['obligatoria'],
                    ':e' => $encuesta['id_especialidad'] ?: null,
                ]);
            $idEncuesta = (int) $pdo->lastInsertId();

            $ins = $pdo->prepare('INSERT INTO pregunta (id_encuesta, n_pregunta, texto, tipo_respuesta, obligatoria) VALUES (:e, :n, :t, :tipo, :o)');
            foreach ($preguntas as $n => $p) {
                $ins->execute([':e' => $idEncuesta, ':n' => $n + 1, ':t' => $p['texto'], ':tipo' => $p['tipo'], ':o' => $p['obligatoria']]);
            }
            auditar($pdo, 'alta', 'encuesta', $idEncuesta, 'Alta de encuesta ' . $encuesta['nombre']);
            $pdo->commit();
            redirigir('/admin/encuestas.php?ok=1');
        } catch (Throwable $ex) {
            $pdo->rollBack();
            throw $ex;
        }
    }
}

$servicios = especialidades_activas($pdo);

$titulo  = 'Nueva encuesta';
$seccion = 'encuestas';
$scripts = ['encuesta_admin.js'];
require dirname(__DIR__) . '/includes/encabezado.php';
?>

<h1 class="h3 mb-3">Nueva encuesta</h1>
<?= lista_errores($errores) ?>

<form method="post" class="card card-body" id="formAltaEncuesta" novalidate>
    <?= csrf_campo() ?>
    <div class="row">
        <div class="col-12 col-md-8 mb-3">
            <label for="nombre" class="form-label">Título *</label>
            <input type="text" id="nombre" name="nombre" class="form-control" maxlength="150" value="<?= e($encuesta['nombre']) ?>" required>
        </div>
        <div class="col-12 col-md-4 mb-3">
            <label for="id_especialidad" class="form-label">Servicio</label>
            <select id="id_especialidad" name="id_especialidad" class="form-select">
                <option value="0">General (todos los servicios)</option>
                <?php foreach ($servicios as $s): ?>
                    <option value="<?= (int) $s['id_especialidad'] ?>" <?= $encuesta['id_especialidad'] === (int) $s['id_especialidad'] ? 'selected' : '' ?>><?= e($s['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="mb-3">
        <label for="descripcion" class="form-label">Descripción</label>
        <textarea id="descripcion" name="descripcion" class="form-control" rows="2"><?= e($encuesta['descripcion']) ?></textarea>
    </div>
    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" id="obligatoria" name="obligatoria" value="1" <?= $encuesta['obligatoria'] ? 'checked' : '' ?>>
        <label class="form-check-label" for="obligatoria">Encuesta obligatoria para los pacientes del programa</label>
    </div>

    <h2 class="h5">Preguntas</h2>
    <div id="listaPreguntas">
        <?php foreach ($preguntas as $i => $p): ?>
            <div class="row g-2 align-items-center mb-2 fila-pregunta">
                <div class="col-12 col-md-7">
                    <input type="text" name="pregunta_texto[<?= $i ?>]" class="form-control" maxlength="255" placeholder="Texto de la pregunta" value="<?= e($p['texto']) ?>" aria-label="Texto de la pregunta">
                </div>
                <div class="col-7 col-md-3">
                    <select name="pregunta_tipo[<?= $i ?>]" class="form-select" aria-label="Tipo de pregunta">
                        <?php foreach (TIPOS_PREGUNTA as $valor => $texto): ?>
                            <option value="<?= $valor ?>" <?= $p['tipo'] === $valor ? 'selected' : '' ?>><?= $texto ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-5 col-md-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="pregunta_obligatoria[<?= $i ?>]" value="1" id="ob<?= $i ?>" <?= $p['obligatoria'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="ob<?= $i ?>">Obligatoria</label>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="mb-4">
        <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarPregunta">+ Agregar pregunta</button>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Guardar como borrador</button>
        <a href="encuestas.php" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

<?php require dirname(__DIR__) . '/includes/pie.php'; ?>
