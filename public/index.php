<?php require_once 'pages/common/header.php'; ?>
<body class="d-flex flex-column min-vh-100" style="background: url('images/login.avif') center / cover no-repeat fixed;">

  <main class="flex-grow-1 d-flex align-items-center">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-4">

          <div class="card shadow">
            <div class="card-body p-4">
              <h3 class="text-center mb-4">Iniciar sesión</h3>

              <form action="pages/dashboard.php">
                <div class="mb-3">
                  <label class="form-label">Usuario</label>
                  <input type="text" class="form-control">
                </div>

                <div class="mb-4">
                  <label class="form-label">Contraseña</label>
                  <input type="password" class="form-control">
                </div>

                <button class="btn btn-primary w-100">Ingresar</button>
              </form>

              <p class="text-center mt-3 mb-0"><a href="#">Registrarse</a></p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </main>

<?php require_once 'pages/common/footer.php'; ?>