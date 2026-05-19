<?php
namespace Auth;

use Core\Auth\Auth as CoreAuth;
use Core\Services\Service;
use Usuario\UsuarioRepository;
use Auth\DTO\LoginDTO;
use Exception;

class AuthService extends Service {

    private UsuarioRepository $usuarioRepo;

    public function __construct() {
        $this->usuarioRepo = new UsuarioRepository();
    }

    public function login(LoginDTO $dto): array {
        // Busca o usuário pelo e-mail ou CPF
        $usuario = $this->usuarioRepo->findByLogin($dto->login);

        if (!$usuario || !password_verify($dto->senha, $usuario['senha'])) {
            throw new Exception("As informações de login que você inseriu estão incorretas.");
        }

        if (!(bool)$usuario['ativo']) {
            throw new Exception("As informações de login que você inseriu estão incorretas.");
        }

        $tiposPermitidos = ['admin', 'funcionario'];
        if (!in_array($usuario['tipo_usuario'], $tiposPermitidos)) {
            throw new Exception("Acesso negado. Esta área é restrita a colaboradores.");
        }

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

    public function logout(): void {
        CoreAuth::logout();
    }


    /**
     * Prepara o fluxo de recuperação de senha.
     * TODO: Implementar quando a tabela 'password_resets' for reintroduzida.
     */
    public function generatePasswordResetToken(string $email): void {
        // 1. Verifica se o usuário existe no banco
        $usuario = $this->usuarioRepo->findByLogin($email);
        
        // Se não existir, retornamos silenciosamente por segurança (evita descoberta de e-mails)
        if (!$usuario) {
            return;
        }

        // 2. Gera um token único e seguro
        $token = bin2hex(random_bytes(32));

        /**
         * TODO: IMPLEMENTAÇÃO FUTURA DE E-MAIL
         * 
         * 1. Persistência:
         *    - Inserir na tabela 'password_resets' o e-mail e o $token (sem hash, conforme solicitado).
         *    - A tabela deve ter o campo 'created_at' para validar a expiração de 1 hora.
         * 
         * 2. Integração com PHPMailer/SMTP:
         *    - Configurar Host (ex: smtp.mailtrap.io ou Gmail).
         *    - Montar o corpo do e-mail em HTML com a identidade visual do Cross C.T.
         *    - O link deve apontar para: /admin/redefinir-senha?token=$token
         * 
         * 3. Segurança:
         *    - Antes de inserir um novo token, deletar tokens antigos deste e-mail para manter o banco limpo.
         */
        
        // Exemplo de como salvar no banco futuramente:
        // $this->usuarioRepo->saveResetToken($email, $token);
        
        // Exemplo de chamada de envio:
        // $this->sendEmail($email, $token);
    }

    /**
     * Processa a troca de senha após validação do token.
     * TODO: Implementar lógica de persistência e expiração futuramente.
     */
    public function updatePasswordWithToken(string $token, string $novaSenha, string $confirmarSenha): void {
        if ($novaSenha !== $confirmarSenha) {
            throw new Exception("As senhas não coincidem.");
        }

        if (strlen($novaSenha) < 6) {
            throw new Exception("A senha deve ter pelo menos 6 caracteres.");
        }

        /**
         * TODO: VALIDAÇÃO DE TOKEN
         * 
         * 1. Buscar o registro na tabela 'password_resets' onde o token seja igual ao recebido.
         * 2. Verificar se (NOW - created_at) < 1 hora.
         * 3. Se válido:
         *    - $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
         *    - Update na tabela 'usuario' set senha = $hash where email = $email_do_token.
         *    - Deletar o registro do token usado para invalidar o link.
         */
    }
}
