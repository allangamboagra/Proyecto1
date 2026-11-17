<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/usuarios.php';
require_once __DIR__ . '/../../includes/prestamos.php';
require_once __DIR__ . '/../../includes/configuracion.php';

requireRole(['operador']);

$usuarios = getUsuariosActivos($conn);
$equipos = getEquiposDisponibles($conn);

$diasAnticipacion = (int) (getConfiguracion($conn, 'anticipacion_max_dias') ?? 0);
$hoy = new DateTimeImmutable('today');
$fechaMinima = $hoy->format('Y-m-d');
$fechaMaxima = $hoy->modify('+' . $diasAnticipacion . ' days')->format('Y-m-d');

$titulo = 'Nuevo préstamo';
require __DIR__ . '/common/header.php';
?>
<div class="app">
  <?php require __DIR__ . '/common/sidebar.php'; ?>

  <main class="contenido">
    <h1>Nuevo préstamo</h1>
    <?php renderAlerta(); ?>

    <form action="/actions/prestamo_guardar.php" method="POST">
      <?= campoCsrf() ?>
      <div class="card card-body mb-4" style="max-width: 640px;">
        <div class="form-group">
          <label for="usuario_id">Solicitante</label>
          <select class="form-control" id="usuario_id" name="usuario_id" required>
            <option value="" disabled selected>Seleccione un usuario</option>
            <?php foreach ($usuarios as $usuario): ?>
              <option value="<?= (int) $usuario['id'] ?>"><?= e($usuario['nombre']) ?> (<?= e($usuario['correo']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="fecha_inicio">Fecha de inicio</label>
            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" min="<?= e($fechaMinima) ?>" max="<?= e($fechaMaxima) ?>" required>
          </div>
          <div class="form-group col-md-6">
            <label for="fecha_fin">Fecha de fin</label>
            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" min="<?= e($fechaMinima) ?>" required>
          </div>
        </div>
        <small class="text-muted">Puede iniciar un préstamo hasta <?= $diasAnticipacion ?> días desde hoy.</small>
      </div>

      <h2 class="h5 mb-3">Equipos disponibles</h2>
      <div class="table-responsive mb-3">
        <table class="table table-bordered table-striped">
          <thead class="thead-verde">
            <tr>
              <th style="width: 60px;">Elegir</th>
              <th>Código</th>
              <th>Nombre</th>
              <th>Categoría</th>
              <th>Días máximos</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($equipos as $equipo): ?>
              <tr>
                <td class="text-center"><input type="checkbox" name="equipos[]" value="<?= (int) $equipo['id'] ?>"></td>
                <td><?= e($equipo['codigo']) ?></td>
                <td><?= e($equipo['nombre']) ?></td>
                <td><?= e($equipo['categoria']) ?></td>
                <td><?= (int) $equipo['dias_max_prestamo'] ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($equipos)): ?>
              <tr><td colspan="5" class="text-center text-muted">No hay equipos disponibles.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <button type="submit" class="btn btn-verde">Registrar préstamo</button>
      <a href="/pages/dashboard.php" class="btn btn-link">Cancelar</a>
    </form>
  </main>
</div>
<?php require __DIR__ . '/common/footer.php'; ?>
