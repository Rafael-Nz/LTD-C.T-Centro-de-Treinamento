<?php
namespace Anamnese;

use Anamnese\AnamneseRepository;
use Anamnese\DTO\EnvioAnamneseDTO;
use Anamnese\DTO\FormularioDTO;
use Anamnese\DTO\PerguntaDTO;
use Anamnese\DTO\RespostaDTO;
use Anamnese\Validation\RespostaValorRule;
use Core\Services\Service;

class AnamneseService extends Service {
    private AnamneseRepository $repo;

    public function __construct() {
        $this->repo = new AnamneseRepository();
    }

    public function listarFormularios(): array {
        return $this->repo->listarFormulariosAtivos();
    }

    public function obterFormularioDTO(int $formularioId): ?FormularioDTO {
        return $this->repo->getFormularioDTO($formularioId);
    }

    public function getFormulario(int $formularioId = 1): array {
        $this->validateData(
            ['formulario_id' => $formularioId],
            ['formulario_id' => ['required', 'integer', 'min:1']],
            ['formulario_id.min' => 'Formulario invalido'],
            ['formulario_id' => 'Formulario']
        );

        return $this->repo->getFormulario($formularioId);
    }

    public function getRespostas(int $alunoId): array {
        $this->validateData(
            ['aluno_id' => $alunoId],
            ['aluno_id' => ['required', 'integer', 'min:1']],
            ['aluno_id.min' => 'Aluno invalido'],
            ['aluno_id' => 'Aluno']
        );

        return $this->repo->getRespostasByAluno($alunoId);
    }

    public function salvar(EnvioAnamneseDTO $dto): void {
        $this->validateData($dto, $this->rulesForSalvar(), $this->messages(), $this->attributes());

        $formularioId = $dto->formulario_id ?? 1;
        $perguntasMap = $this->repo->getPerguntasMap($formularioId);
        if (empty($perguntasMap)) {
            throw new \RuntimeException("Nenhuma pergunta ativa encontrada");
        }

        $this->transaction(function () use ($dto, $perguntasMap) {
            $respostas = [];

            foreach ($dto->respostas as $respostaDTO) {
                $pergunta = $perguntasMap[$respostaDTO->pergunta_id] ?? null;
                if (!$pergunta) {
                    throw new \InvalidArgumentException("Pergunta ID {$respostaDTO->pergunta_id} nao encontrada ou inativa");
                }

                $this->validateResposta($respostaDTO, $pergunta);

                $respostas[] = [
                    'pergunta_id' => $respostaDTO->pergunta_id,
                    'valor' => $this->normalizarValor($respostaDTO->valor, $pergunta),
                    'observacao' => $respostaDTO->observacao
                ];
            }

            $this->repo->salvarRespostas($dto->aluno_id, $respostas);
        });
    }

    private function validateResposta(RespostaDTO $resposta, PerguntaDTO $pergunta): void {
        $this->validateData(
            $resposta,
            [
                'pergunta_id' => ['required', 'integer', 'min:1'],
                'valor' => [$pergunta->obrigatoria ? 'required' : 'nullable', new RespostaValorRule($pergunta)],
                'observacao' => ['nullable', 'string'],
            ],
            [],
            [
                'pergunta_id' => 'Pergunta',
                'valor' => $pergunta->pergunta,
                'observacao' => 'Observacao',
            ]
        );
    }

    private function normalizarValor(mixed $valor, PerguntaDTO $pergunta): mixed {
        if ($valor === null || $valor === '') {
            return null;
        }

        switch ($pergunta->tipo_input) {
            case 'boolean':
                if (is_string($valor)) {
                    $lower = strtolower(trim($valor));
                    if ($lower === 'sim' || $lower === 'true' || $lower === '1') return true;
                    if ($lower === 'nao' || $lower === 'false' || $lower === '0') return false;
                }
                return (bool) $valor;

            case 'number':
                return is_numeric($valor) ? $valor + 0 : null;

            case 'checkbox':
                return is_array($valor) ? array_values($valor) : [$valor];

            default:
                return $valor;
        }
    }

    private function rulesForSalvar(): array {
        return [
            'aluno_id' => ['required', 'integer', 'min:1'],
            'formulario_id' => ['nullable', 'integer', 'min:1'],
            'respostas' => ['array'],
        ];
    }

    private function messages(): array {
        return [
            'aluno_id.required' => 'Aluno e obrigatorio',
            'aluno_id.min' => 'Aluno e obrigatorio',
            'formulario_id.min' => 'Formulario invalido',
        ];
    }

    private function attributes(): array {
        return [
            'aluno_id' => 'Aluno',
            'formulario_id' => 'Formulario',
            'respostas' => 'Respostas',
        ];
    }
}
