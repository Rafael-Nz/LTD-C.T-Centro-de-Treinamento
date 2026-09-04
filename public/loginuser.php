<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T - Login</title>
    <link rel="stylesheet" href="css/login-user.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-user-card">

        <img src="<?= PUBLIC_URL ?>img/logo.png" alt="Cross C.T">
        <h1>Cross C.T</h1>
        <p>Acesse sua conta para continuar</p>
        
        <form action="">
            <label for="uEmail">E-mail:</label>
            <input type="email" id="uEmail" placeholder="nome@email.com">
            <label for="uSenha">Senha:</label>
            <input type="password" id="uSenha" placeholder="●●●●●●●●">
            <a href="#">Esqueceu a senha?</a>
            <input type="button" value="Entrar">
        </form>

    </div>
</body>
</html>