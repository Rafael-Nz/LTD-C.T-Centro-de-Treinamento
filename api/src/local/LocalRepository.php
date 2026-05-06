<?php
namespace Local;

use Core\Repository;
use Core\DataTablesRepositoryInterface;
use Local\DTO\LocalDTO;   

class LocalRepository extends Repository implements DataTablesRepositoryInterface {

    public function countAll(): int {
        $result = $this->fetch("SELECT COUNT(*) as total FROM espaco_treino");
        return (int) ($result['total'] ?? 0);
    }

    public function findPaginated(int $start, int $length, string $search = '', array $filters = []): array {
        $params = [];
        $where  = [];

        $sql = "SELECT
                    id,
                    nome,
                    capacidade_minima,
                    capacidade_maxima,
                    equipamentos,
                    ativo,
                    data_criacao,
                    data_atualizacao
                FROM espaco_treino";

        if (!empty($search)) {
            $where[] = "(nome LIKE ? OR equipamentos LIKE ?)";
            array_push($params, "%$search%", "%$search%");
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "ativo = ?";
            $params[] = $filters['status'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY nome ASC LIMIT ? OFFSET ?";
        $params[] = $length;
        $params[] = $start;

        return $this->fetchAll($sql, $params);
    }

    public function countFiltered(string $search = '', array $filters = []): int {
        $params = [];
        $where  = [];

        $sql = "SELECT COUNT(*) as total FROM espaco_treino"; 

        if (!empty($search)) {
            $where[] = "(nome LIKE ? OR equipamentos LIKE ?)";
            array_push($params, "%$search%", "%$search%");
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "ativo = ?";
            $params[] = $filters['status'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $result = $this->fetch($sql, $params);
        return (int) ($result['total'] ?? 0);
    }

    public function findAll(): array {
        return $this->fetchAll("
            SELECT id, nome, capacidade_minima, capacidade_maxima, equipamentos, ativo
            FROM espaco_treino
            ORDER BY nome
        ");
    }

    public function findById(int $id): ?array {
        return $this->fetch("
            SELECT id, nome, capacidade_minima, capacidade_maxima, equipamentos, ativo,
                   data_criacao, data_atualizacao
            FROM espaco_treino
            WHERE id = ?
        ", [$id]);
    }

    public function findByNome(string $nome): ?array {
        return $this->fetch(
            "SELECT id FROM espaco_treino WHERE nome = ?",  
            [$nome]
        );
    }

    public function create(LocalDTO $dto): int {
        $this->execute("
            INSERT INTO espaco_treino (nome, capacidade_minima, capacidade_maxima, equipamentos, ativo)
            VALUES (?, ?, ?, ?, ?)
        ", [
            $dto->nome,
            $dto->capacidade_minima,
            $dto->capacidade_maxima,
            $dto->equipamentos,
            $dto->ativo ? 1 : 0,
        ]);

        return (int) $this->lastInsertId();
    }

    public function update(int $id, LocalDTO $dto): bool {
        return $this->execute("
            UPDATE espaco_treino SET    
                nome              = ?,
                capacidade_minima = ?,
                capacidade_maxima = ?,
                equipamentos      = ?,
                ativo             = ?
            WHERE id = ?
        ", [
            $dto->nome,
            $dto->capacidade_minima,
            $dto->capacidade_maxima,
            $dto->equipamentos,
            $dto->ativo ? 1 : 0,
            $id,
        ]);
    }

    public function deactivate(int $id): bool {
        return $this->execute("UPDATE espaco_treino SET ativo = 0 WHERE id = ?", [$id]); 
    }
    public function reactivate(int $id): bool
    {
        return $this->execute("UPDATE espaco_treino SET ativo = 1 WHERE id = ?", [$id]); 
    }
    
}