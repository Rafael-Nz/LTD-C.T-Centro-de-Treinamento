<?php
namespace Treino;

use Core\Services\Service;
use Treino\DTO\TreinoAgendaDTO;
use Treino\DTO\TreinoDTO;

class TreinoService extends Service {
    private TreinoRepository $repo;

    public function __construct() {
        $this->repo = new TreinoRepository();
    }

    public function create(TreinoDTO $dto): int {
        return $this->transaction(function () use ($dto) {
            $this->validateData($dto, $this->rulesForCreate(), $this->messages(), $this->attributes());

            if (!$this->repo->verificaModalidadeAtiva($dto->modalidade_id)) {
                throw new \RuntimeException("Modalidade invalida ou inativa.");
            }

            if ($this->repo->existsByNomeModalidade($dto->nome, $dto->modalidade_id)) {
                throw new \RuntimeException("Ja existe um treino com este nome para a modalidade informada.");
            }

            return $this->repo->create($dto);
        });
    }

    public function update(int $id, TreinoDTO $dto): void {
        $this->transaction(function () use ($id, $dto) {
            $existing = $this->repo->findById($id);
            if (!$existing) {
                throw new \RuntimeException("Treino nao encontrado.");
            }

            $this->validateData($dto, $this->rulesForUpdate(), $this->messages(), $this->attributes());

            $nome = $dto->nome ?? $existing['nome'];
            $modalidadeId = $dto->modalidade_id ?? (int) $existing['modalidade_id'];

            if (!$this->repo->verificaModalidadeAtiva((int) $modalidadeId)) {
                throw new \RuntimeException("Modalidade invalida ou inativa.");
            }

            if ($this->repo->existsByNomeModalidade($nome, (int) $modalidadeId, $id)) {
                throw new \RuntimeException("Ja existe outro treino com este nome para a modalidade informada.");
            }

            $this->repo->update($id, $dto);
        });
    }

    public function deactivate(int $id): void {
        if (!$this->repo->exists($id)) {
            throw new \RuntimeException("Treino nao encontrado.");
        }

        $this->repo->deactivate($id);
    }

    public function reactivate(int $id): void {
        if (!$this->repo->exists($id)) {
            throw new \RuntimeException("Treino nao encontrado.");
        }

        $this->repo->reactivate($id);
    }

    public function createAgenda(TreinoAgendaDTO $dto): int {
        return $this->transaction(function () use ($dto) {
            $this->validateData($dto, $this->rulesForAgenda(), $this->agendaMessages(), $this->agendaAttributes());

            if (!$this->repo->verificaTreinoAtivo($dto->treino_id)) {
                throw new \RuntimeException("Treino invalido ou inativo.");
            }

            if (!$this->repo->verificaTurmaExiste($dto->turma_id)) {
                throw new \RuntimeException("Turma nao encontrada.");
            }

            if (!$this->repo->verificaEspacoAtivo($dto->espaco_id)) {
                throw new \RuntimeException("Local de treino invalido ou inativo.");
            }

            if ($dto->instrutor_id !== null && !$this->repo->verificaInstrutorAtivo($dto->instrutor_id)) {
                throw new \RuntimeException("Instrutor invalido ou inativo.");
            }

            if ($this->repo->verificaConflitoTurma($dto->turma_id, $dto->data_hora_inicio, $dto->data_hora_fim)) {
                throw new \RuntimeException("A turma ja possui um treino agendado para este horario.");
            }

            if ($this->repo->verificaConflitoEspaco($dto->espaco_id, $dto->data_hora_inicio, $dto->data_hora_fim)) {
                throw new \RuntimeException("Este local ja esta ocupado no horario informado.");
            }

            if ($dto->instrutor_id !== null && $this->repo->verificaConflitoInstrutor($dto->instrutor_id, $dto->data_hora_inicio, $dto->data_hora_fim)) {
                throw new \RuntimeException("Este instrutor ja possui outro treino agendado no horario informado.");
            }

            return $this->repo->createAgenda($dto);
        });
    }

    private function rulesForCreate(): array {
        return [
            'nome' => ['required', 'string', 'max_length:100'],
            'modalidade_id' => ['required', 'integer', 'min:1'],
        ];
    }

    private function rulesForUpdate(): array {
        return [
            'nome' => ['string', 'max_length:100'],
            'modalidade_id' => ['integer', 'min:1'],
        ];
    }

    private function rulesForAgenda(): array {
        return [
            'treino_id' => ['required', 'integer', 'min:1'],
            'turma_id' => ['required', 'integer', 'min:1'],
            'espaco_id' => ['required', 'integer', 'min:1'],
            'data_hora_inicio' => ['required', 'date', 'before_field:data_hora_fim'],
            'data_hora_fim' => ['required', 'date', 'after_field:data_hora_inicio'],
            'status' => ['nullable', 'in:agendado,concluido,cancelado'],
        ];
    }

    private function messages(): array {
        return [
            'nome.required' => 'Nome e obrigatorio.',
            'nome.max_length' => 'Nome nao pode exceder 100 caracteres.',
            'modalidade_id.required' => 'Modalidade e obrigatoria.',
            'modalidade_id.min' => 'Modalidade invalida.',
        ];
    }

    private function attributes(): array {
        return [
            'nome' => 'Nome',
            'modalidade_id' => 'Modalidade',
        ];
    }

    private function agendaMessages(): array {
        return [
            'treino_id.required' => 'Treino e obrigatorio.',
            'turma_id.required' => 'Turma e obrigatoria.',
            'espaco_id.required' => 'Espaco de treino e obrigatorio.',
            'data_hora_inicio.required' => 'Data e horario do treino sao obrigatorios.',
            'data_hora_fim.required' => 'Data e horario do treino sao obrigatorios.',
            'data_hora_inicio.before_field' => 'Data/hora de inicio deve ser anterior ao termino.',
            'data_hora_fim.after_field' => 'Data/hora de fim deve ser posterior ao inicio.',
            'status.in' => 'Status invalido. Use: agendado, concluido ou cancelado.',
        ];
    }

    private function agendaAttributes(): array {
        return [
            'treino_id' => 'Treino',
            'turma_id' => 'Turma',
            'espaco_id' => 'Espaco de treino',
            'data_hora_inicio' => 'Data/hora de inicio',
            'data_hora_fim' => 'Data/hora de fim',
            'status' => 'Status',
        ];
    }
}
