<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/categorias.php';

requireRole(['admin']);

$id = $_GET['id'] ?? null;
$editando = $id !== null && ctype_digit((string) $id);

$categoria = ['id' => null, 'nombre' => '', 'dias_max_prestamo' => ''];

if ($editando) {
    $encontrado = findCategoria($conn, (int) $id);
    if ($encontrado === false) {
        header('Location: /pages/categorias.php?error=no_encontrado');
        exit;
    }
    $categoria = $encontrado;
}

$titulo = $editando ? 'Editar categoría' : 'Nueva categoría';
require __DIR__ . '/common/header.php';
?>
<div class="app">
  <?php require __DIR__ . '/common/sidebar.php'; ?>

  <main class="contenido">
    <h1><?= e($titulo) ?></h1>
    <?php renderAlerta(); ?>

    <div class="card card-body" style="max-width: 520px;">
      <form action="/actions/categoria_guardar.php" method="POST">
      <?= campoCsrf() ?>
        <?php if ($editando): ?>
          <input type="hidden" name="id" value="<?= (int) $categoria['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label for="nombre">Nombre</label>
          <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" value="<?= e($categoria['nombre']) ?>" required>
        </div>

        <div class="form-group">
          <label for="dias_max_prestamo">Días máximos de préstamo</label>
          <input type="number" class="form-control" id="dias_max_prestamo" name="dias_max_prestamo" min="1" max="365" value="<?= e($categoria['dias_max_prestamo']) ?>" required>
        </div>

        <button type="submit" class="btn btn-verde">Guardar</button>
        <a href="/pages/categorias.php" class="btn btn-link">Cancelar</a>
      </form>
    </div>
  </main>
</div>
<?php require __DIR__ . '/common/footer.php'; ?>
