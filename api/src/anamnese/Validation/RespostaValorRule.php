<?php
namespace Anamnese\Validation;

use Anamnese\DTO\PerguntaDTO;
use Core\Validation\ValidationRuleInterface;

class RespostaValorRule implements ValidationRuleInterface {
    private PerguntaDTO $pergunta;

    public function __construct(PerguntaDTO $pergunta) {
        $this->pergunta = $pergunta;
    }

    public function validate(string $field, mixed $value, array $data, callable $fail): void {
        if (!$this->pergunta->obrigatoria && ($value === null || $value === '')) {
            return;
        }

        switch ($this->pergunta->tipo_input) {
            case 'text':
            case 'textarea':
            case 'date':
                if (!is_string($value)) {
                    $fail("Campo '{$this->pergunta->pergunta}' deve ser texto");
                }
                return;

            case 'number':
                if (!is_numeric($value)) {
                    $fail("Campo '{$this->pergunta->pergunta}' deve ser um numero");
                }
                return;

            case 'boolean':
                if (!is_bool($value) && !in_array($value, [0, 1, '0', '1', 'sim', 'nao', true, false], true)) {
                    $fail("Campo '{$this->pergunta->pergunta}' deve ser verdadeiro/falso");
                }
                return;

            case 'select':
            case 'radio':
                $valoresPermitidos = array_map(fn ($op) => $op->valor, $this->pergunta->opcoes);
                if (!in_array($value, $valoresPermitidos, true)) {
                    $fail("Valor invalido para '{$this->pergunta->pergunta}'");
                }
                return;

            case 'checkbox':
                if (!is_array($value)) {
                    $fail("Campo '{$this->pergunta->pergunta}' deve ser uma lista de opcoes");
                    return;
                }

                $valoresPermitidos = array_map(fn ($op) => $op->valor, $this->pergunta->opcoes);
                foreach ($value as $item) {
                    if (!in_array($item, $valoresPermitidos, true)) {
                        $fail("Opcao invalida em '{$this->pergunta->pergunta}': {$item}");
                    }
                }
                return;
        }
    }
}
