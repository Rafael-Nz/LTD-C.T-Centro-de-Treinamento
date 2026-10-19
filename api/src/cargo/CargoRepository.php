<?php
namespace Cargo;

use Cargo\DTO\CargoDTO;
use Core\DataTables\DataTablesRepositoryInterface;
use Core\Database\Repository;

class CargoRepository extends Repository implements DataTablesRepositoryInterface {
    public function countAll(): int {
        $result = $this->fetch('SELECT COUNT(*) as total FROM cargo');
        return (int) ($result['total'] ?? 0);
    }

    public function findPaginated(int $start, int $length, string $search = '', array $filters = []): array {
        $params = [];
        $where = [];

        $sql = "
            SELECT
                c.id,
                c.nome,
                c.descricao,
                c.salario_base,
                c.ativo,
                c.data_criacao,
                c.data_atualizacao
            FROM cargo c
        ";

        if ($search !== '') {
            $where[] = '(c.nome LIKE ?)';
            $params[] = "%{$search}%";
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = 'c.ativo = ?';
            $params[] = $filters['status'];
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY c.nome ASC LIMIT ? OFFSET ?';
        $params[] = $length;
        $params[] = $start;

        return $this->fetchAll($sql, $params);
    }

    public function countFiltered(string $search = '', array $filters = []): int {
        $params = [];
        $where = [];

        $sql = 'SELECT COUNT(*) as total FROM cargo c';

        if ($search !== '') {
            $where[] = '(c.nome LIKE ?)';
            $params[] = "%{$search}%";
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = 'c.ativo = ?';
            $params[] = $filters['status'];
        }

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $result = $this->fetch($sql, $params);
        return (int) ($result['total'] ?? 0);
    }

    public function findAll(): array {
        return $this->fetchAll("
            SELECT
                c.id,
                c.nome,
                c.descricao,
                c.salario_base,
                c.ativo,
                c.data_criacao,
                c.data_atualizacao
            FROM cargo c
            ORDER BY c.nome
        ");
    }

    public function findSimple(bool $somenteAtivos = true): array {
        $sql = "
            SELECT
                c.id,
                c.nome,
                c.descricao,
                c.salario_base,
                c.ativo
            FROM cargo c
        ";

        if ($somenteAtivos) {
            $sql .= ' WHERE c.ativo = 1';
        }

        $sql .= ' ORDER BY c.nome';

        return $this->fetchAll($sql);
    }

    public function findById(int $id): ?array {
        return $this->fetch("
            SELECT c.id, c.nome, c.descricao, c.salario_base, c.ativo, c.data_criacao, c.data_atualizacao
            FROM cargo c
            WHERE c.id = ?
        ", [$id]);
    }

    public function findByNome(string $nome): ?array {
        return $this->fetch('SELECT id FROM cargo WHERE nome = ?', [$nome]);
    }

    public function create(CargoDTO $dto): int {
        $this->execute("
            INSERT INTO cargo (nome, descricao, salario_base, ativo)
            VALUES (:nome, :descricao, :salario_base, :ativo)
        ", [
            ':nome' => $dto->nome,
            ':descricao' => $dto->descricao,
            ':salario_base' => $dto->salario_base,
            ':ativo' => $dto->ativo ? 1 : 0,
        ]);

        return (int) $this->lastInsertId();
    }

    public function update(int $id, CargoDTO $dto): bool {
        return $this->execute("
            UPDATE cargo SET
                nome = :nome,
                descricao = :descricao,
                salario_base = :salario_base,
                ativo = :ativo
            WHERE id = :id
        ", [
            ':nome' => $dto->nome,
            ':descricao' => $dto->descricao,
            ':salario_base' => $dto->salario_base,
            ':ativo' => $dto->ativo ? 1 : 0,
            ':id' => $id,
        ]);
    }

    public function deactivate(int $id): bool {
        return $this->execute('UPDATE cargo SET ativo = 0 WHERE id = ?', [$id]);
    }

    public function reactivate(int $id): bool {
        return $this->execute('UPDATE cargo SET ativo = 1 WHERE id = ?', [$id]);
    }

    public function listAllPermissions(): array {
        return $this->fetchAll("
            SELECT id, slug, modulo, nome, descricao
            FROM permissao
            ORDER BY modulo ASC, nome ASC
        ");
    }

    public function findPermissionIds(int $cargoId): array {
        $rows = $this->fetchAll("
            SELECT permissao_id
            FROM cargo_permissao
            WHERE cargo_id = ?
        ", [$cargoId]);

        return array_map('intval', array_column($rows, 'permissao_id'));
    }

    public function findPermissionSlugs(int $cargoId): array {
        $rows = $this->fetchAll("
            SELECT p.slug
            FROM permissao p
            INNER JOIN cargo_permissao cp ON cp.permissao_id = p.id
            WHERE cp.cargo_id = ?
        ", [$cargoId]);

        return array_column($rows, 'slug');
    }

    public function syncPermissions(int $cargoId, array $permissionIdentifiers): void {
        $this->execute("DELETE FROM cargo_permissao WHERE cargo_id = ?", [$cargoId]);

        if (empty($permissionIdentifiers)) {
            return;
        }

        $validIds = [];
        $slugsToLookup = [];
        foreach ($permissionIdentifiers as $identifier) {
            if (is_numeric($identifier) && (int) $identifier > 0) {
                $validIds[] = (int) $identifier;
            } elseif (is_string($identifier) && trim($identifier) !== '') {
                $slugsToLookup[] = trim($identifier);
            }
        }

        if (!empty($slugsToLookup)) {
            $inPlaceholders = implode(',', array_fill(0, count($slugsToLookup), '?'));
            $rows = $this->fetchAll("SELECT id FROM permissao WHERE slug IN ({$inPlaceholders})", $slugsToLookup);
            foreach ($rows as $r) {
                $validIds[] = (int) $r['id'];
            }
        }

        $validIds = array_unique($validIds);
        if (empty($validIds)) {
            return;
        }

        $placeholders = [];
        $params = [];
        foreach ($validIds as $pId) {
            $placeholders[] = "(?, ?)";
            $params[] = $cargoId;
            $params[] = $pId;
        }

        $sql = "INSERT INTO cargo_permissao (cargo_id, permissao_id) VALUES " . implode(', ', $placeholders);
        $this->execute($sql, $params);
    }
}
