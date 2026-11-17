<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/configuracion.php';

requireRole(['admin']);

$diasAnticipacion = getConfiguracion($conn, 'anticipacion_max_dias') ?? '';

$titulo = 'Configurar días';
require __DIR__ . '/common/header.php';
?>
<div class="app">
  <?php require __DIR__ . '/common/sidebar.php'; ?>

  <main class="contenido">
    <h1>Configurar días</h1>
    <?php renderAlerta(); ?>

    <div class="card card-body" style="max-width: 520px;">
      <form action="/actions/configuracion_guardar.php" method="POST">
      <?= campoCsrf() ?>
        <div class="form-group">
          <label for="dias_anticipacion">Días de anticipación máxima</label>
          <input type="number" class="form-control" id="dias_anticipacion" name="dias_anticipacion" min="0" max="365" value="<?= e($diasAnticipacion) ?>" required>
          <small class="form-text text-muted">Un préstamo no puede empezar más de esta cantidad de días en el futuro.</small>
        </div>

        <button type="submit" class="btn btn-verde">Guardar</button>
        <a href="/pages/dashboard.php" class="btn btn-link">Cancelar</a>
      </form>
    </div>
  </main>
</div>
<?php require __DIR__ . '/common/footer.php'; ?>
