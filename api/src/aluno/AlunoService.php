<?php
namespace Aluno;

use Usuario\UsuarioRepository;

class AlunoService {

    private AlunoRepository $alunoRepo;
    private UsuarioRepository $usuarioRepo;

    public function __construct() {
        $this->alunoRepo = new AlunoRepository();
        $this->usuarioRepo = new UsuarioRepository();
    }

    // =========================
    // CRIAR ALUNO COMPLETO
    // =========================
    public function create(array $data): int {
        $db = \Core\Database::getConnection();
        $db->beginTransaction();

        try {
            // 1. Gerar matrícula
            $codigoMatricula = $this->gerarMatricula();

            // 2. Montar estrutura para usuário
            $usuarioData = [
                ...$data,
                'tipo_usuario' => 'aluno'
            ];
            unset($usuarioData['data_matricula'], $usuarioData['cadastrado_por']);

            // 3. Criar usuário
            $usuarioId = $this->usuarioRepo->create($usuarioData);

            // 4. Criar aluno
            $alunoData = [
                'usuario_id' => $usuarioId,
                'data_matricula' => $data['data_matricula'] ?? date('Y-m-d'),
                'codigo_matricula' => $codigoMatricula,
                'cadastrado_por' => $data['cadastrado_por'] ?? 1
            ];

            $this->alunoRepo->create($alunoData);

            $db->commit();
            return $usuarioId;

        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    // UPDATE
    public function update(int $id, array $data): void {
        // Separar dados de usuário e aluno
        $usuarioData = $data;
        unset($usuarioData['data_matricula']);

        $alunoData = [
            'data_matricula' => $data['data_matricula'] ?? null
        ];

        // Atualizar usuário
        if (!empty($usuarioData)) {
            $this->usuarioRepo->update($id, $usuarioData);
        }

        // Atualizar aluno
        if ($alunoData['data_matricula']) {
            $this->alunoRepo->update($id, $alunoData);
        }
    }

    // DELETE (soft)
    public function delete(int $id): void {
        $this->alunoRepo->deactivate($id);
    }

    public function reactivate(int $id): void {
        $this->alunoRepo->reactivate($id);
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