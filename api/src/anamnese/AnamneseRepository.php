<?php
namespace Anamnese;

use Core\Database\Repository;
use Anamnese\DTO\PerguntaDTO;
use Anamnese\DTO\FormularioDTO;
use Anamnese\DTO\OpcaoDTO;
use Anamnese\DTO\RegraExibicaoDTO;

class AnamneseRepository extends Repository {

    public function getFormularioDTO(int $formularioId): ?FormularioDTO {
        $formulario = $this->fetch(
            "SELECT * FROM anamnese_formulario WHERE id = ? AND ativo = 1",
            [$formularioId]
        );

        if (!$formulario) return null;

        $perguntas = $this->fetchPerguntasComOpcoes($formularioId);

        $dto = new FormularioDTO();
        $dto->id = $formulario['id'];
        $dto->nome = $formulario['nome'];
        $dto->descricao = $formulario['descricao'];
        $dto->versao = $formulario['versao'];
        $dto->ativo = (bool) $formulario['ativo'];
        $dto->perguntas = array_map(fn($p) => PerguntaDTO::fromArray($p), $perguntas);

        return $dto;
    }

    public function listarFormulariosAtivos(): array {
        $formularios = $this->fetchAll("
            SELECT id, nome, descricao, versao, ativo 
            FROM anamnese_formulario 
            WHERE ativo = 1 
            ORDER BY nome ASC
        ");

        return array_map(function($f) {
            $dto = new FormularioDTO();
            $dto->id = $f['id'];
            $dto->nome = $f['nome'];
            $dto->descricao = $f['descricao'];
            $dto->versao = $f['versao'];
            $dto->ativo = (bool) $f['ativo'];
            $dto->perguntas = [];
            return $dto;
        }, $formularios);
    }

    private function fetchPerguntasComOpcoes(int $formularioId = 1): array {
        $perguntas = $this->fetchAll("
            SELECT * FROM anamnese_pergunta 
            WHERE formulario_id = ? AND ativo = 1 
            ORDER BY ordem ASC
        ", [$formularioId]);

        if (empty($perguntas)) return [];

        $ids          = array_column($perguntas, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $opcoes = $this->fetchAll("
            SELECT * FROM anamnese_opcao
            WHERE pergunta_id IN ($placeholders)
            ORDER BY ordem ASC
        ", $ids);

        $opcoesMap = [];
        foreach ($opcoes as $op) {
            $op['config'] = $op['config'] ? json_decode($op['config'], true) : null;
            $opcoesMap[$op['pergunta_id']][] = $op;
        }

        foreach ($perguntas as &$p) {
            $p['config']         = $p['config']         ? json_decode($p['config'], true)         : null;
            $p['regra_exibicao'] = $p['regra_exibicao'] ? json_decode($p['regra_exibicao'], true) : null;
            $p['opcoes']         = $opcoesMap[$p['id']] ?? [];
        }

        return $perguntas;
    }

    public function getFormulario(int $formularioId = 1): array {
        if ($formularioId <= 0) {
            return [];
        }

        return $this->fetchPerguntasComOpcoes($formularioId);
    }

    public function getPerguntasMap(int $formularioId = 1): array {
        $map = [];
        foreach ($this->fetchPerguntasComOpcoes($formularioId) as $p) {
            $map[$p['id']] = PerguntaDTO::fromArray($p);
        }
        return $map;
    }

    /**
     * Salva respostas (UPSERT)
     */
    public function salvarRespostas(int $alunoId, array $respostas): void {
        foreach ($respostas as $r) {
            $sql = "
                INSERT INTO anamnese_resposta (aluno_id, pergunta_id, valor, observacao)
                VALUES (:aluno_id, :pergunta_id, :valor, :observacao)
                ON DUPLICATE KEY UPDATE
                    valor = VALUES(valor),
                    observacao = VALUES(observacao)
            ";

            $this->execute($sql, [
                'aluno_id'    => $alunoId,
                'pergunta_id' => $r['pergunta_id'],
                'valor'       => json_encode($r['valor']),
                'observacao'  => $r['observacao'] ?? null
            ]);
        }
    }

    /**
     * Busca respostas de um aluno
     */
    public function getRespostasByAluno(int $alunoId): array {
        $rows = $this->fetchAll("
            SELECT *
            FROM anamnese_resposta
            WHERE aluno_id = ?
        ", [$alunoId]);

        foreach ($rows as &$r) {
            $r['valor'] = json_decode($r['valor'], true);
        }

        return $rows;
    }
}
