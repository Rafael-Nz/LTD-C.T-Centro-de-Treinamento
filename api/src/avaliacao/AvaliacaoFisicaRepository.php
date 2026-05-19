<?php
namespace Avaliacao;

use Avaliacao\DTO\AvaliacaoFisicaDTO;
use Core\Database\Repository;

class AvaliacaoFisicaRepository extends Repository {
    public function isFuncionario(int $usuarioId): bool {
        return $this->fetch("SELECT usuario_id FROM funcionario WHERE usuario_id = ?", [$usuarioId]) !== null;
    }

    public function findById(int $id): ?array {
        return $this->fetch("
            SELECT
                af.*,
                u.nome AS aluno_nome,
                u.sobrenome AS aluno_sobrenome,
                u.genero AS aluno_genero,
                u.data_nascimento AS aluno_data_nascimento,
                a.codigo_matricula,
                av.nome AS avaliador_nome,
                av.sobrenome AS avaliador_sobrenome
            FROM avaliacao_fisica af
            INNER JOIN aluno a ON a.usuario_id = af.aluno_id
            INNER JOIN usuario u ON u.id = a.usuario_id
            INNER JOIN funcionario f ON f.usuario_id = af.avaliador_id
            INNER JOIN usuario av ON av.id = f.usuario_id
            WHERE af.id = ?
        ", [$id]);
    }

    public function findByAlunoId(int $alunoId): array {
        return $this->fetchAll("
            SELECT
                af.*,
                u.nome AS aluno_nome,
                u.sobrenome AS aluno_sobrenome,
                u.genero AS aluno_genero,
                u.data_nascimento AS aluno_data_nascimento,
                a.codigo_matricula,
                av.nome AS avaliador_nome,
                av.sobrenome AS avaliador_sobrenome
            FROM avaliacao_fisica af
            INNER JOIN aluno a ON a.usuario_id = af.aluno_id
            INNER JOIN usuario u ON u.id = a.usuario_id
            INNER JOIN funcionario f ON f.usuario_id = af.avaliador_id
            INNER JOIN usuario av ON av.id = f.usuario_id
            WHERE af.aluno_id = ?
            ORDER BY af.data_avaliacao DESC, af.id DESC
        ", [$alunoId]);
    }

    public function create(int $alunoId, int $avaliadorId, AvaliacaoFisicaDTO $dto): int {
        $this->execute("
            INSERT INTO avaliacao_fisica (
                aluno_id,
                avaliador_id,
                data_avaliacao,
                peso,
                altura,
                imc,
                cintura,
                torax,
                braco_dc,
                braco_d,
                braco_ec,
                braco_e,
                coxa_d,
                coxa_e,
                panturrilha_d,
                panturrilha_e,
                percentual_gordura,
                percentual_musculo,
                metabolismo_repouso,
                idade_biologica,
                gordura_visceral,
                observacoes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [
            $alunoId,
            $avaliadorId,
            $dto->data_avaliacao,
            $dto->peso,
            $dto->altura,
            $dto->imc,
            $dto->cintura,
            $dto->torax,
            $dto->braco_dc,
            $dto->braco_d,
            $dto->braco_ec,
            $dto->braco_e,
            $dto->coxa_d,
            $dto->coxa_e,
            $dto->panturrilha_d,
            $dto->panturrilha_e,
            $dto->percentual_gordura,
            $dto->percentual_musculo,
            $dto->metabolismo_repouso,
            $dto->idade_biologica,
            $dto->gordura_visceral,
            $dto->observacoes,
        ]);

        return (int) $this->lastInsertId();
    }

    public function update(int $id, AvaliacaoFisicaDTO $dto): void {
        $this->execute("
            UPDATE avaliacao_fisica SET
                data_avaliacao = ?,
                peso = ?,
                altura = ?,
                imc = ?,
                cintura = ?,
                torax = ?,
                braco_dc = ?,
                braco_d = ?,
                braco_ec = ?,
                braco_e = ?,
                coxa_d = ?,
                coxa_e = ?,
                panturrilha_d = ?,
                panturrilha_e = ?,
                percentual_gordura = ?,
                percentual_musculo = ?,
                metabolismo_repouso = ?,
                idade_biologica = ?,
                gordura_visceral = ?,
                observacoes = ?
            WHERE id = ?
        ", [
            $dto->data_avaliacao,
            $dto->peso,
            $dto->altura,
            $dto->imc,
            $dto->cintura,
            $dto->torax,
            $dto->braco_dc,
            $dto->braco_d,
            $dto->braco_ec,
            $dto->braco_e,
            $dto->coxa_d,
            $dto->coxa_e,
            $dto->panturrilha_d,
            $dto->panturrilha_e,
            $dto->percentual_gordura,
            $dto->percentual_musculo,
            $dto->metabolismo_repouso,
            $dto->idade_biologica,
            $dto->gordura_visceral,
            $dto->observacoes,
            $id,
        ]);
    }
}
