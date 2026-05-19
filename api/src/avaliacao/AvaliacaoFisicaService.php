<?php
namespace Avaliacao;

use Aluno\AlunoService;
use Avaliacao\DTO\AvaliacaoFisicaDTO;
use Core\Services\Service;

class AvaliacaoFisicaService extends Service {
    private AvaliacaoFisicaRepository $repo;
    private AlunoService $alunoService;

    public function __construct() {
        $this->repo = new AvaliacaoFisicaRepository();
        $this->alunoService = new AlunoService();
    }

    public function listByAlunoId(int $alunoId): array {
        $aluno = $this->alunoService->findById($alunoId);
        if (!$aluno) {
            throw new \RuntimeException('Aluno nao encontrado.');
        }

        return array_map(fn (array $item) => $this->enrich($item), $this->repo->findByAlunoId($alunoId));
    }

    public function findById(int $id): ?array {
        $avaliacao = $this->repo->findById($id);
        if (!$avaliacao) {
            return null;
        }

        return $this->enrich($avaliacao);
    }

    public function createForAluno(int $alunoId, int $avaliadorId, AvaliacaoFisicaDTO $dto): int {
        $aluno = $this->alunoService->findById($alunoId);
        if (!$aluno) {
            throw new \InvalidArgumentException('Aluno nao encontrado.');
        }

        if (!$this->repo->isFuncionario($avaliadorId)) {
            throw new \InvalidArgumentException('O usuario autenticado nao pode ser registrado como avaliador.');
        }

        $this->validateDto($dto);
        $dto->imc = $this->calculateImc($dto->peso, $dto->altura);

        return $this->transaction(function () use ($alunoId, $avaliadorId, $dto) {
            return $this->repo->create($alunoId, $avaliadorId, $dto);
        });
    }

    public function update(int $id, AvaliacaoFisicaDTO $dto): void {
        $avaliacao = $this->repo->findById($id);
        if (!$avaliacao) {
            throw new \RuntimeException('Avaliacao nao encontrada.');
        }

        $this->validateDto($dto);
        $dto->imc = $this->calculateImc($dto->peso, $dto->altura);

        $this->transaction(function () use ($id, $dto) {
            $this->repo->update($id, $dto);
        });
    }

    private function validateDto(AvaliacaoFisicaDTO $dto): void {
        $this->validateData($dto, [
            'data_avaliacao' => ['required', 'date'],
            'peso' => ['nullable', 'numeric', 'min:0'],
            'altura' => ['nullable', 'numeric', 'min:0.1'],
            'cintura' => ['nullable', 'numeric', 'min:0'],
            'torax' => ['nullable', 'numeric', 'min:0'],
            'braco_dc' => ['nullable', 'numeric', 'min:0'],
            'braco_d' => ['nullable', 'numeric', 'min:0'],
            'braco_ec' => ['nullable', 'numeric', 'min:0'],
            'braco_e' => ['nullable', 'numeric', 'min:0'],
            'coxa_d' => ['nullable', 'numeric', 'min:0'],
            'coxa_e' => ['nullable', 'numeric', 'min:0'],
            'panturrilha_d' => ['nullable', 'numeric', 'min:0'],
            'panturrilha_e' => ['nullable', 'numeric', 'min:0'],
            'percentual_gordura' => ['nullable', 'numeric', 'min:0'],
            'percentual_musculo' => ['nullable', 'numeric', 'min:0'],
            'metabolismo_repouso' => ['nullable', 'integer', 'min:0'],
            'idade_biologica' => ['nullable', 'integer', 'min:0'],
            'gordura_visceral' => ['nullable', 'numeric', 'min:0'],
            'observacoes' => ['nullable', 'string'],
        ]);
    }

    private function enrich(array $avaliacao): array {
        $idade = $this->calculateAgeAtEvaluation(
            $avaliacao['aluno_data_nascimento'] ?? null,
            $avaliacao['data_avaliacao'] ?? null
        );

        $imc = $avaliacao['imc'] !== null
            ? (float) $avaliacao['imc']
            : $this->calculateImc($avaliacao['peso'] ?? null, $avaliacao['altura'] ?? null);

        return array_merge($avaliacao, [
            'aluno' => [
                'id' => (int) $avaliacao['aluno_id'],
                'nome' => trim(($avaliacao['aluno_nome'] ?? '') . ' ' . ($avaliacao['aluno_sobrenome'] ?? '')),
                'codigo_matricula' => $avaliacao['codigo_matricula'] ?? null,
                'genero' => $avaliacao['aluno_genero'] ?? null,
                'idade_na_avaliacao' => $idade,
            ],
            'avaliador' => [
                'id' => (int) $avaliacao['avaliador_id'],
                'nome' => trim(($avaliacao['avaliador_nome'] ?? '') . ' ' . ($avaliacao['avaliador_sobrenome'] ?? '')),
            ],
            'imc' => $imc,
            'classificacoes' => [
                'imc' => $this->classifyImc($imc),
                'percentual_gordura' => $this->classifyBodyFat(
                    $avaliacao['aluno_genero'] ?? null,
                    $idade,
                    $avaliacao['percentual_gordura'] ?? null
                ),
                'percentual_musculo' => $this->classifyMuscle(
                    $avaliacao['aluno_genero'] ?? null,
                    $idade,
                    $avaliacao['percentual_musculo'] ?? null
                ),
                'gordura_visceral' => $this->classifyVisceralFat($avaliacao['gordura_visceral'] ?? null),
            ],
        ]);
    }

    private function calculateAgeAtEvaluation(?string $birthDate, ?string $evaluationDate): ?int {
        if (!$birthDate || !$evaluationDate) {
            return null;
        }

        try {
            $birth = new \DateTimeImmutable($birthDate);
            $evaluation = new \DateTimeImmutable($evaluationDate);
        } catch (\Throwable) {
            return null;
        }

        return (int) $birth->diff($evaluation)->y;
    }

    private function calculateImc(mixed $peso, mixed $altura): ?float {
        if (!is_numeric($peso) || !is_numeric($altura) || (float) $altura <= 0) {
            return null;
        }

        return round(((float) $peso) / (((float) $altura) * ((float) $altura)), 2);
    }

    private function classifyImc(?float $imc): ?array {
        if ($imc === null) {
            return null;
        }

        if ($imc < 18.5) {
            return ['codigo' => '-', 'label' => 'Abaixo do peso'];
        }

        if ($imc < 25) {
            return ['codigo' => '0', 'label' => 'Normal'];
        }

        if ($imc < 30) {
            return ['codigo' => '+', 'label' => 'Sobrepeso'];
        }

        return ['codigo' => '++', 'label' => 'Obeso'];
    }

    private function classifyBodyFat(?string $genero, ?int $idade, mixed $valor): ?array {
        if (!is_numeric($valor) || !$idade) {
            return null;
        }

        $faixas = $this->bodyFatRanges($genero, $idade);
        return $this->classifyRange((float) $valor, $faixas);
    }

    private function classifyMuscle(?string $genero, ?int $idade, mixed $valor): ?array {
        if (!is_numeric($valor) || !$idade) {
            return null;
        }

        $faixas = $this->muscleRanges($genero, $idade);
        return $this->classifyRange((float) $valor, $faixas);
    }

    private function classifyVisceralFat(mixed $valor): ?array {
        if (!is_numeric($valor)) {
            return null;
        }

        $numero = (float) $valor;
        if ($numero <= 9) {
            return ['codigo' => 'normal', 'label' => 'Nivel Normal'];
        }

        if ($numero <= 14) {
            return ['codigo' => 'alto', 'label' => 'Nivel Alto'];
        }

        return ['codigo' => 'muito_alto', 'label' => 'Nivel Muito Alto'];
    }

    private function classifyRange(float $valor, ?array $faixas): ?array {
        if (!$faixas) {
            return null;
        }

        if ($valor < $faixas['baixo_ate']) {
            return ['codigo' => '-', 'label' => 'Baixo'];
        }

        if ($valor <= $faixas['normal_ate']) {
            return ['codigo' => '0', 'label' => 'Normal'];
        }

        if ($valor <= $faixas['alto_ate']) {
            return ['codigo' => '+', 'label' => 'Alto'];
        }

        return ['codigo' => '++', 'label' => 'Muito Alto'];
    }

    private function bodyFatRanges(?string $genero, int $idade): ?array {
        $genero = strtoupper((string) $genero);

        if ($genero === 'F') {
            if ($idade >= 20 && $idade <= 39) return ['baixo_ate' => 21.0, 'normal_ate' => 32.9, 'alto_ate' => 38.9];
            if ($idade >= 40 && $idade <= 59) return ['baixo_ate' => 23.0, 'normal_ate' => 33.9, 'alto_ate' => 39.9];
            if ($idade >= 60 && $idade <= 79) return ['baixo_ate' => 24.0, 'normal_ate' => 35.9, 'alto_ate' => 41.9];
            return null;
        }

        if ($genero === 'M') {
            if ($idade >= 20 && $idade <= 39) return ['baixo_ate' => 8.0, 'normal_ate' => 19.9, 'alto_ate' => 24.9];
            if ($idade >= 40 && $idade <= 59) return ['baixo_ate' => 11.0, 'normal_ate' => 21.9, 'alto_ate' => 27.9];
            if ($idade >= 60 && $idade <= 79) return ['baixo_ate' => 13.0, 'normal_ate' => 24.9, 'alto_ate' => 29.9];
        }

        return null;
    }

    private function muscleRanges(?string $genero, int $idade): ?array {
        $genero = strtoupper((string) $genero);

        if ($genero === 'F') {
            if ($idade >= 18 && $idade <= 39) return ['baixo_ate' => 24.3, 'normal_ate' => 30.3, 'alto_ate' => 35.3];
            if ($idade >= 40 && $idade <= 59) return ['baixo_ate' => 24.1, 'normal_ate' => 30.1, 'alto_ate' => 35.1];
            if ($idade >= 60 && $idade <= 80) return ['baixo_ate' => 23.9, 'normal_ate' => 29.9, 'alto_ate' => 34.9];
            return null;
        }

        if ($genero === 'M') {
            if ($idade >= 18 && $idade <= 39) return ['baixo_ate' => 33.3, 'normal_ate' => 39.3, 'alto_ate' => 44.0];
            if ($idade >= 40 && $idade <= 59) return ['baixo_ate' => 33.1, 'normal_ate' => 39.1, 'alto_ate' => 43.8];
            if ($idade >= 60 && $idade <= 80) return ['baixo_ate' => 32.9, 'normal_ate' => 38.9, 'alto_ate' => 43.6];
        }

        return null;
    }
}
