<?php
namespace Aluno;

use Core\Service;
use Usuario\UsuarioService;
use Core\Database;


class AlunoService extends Service {
    private AlunoRepository $alunoRepo;
    private UsuarioService $usuarioService;

    public function __construct() {
        $this->alunoRepo = new AlunoRepository();
        $this->usuarioService = new UsuarioService();
    }

    public function create(array $data): int {
        return $this->transaction(function() use ($data) {
            // 1. Gerar matrícula
            $codigoMatricula = $this->gerarMatricula();

            // 2. Montar estrutura para usuário
            $usuarioData = [
                'nome' => $data['nome'] ?? null,
                'sobrenome' => $data['sobrenome'] ?? null,
                'cpf' => $data['cpf'] ?? null,
                'email' => $data['email'] ?? null,
                'data_nascimento' => $data['data_nascimento'] ?? null,
                'genero' => $data['genero'] ?? 'O',
                'senha' => $data['senha'] ?? null,
                'tipo_usuario' => 'aluno',
                'endereco' => $data['endereco'] ?? null,
                'contatos' => $data['contatos'] ?? null
            ];

            // 3. Criar a base do usuário
            $usuarioId = $this->usuarioService->create($usuarioData);

            // 4. Criar o registro na tabela 'aluno'
            $this->alunoRepo->create([
                'usuario_id' => $usuarioId,
                'data_matricula' => $data['data_matricula'] ?? date('Y-m-d'),
                'codigo_matricula' => $codigoMatricula,
                'cadastrado_por' => $data['cadastrado_por'] ?? 1
            ]);

            return $usuarioId;
        });
    }

    public function update(int $id, array $data): void {
        $this->transaction(function() use ($id, $data) {
            $this->alunoRepo->update($id, $data);
        });
    }

    // DELETE (soft)
    public function deactivate(int $id): void {
        $this->usuarioService->deactivate($id);
    }

    public function reactivate(int $id): void {
        $this->usuarioService->reactivate($id);
    }

    // REGRA DE NEGÓCIO
    private function gerarMatricula(): string {
        // AAAAMM000001
        $prefixo = date('Ym');

        // conta quantos alunos existem no mês
        $total = $this->alunoRepo->countByMonth($prefixo);

        $sequencial = str_pad($total + 1, 6, '0', STR_PAD_LEFT);

        return $prefixo . $sequencial;
    }
}