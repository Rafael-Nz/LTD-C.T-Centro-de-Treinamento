<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | <?php echo $id ? 'Editar Modalidade' : 'Nova Modalidade'; ?></title>
    
    <link rel="stylesheet" href="../public/css/bootstrap-5.3.8/bootstrap.css">
    <link rel="stylesheet" href="../public/css/admin-styles.css">
    <link rel="stylesheet" href="../public/css/form.css">
    <link rel="stylesheet" href="../public/css/sidebar.css">
    
    <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css"
        href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css"
        href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main class="flex-fill d-flex" id="mainContent">
        <div class="container-lg p-4 flex-column flex-fill">
            <h1 class="h4 mb-4"><?php echo $id ? 'Editar Modalidade' : 'Nova Modalidade'; ?></h1>
            
            <div class="card border-0 shadow-sm d-flex flex-fill">
                <div class="card-body">
                    <form id="formModalidade">
                        <?php if ($id): ?>
                            <input type="hidden" name="id" id="modalidadeId" value="<?php echo $id; ?>">
                        <?php endif; ?>

                        <div class="row gy-4">
                            <div class="col-12">
                                <div class="mb-4">
                                    <h3 class="h6 section-title border-bottom border-1 pb-1 mb-3">Informações da Modalidade</h3>
                                    
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="nome" class="form-label">Nome da Modalidade *</label>
                                            <input type="text" class="form-control" id="nome" name="nome" 
                                                   placeholder="Ex: CrossFit, Musculação, Pilates..." required>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label for="ativo" class="form-label">Status</label>
                                            <select class="form-select" id="ativo" name="ativo">
                                                <option value="1" selected>Ativo</option>
                                                <option value="0">Inativo</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="descricao" class="form-label">Descrição</label>
                                        <textarea class="form-control" id="descricao" name="descricao" rows="3" 
                                                  placeholder="Descreva brevemente a modalidade..."
                                                  style="resize: none;"></textarea>
                                        <small class="text-muted">Informações sobre a modalidade para melhor identificação</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="button" class="btn btn-red color" onclick="voltar('modalidades')">Voltar</button>
                            <button type="submit" class="btn btn-red color">
                                <?php echo $id ? 'Salvar Alterações' : 'Cadastrar'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script defer src="../public/js/bootstrap-5.3.8/bootstrap.bundle.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script defer src="../public/js/admin/sidebar.js"></script>
    <script src="../public/js/admin/modalidade_form.js"></script>
</body>
</html>