<?php
namespace Core;

trait DataTablesResponseTrait {
    
    /**
     * Gera resposta padrão para DataTables
     */
    protected function dataTablesResponse(
        DataTablesRepositoryInterface $repository,
        int $draw,
        int $start,
        int $length,
        string $search,
        array $filters = []
    ): void {
        // Se length for -1, usar valor padrão
        if ($length === -1) {
            $length = 10;
        }

        // Buscar dados paginados
        $data = $repository->findPaginated($start, $length, $search, $filters);
        
        // Total geral (sem filtros)
        $total = $repository->countAll();
        
        // Verificar se tem filtros ativos
        $hasActiveFilters = !empty($search) || !empty(array_filter($filters));
        
        // Total com filtros aplicados
        $totalFiltered = $hasActiveFilters 
            ? $repository->countFiltered($search, $filters)
            : $total;
        
        // Retornar resposta padronizada
        $this->json([
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $totalFiltered,
            "data" => $data
        ]);
    }

    /**
     * Versão mais flexível com callback
     */
    protected function dataTablesResponseCustom(
        DataTablesRepositoryInterface $repository,
        int $draw,
        int $start,
        int $length,
        string $search,
        array $filters = [],
        ?callable $dataTransformer = null
    ): void {
        if ($length === -1) {
            $length = 10;
        }

        $data = $repository->findPaginated($start, $length, $search, $filters);
        
        // Aplicar transformação personalizada se fornecida
        if ($dataTransformer !== null) {
            $data = $dataTransformer($data);
        }
        
        $total = $repository->countAll();
        $hasActiveFilters = !empty($search) || !empty(array_filter($filters));
        $totalFiltered = $hasActiveFilters 
            ? $repository->countFiltered($search, $filters)
            : $total;
        
        $this->json([
            "draw" => $draw,
            "recordsTotal" => $total,
            "recordsFiltered" => $totalFiltered,
            "data" => $data
        ]);
    }
}