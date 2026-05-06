<?php
namespace Core;

interface DataTablesRepositoryInterface {
    /**
     * Busca dados paginados para o DataTables
     * 
     * @param int $start Offset inicial
     * @param int $length Quantidade de registros
     * @param string $search Termo de busca global
     * @param array $filters Filtros adicionais
     * @return array Lista de registros
     */
    public function findPaginated(int $start, int $length, string $search = '', array $filters = []): array;
    
    /**
     * Conta total de registros (sem filtros)
     * 
     * @return int Total de registros
     */
    public function countAll(): int;
    
    /**
     * Conta registros com filtros aplicados
     * 
     * @param string $search Termo de busca global
     * @param array $filters Filtros adicionais
     * @return int Total de registros filtrados
     */
    public function countFiltered(string $search = '', array $filters = []): int;
}