<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/categorias.php';

requireRole(['admin']);

$busqueda = busquedaDesdeUrl();
$resultado = paginar(getCategorias($conn, $busqueda));

$titulo = 'Categorías';
require __DIR__ . '/common/header.php';
?>
<div class="app">
  <?php require __DIR__ . '/common/sidebar.php'; ?>

  <main class="contenido">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="mb-0">Categorías</h1>
      <?php renderBuscador($busqueda, 'Buscar'); ?>
      <a href="/pages/categoria_form.php" class="btn btn-verde">Nueva categoría</a>
    </div>

    <?php renderAlerta(); ?>

    <div class="table-responsive">
      <table class="table table-bordered table-striped">
        <thead class="thead-verde">
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Días máximos de préstamo</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resultado['filas'] as $categoria): ?>
            <tr>
              <td><?= (int) $categoria['id'] ?></td>
              <td><?= e($categoria['nombre']) ?></td>
              <td><?= (int) $categoria['dias_max_prestamo'] ?></td>
              <td class="text-nowrap">
                <a href="/pages/categoria_form.php?id=<?= (int) $categoria['id'] ?>" class="btn btn-sm btn-outline-secondary">Editar</a>
                <form action="/actions/categoria_eliminar.php" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de que desea eliminar esta categoría?');">
                  <?= campoCsrf() ?>
                  <input type="hidden" name="id" value="<?= (int) $categoria['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if ($resultado['total'] === 0): ?>
            <tr><td colspan="4" class="text-center text-muted"><?= $busqueda !== '' ? 'No se encontraron resultados.' : 'No hay categorías registradas.' ?></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php renderPaginacion($resultado); ?>
  </main>
</div>
<?php require __DIR__ . '/common/footer.php'; ?>
