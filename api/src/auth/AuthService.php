<?php

namespace Auth;

use Core\Auth\Auth as CoreAuth;
use Core\Auth\AuthRateLimitException;
use Core\Auth\PasswordPolicy;
use Core\Services\Service;
use Usuario\UsuarioRepository;
use Auth\DTO\LoginDTO;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;

class AuthService extends Service
{

    private UsuarioRepository $usuarioRepo;

    public function __construct()
    {
        $this->usuarioRepo = new UsuarioRepository();
    }

    public function login(LoginDTO $dto, string $area): array
    {
        if (!in_array($area, ['aluno', 'admin'], true)) {
            throw new Exception('Area de login invalida.');
        }

        $identifier = strtolower(trim($dto->login));
        $this->assertRateLimit('login', $identifier, 5, 900);
        $this->assertRateLimit('login_ip', 'global_ip', 25, 900);

        // Busca o usuário pelo identificador correspondente à área (e-mail ou matrícula para aluno; e-mail ou CPF para admin)
        $usuario = $this->usuarioRepo->findByLogin($dto->login, $area);

        $senhaHash = $area === 'aluno'
            ? ($usuario['senha_aluno'] ?? null)
            : (($usuario['tipo_usuario'] ?? null) === 'admin'
                ? ($usuario['senha'] ?? null)
                : ($usuario['senha_funcionario'] ?? null));

        if (!$usuario || !$senhaHash || !password_verify($dto->senha, $senhaHash)) {
            $this->usuarioRepo->recordAuthAttempt('login', $identifier, $this->requestIp(), 5, 900, 900);
            $this->usuarioRepo->recordAuthAttempt('login_ip', 'global_ip', $this->requestIp(), 25, 900, 900);
            throw new Exception("As informações de login que você inseriu estão incorretas.");
        }

        if (!(bool)$usuario['ativo']) {
            $this->usuarioRepo->recordAuthAttempt('login', $identifier, $this->requestIp(), 5, 900, 900);
            $this->usuarioRepo->recordAuthAttempt('login_ip', 'global_ip', $this->requestIp(), 25, 900, 900);
            throw new Exception("As informações de login que você inseriu estão incorretas.");
        }

        $possuiAluno = (bool) ($usuario['possui_aluno'] ?? false);
        $possuiFuncionario = (bool) ($usuario['possui_funcionario'] ?? false);
        $possuiAdmin = ($usuario['tipo_usuario'] ?? null) === 'admin';
        $acessoPermitido = $area === 'aluno'
            ? $possuiAluno
            : ($possuiAdmin || $possuiFuncionario);

        if (!$acessoPermitido) {
            throw new Exception($area === 'aluno'
                ? 'Esta conta pertence a area administrativa. Use o login administrativo.'
                : 'Esta conta pertence a area do aluno. Use o login do aluno.');
        }

        $tiposPermitidos = ['admin', 'funcionario', 'aluno'];
        if (!in_array($usuario['tipo_usuario'], $tiposPermitidos)) {
            throw new Exception("Acesso negado. Esta área é restrita a colaboradores.");
        }

        $this->usuarioRepo->clearAuthAttempts('login', $identifier, $this->requestIp());

        // Inicia a sessão no Core
        $tipoSessao = $area === 'aluno'
            ? 'aluno'
            : ($possuiAdmin ? 'admin' : 'funcionario');

        $cargoId = null;
        $cargoNome = null;
        $permissoes = [];

        if ($tipoSessao === 'admin') {
            $cargoNome = 'Administrador';
            $permissoes = ['*'];
        } elseif ($tipoSessao === 'funcionario') {
            $cargoId = isset($usuario['cargo_id']) ? (int) $usuario['cargo_id'] : null;
            $cargoNome = $usuario['cargo_nome'] ?? 'Colaborador';
            if ($cargoId) {
                $permissoes = $this->usuarioRepo->findPermissionsByCargoId($cargoId);
            }
        } else {
            $cargoNome = 'Aluno';
            $permissoes = [];
        }

        CoreAuth::login([
            'id' => (int) $usuario['id'],
            'nome' => $usuario['nome'],
            'tipo' => $tipoSessao,
            'cargo_id' => $cargoId,
            'cargo_nome' => $cargoNome,
            'permissoes' => $permissoes
        ], $area, $senhaHash);

        return [
            'id' => (int) $usuario['id'],
            'nome' => $usuario['nome'],
            'tipo' => $tipoSessao,
            'cargo_id' => $cargoId,
            'cargo_nome' => $cargoNome,
            'permissoes' => $permissoes
        ];
    }

    public function logout(?string $area = null): void
    {
        CoreAuth::logout($area);
    }


    public function generatePasswordResetToken(string $email, string $perfil = 'admin'): void
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Informe um e-mail válido.");
        }

        $identifier = strtolower($email);
        $this->assertRateLimit('password_reset', $identifier, 3, 900);
        $this->usuarioRepo->recordAuthAttempt('password_reset', $identifier, $this->requestIp(), 3, 900, 900);

        if (!in_array($perfil, ['admin', 'aluno', 'funcionario'], true)) {
            throw new Exception('Perfil de senha invalido.');
        }

        $usuario = $this->usuarioRepo->findByEmail($email);

        if (!$usuario || !(bool) $usuario['ativo']) {
            return;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 900);

        $this->transaction(function () use ($usuario, $perfil, $token, $expiresAt): void {
            $this->usuarioRepo->deletePasswordResetTokens((int) $usuario['id']);
            $this->usuarioRepo->createPasswordResetToken(
                (int) $usuario['id'],
                $perfil,
                hash('sha256', $token),
                $expiresAt
            );
        });

        $this->sendPasswordResetEmail($usuario, $token);
    }

    public function updatePasswordWithToken(string $token, string $novaSenha, string $confirmarSenha): void
    {
        $token = trim($token);

        if ($token === '') {
            throw new Exception("Token de recuperação inválido.");
        }

        $ip = $this->requestIp();
        $this->assertRateLimit('password_reset_confirm', $ip, 10, 900);

        if ($novaSenha !== $confirmarSenha) {
            throw new Exception("As senhas não coincidem.");
        }

        PasswordPolicy::validate($novaSenha);

        $reset = $this->usuarioRepo->findValidPasswordReset(hash('sha256', $token));
        if (!$reset) {
            $this->usuarioRepo->recordAuthAttempt('password_reset_confirm', $ip, $ip, 10, 900, 900);
            throw new Exception("Link inválido ou expirado.");
        }

        $passwordHash = password_hash($novaSenha, PASSWORD_ARGON2ID);

        $this->transaction(function () use ($reset, $passwordHash, $ip): void {
            if ($reset['perfil'] === 'admin') {
                $this->usuarioRepo->updatePassword((int) $reset['usuario_id'], $passwordHash);
            } else {
                $this->usuarioRepo->updateProfilePassword((int) $reset['usuario_id'], $reset['perfil'], $passwordHash);
            }
            $this->usuarioRepo->invalidatePasswordResetToken((int) $reset['id']);
            $this->usuarioRepo->clearAuthAttempts('password_reset_confirm', $ip, $ip);
        });
    }

    public function sendInitialPassword(int $usuarioId, string $senhaInicial): void
    {
        $usuario = $this->usuarioRepo->findById($usuarioId);
        if (!$usuario || !(bool) $usuario['ativo']) {
            throw new Exception('Usuario inválido para ativação.');
        }

        $this->sendInitialPasswordEmail($usuario, $senhaInicial);
    }

    public function resendAccountActivationLink(int $usuarioId, string $perfil): void
    {
        if (!in_array($perfil, ['aluno', 'funcionario'], true)) {
            throw new Exception('Perfil de ativacao invalido.');
        }

        $identifier = 'activation_resend_' . $usuarioId;
        $this->assertRateLimit('activation_resend', $identifier, 3, 900);
        $this->usuarioRepo->recordAuthAttempt('activation_resend', $identifier, $this->requestIp(), 3, 900, 900);

        $usuario = $this->usuarioRepo->findById($usuarioId);
        if (!$usuario || !(bool) $usuario['ativo']) {
            throw new Exception('Usuario invalido para ativacao.');
        }

        $loginData = $this->usuarioRepo->findByLogin($usuario['email']);
        $campoAtivacao = $perfil === 'aluno' ? 'conta_ativada_aluno' : 'conta_ativada_funcionario';
        if (!$loginData || !(bool) ($loginData['possui_' . $perfil] ?? false)) {
            throw new Exception('Este usuario nao possui o perfil informado.');
        }

        if ((bool) ($loginData[$campoAtivacao] ?? false)) {
            throw new Exception('Esta conta ja foi ativada.');
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 86400);

        $this->transaction(function () use ($usuarioId, $perfil, $token, $expiresAt): void {
            $this->usuarioRepo->deleteAccountActivationTokens($usuarioId, $perfil);
            $this->usuarioRepo->createStudentActivationToken(
                $usuarioId,
                $perfil,
                hash('sha256', $token),
                $expiresAt
            );
        });

        $this->sendAccountActivationEmail($usuario, $token, $perfil);
    }

    public function activateAccount(string $token, string $novaSenha, string $confirmarSenha): void
    {
        $token = trim($token);
        if ($token === '' || $novaSenha !== $confirmarSenha) {
            throw new Exception('Link ou senhas invalidos.');
        }

        $ip = $this->requestIp();
        $this->assertRateLimit('account_activation_confirm', $ip, 10, 900);

        PasswordPolicy::validate($novaSenha);
        $activation = $this->usuarioRepo->findValidAccountActivation(hash('sha256', $token));
        if (!$activation) {
            $this->usuarioRepo->recordAuthAttempt('account_activation_confirm', $ip, $ip, 10, 900, 900);
            throw new Exception('Link invalido ou expirado.');
        }

        $this->transaction(function () use ($activation, $novaSenha, $ip): void {
            $this->usuarioRepo->activateProfileAccount(
                (int) $activation['usuario_id'],
                (int) $activation['id'],
                (string) $activation['perfil'],
                password_hash($novaSenha, PASSWORD_ARGON2ID)
            );
            $this->usuarioRepo->clearAuthAttempts('account_activation_confirm', $ip, $ip);
        });
    }

    private function sendPasswordResetEmail(array $usuario, string $token): void
    {
        $mailer = new PHPMailer(true);
        $appUrl = rtrim($_ENV['APP_URL'], '/');
        $resetUrl = $appUrl . '/admin/redefinir-senha/' . urlencode($token);
        $nome = htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8');

        $mailer->isSMTP();
        $mailer->Host = $_ENV['MAIL_HOST'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $_ENV['MAIL_USERNAME'];
        $mailer->Password = $_ENV['MAIL_PASSWORD'];
        $mailer->Port = (int) $_ENV['MAIL_PORT'];
        $mailer->SMTPSecure = strtolower($_ENV['MAIL_ENCRYPTION']) === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->CharSet = 'UTF-8';
        $mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
        $mailer->addAddress($usuario['email'], $usuario['nome']);
        $mailer->isHTML(true);
        $mailer->Subject = 'Recuperação de senha - Cross C.T';
        $safeResetUrl = htmlspecialchars($resetUrl, ENT_QUOTES, 'UTF-8');
        $templatePath = __DIR__ . '/password-reset.html';
        $template = file_get_contents($templatePath);

        if ($template === false) {
            throw new Exception('Template de e-mail não encontrado. Execute npm run email:build.');
        }

        $mailer->Body = str_replace(
            ['{{nome}}', '{{link}}', '{{ano}}'],
            [$nome, $safeResetUrl, date('Y')],
            $template
        );
        $mailer->AltBody = "Olá, {$usuario['nome']}. Recebemos uma solicitação para redefinir sua senha no Cross C.T. Acesse {$resetUrl} para definir sua nova senha. Este link expira em 15 minutos.";
        $mailer->send();
    }

    private function sendAccountActivationEmail(array $usuario, string $token, string $perfil): void
    {
        $mailer = new PHPMailer(true);
        $appUrl = rtrim($_ENV['APP_URL'], '/');
        $activationUrl = $appUrl . '/ativar-conta/' . urlencode($token) . '?perfil=' . urlencode($perfil);
        $nome = htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8');
        $safeActivationUrl = htmlspecialchars($activationUrl, ENT_QUOTES, 'UTF-8');

        $mailer->isSMTP();
        $mailer->Host = $_ENV['MAIL_HOST'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $_ENV['MAIL_USERNAME'];
        $mailer->Password = $_ENV['MAIL_PASSWORD'];
        $mailer->Port = (int) $_ENV['MAIL_PORT'];
        $mailer->SMTPSecure = strtolower($_ENV['MAIL_ENCRYPTION']) === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->CharSet = 'UTF-8';
        $mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
        $mailer->addAddress($usuario['email'], $usuario['nome']);
        $mailer->isHTML(true);
        $mailer->Subject = 'Ative sua conta - Cross C.T';
        $template = file_get_contents(__DIR__ . '/account-activation.html');
        if ($template === false) {
            throw new Exception('Template de ativação não encontrado.');
        }

        $mailer->Body = str_replace(
            ['{{nome}}', '{{link}}', '{{ano}}'],
            [$nome, $safeActivationUrl, date('Y')],
            $template
        );
        $mailer->AltBody = "Olá, {$usuario['nome']}. Acesse {$activationUrl} para ativar sua conta no Cross C.T e definir sua senha definitiva. Este link expira em 24 horas.";
        $mailer->send();
    }

    private function sendInitialPasswordEmail(array $usuario, string $senhaInicial): void
    {
        $mailer = new PHPMailer(true);
        $appUrl = rtrim($_ENV['APP_URL'], '/');
        $nome = htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars($usuario['email'] ?? '', ENT_QUOTES, 'UTF-8');
        $safeInitialPassword = htmlspecialchars($senhaInicial, ENT_QUOTES, 'UTF-8');
        $matricula = !empty($usuario['codigo_matricula'])
            ? htmlspecialchars((string) $usuario['codigo_matricula'], ENT_QUOTES, 'UTF-8')
            : 'Não informada';

        $isStaff = in_array($usuario['tipo_usuario'] ?? '', ['admin', 'funcionario'], true) && empty($usuario['codigo_matricula']);
        $loginUrl = $isStaff ? $appUrl . '/admin/login' : $appUrl . '/login';
        $safeLoginUrl = htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8');

        $mailer->isSMTP();
        $mailer->Host = $_ENV['MAIL_HOST'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $_ENV['MAIL_USERNAME'];
        $mailer->Password = $_ENV['MAIL_PASSWORD'];
        $mailer->Port = (int) $_ENV['MAIL_PORT'];
        $mailer->SMTPSecure = strtolower($_ENV['MAIL_ENCRYPTION']) === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->CharSet = 'UTF-8';
        $mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
        $mailer->addAddress($usuario['email'], $usuario['nome']);
        $mailer->isHTML(true);
        $mailer->Subject = 'Sua senha inicial - Cross C.T';
        $template = file_get_contents(__DIR__ . '/initial-password.html');
        if ($template === false) {
            throw new Exception('Template de senha inicial nao encontrado.');
        }

        $mailer->Body = str_replace(
            ['{{nome}}', '{{matricula}}', '{{email}}', '{{senha_inicial}}', '{{login_url}}', '{{ano}}'],
            [$nome, $matricula, $email, $safeInitialPassword, $safeLoginUrl, date('Y')],
            $template
        );
        $mailer->AltBody = "Olá, {$usuario['nome']}.\nSuas credenciais de acesso ao Cross C.T:\nMatrícula: {$matricula}\nE-mail: {$usuario['email']}\nSenha inicial: {$senhaInicial}\nAcesse {$loginUrl} para entrar no sistema e ativar sua conta.";
        $mailer->send();
    }

    private function sendAccountActivationLinkEmailUnused(array $usuario, string $token): void
    {
        $mailer = new PHPMailer(true);
        $appUrl = rtrim($_ENV['APP_URL'], '/');
        $activationUrl = $appUrl . '/ativar-conta/' . urlencode($token);
        $nome = htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8');
        $safeActivationUrl = htmlspecialchars($activationUrl, ENT_QUOTES, 'UTF-8');

        $mailer->isSMTP();
        $mailer->Host = $_ENV['MAIL_HOST'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $_ENV['MAIL_USERNAME'];
        $mailer->Password = $_ENV['MAIL_PASSWORD'];
        $mailer->Port = (int) $_ENV['MAIL_PORT'];
        $mailer->SMTPSecure = strtolower($_ENV['MAIL_ENCRYPTION']) === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->CharSet = 'UTF-8';
        $mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
        $mailer->addAddress($usuario['email'], $usuario['nome']);
        $mailer->isHTML(true);
        $mailer->Subject = 'Link para ativar sua conta - Cross C.T';
        $mailer->Body = '<p>Olá, ' . $nome . '.</p><p>Conforme solicitado, segue um novo link para ativar sua conta:</p><p><a href="' . $safeActivationUrl . '">Ativar minha conta</a></p><p>O link expira em 24 horas e pode ser usado uma única vez.</p>';
        $mailer->AltBody = "Acesse {$activationUrl} para ativar sua conta. Este link expira em 24 horas.";
        $mailer->send();
    }

    private function sendAccountActivationLinkEmail(array $usuario, string $token): void
    {
        $mailer = new PHPMailer(true);
        $appUrl = rtrim($_ENV['APP_URL'], '/');
        $activationUrl = $appUrl . '/ativar-conta/' . urlencode($token);
        $nome = htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8');
        $safeActivationUrl = htmlspecialchars($activationUrl, ENT_QUOTES, 'UTF-8');

        $mailer->isSMTP();
        $mailer->Host = $_ENV['MAIL_HOST'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $_ENV['MAIL_USERNAME'];
        $mailer->Password = $_ENV['MAIL_PASSWORD'];
        $mailer->Port = (int) $_ENV['MAIL_PORT'];
        $mailer->SMTPSecure = strtolower($_ENV['MAIL_ENCRYPTION']) === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->CharSet = 'UTF-8';
        $mailer->setFrom($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME']);
        $mailer->addAddress($usuario['email'], $usuario['nome']);
        $mailer->isHTML(true);
        $mailer->Subject = 'Link para ativar sua conta - Cross C.T';
        $mailer->Body = '<p>Olá, ' . $nome . '.</p><p>Conforme solicitado, segue um novo link para ativar sua conta:</p><p><a href="' . $safeActivationUrl . '">Ativar minha conta</a></p><p>O link expira em 24 horas e pode ser usado uma única vez.</p>';
        $mailer->AltBody = "Acesse {$activationUrl} para ativar sua conta. Este link expira em 24 horas.";
        $mailer->send();
    }

    private function assertRateLimit(string $action, string $identifier, int $maxAttempts, int $windowSeconds): void
    {
        if ($this->usuarioRepo->isAuthRateLimited($action, $identifier, $this->requestIp(), $maxAttempts, $windowSeconds)) {
            throw new AuthRateLimitException('Muitas tentativas. Aguarde alguns minutos e tente novamente.');
        }
    }

    private function requestIp(): string
    {
        return substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 45);
    }
}
