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

    public function login(LoginDTO $dto): array
    {
        $identifier = strtolower(trim($dto->login));
        $this->assertRateLimit('login', $identifier, 5, 900);

        // Busca o usuário pelo e-mail ou CPF
        $usuario = $this->usuarioRepo->findByLogin($dto->login);

        if (!$usuario || !password_verify($dto->senha, $usuario['senha'])) {
            $this->usuarioRepo->recordAuthAttempt('login', $identifier, $this->requestIp(), 5, 900, 900);
            throw new Exception("As informações de login que você inseriu estão incorretas.");
        }

        if (!(bool)$usuario['ativo']) {
            $this->usuarioRepo->recordAuthAttempt('login', $identifier, $this->requestIp(), 5, 900, 900);
            throw new Exception("As informações de login que você inseriu estão incorretas.");
        }

        $tiposPermitidos = ['admin', 'funcionario'];
        if (!in_array($usuario['tipo_usuario'], $tiposPermitidos)) {
            throw new Exception("Acesso negado. Esta área é restrita a colaboradores.");
        }

        $this->usuarioRepo->clearAuthAttempts('login', $identifier, $this->requestIp());

        // Inicia a sessão no Core
        CoreAuth::login([
            'id' => (int)$usuario['id'],
            'nome' => $usuario['nome'],
            'tipo' => $usuario['tipo_usuario']
        ]);

        return [
            'id' => (int)$usuario['id'],
            'nome' => $usuario['nome'],
            'tipo' => $usuario['tipo_usuario']
        ];
    }

    public function logout(): void
    {
        CoreAuth::logout();
    }


    public function generatePasswordResetToken(string $email): void
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Informe um e-mail válido.");
        }

        $identifier = strtolower($email);
        $this->assertRateLimit('password_reset', $identifier, 3, 900);
        $this->usuarioRepo->recordAuthAttempt('password_reset', $identifier, $this->requestIp(), 3, 900, 900);

        $usuario = $this->usuarioRepo->findByEmail($email);

        if (!$usuario || !(bool) $usuario['ativo']) {
            return;
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 900);

        $this->transaction(function () use ($usuario, $token, $expiresAt): void {
            $this->usuarioRepo->deletePasswordResetTokens((int) $usuario['id']);
            $this->usuarioRepo->createPasswordResetToken(
                (int) $usuario['id'],
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

        if ($novaSenha !== $confirmarSenha) {
            throw new Exception("As senhas não coincidem.");
        }

        PasswordPolicy::validate($novaSenha);

        $reset = $this->usuarioRepo->findValidPasswordReset(hash('sha256', $token));
        if (!$reset) {
            throw new Exception("Link inválido ou expirado.");
        }

        $passwordHash = password_hash($novaSenha, PASSWORD_ARGON2ID);

        $this->transaction(function () use ($reset, $passwordHash): void {
            $this->usuarioRepo->updatePassword((int) $reset['usuario_id'], $passwordHash);
            $this->usuarioRepo->invalidatePasswordResetToken((int) $reset['id']);
        });
    }

    public function sendStudentActivationInvite(int $usuarioId): void
    {
        $usuario = $this->usuarioRepo->findById($usuarioId);
        if (!$usuario || $usuario['tipo_usuario'] !== 'aluno' || (bool) $usuario['ativo']) {
            throw new Exception('Aluno invalido para ativacao.');
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 86400);

        $this->transaction(function () use ($usuarioId, $token, $expiresAt): void {
            $this->usuarioRepo->deleteStudentActivationTokens($usuarioId);
            $this->usuarioRepo->createStudentActivationToken(
                $usuarioId,
                hash('sha256', $token),
                $expiresAt
            );
        });

        $this->sendStudentActivationEmail($usuario, $token);
    }

    public function activateStudent(string $token, string $novaSenha, string $confirmarSenha): void
    {
        $token = trim($token);
        if ($token === '' || $novaSenha !== $confirmarSenha) {
            throw new Exception('Link ou senhas invalidos.');
        }

        PasswordPolicy::validate($novaSenha);
        $activation = $this->usuarioRepo->findValidStudentActivation(hash('sha256', $token));
        if (!$activation) {
            throw new Exception('Link invalido ou expirado.');
        }

        $this->transaction(function () use ($activation, $novaSenha): void {
            $this->usuarioRepo->activateStudentAccount(
                (int) $activation['usuario_id'],
                (int) $activation['id'],
                password_hash($novaSenha, PASSWORD_ARGON2ID)
            );
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
            ['{{nome}}', '{{link}}'],
            [$nome, $safeResetUrl],
            $template
        );
        $mailer->AltBody = "Acesse {$resetUrl} para redefinir sua senha. Este link expira em 15 minutos.";
        $mailer->send();
    }

    private function sendStudentActivationEmail(array $usuario, string $token): void
    {
        $mailer = new PHPMailer(true);
        $appUrl = rtrim($_ENV['APP_URL'], '/');
        $activationUrl = $appUrl . '/admin/ativar-conta/' . urlencode($token);
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
        $template = file_get_contents(__DIR__ . '/student-activation.html');
        if ($template === false) {
            throw new Exception('Template de ativacao nao encontrado.');
        }

        $mailer->Body = str_replace(
            ['{{nome}}', '{{link}}'],
            [$nome, $safeActivationUrl],
            $template
        );
        $mailer->AltBody = "Acesse {$activationUrl} para ativar sua conta. Este convite expira em 24 horas.";
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
