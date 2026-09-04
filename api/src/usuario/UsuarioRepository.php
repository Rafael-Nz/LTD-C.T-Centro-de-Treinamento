<?php

namespace Usuario;

use Core\DataTables\DataTablesRepositoryInterface;
use Core\Database\Repository;
use Usuario\DTO\UsuarioDTO;

class UsuarioRepository extends Repository implements DataTablesRepositoryInterface
{

    public function countAll(): int
    {
        $result = $this->fetch("
            SELECT COUNT(*) as total
            FROM usuario
        ");
        return (int) ($result['total'] ?? 0);
    }

    public function findPaginated(int $start, int $length, string $search = '', array $filters = []): array
    {
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
                tipo_usuario,
                data_criacao
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

    public function countFiltered(string $search = '', array $filters = []): int
    {
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

    public function findById(int $id): ?array
    {
        $usuario = $this->fetch("
            SELECT
                u.id,
                u.nome,
                u.sobrenome,
                u.cpf,
                u.email,
                u.data_nascimento,
                u.genero,
                u.endereco_id,
                u.tipo_usuario,
                u.ativo,
                u.data_criacao,
                u.data_atualizacao,
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

    public function create(UsuarioDTO $dto, ?int $enderecoId = null): int
    {
        $sql = "INSERT INTO usuario
                (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $this->execute($sql, [
            $dto->nome,
            $dto->sobrenome,
            $dto->cpf,
            $dto->email,
            password_hash($dto->senha, PASSWORD_ARGON2ID),
            $dto->data_nascimento,
            $dto->genero,
            $enderecoId,
            $dto->tipo_usuario
        ]);

        return (int) $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
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

    public function deactivate(int $id): void
    {
        $this->execute("UPDATE usuario SET ativo = 0 WHERE id = ?", [$id]);
    }

    public function reactivate(int $id): void
    {
        $this->execute("UPDATE usuario SET ativo = 1 WHERE id = ?", [$id]);
    }

    public function getEnderecoId(int $usuarioId): ?int
    {
        $result = $this->fetch("
            SELECT endereco_id
            FROM usuario
            WHERE id = ?
        ", [$usuarioId]);

        return $result['endereco_id'] ?? null;
    }

    public function findByLogin(string $login): ?array
    {
        return $this->fetch("
            SELECT id, nome, email, senha, ativo, tipo_usuario
            FROM usuario
            WHERE email = ? OR cpf = ?
        ", [$login, $login]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->fetch("
            SELECT id, nome, email, ativo
            FROM usuario
            WHERE email = ?
            LIMIT 1
        ", [$email]);
    }

    public function deletePasswordResetTokens(int $usuarioId): void
    {
        $this->execute(
            'DELETE FROM password_resets WHERE usuario_id = ?',
            [$usuarioId]
        );
    }

    public function createPasswordResetToken(
        int $usuarioId,
        string $tokenHash,
        string $expiresAt
    ): void {
        $this->execute(
            'INSERT INTO password_resets (usuario_id, token_hash, expires_at)
             VALUES (?, ?, ?)',
            [$usuarioId, $tokenHash, $expiresAt]
        );
    }

    public function findValidPasswordReset(string $tokenHash): ?array
    {
        return $this->fetch(
            'SELECT id, usuario_id
             FROM password_resets
             WHERE token_hash = ?
               AND used_at IS NULL
               AND expires_at > NOW()
             LIMIT 1',
            [$tokenHash]
        );
    }

    public function updatePassword(int $usuarioId, string $passwordHash): void
    {
        $this->execute(
            'UPDATE usuario SET senha = ? WHERE id = ?',
            [$passwordHash, $usuarioId]
        );
    }

    public function invalidatePasswordResetToken(int $resetId): void
    {
        $this->execute(
            'UPDATE password_resets SET used_at = NOW()
             WHERE id = ? AND used_at IS NULL',
            [$resetId]
        );
    }

    public function createStudentActivationToken(int $usuarioId, string $tokenHash, string $expiresAt): void
    {
        $this->execute(
            'INSERT INTO student_account_activations (usuario_id, token_hash, expires_at)
             VALUES (?, ?, ?)',
            [$usuarioId, $tokenHash, $expiresAt]
        );
    }

    public function deleteStudentActivationTokens(int $usuarioId): void
    {
        $this->execute('DELETE FROM student_account_activations WHERE usuario_id = ?', [$usuarioId]);
    }

    public function findValidStudentActivation(string $tokenHash): ?array
    {
        return $this->fetch(
            'SELECT a.id, a.usuario_id, u.nome, u.email
             FROM student_account_activations a
             INNER JOIN usuario u ON u.id = a.usuario_id
             INNER JOIN aluno al ON al.usuario_id = u.id
             WHERE a.token_hash = ?
               AND a.used_at IS NULL
               AND a.expires_at > NOW()
               AND u.ativo = 0
             LIMIT 1',
            [$tokenHash]
        );
    }

    public function activateStudentAccount(int $usuarioId, int $activationId, string $passwordHash): void
    {
        $this->execute(
            'UPDATE usuario SET senha = ?, ativo = 1 WHERE id = ? AND ativo = 0',
            [$passwordHash, $usuarioId]
        );
        $this->execute(
            'UPDATE student_account_activations SET used_at = NOW()
             WHERE id = ? AND usuario_id = ? AND used_at IS NULL',
            [$activationId, $usuarioId]
        );
    }

    public function isAuthRateLimited(string $action, string $identifier, string $ip, int $maxAttempts, int $windowSeconds): bool
    {
        $row = $this->findAuthRateLimit($action, $identifier, $ip);
        if (!$row) {
            return false;
        }

        if (strtotime($row['window_started_at']) <= time() - $windowSeconds) {
            $this->clearAuthAttempts($action, $identifier, $ip);
            return false;
        }

        return (int) $row['attempts'] >= $maxAttempts
            || ($row['blocked_until'] !== null && strtotime($row['blocked_until']) > time());
    }

    public function recordAuthAttempt(string $action, string $identifier, string $ip, int $maxAttempts, int $windowSeconds, int $blockSeconds): void
    {
        $actionHash = hash('sha256', $action . '|' . strtolower($identifier));
        $ipHash = hash('sha256', $ip);
        $row = $this->findAuthRateLimit($action, $identifier, $ip);

        if (!$row || strtotime($row['window_started_at']) <= time() - $windowSeconds) {
            $this->execute(
                'INSERT INTO auth_rate_limits (action, identifier_hash, ip_hash, attempts, window_started_at, blocked_until)
                 VALUES (?, ?, ?, 1, NOW(), NULL)
                 ON DUPLICATE KEY UPDATE attempts = 1, window_started_at = NOW(), blocked_until = NULL',
                [$action, $actionHash, $ipHash]
            );
            return;
        }

        $attempts = (int) $row['attempts'] + 1;
        $blockedUntil = $attempts >= $maxAttempts ? date('Y-m-d H:i:s', time() + $blockSeconds) : null;
        $this->execute(
            'UPDATE auth_rate_limits SET attempts = ?, blocked_until = ? WHERE action = ? AND identifier_hash = ? AND ip_hash = ?',
            [$attempts, $blockedUntil, $action, $actionHash, $ipHash]
        );
    }

    public function clearAuthAttempts(string $action, string $identifier, string $ip): void
    {
        $this->execute(
            'DELETE FROM auth_rate_limits WHERE action = ? AND identifier_hash = ? AND ip_hash = ?',
            [$action, hash('sha256', $action . '|' . strtolower($identifier)), hash('sha256', $ip)]
        );
    }

    private function findAuthRateLimit(string $action, string $identifier, string $ip): ?array
    {
        return $this->fetch(
            'SELECT attempts, window_started_at, blocked_until
             FROM auth_rate_limits
             WHERE action = ? AND identifier_hash = ? AND ip_hash = ?
             LIMIT 1',
            [$action, hash('sha256', $action . '|' . strtolower($identifier)), hash('sha256', $ip)]
        );
    }
}
