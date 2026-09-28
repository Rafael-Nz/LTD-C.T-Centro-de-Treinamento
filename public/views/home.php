<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CT Sempre em Forma - Centro de Treinamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>

    <header class="header sticky-top">
        <nav class="navbar navbar-expand-lg navbar-dark container py-0" style="height: 64px;">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="/ctt/">
                <img src="<?= PUBLIC_URL ?>img/logo.png" alt="CT Sempre em Forma" width="30" height="30">
                CT CROSS
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarHome">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarHome">
                <ul class="navbar-nav mx-auto gap-lg-4">
                    <li class="nav-item"><a class="nav-link" href="#inicio">INÍCIO</a></li>
                    <li class="nav-item"><a class="nav-link" href="#sobre">SOBRE</a></li>
                    <li class="nav-item"><a class="nav-link" href="#modalidades">BENEFÍCIOS</a></li>
                    <li class="nav-item"><a class="nav-link" href="#depoimentos">AVALIAÇÕES</a></li>
                </ul>
                <a href="/ctt/login" class="btn btn-danger btn-sm fw-semibold px-4">Área do Aluno</a>
            </div>
        </nav>
    </header>

    <section class="hero d-flex align-items-center" id="inicio">
        <div class="container">
            <div class="hero-accent"></div>
            <p class="hero-subtitle">SEÇÃO INTRODUTÓRIA</p>
            <h1 class="hero-title">LOREM IPSUM <br>DOLOR SIT</h1>
            <p class="hero-desc">Lorem ipsum dolor sit amet, consectetur adipiscing elit. <br>Sed do eiusmod tempor incididunt ut labore et dolore.</p>
            <div class="d-flex gap-3 flex-wrap">
                <a href="#planos" class="btn btn-danger fw-semibold px-5 py-2">LOREM IPSUM</a>
                <a href="#sobre" class="btn btn-outline-light fw-semibold px-5 py-2">DOLOR SIT AMET</a>
            </div>
        </div>
    </section>

    <section class="stats-strip">
        <div class="container d-flex justify-content-around align-items-center text-center py-4">
            <div class="stat-item">
                <span class="stat-number">+000</span>
                <span class="stat-label">LOREM IPSUM</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number">+0</span>
                <span class="stat-label">DOLOR SIT AMET</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number">+00</span>
                <span class="stat-label">CONSECTETUR ELIT</span>
            </div>
        </div>
    </section>

    <section class="sobre py-5" id="sobre">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-6">
                    <p class="section-tag">SEÇÃO SOBRE NÓS</p>
                    <div class="tag-line"></div>
                    <h2 class="sobre-title">LOREM IPSUM <br>DOLOR SIT AMET</h2>
                    <p class="sobre-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                    <p class="sobre-text">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident.</p>
                </div>
                <div class="col-lg-6">
                    <div class="sobre-img-placeholder">
                        <span>FOTO DA EQUIPE</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="modalidades py-5" id="modalidades">
        <div class="container text-center">
            <p class="section-tag">SEÇÃO BENEFÍCIOS</p>
            <h2 class="modalidades-title">LOREM IPSUM DOLOR</h2>

            <div class="modalidades-carousel mt-4" id="modalidadesCarousel">
                <div class="modalidades-track">
                    <div class="modalidade-card">
                        <div class="modalidade-img"></div>
                        <div class="modalidade-body">
                            <h3>Lorem<br>Ipsum</h3>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit sed do eiusmod tempor.</p>
                        </div>
                    </div>
                    <div class="modalidade-card">
                        <div class="modalidade-img"></div>
                        <div class="modalidade-body">
                            <h3>Dolor Sit<br>Amet</h3>
                            <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>
                        </div>
                    </div>
                    <div class="modalidade-card">
                        <div class="modalidade-img"></div>
                        <div class="modalidade-body">
                            <h3>Consectetur<br>Adipiscing</h3>
                            <p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore.</p>
                        </div>
                    </div>
                </div>
                <div class="modalidade-dots mt-4">
                    <span class="dot active" data-index="0"></span>
                    <span class="dot" data-index="1"></span>
                    <span class="dot" data-index="2"></span>
                </div>
            </div>
        </div>
    </section>

    <section class="planos py-5" id="planos">
        <div class="container text-center">
            <h2 class="planos-title">SEÇÃO PLANOS E PREÇOS</h2>
            <div class="planos-accent mx-auto"></div>
            <p class="planos-subtitulo mb-4">Lorem ipsum dolor sit amet consectetur</p>

            <div class="planos-carousel" id="planosCarousel">
                <div class="planos-track">
                    <div class="plano-slide">
                        <div class="plano-card text-start">
                            <h3>Lorem Ipsum</h3>
                            <p class="plano-desc">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                            <p class="plano-fidelidade">Lorem ipsum</p>
                            <p class="plano-preco-antigo">R$ 0,00</p>
                            <p class="plano-preco">R$ 0,00<span>/mês</span></p>
                            <a href="#" class="btn btn-outline-dark w-100 fw-semibold rounded-pill">Lorem Ipsum Dolor</a>
                            <hr>
                            <p class="plano-inclui"><strong>Inclui:</strong></p>
                            <ul class="plano-lista">
                                <li class="incluso"><i class="bi bi-check"></i> Lorem ipsum dolor sit</li>
                                <li class="incluso"><i class="bi bi-check"></i> Consectetur adipiscing elit</li>
                                <li class="incluso"><i class="bi bi-check"></i> Sed do eiusmod tempor</li>
                                <li class="nao-incluso"><i class="bi bi-x"></i> Ut enim ad minim</li>
                                <li class="nao-incluso"><i class="bi bi-x"></i> Quis nostrud exercitation</li>
                            </ul>
                        </div>
                    </div>

                    <div class="plano-slide">
                        <div class="plano-card plano-destaque text-start">
                            <span class="plano-badge">LOREM IPSUM</span>
                            <h3>Dolor Sit Amet</h3>
                            <p class="plano-desc">Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi.</p>
                            <p class="plano-fidelidade">Lorem ipsum dolor</p>
                            <p class="plano-preco-antigo">R$ 0,00</p>
                            <p class="plano-preco">R$ 0,00<span>/mês</span></p>
                            <a href="#" class="btn btn-danger w-100 fw-semibold rounded-pill">Lorem Ipsum Dolor</a>
                            <hr>
                            <p class="plano-inclui"><strong>Inclui:</strong></p>
                            <ul class="plano-lista">
                                <li class="incluso"><i class="bi bi-check"></i> Lorem ipsum dolor sit</li>
                                <li class="incluso"><i class="bi bi-check"></i> Consectetur adipiscing elit</li>
                                <li class="incluso"><i class="bi bi-check"></i> Sed do eiusmod tempor</li>
                                <li class="incluso"><i class="bi bi-check"></i> Ut enim ad minim</li>
                                <li class="nao-incluso"><i class="bi bi-x"></i> Quis nostrud exercitation</li>
                            </ul>
                        </div>
                    </div>

                    <div class="plano-slide">
                        <div class="plano-card text-start">
                            <h3>Consectetur Elit</h3>
                            <p class="plano-desc">Duis aute irure dolor in reprehenderit in voluptate velit esse cillum.</p>
                            <p class="plano-fidelidade">Lorem ipsum dolor</p>
                            <p class="plano-preco-antigo">R$ 0,00</p>
                            <p class="plano-preco">R$ 0,00<span>/mês</span></p>
                            <a href="#" class="btn btn-outline-dark w-100 fw-semibold rounded-pill">Lorem Ipsum Dolor</a>
                            <hr>
                            <p class="plano-inclui"><strong>Inclui:</strong></p>
                            <ul class="plano-lista">
                                <li class="incluso"><i class="bi bi-check"></i> Lorem ipsum dolor sit</li>
                                <li class="incluso"><i class="bi bi-check"></i> Consectetur adipiscing elit</li>
                                <li class="incluso"><i class="bi bi-check"></i> Sed do eiusmod tempor</li>
                                <li class="incluso"><i class="bi bi-check"></i> Ut enim ad minim</li>
                                <li class="incluso"><i class="bi bi-check"></i> Quis nostrud exercitation</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="plano-dots mt-4">
                    <span class="dot active" data-index="0"></span>
                    <span class="dot" data-index="1"></span>
                    <span class="dot" data-index="2"></span>
                </div>
            </div>
        </div>
    </section>

    <section class="depoimentos py-5" id="depoimentos">
        <div class="container text-center">
            <h2 class="depoimentos-title">SEÇÃO AVALIAÇÕES</h2>
            <p class="depoimentos-subtitulo">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
            <a href="#planos" class="btn btn-danger fw-semibold px-5 py-2 mb-5">LOREM IPSUM</a>

            <div class="depoimentos-grid">
                <div class="depoimento-foto">
                    <span>foto genérica de treinos</span>
                </div>
                <div class="depoimento-card">
                    <div class="depoimento-stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                    <p class="depoimento-texto">"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua."</p>
                    <div class="depoimento-autor">
                        <div>
                            <p class="depoimento-nome">Lorem Ipsum</p>
                            <p class="depoimento-tempo">Lorem 1 amet</p>
                        </div>
                        <span class="depoimento-logo"></span>
                    </div>
                </div>
                <div class="depoimento-card">
                    <div class="depoimento-stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                    <p class="depoimento-texto">"Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat."</p>
                    <div class="depoimento-autor">
                        <div>
                            <p class="depoimento-nome">Dolor Sit</p>
                            <p class="depoimento-tempo">Lorem 2 amet</p>
                        </div>
                        <span class="depoimento-logo"></span>
                    </div>
                </div>
                <div class="depoimento-card">
                    <div class="depoimento-stars"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i></div>
                    <p class="depoimento-texto">"Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat."</p>
                    <div class="depoimento-autor">
                        <div>
                            <p class="depoimento-nome">Amet Consectetur</p>
                            <p class="depoimento-tempo">Lorem 6 ipsum</p>
                        </div>
                        <span class="depoimento-logo"></span>
                    </div>
                </div>
                <div class="depoimento-foto">
                    <span>foto genérica de treinos</span>
                </div>
                <div class="depoimento-foto">
                    <span>foto genérica de treinos</span>
                </div>
            </div>

        </div>
    </section>

    <footer class="footer-ct py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h3 class="footer-brand">CT SEMPRE EM FORMA</h3>
                    <p class="footer-tagline">Transformando vidas através do treino funcional.</p>
                </div>
                <div class="col-6 col-lg-2">
                    <h4 class="footer-heading">LINKS RÁPIDOS</h4>
                    <nav class="d-flex flex-column gap-1">
                        <a href="#inicio" class="footer-link">Início</a>
                        <a href="#sobre" class="footer-link">Sobre</a>
                        <a href="#modalidades" class="footer-link">Benefícios</a>
                        <a href="#planos" class="footer-link">Horários</a>
                        <a href="#depoimentos" class="footer-link">Avaliações</a>
                    </nav>
                </div>
                <div class="col-6 col-lg-3">
                    <h4 class="footer-heading">CONTATO</h4>
                    <p class="footer-info">(00) 00000-0000</p>
                    <p class="footer-info">contato@ctsempreemforma.com.br</p>
                    <p class="footer-info footer-address">Endereço, Cidade - Estado</p>
                </div>
                <div class="col-lg-3">
                    <h4 class="footer-heading">REDES SOCIAIS</h4>
                    <div class="d-flex gap-3">
                        <a href="https://www.instagram.com/cross_sempreemforma" target="_blank" class="footer-social-icon instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="footer-social-icon whatsapp"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
            </div>
            <hr class="footer-divider">
            <p class="footer-copy">&copy; <?= date('Y') ?> CT Sempre em Forma. Todos os direitos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= PUBLIC_URL ?>js/home.js"></script>
</body>
</html>
