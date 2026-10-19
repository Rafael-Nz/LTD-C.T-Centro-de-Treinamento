<?php

namespace Auth;

use Core\Http\Controller;
use Auth\DTO\LoginDTO;
use Core\Auth\AuthRateLimitException;
use Throwable;

class AuthController extends Controller
{

    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login()
    {
        $this->only('POST');

        try {
            $body = $this->body();
            $dto = LoginDTO::fromArray($body);
            $area = (string) ($body['area'] ?? '');

            if (empty($dto->login) || empty($dto->senha)) {
                $this->error("Login e senha são obrigatórios.");
            }

            $user = $this->authService->login($dto, $area);
            $this->json(['message' => 'Login realizado com sucesso', 'user' => $user]);
        } catch (AuthRateLimitException $e) {
            $this->error($e->getMessage(), 429);
        } catch (\PDOException $e) {
            $this->error("Algo deu errado. Por favor, tente novamente em instantes.", 500);
        } catch (Throwable $e) {
            $this->error($e->getMessage(), 401);
        }
    }

    public function logout()
    {
        $body = $this->body();
        $area = $body['area'] ?? ($_SERVER['HTTP_X_AUTH_AREA'] ?? null);
        if ($area && !in_array($area, ['admin', 'aluno'], true)) {
            $area = null;
        }
        $this->authService->logout($area);
        $this->json(['message' => 'Sessão encerrada com sucesso.']);
    }

    public function requestPasswordReset()
    {
        $this->only('POST');
        try {
            $body = $this->body();
            $email = $body['email'] ?? '';
            $perfil = (string) ($body['perfil'] ?? 'admin');
            if (empty($email)) {
                $this->error("O e-mail é obrigatório.");
            }

            $this->authService->generatePasswordResetToken($email, $perfil);

            // Mensagem genérica por segurança: não revela se o e-mail existe
            $this->json(['message' => 'Se o e-mail estiver cadastrado, um link de recuperação será enviado.']);
        } catch (AuthRateLimitException $e) {
            $this->error($e->getMessage(), 429);
        } catch (\PDOException $e) {
            $this->error("Algo deu errado. Tente novamente em instantes.", 500);
        } catch (Throwable $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    public function resetPassword()
    {
        $this->only('POST');
        try {
            $body = $this->body();
            $this->authService->updatePasswordWithToken(
                $body['token'],
                $body['nova_senha'],
                $body['confirmar_senha']
            );

            $this->json(['message' => 'Senha redefinida com sucesso!']);
        } catch (\PDOException $e) {
            $this->error("Erro ao conectar ao banco.", 500);
        } catch (Throwable $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    public function activateAccount()
    {
        $this->only('POST');
        try {
            $body = $this->body();
            $this->authService->activateAccount(
                (string) ($body['token'] ?? ''),
                (string) ($body['nova_senha'] ?? ''),
                (string) ($body['confirmar_senha'] ?? '')
            );
            $this->json(['message' => 'Conta ativada com sucesso.']);
        } catch (\PDOException $e) {
            $this->error('Erro ao conectar ao banco.', 500);
        } catch (Throwable $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    public function resendActivationLink()
    {
        $this->only('POST');
        try {
            $body = $this->body();
            $perfil = (string) ($body['perfil'] ?? (\Core\Auth\Auth::user('tipo') === 'aluno' ? 'aluno' : 'funcionario'));
            $this->authService->resendAccountActivationLink(\Core\Auth\Auth::id(), $perfil);
            $this->json(['message' => 'Link de ativacao enviado para o e-mail cadastrado.']);
        } catch (\PDOException $e) {
            $this->error('Erro ao conectar ao banco.', 500);
        } catch (Throwable $e) {
            $this->error($e->getMessage(), 400);
        }
    }
}
