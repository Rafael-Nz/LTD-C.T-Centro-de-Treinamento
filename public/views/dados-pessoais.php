<?php
$pageTitle = 'Cross C.T - Dados Pessoais';
$currentPage = 'dados-pessoais';
$pageCss = 'dados-pessoais.css';
$extraCss = [
    '/ctt/plugins/select2/css/select2.min.css',
    '/ctt/plugins/select2/css/select2-bootstrap-5-theme.min.css'
];

$diasSemana = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
$meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$hojeTexto = $diasSemana[(int)date('w')] . ', ' . date('d') . ' de ' . $meses[(int)date('m')] . ' de ' . date('Y');
$usuarioNome = htmlspecialchars(\Core\Auth\Auth::aluno()->user('nome') ?? $_SESSION['auth_aluno']['user_nome'] ?? $_SESSION['user_nome'] ?? 'Aluno');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php include __DIR__ . '/partials/head.php'; ?>

<body>
    <div class="d-flex vh-100">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <div class="d-flex flex-column flex-grow-1 min-width-0">
            <main class="dashboard-main">
                <div class="section-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h2 class="section-title">Dados Pessoais</h2>
                        <p class="section-subtitle">Consulte e gerencie suas informações cadastrais e de contato</p>
                    </div>
                    <button class="btn btn-primary-ctt" id="btnEditarPerfil">
                        <i class="bi bi-pencil-square me-1"></i> Editar Dados
                    </button>
                </div>

                <div class="profile-card mb-4">
                    <div class="profile-avatar-lg" id="profileAvatar">--</div>
                    <div class="profile-meta flex-grow-1">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h3 class="profile-name" id="profileName">Carregando...</h3>
                            <span class="badge-status badge-status--green" id="infoStatus">Ativo</span>
                        </div>
                        <p class="profile-sub" id="profileEmailSub">aluno@exemplo.com</p>
                    </div>
                </div>

                <div class="content-grid">
                    <div class="content-left">
                        <!-- Dados Cadastrais -->
                        <div class="info-card">
                            <div class="info-card-header">
                                <h3 class="info-card-title"><i class="bi bi-person-badge"></i> Dados Cadastrais</h3>
                            </div>
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">Nome</span>
                                    <span class="info-value" id="infoNome">—</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Sobrenome</span>
                                    <span class="info-value" id="infoSobrenome">—</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">CPF</span>
                                    <span class="info-value" id="infoCpf">—</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">E-mail Principal</span>
                                    <span class="info-value" id="infoEmail">—</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Data de Nascimento</span>
                                    <span class="info-value" id="infoNascimento">—</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Gênero</span>
                                    <span class="info-value" id="infoGenero">—</span>
                                </div>
                            </div>
                        </div>

                        <!-- Endereço Residencial -->
                        <div class="info-card">
                            <div class="info-card-header">
                                <h3 class="info-card-title"><i class="bi bi-geo-alt"></i> Endereço Residencial</h3>
                            </div>
                            <div class="info-grid">
                                <div class="info-item info-item--full">
                                    <span class="info-label">Logradouro / Número</span>
                                    <span class="info-value" id="infoLogradouro">—</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Bairro</span>
                                    <span class="info-value" id="infoBairro">—</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Cidade</span>
                                    <span class="info-value" id="infoCidade">—</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">CEP</span>
                                    <span class="info-value" id="infoCep">—</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Complemento</span>
                                    <span class="info-value" id="infoComplemento">—</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="content-right">
                        <!-- Telefones e Contato -->
                        <div class="info-card">
                            <div class="info-card-header">
                                <h3 class="info-card-title"><i class="bi bi-telephone"></i> Contatos</h3>
                            </div>
                            <div class="d-flex flex-column gap-3" id="infoContatosList">
                                <p class="text-muted small mb-0">Carregando telefones...</p>
                            </div>
                        </div>

                        <!-- Card de Suporte -->
                        <div class="info-card">
                            <div class="info-card-header">
                                <h3 class="info-card-title"><i class="bi bi-shield-check"></i> Segurança</h3>
                            </div>
                            <p class="text-muted small mb-3">
                                Para alterar seu CPF ou data de nascimento, solicite atendimento diretamente na recepção do CT.
                            </p>
                            <a href="https://wa.me/5500000000000" target="_blank" class="btn btn-outline-success w-100 btn-sm">
                                <i class="bi bi-whatsapp me-1"></i> Falar com a Recepção
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Editar Perfil -->
    <div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-labelledby="modalEditarPerfilLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarPerfilLabel"><i class="bi bi-pencil-square text-danger me-2"></i>Editar Informações</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <form id="formEditarPerfil">
                    <div class="modal-body">
                        <h6 class="text-muted fw-bold text-uppercase small mb-3">Identificação</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="editNome" class="form-label">Nome *</label>
                                <input type="text" class="form-control" id="editNome" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editSobrenome" class="form-label">Sobrenome *</label>
                                <input type="text" class="form-control" id="editSobrenome" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editGenero" class="form-label">Gênero</label>
                                <select class="form-select select2" id="editGenero">
                                    <option value="M">Masculino</option>
                                    <option value="F">Feminino</option>
                                    <option value="O">Outro</option>
                                </select>
                            </div>
                        </div>

                        <h6 class="text-muted fw-bold text-uppercase small mb-3">Contatos & Comunicação</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="editWhatsapp" class="form-label"><i class="bi bi-whatsapp text-success me-1"></i> Contato 1 (WhatsApp)</label>
                                <input type="text" class="form-control" id="editWhatsapp" placeholder="(00) 00000-0000">
                            </div>
                            <div class="col-md-6">
                                <label for="editTelefoneSecundario" class="form-label"><i class="bi bi-telephone text-primary me-1"></i> Contato 2 (Telefone/Celular)</label>
                                <input type="text" class="form-control" id="editTelefoneSecundario" placeholder="(00) 00000-0000">
                            </div>
                            <div class="col-12">
                                <label for="editEmailSecundario" class="form-label"><i class="bi bi-envelope-at text-info me-1"></i> E-mail Secundário</label>
                                <input type="email" class="form-control" id="editEmailSecundario" placeholder="seuemail.alternativo@exemplo.com">
                                <div class="form-text small">E-mail alternativo para notificações e avisos de recuperação.</div>
                            </div>
                        </div>

                        <h6 class="text-muted fw-bold text-uppercase small mb-3">Endereço Residencial</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="editCep" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="editCep" placeholder="00000-000">
                            </div>
                            <div class="col-md-8">
                                <label for="editLogradouro" class="form-label">Logradouro / Rua</label>
                                <input type="text" class="form-control" id="editLogradouro" placeholder="Av. Brasil">
                            </div>
                            <div class="col-md-3">
                                <label for="editNumero" class="form-label">Número</label>
                                <input type="text" class="form-control" id="editNumero" placeholder="123">
                            </div>
                            <div class="col-md-5">
                                <label for="editBairro" class="form-label">Bairro</label>
                                <input type="text" class="form-control" id="editBairro" placeholder="Centro">
                            </div>
                            <div class="col-md-4">
                                <label for="editCidade" class="form-label">Cidade</label>
                                <input type="text" class="form-control" id="editCidade" placeholder="São Paulo">
                            </div>
                            <div class="col-12">
                                <label for="editComplemento" class="form-label">Complemento (opcional)</label>
                                <input type="text" class="form-control" id="editComplemento" placeholder="Apto 101, Bloco B">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary-ctt" id="btnSalvarPerfil">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/ctt/plugins/jquery/jquery-3.7.1.min.js"></script>
    <script src="/ctt/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/ctt/plugins/imask/imask.min.js"></script>
    <script src="/ctt/plugins/select2/js/select2.min.js"></script>
    <script src="/ctt/plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <script src="<?= PUBLIC_URL ?>js/sidebar.js"></script>
    <script src="<?= PUBLIC_URL ?>js/aluno/dados-pessoais.js"></script>
</body>

</html>
