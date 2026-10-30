<?php
/* ============================================================
   includes/form_usuario.php  (presentacion)
   Formulario compartido por alta_usuario.php y editar_usuario.php
   Usa: $usuario (valores), $roles, $esAlta.
   ============================================================ */
?>
<form id="formUsuario" method="post" class="card card-body" novalidate data-alta="<?= $esAlta ? '1' : '0' ?>">
    <?= csrf_campo() ?>
    <div class="row">
        <div class="col-12 col-md-6 mb-3">
            <label for="nombre" class="form-label">Nombre completo *</label>
            <input type="text" id="nombre" name="nombre" class="form-control" maxlength="100" value="<?= e($usuario['nombre']) ?>" required>
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label for="cedula" class="form-label">Cédula * <small class="text-muted">(sin puntos ni guion)</small></label>
            <input type="text" id="cedula" name="cedula" class="form-control" inputmode="numeric" maxlength="8" value="<?= e($usuario['cedula']) ?>" required>
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label for="email" class="form-label">Email *</label>
            <input type="email" id="email" name="email" class="form-control" maxlength="150" value="<?= e($usuario['email']) ?>" required>
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label for="id_rol" class="form-label">Rol *</label>
            <select id="id_rol" name="id_rol" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= (int) $r['id_rol'] ?>" <?= (int) $usuario['id_rol'] === (int) $r['id_rol'] ? 'selected' : '' ?>><?= e($r['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label for="contrasena" class="form-label">Contraseña <?= $esAlta ? '*' : '<small class="text-muted">(dejar vacío para no cambiarla)</small>' ?></label>
            <input type="password" id="contrasena" name="contrasena" class="form-control" autocomplete="new-password">
            <div class="form-text">Mínimo 8 caracteres.</div>
        </div>
        <div class="col-12 col-md-6 mb-3">
            <label for="repetir" class="form-label">Repetir contraseña</label>
            <input type="password" id="repetir" name="repetir" class="form-control" autocomplete="new-password">
        </div>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary"><?= $esAlta ? 'Crear usuario' : 'Guardar cambios' ?></button>
        <a href="usuarios.php" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>
