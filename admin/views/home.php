<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
	<meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Cross C.T | Home</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
	<link rel="stylesheet" href="/ctt/css/sidebar.css">
	<link href="/ctt/plugins/overlayscrollbars/css/overlayscrollbars.min.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="/ctt/plugins/phosphor-icons/src/regular/style.css" />
	<link rel="stylesheet" type="text/css"href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css" />
	<link rel="stylesheet" href="/ctt/plugins/bootstrap-icons/font/bootstrap-icons.min.css">
	<link rel="stylesheet" href="/ctt/plugins/fontawesome/css/all.min.css">
	<link href="/ctt/plugins/fonts/manrope.css" rel="stylesheet">
	<script src="/ctt/plugins/sweetalert2/sweetalert2.all.min.js"></script>
	<link rel="stylesheet" href="/ctt/plugins/datatables/css/dataTables.bootstrap5.min.css" />
	<link rel="stylesheet" href="/ctt/plugins/datatables/css/responsive.bootstrap5.min.css" />
	<link href="/ctt/plugins/select2/css/select2.min.css" rel="stylesheet" />
	<link href="/ctt/plugins/select2/css/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>

<body class="d-flex flex-column min-vh-100">

	<?php include __DIR__ . "../partials/sidebar.php"; ?>
	<?php include __DIR__ . "../partials/header.php"; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 d-flex flex-column flex-fill">
    <h1 class="h4 mb-4">Página Inicial</h1>

    <div class="row mb-4">
      <div class="col-md-3 mb-3">
        <div class="card bg-primary border-0 text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="card-title">R$ 0.00</h4>
                <p class="card-text">Receita do Mês</p>
              </div>
              <div class="align-self-center">
                <i class="ph ph-currency-dollar fs-1"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3 mb-3">
        <div class="card bg-success border-0 text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="card-title">0</h4>
                <p class="card-text">Alunos Ativos</p>
              </div>
              <div class="align-self-center">
                <i class="ph ph-users fs-1"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3 mb-3">
        <div class="card bg-warning border-0 text-dark">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="card-title">0</h4>
                <p class="card-text">Pagamentos Atrasados</p>
              </div>
              <div class="align-self-center">
                <i class="ph ph-clock fs-1"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-md-3 mb-3">
        <div class="card bg-info border-0 text-white">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <div>
                <h4 class="card-title">0</h4>
                <p class="card-text">Pagamentos Pendente</p>
              </div>
              <div class="align-self-center">
                <i class="ph ph-clipboard-text fs-1"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

      <!-- Nova seção: Calendário -->
      <div class="row mt-4">
        <div class="col-md-8">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0">Calendário de Treinos</h5>
            </div>
            <div class="card-body">
              <div id="calendar" style="max-height: 400px;"></div>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0">Próximos Treinos</h5>
            </div>
            <div class="card-body">
              <ul class="list-group list-group-flush" id="eventList">
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

	<?php include __DIR__ . "../partials/footer.php"; ?>

	<script src="/ctt/plugins/jquery/jquery-3.7.1.min.js"></script>
	<script src="/ctt/plugins/popper/popper.min.js"></script>
  <script src="/ctt/plugins/bootstrap/js/bootstrap.min.js"></script>
	<script defer src="/ctt/plugins/overlayscrollbars/js/overlayscrollbars.browser.es6.min.js"></script>
  <script src="/ctt/plugins/fullcalendar/index.global.min.js"></script>
	<script src="/ctt/js/admin/sidebar.js"></script>
</body>
</html>
