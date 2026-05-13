<?php
namespace Core\DataTables;

interface DataTablesRepositoryInterface {
    public function findPaginated(int $start, int $length, string $search = '', array $filters = []): array;
    public function countAll(): int;
    public function countFiltered(string $search = '', array $filters = []): int;
}
