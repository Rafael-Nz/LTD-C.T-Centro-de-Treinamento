<?php
require_once __DIR__ . '/financeiro_bootstrap.php';

// Teste unitário das regras; repositório em memória, sem acessar o banco real.
class RepositorioAcoesTeste extends \Financeiro\FinanceiroRepository {
    public array $catalogo = ['id' => 1, 'ativo' => 0, 'nome' => 'Original'];
    public array $contratoTeste = ['id' => 1, 'status' => 'ativo', 'data_fim' => null, 'valor_contratado' => 100];
    public function __construct() {}
    public function bloquearCatalogo(string $table, int $id): ?array { return $id === 1 ? $this->catalogo : null; }
    public function servicoPorNome(string $nome): ?array { return null; }
    public function planoPorNome(string $nome): ?array { return $nome === 'Duplicado' ? ['id' => 2] : null; }
    public function salvarCatalogo(string $table, int $id, array $dados): void { $this->catalogo = array_replace($this->catalogo, $dados); }
    public function bloquearContrato(int $id): ?array { return $id === 1 ? $this->contratoTeste : null; }
    public function salvarStatusContrato(int $id, string $status): void { $this->contratoTeste['status'] = $status; }
}
class ServicoAcoesTeste extends \Financeiro\FinanceiroService {
    protected function transaction(callable $callback) { return $callback(); }
}
$repo = new RepositorioAcoesTeste();
$service = new ServicoAcoesTeste();
(new ReflectionProperty(\Financeiro\FinanceiroService::class, 'repo'))->setValue($service, $repo);
function verificarAcao(bool $ok): void { if (!$ok) throw new RuntimeException('Falha nas ações financeiras.'); }
function rejeitarAcao(callable $fn): void {
    try { $fn(); } catch (InvalidArgumentException $e) { return; }
    throw new RuntimeException('Operação inválida foi aceita.');
}
$service->editarCatalogo('plano', 1, \Financeiro\DTO\PlanoDTO::fromArray(['nome' => 'Novo', 'valor' => '150.00', 'periodicidade' => 'trimestral']));
verificarAcao($repo->catalogo['ativo'] === 0 && $repo->contratoTeste['valor_contratado'] === 100);
$service->atualizarServico(1, \Financeiro\DTO\ServicoDTO::fromArray(['nome' => 'Serviço', 'valor_base' => '30.00', 'tipo' => 'taxa', 'recorrente' => false]));
verificarAcao($repo->catalogo['ativo'] === 0 && $repo->catalogo['recorrente'] === 0);
$service->statusCatalogo('servico', 1, true);
verificarAcao($repo->catalogo['ativo'] === 1);
rejeitarAcao(fn () => $service->statusCatalogo('plano', 1, 'false'));
rejeitarAcao(fn () => $service->editarCatalogo('plano', 1, \Financeiro\DTO\PlanoDTO::fromArray(['nome' => 'Duplicado', 'valor' => '10.00'])));
rejeitarAcao(fn () => $service->statusContrato(1, 'invalido'));
$service->statusContrato(1, 'pausado');
$service->statusContrato(1, 'ativo');
verificarAcao($repo->contratoTeste['status'] === 'ativo');
$repo->contratoTeste['data_fim'] = '2000-01-01';
$service->statusContrato(1, 'pausado');
rejeitarAcao(fn () => $service->statusContrato(1, 'ativo'));
foreach (['encerrado', 'cancelado'] as $status) {
    $repo->contratoTeste['status'] = 'ativo';
    $service->statusContrato(1, $status);
    rejeitarAcao(fn () => $service->statusContrato(1, 'ativo'));
}
echo "OK: edição preserva status e contrato, ativação, validações e transições de contrato.\n";
