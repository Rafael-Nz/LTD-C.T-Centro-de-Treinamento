<?php
namespace Usuario;

use Core\Repository;
use Core\DataTablesRepositoryInterface;
use Usuario\DTO\UsuarioDTO;

class UsuarioRepository extends Repository implements DataTablesRepositoryInterface {

    public function countAll(): int {
        $result = $this->fetch("
            SELECT COUNT(*) as total
            FROM usuario
        ");
        return (int) ($result['total'] ?? 0);
    }

    public function findPaginated(int $start, int $length, string $search = '', array $filters = []): array {
        $params = [];
        $where = [];

        $sql = "
            SELECT 
                id,
                nome,
                sobrenome,
                email,
                cpf,
                ativo,
                tipo_usuario
            FROM usuario
        ";

        if (!empty($search)) {
            $where[] = "(nome LIKE ? OR sobrenome LIKE ? OR email LIKE ? OR cpf LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%", "%$search%");
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
        $where = [];

        $sql = "SELECT COUNT(*) as total FROM usuario";

        if (!empty($search)) {
            $where[] = "(nome LIKE ? OR sobrenome LIKE ? OR email LIKE ? OR cpf LIKE ?)";
            array_push($params, "%$search%", "%$search%", "%$search%", "%$search%");
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

    public function findById(int $id): ?array {
        $usuario = $this->fetch("
            SELECT 
                u.*,
                e.logradouro,
                e.numero,
                e.cidade,
                e.bairro,
                e.cep,
                e.complemento
            FROM usuario u
            LEFT JOIN endereco e ON e.id = u.endereco_id
            WHERE u.id = ?
        ", [$id]);

        if (!$usuario) {
            return null;
        }

        // Buscar contatos
        $contatos = $this->fetchAll("
            SELECT tipo, valor
            FROM contato
            WHERE usuario_id = ?
        ", [$id]);

        $usuario['contatos'] = $contatos;

        return $usuario;
    }

    public function create(UsuarioDTO $dto, ?int $enderecoId = null): int {
        $sql = "INSERT INTO usuario 
                (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
        $this->execute($sql, [
            $dto->nome,
            $dto->sobrenome,
            $dto->cpf,
            $dto->email,
            password_hash($dto->senha ?? $dto->cpf, PASSWORD_ARGON2ID), // Gera senha a partir do CPF se não for fornecida
            $dto->data_nascimento,
            $dto->genero,
            $enderecoId,
            $dto->tipo_usuario
        ]);

        return (int) $this->lastInsertId();
    }

    public function update(int $id, array $data): void {
        $fields = [];
        $params = [];

        // Lista de campos permitidos para update na tabela usuario
        $allowed = [
            'nome',
            'sobrenome',
            'email',
            'cpf',
            'genero',
            'endereco_id'
        ];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (!empty($fields)) {
            $params[] = $id;
            $this->execute("UPDATE usuario SET " . implode(', ', $fields) . " WHERE id = ?", $params);
        }
    }

    public function deactivate(int $id): void {
        $this->execute("UPDATE usuario SET ativo = 0 WHERE id = ?", [$id]);
    }

    public function reactivate(int $id): void {
        $this->execute("UPDATE usuario SET ativo = 1 WHERE id = ?", [$id]);
    }

    public function getEnderecoId(int $usuarioId): ?int {
        $result = $this->fetch("
            SELECT endereco_id
            FROM usuario
            WHERE id = ?
        ", [$usuarioId]);

        return $result['endereco_id'] ?? null;
    }
}