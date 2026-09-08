<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T - Centro de Treinamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>

    <header class="sticky-top bg-white border-bottom">
        <nav class="navbar navbar-expand-lg container py-0" style="height: 72px;">
            <a class="navbar-brand d-flex align-items-center gap-2" href="/ctt/">
                <img src="<?= PUBLIC_URL ?>img/logo.png" alt="Cross C.T" width="36" height="36">
                <span class="fw-bold">Cross C.T</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHome">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarHome">
                <ul class="navbar-nav me-auto gap-lg-3">
                    <li class="nav-item"><a class="nav-link" href="#">Locais</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Planos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Produtos</a></li>
                </ul>

                <div class="d-flex align-items-center gap-3">
                    <a href="/ctt/admin/login" class="nav-link">Log In Admin</a>
                    <a href="/ctt/loginuser" class="btn btn-danger btn-sm fw-semibold">Log In Aluno</a>
                </div>
            </div>
        </nav>
    </header>

    <section class="hero">
        <div class="row g-0 min-vh-75">
            <div class="col-lg-6 d-flex align-items-center">
                <div class="hero-text p-4 p-md-5">
                    <h1 class="mb-4">Seção Boas-Vindas</h1>
                    <p class="mb-4">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="#" class="btn btn-danger fw-semibold">Lorem ipsum</a>
                        <a href="#" class="btn btn-light fw-semibold">Dolor sit</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 hero-imagem-ct">
                <img src="<?= PUBLIC_URL ?>img/ct.png" alt="Cross C.T" class="w-100 h-100 object-fit-cover">
            </div>
        </div>
    </section>

    <section class="beneficios py-5">
        <div class="container">
            <h2 class="mb-5">Seção Benefícios</h2>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="beneficio-card">
                        <div class="beneficio-body">
                            <h3>Lorem ipsum</h3>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut enim ad minim veniam.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="beneficio-card">
                        <div class="beneficio-body">
                            <h3>Dolor sit amet</h3>
                            <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="beneficio-card">
                        <div class="beneficio-body">
                            <h3>Consectetur</h3>
                            <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="planos py-5" id="planos">
        <div class="container text-center">
            <h2 class="text-white mb-2">Seção Planos</h2>
            <p class="planos-subtitulo mb-5">Lorem ipsum dolor sit amet consectetur adipiscing</p>

            <div class="row g-4 justify-content-center">
                <div class="col-md-6 col-lg-4">
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
                </div>
                <div class="col-md-6 col-lg-4">
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
                </div>
                <div class="col-md-6 col-lg-4">
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
            </div>
        </div>
    </section>

    <section class="secao-final text-center py-5" id="final">
        <div class="container">
            <h2>Seção Final</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            <a href="#final" class="btn btn-danger fw-semibold">Lorem ipsum</a>
        </div>
    </section>

    <footer class="bg-white border-top py-4">
        <div class="container d-flex flex-column flex-md-row align-items-center gap-3">
            <nav class="d-flex gap-4">
                <a href="#sobre" class="footer-link">Sobre</a>
                <a href="#contatos" class="footer-link">Contato</a>
            </nav>
            <div class="d-flex flex-column align-items-center gap-2 ms-md-auto">
                <a href="https://www.instagram.com/cross_sempreemforma" target="_blank" class="footer-social"><i class="bi bi-instagram"></i></a>
                <p class="footer-copy mb-0">&copy; <?= date('Y') ?> Cross C.T. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
