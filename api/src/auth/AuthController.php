<?php
namespace Auth;

use Core\Controller;
use Auth\DTO\LoginDTO;
use Throwable;

class AuthController extends Controller {
    
    private AuthService $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function login() {
        $this->only('POST');

        try {
            $dto = LoginDTO::fromArray($this->body());
            
            if (empty($dto->login) || empty($dto->senha)) {
                $this->error("Login e senha são obrigatórios.");
            }

            $user = $this->authService->login($dto);
            $this->json(['message' => 'Login realizado com sucesso', 'user' => $user]);
            
        } catch (\PDOException $e) {
            $this->error("Algo deu errado. Por favor, tente novamente em instantes.", 500);
        } catch (Throwable $e) {
            $this->error($e->getMessage(), 401);
        }
    }

    public function logout() {
        $this->authService->logout();
        $this->json(['message' => 'Sessão encerrada com sucesso.']);
    }

    public function requestPasswordReset() {
        $this->only('POST');
        try {
            $email = $this->body()['email'] ?? '';
            if (empty($email)) {
                $this->error("O e-mail é obrigatório.");
            }

            $this->authService->generatePasswordResetToken($email);
            
            // Mensagem genérica por segurança: não revela se o e-mail existe
            $this->json(['message' => 'Se o e-mail estiver cadastrado, um link de recuperação será enviado.']);
        } catch (\PDOException $e) {
            $this->error("Algo deu errado. Tente novamente em instantes.", 500);
        } catch (Throwable $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    public function resetPassword() {
        $this->only('POST');
        try {
            $body = $this->body();
            // Em um sistema real, você usaria um ResetPasswordDTO aqui
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
}