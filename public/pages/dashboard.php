<?php
$titulo = 'Panel';

require_once 'common/header.php';
?>
<body class="d-flex flex-column min-vh-100">

  <style>
    .sidebar {
      position: fixed;
      top: 56px;
      left: 0;
      bottom: 0;
      width: 240px;
      overflow-y: auto;
    }
    .main-content {
      margin-top: 56px;
      margin-left: 240px;
    }
  </style>

  <!-- Barra superior -->
  <nav class="navbar navbar-dark bg-dark fixed-top px-3">
    <span class="navbar-brand mb-0 h5">Prestamo de Dispositivos Institucionales</span>

    <div class="ml-auto">
      <a class="btn btn-link text-light text-decoration-none" href="#">Usuario</a>
      <a class="btn btn-link text-light text-decoration-none" href="../index.php">Cerrar sesión</a>
    </div>
  </nav>

  <!-- Menú lateral -->
  <aside class="sidebar bg-white border-right">
    <nav class="nav flex-column py-2">
      <a class="nav-link text-dark" href="#">Usuarios</a>
      <a class="nav-link text-dark" href="#">Categorías</a>
      <a class="nav-link text-dark" href="#">Equipos</a>
      <a class="nav-link text-dark" href="#">Reportes</a>
      <a class="nav-link text-dark" href="#">Préstamos</a>
    </nav>
  </aside>

  <!-- Contenido -->
  <main class="main-content flex-grow-1 p-4">
  </main>

<?php require_once 'common/footer.php'; ?>
