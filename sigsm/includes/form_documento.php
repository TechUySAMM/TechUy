<?php
/* ============================================================
   includes/form_documento.php  (presentacion)
   Formulario compartido por alta_documento.php y
   editar_documento.php. Usa: $doc (valores), $servicios,
   $esAlta (bool). Conserva lo escrito si hubo errores.
   ============================================================ */
?>
<form id="formDocumento" method="post" enctype="multipart/form-data" class="card card-body" novalidate
      data-pdf-obligatorio="<?= $esAlta ? '1' : '0' ?>">
    <?= csrf_campo() ?>

    <div class="mb-3">
        <label for="titulo" class="form-label">Título *</label>
        <input type="text" id="titulo" name="titulo" class="form-control" maxlength="150" required
               value="<?= e($doc['titulo']) ?>" placeholder="Ej: Dieta para examen de sangre">
    </div>

    <div class="mb-3">
        <label for="descripcion" class="form-label">Descripción <small class="text-muted">(opcional)</small></label>
        <textarea id="descripcion" name="descripcion" class="form-control" rows="2" maxlength="1000"><?= e($doc['descripcion']) ?></textarea>
    </div>

    <div class="row">
        <div class="col-12 col-md-4 mb-3">
            <label for="tipo" class="form-label">Tipo *</label>
            <select id="tipo" name="tipo" class="form-select" required>
                <option value="">Seleccione...</option>
                <option value="indicacion" <?= $doc['tipo'] === 'indicacion' ? 'selected' : '' ?>>Indicación médica</option>
                <option value="informacion" <?= $doc['tipo'] === 'informacion' ? 'selected' : '' ?>>Información general</option>
            </select>
        </div>
        <div class="col-12 col-md-4 mb-3">
            <label for="id_especialidad" class="form-label">Servicio *</label>
            <select id="id_especialidad" name="id_especialidad" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php foreach ($servicios as $s): ?>
                    <option value="<?= (int) $s['id_especialidad'] ?>" <?= (int) $doc['id_especialidad'] === (int) $s['id_especialidad'] ? 'selected' : '' ?>><?= e($s['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-4 mb-3">
            <label for="fecha_emision" class="form-label">Fecha de emisión *</label>
            <input type="date" id="fecha_emision" name="fecha_emision" class="form-control" required value="<?= e($doc['fecha_emision']) ?>">
        </div>
    </div>

    <div class="mb-3">
        <label for="archivo" class="form-label">
            <?= $esAlta ? 'Archivo PDF *' : 'Reemplazar PDF <small class="text-muted">(opcional, deje vacío para conservar el actual)</small>' ?>
        </label>
        <input type="file" id="archivo" name="archivo" class="form-control" accept="application/pdf,.pdf">
        <div class="form-text">Solo PDF, máximo 5 MB.</div>
        <?php if (!$esAlta): ?>
            <div class="form-text">Archivo actual:
                <a href="<?= URL_BASE ?>/ver_documento.php?id=<?= (int) $doc['id_documento'] ?>" target="_blank" rel="noopener"><?= e($doc['ruta_archivo']) ?></a>
            </div>
        <?php endif; ?>
    </div>

    <div class="form-check form-switch mb-4">
        <input class="form-check-input" type="checkbox" role="switch" id="publicado" name="publicado" value="1" <?= $doc['publicado'] ? 'checked' : '' ?>>
        <label class="form-check-label" for="publicado">Publicado (visible para los pacientes). Si se desmarca, queda oculto.</label>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <button type="submit" class="btn btn-primary"><?= $esAlta ? 'Guardar documento' : 'Guardar cambios' ?></button>
        <a href="documentos.php" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
