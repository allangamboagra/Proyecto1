<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/prestamos.php';

requireLogin();

$esOperador = $_SESSION['rol'] === 'operador';

$busqueda = busquedaDesdeUrl();
$resultado = paginar(getPrestamosActivos($conn, $busqueda));

$etiquetas = [
    'aprobado' => 'Aprobado',
    'entregado' => 'Entregado',
    'devuelto' => 'Devuelto',
    'cancelado' => 'Cancelado',
];

$transiciones = transicionesPermitidas();

$columnas = $esOperador ? 7 : 6;

$titulo = 'Préstamos';
require __DIR__ . '/common/header.php';
?>
<div class="app">
  <?php require __DIR__ . '/common/sidebar.php'; ?>

  <main class="contenido">
    <div class="card card-body mb-4" style="max-width: 640px;">
      <p class="mb-1">Bienvenido, <strong><?= e($_SESSION['nombre']) ?></strong>.</p>
      <p class="mb-0">Tu rol es <?= e(nombreRol($_SESSION['rol'])) ?>. Desde el menú puedes ir a las secciones disponibles para tu rol.</p>
    </div>

    <?php renderAlerta(); ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="mb-0">Préstamos actuales</h1>
      <?php renderBuscador($busqueda, 'Buscar por nombre del solicitante'); ?>
      <?php if ($esOperador): ?>
        <a href="/pages/prestamo_form.php" class="btn btn-verde">Nuevo préstamo</a>
      <?php else: ?>
        <div></div>
      <?php endif; ?>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="thead-verde">
          <tr>
            <th>ID</th>
            <th>Solicitante</th>
            <th>Equipos</th>
            <th>Estado</th>
            <th>Inicio</th>
            <th>Fin</th>
            <?php if ($esOperador): ?><th>Cambiar estado</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resultado['filas'] as $prestamo): ?>
            <tr>
              <td><?= (int) $prestamo['id'] ?></td>
              <td><?= e($prestamo['solicitante']) ?></td>
              <td><?= e($prestamo['equipos']) ?></td>
              <td><?= e($prestamo['estado']) ?></td>
              <td><?= e($prestamo['fecha_inicio']) ?></td>
              <td><?= e($prestamo['fecha_fin']) ?></td>
              <?php if ($esOperador): ?>
                <td class="text-nowrap">
                  <?php foreach ($etiquetas as $nuevoEstado => $texto): ?>
                    <form action="/actions/prestamo_estado.php" method="POST" class="d-inline">
                      <?= campoCsrf() ?>
                      <input type="hidden" name="id" value="<?= (int) $prestamo['id'] ?>">
                      <input type="hidden" name="estado" value="<?= e($nuevoEstado) ?>">
                      <button type="submit" class="btn btn-sm btn-outline-secondary" <?= in_array($prestamo['estado'], $transiciones[$nuevoEstado], true) ? '' : 'disabled' ?>><?= e($texto) ?></button>
                    </form>
                  <?php endforeach; ?>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
          <?php if ($resultado['total'] === 0): ?>
            <tr><td colspan="<?= $columnas ?>" class="text-center text-muted"><?= $busqueda !== '' ? 'No se encontraron resultados.' : 'No hay préstamos activos.' ?></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php renderPaginacion($resultado); ?>
  </main>
</div>
<?php require __DIR__ . '/common/footer.php'; ?>
