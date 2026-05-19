<?php
namespace Turma;

use Core\Services\Service;
use Treino\DTO\TreinoAgendaDTO;
use Treino\TreinoService;
use Turma\DTO\TurmaDTO;
use Turma\Validation\ConfigHorariosRule;

class TurmaService extends Service {
    private TurmaRepository $repository;
    private TreinoService $treinoService;

    public function __construct() {
        $this->repository = new TurmaRepository();
        $this->treinoService = new TreinoService();
    }

    public function create(TurmaDTO $dto): int {
        return $this->transaction(function () use ($dto) {
            $this->validateData($dto, $this->rulesForCreate(), $this->messages(), $this->attributes());

            if ($this->repository->existsByNomeExcluding($dto->nome, 0)) {
                throw new \Exception("Ja existe uma turma com este nome.");
            }

            if ($dto->instrutor_id !== null && !$this->repository->verificaInstrutorAtivo($dto->instrutor_id)) {
                throw new \Exception("Instrutor informado e invalido ou esta inativo.");
            }

            return $this->repository->create($dto);
        });
    }

    public function update(int $id, TurmaDTO $dto): void {
        $this->transaction(function () use ($id, $dto) {
            $turmaExistente = $this->repository->findById($id);
            if (!$turmaExistente) {
                throw new \Exception("Turma nao encontrada.");
            }

            $payloadValidacao = [
                'nome' => $dto->nome,
                'capacidade_minima' => $dto->capacidade_minima ?? (int) $turmaExistente['capacidade_minima'],
                'capacidade_maxima' => $dto->capacidade_maxima ?? (int) $turmaExistente['capacidade_maxima'],
                'instrutor_id' => $dto->instrutor_id,
                'config_horarios' => $dto->config_horarios,
            ];
            $this->validateData($payloadValidacao, $this->rulesForUpdate(), $this->messages(), $this->attributes());

            if ($dto->nome !== null && $dto->nome !== $turmaExistente['nome']) {
                if ($this->repository->existsByNomeExcluding($dto->nome, $id)) {
                    throw new \Exception("Ja existe uma turma com este nome.");
                }
            }

            if ($dto->instrutor_id !== null && !$this->repository->verificaInstrutorAtivo($dto->instrutor_id)) {
                throw new \Exception("O novo instrutor informado e invalido ou esta inativo.");
            }

            $this->repository->update($id, $dto);
        });
    }

    public function findById(int $id): ?array {
        return $this->repository->findById($id);
    }

    public function findManagementData(int $id, ?string $start = null, ?string $end = null): ?array {
        $turma = $this->repository->findById($id);
        if (!$turma) {
            return null;
        }

        $treinos = $this->repository->findTreinosByTurmaId($id, $turma);
        $metricas = $this->buildManagementMetrics($treinos);
        [$rangeStart, $rangeEnd] = $this->resolveManagementRange($start, $end);
        $sugestoes = $this->buildSuggestedTreinos($turma, $treinos, $rangeStart, $rangeEnd);

        return [
            'turma' => [
                'id' => (int) $turma['id'],
                'nome' => $turma['nome'],
                'ativo' => (bool) $turma['ativo'],
                'instrutor' => !empty($turma['instrutor_id']) ? [
                    'id' => (int) $turma['instrutor_id'],
                    'nome' => $turma['instrutor_nome'] ?? null,
                ] : null,
                'capacidade_minima' => isset($turma['capacidade_minima']) ? (int) $turma['capacidade_minima'] : null,
                'capacidade_maxima' => isset($turma['capacidade_maxima']) ? (int) $turma['capacidade_maxima'] : null,
                'config_horarios' => $turma['config_horarios'] ?? [],
                'horarios_resumo' => $turma['horarios_resumo'] ?? '',
                'total_alunos' => count($turma['alunos'] ?? []),
            ],
            'alunos' => $turma['alunos'] ?? [],
            'metricas' => $metricas,
            'treinos' => $treinos,
            'sugestoes' => $sugestoes,
            'periodo' => [
                'start' => $rangeStart->format('Y-m-d H:i:s'),
                'end' => $rangeEnd->format('Y-m-d H:i:s'),
            ],
        ];
    }

    public function findAll(): array {
        return $this->repository->findAll();
    }

    public function findAllSimple(bool $somenteAtivas = true): array {
        return $this->repository->findSimple($somenteAtivas);
    }

    public function confirmTreino(int $turmaId, array $payload): int {
        $dto = TreinoAgendaDTO::fromArray([
            'treino_id' => isset($payload['treino_id']) ? (int) $payload['treino_id'] : null,
            'turma_id' => $turmaId,
            'espaco_id' => isset($payload['espaco_id']) ? (int) $payload['espaco_id'] : null,
            'instrutor_id' => isset($payload['instrutor_id']) ? (int) $payload['instrutor_id'] : null,
            'data_hora_inicio' => trim((string) ($payload['data_hora_inicio'] ?? '')),
            'data_hora_fim' => trim((string) ($payload['data_hora_fim'] ?? '')),
            'status' => $payload['status'] ?? 'agendado',
            'observacoes' => isset($payload['observacoes']) ? trim((string) $payload['observacoes']) : null,
        ]);

        return $this->treinoService->createAgenda($dto);
    }

    public function savePresencas(int $turmaId, int $treinoId, array $payload): array {
        return $this->transaction(function () use ($turmaId, $treinoId, $payload) {
            $treino = $this->repository->findTreinoAgendaByIdAndTurmaId($treinoId, $turmaId);
            if (!$treino) {
                throw new \RuntimeException("Treino da turma nao encontrado.");
            }

            if (($treino['status'] ?? null) === 'cancelado') {
                throw new \InvalidArgumentException("Nao e possivel lancar presencas para um treino cancelado.");
            }

            $presencas = $payload['presencas'] ?? null;
            if (!is_array($presencas)) {
                throw new \InvalidArgumentException("A lista de presencas informada e invalida.");
            }

            $alunosDaTurma = $this->repository->findAlunosByTurmaId($turmaId);
            $alunoIdsValidos = array_map(
                fn (array $aluno) => (int) $aluno['aluno_id'],
                array_filter($alunosDaTurma, fn (array $aluno) => (int) ($aluno['ativo'] ?? 0) === 1)
            );
            $alunoIdsValidos = array_values(array_unique($alunoIdsValidos));

            $allowedSituacoes = ['presente', 'ausente', 'justificado'];
            $presencasNormalizadas = [];

            foreach ($presencas as $index => $presenca) {
                if (!is_array($presenca)) {
                    throw new \InvalidArgumentException("Presenca invalida na posicao " . ($index + 1) . ".");
                }

                $alunoId = isset($presenca['aluno_id']) ? (int) $presenca['aluno_id'] : 0;
                $situacao = isset($presenca['situacao']) ? trim((string) $presenca['situacao']) : '';

                if ($alunoId < 1 || !in_array($alunoId, $alunoIdsValidos, true)) {
                    throw new \InvalidArgumentException("Ha alunos informados que nao pertencem a turma.");
                }

                if ($situacao === '') {
                    continue;
                }

                if (!in_array($situacao, $allowedSituacoes, true)) {
                    throw new \InvalidArgumentException("Situacao de presenca invalida para o aluno {$alunoId}.");
                }

                $presencasNormalizadas[$alunoId] = [
                    'aluno_id' => $alunoId,
                    'situacao' => $situacao,
                ];
            }

            $presencasNormalizadas = array_values($presencasNormalizadas);
            $this->repository->syncPresencasTreino($treinoId, $presencasNormalizadas);

            if (!empty($presencasNormalizadas)) {
                $this->repository->markTreinoAsConcluido($treinoId);
            }

            return [
                'total_alunos' => count($alunoIdsValidos),
                'total_lancados' => count($presencasNormalizadas),
                'presentes' => count(array_filter($presencasNormalizadas, fn (array $item) => $item['situacao'] === 'presente')),
                'ausentes' => count(array_filter($presencasNormalizadas, fn (array $item) => $item['situacao'] === 'ausente')),
                'justificados' => count(array_filter($presencasNormalizadas, fn (array $item) => $item['situacao'] === 'justificado')),
            ];
        });
    }

    public function cancelTreino(int $turmaId, int $treinoId): void {
        $this->transaction(function () use ($turmaId, $treinoId) {
            $treino = $this->repository->findTreinoAgendaByIdAndTurmaId($treinoId, $turmaId);
            if (!$treino) {
                throw new \RuntimeException("Treino da turma nao encontrado.");
            }

            if (($treino['status'] ?? '') === 'cancelado') {
                throw new \InvalidArgumentException("Este treino ja esta cancelado.");
            }

            if (($treino['status'] ?? '') === 'concluido') {
                throw new \InvalidArgumentException("Nao e possivel cancelar um treino que ja foi concluido.");
            }

            $this->repository->cancelTreinoAgendaByIdAndTurmaId($treinoId, $turmaId);
        });
    }

    public function deactivate(int $id): void {
        $this->transaction(function () use ($id) {
            if (!$this->repository->exists($id)) {
                throw new \Exception("Turma nao encontrada.");
            }

            $this->repository->deactivate($id);
        });
    }

    public function reactivate(int $id): void {
        $this->transaction(function () use ($id) {
            if (!$this->repository->exists($id)) {
                throw new \Exception("Turma nao encontrada.");
            }

            $this->repository->reactivate($id);
        });
    }

    private function rulesForCreate(): array {
        return [
            'nome' => ['required', 'string', 'max_length:100'],
            'capacidade_minima' => ['required', 'numeric', 'min:1', 'less_than_field:capacidade_maxima'],
            'capacidade_maxima' => ['required', 'numeric', 'min:1', 'greater_than_field:capacidade_minima'],
            'instrutor_id' => ['nullable', 'integer', 'min:1'],
            'config_horarios' => ['nullable', 'array', new ConfigHorariosRule()],
        ];
    }

    private function rulesForUpdate(): array {
        return [
            'nome' => ['string', 'max_length:100'],
            'capacidade_minima' => ['numeric', 'min:1', 'less_than_field:capacidade_maxima'],
            'capacidade_maxima' => ['numeric', 'min:1', 'greater_than_field:capacidade_minima'],
            'instrutor_id' => ['nullable', 'integer', 'min:1'],
            'config_horarios' => ['nullable', 'array', new ConfigHorariosRule()],
        ];
    }

    private function messages(): array {
        return [
            'nome.required' => 'Nome e obrigatorio.',
            'nome.max_length' => 'Nome nao pode exceder 100 caracteres.',
            'capacidade_minima.required' => 'Capacidade minima e obrigatoria.',
            'capacidade_minima.min' => 'Capacidade minima deve ser um numero positivo.',
            'capacidade_maxima.required' => 'Capacidade maxima e obrigatoria.',
            'capacidade_maxima.min' => 'Capacidade maxima deve ser um numero positivo.',
            'capacidade_minima.less_than_field' => 'Capacidade minima deve ser menor que a maxima.',
            'capacidade_maxima.greater_than_field' => 'Capacidade maxima deve ser maior que a minima.',
            'instrutor_id.min' => 'ID do instrutor invalido.',
        ];
    }

    private function attributes(): array {
        return [
            'nome' => 'Nome',
            'capacidade_minima' => 'Capacidade minima',
            'capacidade_maxima' => 'Capacidade maxima',
            'instrutor_id' => 'ID do instrutor',
            'config_horarios' => 'Config horarios',
        ];
    }

    private function buildManagementMetrics(array $treinos): array {
        $totalTreinos = count($treinos);
        $treinosConcluidos = count(array_filter($treinos, fn (array $treino) => ($treino['status'] ?? '') === 'concluido'));
        $treinosAgendados = count(array_filter($treinos, fn (array $treino) => ($treino['status'] ?? '') === 'agendado'));

        $presencasConsideradas = [];
        foreach ($treinos as $treino) {
            if (($treino['status'] ?? '') === 'cancelado') {
                continue;
            }

            foreach (($treino['presenca'] ?? []) as $presenca) {
                $presencasConsideradas[] = $presenca;
            }
        }

        $totalPresencas = count($presencasConsideradas);
        $presentes = count(array_filter(
            $presencasConsideradas,
            fn (array $presenca) => ($presenca['situacao'] ?? '') === 'presente'
        ));

        return [
            'total_treinos' => $totalTreinos,
            'treinos_concluidos' => $treinosConcluidos,
            'treinos_agendados' => $treinosAgendados,
            'taxa_presenca' => $totalPresencas > 0 ? round(($presentes / $totalPresencas) * 100, 1) : 0,
        ];
    }

    private function resolveManagementRange(?string $start, ?string $end): array {
        try {
            $rangeStart = $start ? new \DateTimeImmutable($start) : new \DateTimeImmutable('first day of this month 00:00:00');
        } catch (\Exception) {
            $rangeStart = new \DateTimeImmutable('first day of this month 00:00:00');
        }

        try {
            $rangeEnd = $end ? new \DateTimeImmutable($end) : $rangeStart->modify('first day of next month 00:00:00');
        } catch (\Exception) {
            $rangeEnd = $rangeStart->modify('first day of next month 00:00:00');
        }

        if ($rangeEnd <= $rangeStart) {
            $rangeEnd = $rangeStart->modify('+1 month');
        }

        return [$rangeStart, $rangeEnd];
    }

    private function buildSuggestedTreinos(
        array $turma,
        array $treinos,
        \DateTimeImmutable $rangeStart,
        \DateTimeImmutable $rangeEnd
    ): array {
        $configHorarios = $turma['config_horarios'] ?? [];
        if (empty($configHorarios)) {
            return [];
        }

        $diasMap = [
            'segunda' => 1,
            'terca' => 2,
            'quarta' => 3,
            'quinta' => 4,
            'sexta' => 5,
            'sabado' => 6,
            'domingo' => 7,
        ];

        $slotsExistentes = [];
        foreach ($treinos as $treino) {
            $slotsExistentes[$treino['data_hora_inicio'] . '|' . $treino['data_hora_fim']] = true;
        }

        $sugestoes = [];
        $cursor = $rangeStart->setTime(0, 0, 0);
        $ultimoDia = $rangeEnd->setTime(0, 0, 0);

        while ($cursor < $ultimoDia) {
            $weekday = (int) $cursor->format('N');

            foreach ($configHorarios as $index => $horario) {
                $diaSemana = $horario['dia_semana'] ?? null;
                if (!isset($diasMap[$diaSemana]) || $diasMap[$diaSemana] !== $weekday) {
                    continue;
                }

                $inicio = $horario['hora_inicio'] ?? null;
                $fim = $horario['hora_fim'] ?? null;
                if (!$inicio || !$fim) {
                    continue;
                }

                $dataBase = $cursor->format('Y-m-d');
                $dataHoraInicio = $dataBase . ' ' . substr($inicio, 0, 8);
                $dataHoraFim = $dataBase . ' ' . substr($fim, 0, 8);
                $slotKey = $dataHoraInicio . '|' . $dataHoraFim;

                if (isset($slotsExistentes[$slotKey])) {
                    continue;
                }

                $sugestoes[] = [
                    'id' => sprintf('sugestao-%d-%s-%d', (int) $turma['id'], $cursor->format('Ymd'), $index + 1),
                    'tipo' => 'sugestao',
                    'dia_semana' => $diaSemana,
                    'data_hora_inicio' => $dataHoraInicio,
                    'data_hora_fim' => $dataHoraFim,
                    'status' => 'pendente',
                ];
            }

            $cursor = $cursor->modify('+1 day');
        }

        usort($sugestoes, fn (array $a, array $b) => strcmp($a['data_hora_inicio'], $b['data_hora_inicio']));

        return $sugestoes;
    }
}
