<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <title>Painel do Aluno</title>
</head>
<body>
    <div class="d-flex vh-100">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <img src="<?= PUBLIC_URL ?>img/logo.png" alt="Cross C.T">
                <span>Cross C.T</span>
            </div>

            <nav class="d-flex flex-column gap-1">
                <a href="#" class="sidebar-link active"><i class="bi bi-person me-2"></i><span>Dados Pessoais</span></a>
                <a href="#" class="sidebar-link"><i class="bi bi-card-checklist me-2"></i><span>Matrícula</span></a>
                <a href="#" class="sidebar-link"><i class="bi bi-clipboard-data me-2"></i><span>Avaliações</span></a>
                <a href="#" class="sidebar-link"><i class="bi bi-clock me-2"></i><span>Horários</span></a>
                <a href="#" class="sidebar-link"><i class="bi bi-wallet2 me-2"></i><span>Mensalidade</span></a>
            </nav>
        </aside>

        <div class="d-flex flex-column flex-grow-1 min-width-0">
            <header class="topbar d-flex align-items-center justify-content-between px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn-menu d-lg-none" id="btnMenu">
                        <i class="bi bi-list"></i>
                    </button>
                    <span class="topbar-welcome">Olá, Nome do Usuário</span>
                </div>
                <a href="/ctt/login" class="btn btn-danger btn-sm fw-semibold">Sair</a>
            </header>

            <main class="p-4 flex-grow-1">
                <h2 class="content-title">Dados Pessoais</h2>

                <div class="card">
                    <div class="card-header d-flex align-items-center gap-3">
                        <div class="avatar">CS</div>
                        <div>
                            <p class="avatar-name mb-0">Nome do Usuário</p>
                            <p class="avatar-status mb-0">Aluno ativo</p>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="card-row">
                            <span class="card-label">Nome completo</span>
                            <span class="card-value">Nome do usuário</span>
                        </div>
                        <div class="card-row">
                            <span class="card-label">E-mail</span>
                            <span class="card-value">jhon.doe@email.com</span>
                        </div>
                        <div class="card-row">
                            <span class="card-label">Telefone</span>
                            <span class="card-value">(11) 98765-4321</span>
                        </div>
                        <div class="card-row">
                            <span class="card-label">Data de nascimento</span>
                            <span class="card-value">12/03/1992</span>
                        </div>
                        <div class="card-row">
                            <span class="card-label">CPF</span>
                            <span class="card-value">123.456.789-00</span>
                        </div>
                        <div class="card-row">
                            <span class="card-label">Endereço</span>
                            <span class="card-value">Rua das Flores, 142 — São Paulo/SP</span>
                        </div>
                        <div class="card-row">
                            <span class="card-label">Contato de emergência</span>
                            <span class="card-value">Maria Silva — (11) 91234-5678</span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="sidebar-overlay d-none" id="sidebarOverlay"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= PUBLIC_URL ?>sidebar.js"></script>
</body>
</html>
