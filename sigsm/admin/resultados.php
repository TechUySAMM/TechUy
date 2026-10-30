<?php
/* ============================================================
   admin/resultados.php
   Estadisticas de una encuesta: cantidad de respuestas,
   promedio, cantidad y porcentaje por opcion (con grafico de
   barras), respuestas de texto. Filtros por encuesta, servicio
   y rango de fechas. Exportacion a CSV. Solo administrador.
   ============================================================ */

require_once dirname(__DIR__) . '/includes/inicio.php';
require_once dirname(__DIR__) . '/includes/filtros_resultados.php';
requerir_admin();

$encuestas = $pdo->query("SELECT id_encuesta, nombre, estado FROM encuesta WHERE activo = 1 AND estado <> 'borrador' ORDER BY nombre")->fetchAll();
$servicios = $pdo->query('SELECT id_especialidad, nombre FROM especialidad ORDER BY nombre')->fetchAll();

$f = leer_filtros_resultados();
if (!$f['encuesta'] && $encuestas) {
    $f['encuesta'] = (int) $encuestas[0]['id_encuesta'];
}
[$where, $params] = where_resultados($f);

// Total de envios con los filtros aplicados
$stmt = $pdo->prepare("SELECT COUNT(*) FROM respuesta r WHERE $where");
$stmt->execute($params);
$totalEnvios = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT n_pregunta, texto, tipo_respuesta FROM pregunta WHERE id_encuesta = :id ORDER BY n_pregunta');
$stmt->execute([':id' => $f['encuesta']]);
$preguntas = $stmt->fetchAll();

// Conteo por valor de cada pregunta (un solo GROUP BY para toda la encuesta)
$stmt = $pdo->prepare(
    "SELECT d.n_pregunta, d.valor, COUNT(*) AS cantidad
     FROM detalle d
     JOIN respuesta r ON r.id_encuesta = d.id_encuesta AND r.n_respuesta = d.n_respuesta
     JOIN pregunta p ON p.id_encuesta = d.id_encuesta AND p.n_pregunta = d.n_pregunta
     WHERE $where AND p.tipo_respuesta <> 'texto_libre'
     GROUP BY d.n_pregunta, d.valor"
);
$stmt->execute($params);
$conteos = [];
foreach ($stmt->fetchAll() as $fila) {
    $conteos[(int) $fila['n_pregunta']][$fila['valor']] = (int) $fila['cantidad'];
}

// Respuestas de texto (las mas recientes primero)
$stmt = $pdo->prepare(
    "SELECT d.n_pregunta, d.valor, r.fecha
     FROM detalle d
     JOIN respuesta r ON r.id_encuesta = d.id_encuesta AND r.n_respuesta = d.n_respuesta
     JOIN pregunta p ON p.id_encuesta = d.id_encuesta AND p.n_pregunta = d.n_pregunta
     WHERE $where AND p.tipo_respuesta = 'texto_libre'
     ORDER BY r.fecha DESC"
);
$stmt->execute($params);
$textos = [];
foreach ($stmt->fetchAll() as $fila) {
    $textos[(int) $fila['n_pregunta']][] = $fila;
}

$titulo  = 'Resultados de encuestas';
$seccion = 'encuestas';
require dirname(__DIR__) . '/includes/encabezado.php';
?>

<h1 class="h3 mb-3">Resultados de encuestas</h1>

<form method="get" class="card card-body mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-lg-4">
            <label for="encuesta" class="form-label">Encuesta</label>
            <select id="encuesta" name="encuesta" class="form-select">
                <?php foreach ($encuestas as $en): ?>
                    <option value="<?= (int) $en['id_encuesta'] ?>" <?= $f['encuesta'] === (int) $en['id_encuesta'] ? 'selected' : '' ?>>
                        <?= e($en['nombre']) ?> (<?= e(texto_estado_encuesta($en['estado'])) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <label for="servicio" class="form-label">Servicio</label>
            <select id="servicio" name="servicio" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($servicios as $s): ?>
                    <option value="<?= (int) $s['id_especialidad'] ?>" <?= $f['servicio'] === (int) $s['id_especialidad'] ? 'selected' : '' ?>><?= e($s['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-6 col-md-2 col-lg-2">
            <label for="desde" class="form-label">Desde</label>
            <input type="date" id="desde" name="desde" class="form-control" value="<?= e($f['desde']) ?>">
        </div>
        <div class="col-6 col-md-2 col-lg-2">
            <label for="hasta" class="form-label">Hasta</label>
            <input type="date" id="hasta" name="hasta" class="form-control" value="<?= e($f['hasta']) ?>">
        </div>
        <div class="col-12 col-md-2 col-lg-1 d-grid"><button class="btn btn-primary">Ver</button></div>
    </div>
</form>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div class="card indicador px-4 py-2">
        <div class="numero"><?= $totalEnvios ?></div>
        <div class="text-muted small">respuestas con estos filtros</div>
    </div>
    <a class="btn btn-outline-primary" href="exportar_csv.php?<?= e(http_build_query($f)) ?>">Exportar a CSV</a>
</div>

<?php foreach ($preguntas as $p): $n = (int) $p['n_pregunta']; ?>
    <div class="card mb-3">
        <div class="card-header"><?= $n ?>. <?= e($p['texto']) ?></div>
        <div class="card-body">
        <?php if ($p['tipo_respuesta'] === 'texto_libre'): ?>
            <?php if (empty($textos[$n])): ?>
                <p class="text-muted mb-0">Sin comentarios.</p>
            <?php else: ?>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($textos[$n] as $t): ?>
                        <li class="border-bottom py-2"><small class="text-muted"><?= e(date('d/m/Y', strtotime($t['fecha']))) ?></small> &middot; <?= e($t['valor']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php else:
            $opciones = $p['tipo_respuesta'] === 'escala_1_5' ? ['1', '2', '3', '4', '5'] : ['si', 'no'];
            $cuenta = $conteos[$n] ?? [];
            $totalPregunta = array_sum($cuenta);
            $suma = 0;
            foreach ($cuenta as $valor => $c) { $suma += is_numeric($valor) ? (int) $valor * $c : 0; }
        ?>
            <?php if ($p['tipo_respuesta'] === 'escala_1_5'): ?>
                <p class="mb-2"><strong>Promedio:</strong> <?= $totalPregunta ? number_format($suma / $totalPregunta, 2, ',', '') : '-' ?> / 5
                   &middot; <strong>Respuestas:</strong> <?= $totalPregunta ?></p>
            <?php else: ?>
                <p class="mb-2"><strong>Respuestas:</strong> <?= $totalPregunta ?></p>
            <?php endif; ?>
            <?php foreach ($opciones as $op):
                $c = $cuenta[$op] ?? 0;
                $porc = $totalPregunta ? round($c * 100 / $totalPregunta, 1) : 0; ?>
                <div class="row align-items-center g-2 mb-1">
                    <div class="col-3 col-md-1 text-end"><?= $op === 'si' ? 'Sí' : ($op === 'no' ? 'No' : e($op)) ?></div>
                    <div class="col-6 col-md-9"><div class="barra-opcion" role="img" aria-label="<?= $porc ?>%"><span style="width: <?= $porc ?>%"></span></div></div>
                    <div class="col-3 col-md-2 small"><?= $c ?> (<?= $porc ?>%)</div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>

<?php require dirname(__DIR__) . '/includes/pie.php'; ?>
