<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/dashboard-aluno.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Painel do Aluno</title>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar">
            <div class="sidebar-logo">
                <img src="<?= PUBLIC_URL ?>img/logo.png" alt="Cross C.T">
                <span>Cross C.T</span>
            </div>

            <nav class="sidebar-nav">
                <a href="#" class="sidebar-link active">Dados Pessoais</a>
                <a href="#" class="sidebar-link">Matrícula</a>
                <a href="#" class="sidebar-link">Avaliações</a>
                <a href="#" class="sidebar-link">Horários</a>
                <a href="#" class="sidebar-link">Mensalidade</a>
            </nav>
        </aside>

        <div class="dashboard-content">
            <header class="topbar">
                <span class="topbar-welcome">Olá, Nome do Usuário</span>
                <a href="/ctt/loginuser" class="topbar-logout">Sair</a>
            </header>
            
            <main class="content">
                <h2 class="content-title">Dados Pessoais</h2>

                <div class="card">
                    <div class="card-header">
                        <div class="avatar">CS</div>
                        <div>
                            <p class="avatar-name">Nome do Usuário</p>
                            <p class="avatar-status">Aluno ativo</p>
                        </div>
                    </div>

                    <div class="card-rows">
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
    <script>
        document.querySelector('.sidebar-logo').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('collapsed');
        });
    </script>
</body>
</html>