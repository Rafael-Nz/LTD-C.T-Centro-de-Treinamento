<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T - Centro de Treinamento</title>
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

     <header>
        <nav>
            <div class="nav-logo">
                <img src="<?= PUBLIC_URL ?>img/logo.png" alt="Cross C.T">
                <span>Cross C.T</span>
            </div>

            <ul class="nav-links">
                <li><a href="#">Locais</a></li>
                <li><a href="#">Planos</a></li>
                <li><a href="#">Produtos</a></li>
            </ul>

            <div class="nav-actions">
                <a href="/ctt/admin/login" class="nav-link">Login do Admin</a>
                <a href="#" class="btn-primary">Sign In usuário</a>
            </div>
        </nav>
     </header>

     <section class="hero">

        <div class="hero-text">

            <h1>Seção Boas-Vindas</h1>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                <div class="hero-buttons">
                    <a href="#" class="btn-primary">Lorem ipsum</a>
                    <a href="#" class="btn-secondary">Dolor sit</a>
                </div>

        </div>

        <div class="hero-imagem-ct">
            <img src="<?= PUBLIC_URL ?>img/ct.png" alt="Cross C.T">
        </div>

    </section>

    <section class="beneficios">

        <h2>Seção Benefícios</h2>

        <div class="beneficios-cards">

            <div class="card">
                <div class="card-body">
                    <h3>Lorem ipsum</h3>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut enim ad minim veniam.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3>Dolor sit amet</h3>
                    <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat.</p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3>Consectetur</h3>
                    <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit.</p>
                </div>
            </div>

        </div>

    </section>

    <section class="planos" id="planos">

        <h2>Seção Planos</h2>
        <p class="planos-subtitulo">Lorem ipsum dolor sit amet consectetur adipiscing</p>

        <div class="planos-cards">

            <div class="plano-card">
                <h3>Lorem</h3>
                <div class="plano-preco">R$ 00<span>/mês</span></div>
                <ul class="plano-itens">
                    <li>Lorem ipsum dolor</li>
                    <li>Sit amet consectetur</li>
                    <li>Adipiscing elit sed</li>
                </ul>
                <a href="#planos" class="btn-plano">Lorem ipsum</a>
            </div>

            <div class="plano-card plano-destaque">
                <h3>Ipsum</h3>
                <div class="plano-preco">R$ 00<span>/mês</span></div>
                <ul class="plano-itens">
                    <li>Lorem ipsum dolor</li>
                    <li>Sit amet consectetur</li>
                    <li>Adipiscing elit sed</li>
                    <li>Do eiusmod tempor</li>
                </ul>
                <a href="#planos" class="btn-plano">Lorem ipsum</a>
            </div>

            <div class="plano-card">
                <h3>Dolor</h3>
                <div class="plano-preco">R$ 00<span>/mês</span></div>
                <ul class="plano-itens">
                    <li>Lorem ipsum dolor</li>
                    <li>Sit amet consectetur</li>
                    <li>Adipiscing elit sed</li>
                    <li>Do eiusmod tempor</li>
                </ul>
                <a href="#planos" class="btn-plano">Lorem ipsum</a>
            </div>

        </div>

    </section>

    <section class="secao-final" id="final">
        <h2>Seção Final</h2>
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        <a href="#final" class="btn-primary">Lorem ipsum</a>
    </section>

    <footer>
        <div class="footer-container">

            <nav class="footer-nav">
                <a href="#sobre">Sobre</a>
                <a href="#contatos">Contato</a>
            </nav>

            <p class="footer-copy">&copy; <?= date('Y') ?> Cross C.T. Todos os direitos reservados.</p>

        </div>
    </footer>
</body>
</html>