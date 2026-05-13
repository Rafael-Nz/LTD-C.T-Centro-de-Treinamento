<?php
namespace Core\DataTables;

trait DataTablesResponseTrait {
    protected function dataTablesResponse(
        DataTablesRepositoryInterface $repository,
        int $draw,
        int $start,
        int $length,
        string $search,
        array $filters = []
    ): void {
        if ($length === -1) {
            $length = 10;
        }

        $data = $repository->findPaginated($start, $length, $search, $filters);
        $total = $repository->countAll();
        $hasActiveFilters = !empty($search) || !empty(array_filter($filters));
        $totalFiltered = $hasActiveFilters
            ? $repository->countFiltered($search, $filters)
            : $total;

        $this->datatable([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }

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

        if ($dataTransformer !== null) {
            $data = $dataTransformer($data);
        }

        $total = $repository->countAll();
        $hasActiveFilters = !empty($search) || !empty(array_filter($filters));
        $totalFiltered = $hasActiveFilters
            ? $repository->countFiltered($search, $filters)
            : $total;

        $this->datatable([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $totalFiltered,
            'data' => $data
        ]);
    }
}
