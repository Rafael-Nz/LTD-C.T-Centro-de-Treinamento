<?php
use Core\Controller;
use Core\Auth;

class AuthController extends Controller {

    private AuthRepository $repo;

    public function __construct() {
        $this->repo = new AuthRepository();
    }

    public function login(): void {
        $this->only('POST');

        $email = $this->input('email');
        $senha = $this->input('senha');

        if (!$email || !$senha) {
            $this->error('Email e senha são obrigatórios');
        }

        $funcionario = $this->repo->findFuncionarioByEmail($email);

        if (!$funcionario || !password_verify($senha, $funcionario['senha'])) {
            $this->error('Credenciais inválidas', 401);
        }

        $permissoes = $this->repo->getPermissoes($funcionario['id']);

        Auth::login([
            'id' => $funcionario['id'],
            'nome' => $funcionario['nome'],
            'email' => $funcionario['email'],
            'permissoes' => $permissoes
        ]);

        $this->json([
            'id' => $funcionario['id'],
            'nome' => $funcionario['nome'],
            'email' => $funcionario['email'],
            'permissoes' => $permissoes
        ]);
    }

    public function logout(): void {
        Auth::logout();
        $this->json(['message' => 'Logout realizado com sucesso']);
    }
}