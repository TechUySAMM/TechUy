<?php
/* ============================================================
   includes/form_servicio.php  (presentacion)
   Formulario compartido por alta_servicio.php y
   editar_servicio.php. Usa: $servicio (valores).
   ============================================================ */
?>
<form method="post" class="card card-body" novalidate>
    <?= csrf_campo() ?>
    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre del servicio *</label>
        <input type="text" id="nombre" name="nombre" class="form-control" maxlength="100" required value="<?= e($servicio['nombre']) ?>">
        <div class="form-text">No puede repetirse (se compara sin distinguir mayúsculas ni tildes).</div>
    </div>
    <div class="mb-3">
        <label for="descripcion" class="form-label">Descripción</label>
        <textarea id="descripcion" name="descripcion" class="form-control" rows="3" maxlength="1000"><?= e($servicio['descripcion']) ?></textarea>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="servicios.php" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
