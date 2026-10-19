<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Cross C.T - 404</title></head>
<body>
    <h1>404 - Página não encontrada</h1>
    <p><?= htmlspecialchars($message ?? 'A página solicitada não existe.', ENT_QUOTES, 'UTF-8') ?></p>
    <a href="<?= BASE_URL ?>">Voltar para o início</a>
</body>
</html>
