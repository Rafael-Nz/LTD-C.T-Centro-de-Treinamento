<?php
namespace Anamnese;

use Core\Service;
use Anamnese\AnamneseRepository;
use Anamnese\DTO\FormularioDTO;
use Anamnese\DTO\EnvioAnamneseDTO;
use Anamnese\DTO\RespostaDTO;
use Anamnese\DTO\PerguntaDTO;

class AnamneseService extends Service {
    private AnamneseRepository $repo;

    public function __construct() {
        $this->repo = new AnamneseRepository();
    }
     
    /**
     * Lista todos os formulários ativos sem perguntas
     */
    public function listarFormularios(): array {
        return $this->repo->listarFormulariosAtivos();
    }

    /**
     * Retorna FormularioDTO completo com perguntas
     */
    public function obterFormularioDTO(int $formularioId): ?FormularioDTO {
        return $this->repo->getFormularioDTO($formularioId);
    }

    /**
     * Retorna perguntas do formulário como array (consumido pelo frontend)
     */
    public function getFormulario(int $formularioId = 1): array {
        return $this->repo->getFormulario($formularioId);
    }

    /**
     * Retorna respostas do aluno
     */
    public function getRespostas(int $alunoId): array {
        if ($alunoId <= 0) {
            throw new \InvalidArgumentException("Aluno inválido");
        }
        return $this->repo->getRespostasByAluno($alunoId);
    }

    /**
     * Salvar anamnese completa com validação robusta
     */
    public function salvar(EnvioAnamneseDTO $dto): void {
        if (!$dto->aluno_id) {
            throw new \InvalidArgumentException("Aluno é obrigatório");
        }

        $formularioId = $dto->formulario_id ?? 1;
        $perguntasMap = $this->repo->getPerguntasMap($formularioId);

        // Mapa de perguntas para validação
        $perguntasMap = $this->repo->getPerguntasMap();
        if (empty($perguntasMap)) {
            throw new \RuntimeException("Nenhuma pergunta ativa encontrada");
        }

        $this->transaction(function () use ($dto, $perguntasMap) {
            $respostas = [];

            foreach ($dto->respostas as $respostaDTO) {
                $pergunta = $perguntasMap[$respostaDTO->pergunta_id] ?? null;
                if (!$pergunta) {
                    throw new \InvalidArgumentException("Pergunta ID {$respostaDTO->pergunta_id} não encontrada ou inativa");
                }

                $this->validarResposta($respostaDTO, $pergunta);

                $respostas[] = [
                    'pergunta_id' => $respostaDTO->pergunta_id,
                    'valor'       => $this->normalizarValor($respostaDTO->valor, $pergunta),
                    'observacao'  => $respostaDTO->observacao
                ];
            }

            $this->repo->salvarRespostas($dto->aluno_id, $respostas);
        });
    }

    /**
     * Valida resposta baseada no tipo da pergunta e nas opções disponíveis
     */
    private function validarResposta(RespostaDTO $resposta, PerguntaDTO $pergunta): void {
        // Obrigatoriedade
        if ($pergunta->obrigatoria && ($resposta->valor === null || $resposta->valor === '')) {
            throw new \InvalidArgumentException("Pergunta '{$pergunta->pergunta}' é obrigatória");
        }

        // Se não é obrigatória e valor vazio, pula outras validações
        if (!$pergunta->obrigatoria && ($resposta->valor === null || $resposta->valor === '')) {
            return;
        }

        $valor = $resposta->valor;

        switch ($pergunta->tipo_input) {
            case 'text':
            case 'textarea':
            case 'date':
                if (!is_string($valor)) {
                    throw new \InvalidArgumentException("Campo '{$pergunta->pergunta}' deve ser texto");
                }
                break;

            case 'number':
                if (!is_numeric($valor)) {
                    throw new \InvalidArgumentException("Campo '{$pergunta->pergunta}' deve ser um número");
                }
                break;

            case 'boolean':
                if (!is_bool($valor) && !in_array($valor, [0, 1, '0', '1', 'sim', 'nao', true, false], true)) {
                    throw new \InvalidArgumentException("Campo '{$pergunta->pergunta}' deve ser verdadeiro/falso");
                }
                break;

            case 'select':
            case 'radio':
                // Valor único deve estar entre as opções disponíveis
                $valoresPermitidos = array_map(fn($op) => $op->valor, $pergunta->opcoes);
                if (!in_array($valor, $valoresPermitidos, true)) {
                    throw new \InvalidArgumentException("Valor inválido para '{$pergunta->pergunta}'");
                }
                break;

            case 'checkbox':
                if (!is_array($valor)) {
                    throw new \InvalidArgumentException("Campo '{$pergunta->pergunta}' deve ser uma lista de opções");
                }
                $valoresPermitidos = array_map(fn($op) => $op->valor, $pergunta->opcoes);
                foreach ($valor as $v) {
                    if (!in_array($v, $valoresPermitidos, true)) {
                        throw new \InvalidArgumentException("Opção inválida em '{$pergunta->pergunta}': {$v}");
                    }
                }
                break;

            default:
                // Tipo desconhecido, deixa passar (ou logar)
                break;
        }
    }

    /**
     * Normaliza o valor conforme o tipo da pergunta para salvar em JSON
     */
    private function normalizarValor(mixed $valor, PerguntaDTO $pergunta): mixed {
        // Se for nulo ou vazio, retorna null
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
                // Mantém array, cada item será salvo como está no JSON
                return is_array($valor) ? array_values($valor) : [$valor];

            default:
                return $valor;
        }
    }
}