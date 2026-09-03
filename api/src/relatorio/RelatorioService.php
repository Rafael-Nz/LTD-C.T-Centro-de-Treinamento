<?php
namespace Relatorio;

class RelatorioService
{
    private RelatorioRepository $repository;

    public function __construct()
    {
        $this->repository = new RelatorioRepository();
    }

    public function metricas(): array
    {
        return $this->repository->metricas();
    }

    public function gerar(string $tipo, array $filters): array
    {
        $relatorios = [
            'alunos' => 'alunos',
            'presenca' => 'presenca',
            'avaliacoes' => 'avaliacoes',
            'turmas' => 'turmas',
            'funcionarios' => 'funcionarios',
            'treinos' => 'treinos',
        ];
        if (!isset($relatorios[$tipo])) {
            throw new \InvalidArgumentException('Tipo de relatorio invalido.');
        }
        return ['tipo' => $tipo, 'registros' => $this->repository->{$relatorios[$tipo]}($filters)];
    }
}
