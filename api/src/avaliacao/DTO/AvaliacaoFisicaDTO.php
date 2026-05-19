<?php
namespace Avaliacao\DTO;

use Core\DTO\BaseDTO;

class AvaliacaoFisicaDTO extends BaseDTO {
    public ?int $id = null;
    public ?string $data_avaliacao = null;
    public ?float $peso = null;
    public ?float $altura = null;
    public ?float $imc = null;
    public ?float $cintura = null;
    public ?float $torax = null;
    public ?float $braco_dc = null;
    public ?float $braco_d = null;
    public ?float $braco_ec = null;
    public ?float $braco_e = null;
    public ?float $coxa_d = null;
    public ?float $coxa_e = null;
    public ?float $panturrilha_d = null;
    public ?float $panturrilha_e = null;
    public ?float $percentual_gordura = null;
    public ?float $percentual_musculo = null;
    public ?int $metabolismo_repouso = null;
    public ?int $idade_biologica = null;
    public ?float $gordura_visceral = null;
    public ?string $observacoes = null;
}
