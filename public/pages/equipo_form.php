<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/equipos.php';
require_once __DIR__ . '/../../includes/categorias.php';

requireRole(['admin']);

$id = $_GET['id'] ?? null;
$editando = $id !== null && ctype_digit((string) $id);

$equipo = ['id' => null, 'codigo' => '', 'nombre' => '', 'categoria_id' => '', 'estado' => 'disponible'];

if ($editando) {
    $encontrado = findEquipo($conn, (int) $id);
    if ($encontrado === false) {
        header('Location: /pages/equipos.php?error=no_encontrado');
        exit;
    }
    $equipo = $encontrado;
}

$categorias = getCategorias($conn);

$estados = [
    'disponible' => 'Disponible',
    'mantenimiento' => 'En mantenimiento',
    'baja' => 'De baja',
];

$titulo = $editando ? 'Editar equipo' : 'Nuevo equipo';
require __DIR__ . '/common/header.php';
?>
<div class="app">
  <?php require __DIR__ . '/common/sidebar.php'; ?>

  <main class="contenido">
    <h1><?= e($titulo) ?></h1>
    <?php renderAlerta(); ?>

    <div class="card card-body" style="max-width: 520px;">
      <form action="/actions/equipo_guardar.php" method="POST">
      <?= campoCsrf() ?>
        <?php if ($editando): ?>
          <input type="hidden" name="id" value="<?= (int) $equipo['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label for="codigo">Código</label>
          <input type="text" class="form-control" id="codigo" name="codigo" maxlength="30" value="<?= e($equipo['codigo']) ?>" required>
        </div>

        <div class="form-group">
          <label for="nombre">Nombre</label>
          <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" value="<?= e($equipo['nombre']) ?>" required>
        </div>

        <div class="form-group">
          <label for="categoria_id">Categoría</label>
          <select class="form-control" id="categoria_id" name="categoria_id" required>
            <option value="" disabled <?= $equipo['categoria_id'] === '' ? 'selected' : '' ?>>Seleccione una categoría</option>
            <?php foreach ($categorias as $categoria): ?>
              <option value="<?= (int) $categoria['id'] ?>" <?= (string) $categoria['id'] === (string) $equipo['categoria_id'] ? 'selected' : '' ?>>
                <?= e($categoria['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="estado">Estado</label>
          <select class="form-control" id="estado" name="estado" required>
            <?php foreach ($estados as $valor => $texto): ?>
              <option value="<?= e($valor) ?>" <?= $equipo['estado'] === $valor ? 'selected' : '' ?>><?= e($texto) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <button type="submit" class="btn btn-verde">Guardar</button>
        <a href="/pages/equipos.php" class="btn btn-link">Cancelar</a>
      </form>
    </div>
  </main>
</div>
<?php require __DIR__ . '/common/footer.php'; ?>
