<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/usuarios.php';

requireRole(['admin']);

$id = $_GET['id'] ?? null;
$editando = $id !== null && ctype_digit((string) $id);

$usuario = ['id' => null, 'nombre' => '', 'correo' => '', 'rol_id' => '', 'activo' => 1];

if ($editando) {
    $encontrado = findUsuario($conn, (int) $id);
    if ($encontrado === false) {
        header('Location: /pages/usuarios.php?error=no_encontrado');
        exit;
    }
    $usuario = $encontrado;
}

$roles = getRoles($conn);

$titulo = $editando ? 'Editar usuario' : 'Nuevo usuario';
require __DIR__ . '/common/header.php';
?>
<div class="app">
  <?php require __DIR__ . '/common/sidebar.php'; ?>

  <main class="contenido">
    <h1><?= e($titulo) ?></h1>
    <?php renderAlerta(); ?>

    <div class="card card-body" style="max-width: 520px;">
      <form action="/actions/usuario_guardar.php" method="POST">
      <?= campoCsrf() ?>
        <?php if ($editando): ?>
          <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label for="nombre">Nombre</label>
          <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" value="<?= e($usuario['nombre']) ?>" required>
        </div>

        <div class="form-group">
          <label for="correo">Correo</label>
          <input type="email" class="form-control" id="correo" name="correo" maxlength="150" value="<?= e($usuario['correo']) ?>" required>
        </div>

        <div class="form-group">
          <label for="rol_id">Rol</label>
          <select class="form-control" id="rol_id" name="rol_id" required>
            <option value="" disabled <?= $usuario['rol_id'] === '' ? 'selected' : '' ?>>Seleccione un rol</option>
            <?php foreach ($roles as $rol): ?>
              <option value="<?= (int) $rol['id'] ?>" <?= (string) $rol['id'] === (string) $usuario['rol_id'] ? 'selected' : '' ?>>
                <?= e(nombreRol($rol['nombre'])) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="password">Contraseña<?= $editando ? ' (déjela vacía para no cambiarla)' : '' ?></label>
          <input type="password" class="form-control" id="password" name="password" minlength="8" <?= $editando ? '' : 'required' ?>>
          <small class="form-text text-muted">Mínimo 8 caracteres.</small>
        </div>

        <div class="form-check mb-3">
          <input type="checkbox" class="form-check-input" id="activo" name="activo" <?= $usuario['activo'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="activo">Activo</label>
        </div>

        <button type="submit" class="btn btn-verde">Guardar</button>
        <a href="/pages/usuarios.php" class="btn btn-link">Cancelar</a>
      </form>
    </div>
  </main>
</div>
<?php require __DIR__ . '/common/footer.php'; ?>
