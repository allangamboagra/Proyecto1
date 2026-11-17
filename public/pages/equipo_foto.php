<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/equipos.php';
require_once __DIR__ . '/../../includes/imagenes.php';
require_once __DIR__ . '/../../includes/recientes.php';

requireRole(['admin', 'operador']);

$esAdmin = $_SESSION['rol'] === 'admin';

$id = $_GET['id'] ?? '';

if (!ctype_digit((string) $id)) {
    header('Location: /pages/equipos.php?error=no_encontrado');
    exit;
}

$equipo = findEquipo($conn, (int) $id);

if ($equipo === false) {
    header('Location: /pages/equipos.php?error=no_encontrado');
    exit;
}

if (!$esAdmin) {
    registrarEquipoReciente((int) $equipo['id']);
}

$fotos = getImagenesEquipo($conn, (int) $equipo['id']);

$titulo = 'Fotos del equipo';
require __DIR__ . '/common/header.php';
?>
<div class="app">
  <?php require __DIR__ . '/common/sidebar.php'; ?>

  <main class="contenido">
    <h1><?= e($equipo['nombre']) ?> <small class="text-muted">(<?= e($equipo['codigo']) ?>)</small></h1>
    <?php renderAlerta(); ?>

    <div class="row">
      <div class="col-md-8 mb-4">
        <div class="card card-body">
          <?php if (count($fotos) > 0): ?>
            <div class="row">
              <?php foreach ($fotos as $foto): ?>
                <div class="col-sm-6 mb-3">
                  <img src="/pages/equipo_imagen.php?id=<?= (int) $foto['id'] ?>" alt="Foto del equipo" class="img-fluid mb-2">
                  <?php if ($esAdmin): ?>
                    <form action="/actions/equipo_foto_eliminar.php" method="POST" onsubmit="return confirm('¿Está seguro de que desea eliminar esta foto?');">
                      <?= campoCsrf() ?>
                      <input type="hidden" name="equipo_id" value="<?= (int) $equipo['id'] ?>">
                      <input type="hidden" name="imagen_id" value="<?= (int) $foto['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar foto</button>
                    </form>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p class="text-muted mb-0">Este equipo todavía no tiene fotos.</p>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($esAdmin): ?>
        <div class="col-md-4 mb-4">
          <div class="card card-body">
            <form action="/actions/equipo_foto_guardar.php" method="POST" enctype="multipart/form-data">
              <?= campoCsrf() ?>
              <input type="hidden" name="id" value="<?= (int) $equipo['id'] ?>">

              <div class="form-group">
                <label for="fotos">Agregar fotos</label>
                <input type="file" class="form-control-file" id="fotos" name="fotos[]" accept="image/jpeg,image/png,image/webp" multiple required>
                <small class="form-text text-muted">
                  JPG, PNG o WEBP, de 2 MB o menos cada una. Máximo <?= MAX_FOTOS_EQUIPO ?> fotos por equipo
                  (tiene <?= count($fotos) ?>).
                </small>
              </div>

              <button type="submit" class="btn btn-verde">Subir fotos</button>
            </form>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <a href="/pages/equipos.php" class="btn btn-link px-0">Volver a equipos</a>
  </main>
</div>
<?php require __DIR__ . '/common/footer.php'; ?>
