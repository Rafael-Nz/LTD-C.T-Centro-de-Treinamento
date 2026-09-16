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
        if ($tipo === 'presenca' && filter_var($filters['aluno'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            throw new \InvalidArgumentException('Selecione um aluno para o relatorio de presenca.');
        }
        if ($tipo === 'avaliacoes' && empty($filters['aluno'])) {
            throw new \InvalidArgumentException('Selecione um aluno para o relatorio de avaliacoes fisicas.');
        }
        if ($tipo === 'treinos' && filter_var($filters['turma'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            throw new \InvalidArgumentException('Selecione uma turma para o relatorio de Agenda de Treinos.');
        }
        return ['tipo' => $tipo, 'registros' => $this->repository->{$relatorios[$tipo]}($filters)];
    }
}
