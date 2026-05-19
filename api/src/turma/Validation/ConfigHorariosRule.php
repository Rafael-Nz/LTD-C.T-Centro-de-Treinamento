<?php
namespace Turma\Validation;

use Core\Validation\ValidationRuleInterface;
use Turma\DTO\TurmaConfigHorarioDTO;

class ConfigHorariosRule implements ValidationRuleInterface {
    private const DIAS_SEMANA_VALIDOS = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];

    public function validate(string $field, mixed $value, array $data, callable $fail): void {
        if ($value === null) {
            return;
        }

        if (!is_array($value)) {
            $fail('Config horaria deve ser uma lista valida.');
            return;
        }

        $erros = [];
        $faixasPorDia = [];

        foreach ($value as $index => $horario) {
            if ($horario instanceof TurmaConfigHorarioDTO) {
                $horario = $horario->toArray();
            } elseif (is_object($horario)) {
                $horario = get_object_vars($horario);
            }

            if (!is_array($horario)) {
                $erros[] = 'Config horario invalido na posicao ' . ($index + 1) . '.';
                continue;
            }

            $posicao = $index + 1;
            $diaSemana = $horario['dia_semana'] ?? null;
            $horaInicio = $horario['hora_inicio'] ?? null;
            $horaFim = $horario['hora_fim'] ?? null;

            if (empty($diaSemana) || !in_array($diaSemana, self::DIAS_SEMANA_VALIDOS, true)) {
                $erros[] = "Dia da semana invalido na configuracao de horario {$posicao}.";
                continue;
            }

            if (!$this->isValidTime($horaInicio)) {
                $erros[] = "Hora de inicio invalida na configuracao de horario {$posicao}. Use HH:MM ou HH:MM:SS.";
            }

            if (!$this->isValidTime($horaFim)) {
                $erros[] = "Hora de fim invalida na configuracao de horario {$posicao}. Use HH:MM ou HH:MM:SS.";
            }

            if (!$this->isValidTime($horaInicio) || !$this->isValidTime($horaFim)) {
                continue;
            }

            if (strtotime((string) $horaInicio) >= strtotime((string) $horaFim)) {
                $erros[] = "Hora de inicio deve ser menor que a hora de fim na configuracao de horario {$posicao}.";
                continue;
            }

            $faixasPorDia[$diaSemana][] = [
                'inicio' => $horaInicio,
                'fim' => $horaFim,
                'posicao' => $posicao,
            ];
        }

        foreach ($faixasPorDia as $dia => $faixas) {
            usort($faixas, fn (array $a, array $b) => strcmp($a['inicio'], $b['inicio']));

            for ($i = 1, $count = count($faixas); $i < $count; $i++) {
                if ($faixas[$i]['inicio'] < $faixas[$i - 1]['fim']) {
                    $erros[] = "Existem horarios sobrepostos para {$dia} nas configuracoes {$faixas[$i - 1]['posicao']} e {$faixas[$i]['posicao']}.";
                }
            }
        }

        foreach ($erros as $erro) {
            $fail($erro);
        }
    }

    private function isValidTime(mixed $time): bool {
        return is_string($time) && preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $time) === 1;
    }
}
