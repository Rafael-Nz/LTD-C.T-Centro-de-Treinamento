<?php
namespace Usuario;

use Core\Repository;

class UsuarioRepository extends Repository {

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

    public function create(array $data): int {
        $db = \Core\Database::getConnection();
        $db->beginTransaction();

        try {
            // ENDEREÇO
            $enderecoId = null;

            if (!empty($data['endereco'])) {
                $this->execute("
                    INSERT INTO endereco (logradouro, numero, cidade, bairro, cep, complemento)
                    VALUES (?, ?, ?, ?, ?, ?)
                ", [
                    $data['endereco']['logradouro'],
                    $data['endereco']['numero'],
                    $data['endereco']['cidade'],
                    $data['endereco']['bairro'],
                    $data['endereco']['cep'],
                    $data['endereco']['complemento'] ?? null
                ]);

                $enderecoId = (int) $this->lastInsertId();
            }

            // USUARIO
            $this->execute("
                INSERT INTO usuario 
                (nome, sobrenome, cpf, email, senha, data_nascimento, genero, endereco_id, tipo_usuario)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $data['nome'],
                $data['sobrenome'],
                $data['cpf'],
                $data['email'],
                password_hash($data['senha'] ?? $data['cpf'], PASSWORD_ARGON2ID),
                $data['data_nascimento'],
                $data['genero'] ?? 'O',
                $enderecoId,
                $data['tipo_usuario'] ?? 'aluno'
            ]);

            $usuarioId = (int) $this->lastInsertId();

            // CONTATOS
            if (!empty($data['contatos'])) {
                foreach ($data['contatos'] as $c) {
                    $this->execute("
                        INSERT INTO contato (usuario_id, tipo, valor)
                        VALUES (?, ?, ?)
                    ", [$usuarioId, $c['tipo'], $c['valor']]);
                }
            }

            $db->commit();
            return $usuarioId;

        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): void {
        $db = \Core\Database::getConnection();
        $db->beginTransaction();

        try {
            // Atualizar endereço se fornecido
            if (!empty($data['endereco'])) {
                // Primeiro, obter o ID do endereço do usuário
                $usuario = $this->findById($id);

                if ($usuario && $usuario['endereco_id']) {
                    // Atualizar endereço existente
                    $this->execute("
                        UPDATE endereco SET
                            logradouro = ?, numero = ?, cidade = ?, bairro = ?, cep = ?, complemento = ?
                        WHERE id = ?
                    ", [
                        $data['endereco']['logradouro'] ?? $usuario['logradouro'],
                        $data['endereco']['numero'] ?? $usuario['numero'],
                        $data['endereco']['cidade'] ?? $usuario['cidade'],
                        $data['endereco']['bairro'] ?? $usuario['bairro'],
                        $data['endereco']['cep'] ?? $usuario['cep'],
                        $data['endereco']['complemento'] ?? $usuario['complemento'],
                        $usuario['endereco_id']
                    ]);
                } else {
                    // Criar novo endereço
                    $this->execute("
                        INSERT INTO endereco (logradouro, numero, cidade, bairro, cep, complemento)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ", [
                        $data['endereco']['logradouro'],
                        $data['endereco']['numero'],
                        $data['endereco']['cidade'],
                        $data['endereco']['bairro'],
                        $data['endereco']['cep'],
                        $data['endereco']['complemento'] ?? null
                    ]);

                    $enderecoId = (int) $this->lastInsertId();

                    $this->execute("UPDATE usuario SET endereco_id = ? WHERE id = ?", [$enderecoId, $id]);
                }
            }

            // Atualizar dados do usuário
            $updateFields = [];
            $updateParams = [];

            if (isset($data['nome'])) {
                $updateFields[] = 'nome = ?';
                $updateParams[] = $data['nome'];
            }
            if (isset($data['sobrenome'])) {
                $updateFields[] = 'sobrenome = ?';
                $updateParams[] = $data['sobrenome'];
            }
            if (isset($data['email'])) {
                $updateFields[] = 'email = ?';
                $updateParams[] = $data['email'];
            }
            if (isset($data['senha'])) {
                $updateFields[] = 'senha = ?';
                $updateParams[] = password_hash($data['senha'], PASSWORD_ARGON2ID);
            }
            if (isset($data['data_nascimento'])) {
                $updateFields[] = 'data_nascimento = ?';
                $updateParams[] = $data['data_nascimento'];
            }
            if (isset($data['genero'])) {
                $updateFields[] = 'genero = ?';
                $updateParams[] = $data['genero'];
            }
            if (isset($data['tipo_usuario'])) {
                $updateFields[] = 'tipo_usuario = ?';
                $updateParams[] = $data['tipo_usuario'];
            }

            if (!empty($updateFields)) {
                $updateParams[] = $id;
                $this->execute(
                    "UPDATE usuario SET " . implode(", ", $updateFields) . " WHERE id = ?",
                    $updateParams
                );
            }

            // Atualizar contatos
            if (isset($data['contatos'])) {
                // Remover contatos antigos
                $this->execute("DELETE FROM contato WHERE usuario_id = ?", [$id]);

                // Inserir novos contatos
                foreach ($data['contatos'] as $c) {
                    $this->execute("
                        INSERT INTO contato (usuario_id, tipo, valor)
                        VALUES (?, ?, ?)
                    ", [$id, $c['tipo'], $c['valor']]);
                }
            }

            $db->commit();

        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function deactivate(int $id): void {
        $this->execute("UPDATE usuario SET ativo = 0 WHERE id = ?", [$id]);
    }

    public function reactivate(int $id): void {
        $this->execute("UPDATE usuario SET ativo = 1 WHERE id = ?", [$id]);
    }
}