<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
	<meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Cross C.T | Home</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
	<link rel="stylesheet" href="/ctt/css/sidebar.css">
	<link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
	<link rel="stylesheet" type="text/css"href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css" />
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
	<script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" />
	<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.7/css/responsive.bootstrap5.min.css" />
	<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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

	<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
	<script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
	<script src="/ctt/js/admin/sidebar.js"></script>
</body>
</html>