<?php
namespace Funcionario;

use Core\Repository;

class FuncionarioRepository extends Repository {
    // Busca funcionário por CPF
    public function findByCpf(string $cpf): ?array {
        return $this->fetch("SELECT * FROM funcionario WHERE cpf = ?", [$cpf]);
    }

        // Busca funcionário por e-mail
    public function findByEmail(string $email): ?array {
        return $this->fetch("SELECT * FROM funcionario WHERE email = ?", [$email]);
    }
    // Insere um novo funcionário
    public function insert(array $data): int {
        $sql = "INSERT INTO funcionario (
            nome, sobrenome, cpf, data_nascimento, genero, email, senha, cargo_id, registro_profissional, observacoes, ativo, endereco_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)";
        $this->execute($sql, [
            $data['nome'],
            $data['sobrenome'],
            $data['cpf'],
            $data['data_nascimento'],
            $data['genero'] ?? 'O',
            $data['email'] ?? null,
            $data['senha'] ?? null,
            $data['cargo_id'],
            $data['registro_profissional'] ?? null,
            $data['observacoes'] ?? null,
            $data['endereco_id']
        ]);
        return (int)$this->lastInsertId();
    }

    // Atualiza funcionário existente
    public function update(int $id, array $data): bool {
        $sql = "UPDATE funcionario SET
            nome = ?, sobrenome = ?, cpf = ?, data_nascimento = ?, genero = ?, email = ?, cargo_id = ?, registro_profissional = ?, observacoes = ?, endereco_id = ?
            WHERE id = ?";
        return $this->execute($sql, [
            $data['nome'],
            $data['sobrenome'],
            $data['cpf'],
            $data['data_nascimento'],
            $data['genero'] ?? 'O',
            $data['email'] ?? null,
            $data['cargo_id'],
            $data['registro_profissional'] ?? null,
            $data['observacoes'] ?? null,
            $data['endereco_id'],
            $id
        ]);
    }

    // Soft delete (desativa)
    public function softDelete(int $id): bool {
        $sql = "UPDATE funcionario SET ativo = 0 WHERE id = ?";
        return $this->execute($sql, [$id]);
    }

    // Busca todos os funcionários
    public function findAll(): array {
        return $this->fetchAll("
            SELECT
                f.id,
                f.nome,
                f.sobrenome,
                f.cpf,
                f.genero,
                f.email,
                f.data_nascimento,
                f.ativo,
                f.cargo_id,
                c.nome AS cargo_nome,
                f.registro_profissional,
                f.observacoes,
                f.data_criacao,
                e.logradouro,
                e.numero,
                e.cidade,
                e.bairro,
                e.cep,
                e.complemento
            FROM funcionario f
            INNER JOIN cargo c ON c.id = f.cargo_id
            INNER JOIN endereco e ON e.id = f.endereco_id
            ORDER BY f.nome, f.sobrenome
        ");
    }

    // Busca funcionário por ID
    public function findById(int $id): ?array {
        $func = $this->fetch("
            SELECT
                f.*,
                c.nome AS cargo_nome,
                e.logradouro, e.numero, e.cidade, e.bairro, e.cep, e.complemento
            FROM funcionario f
            INNER JOIN cargo c ON c.id = f.cargo_id
            INNER JOIN endereco e ON e.id = f.endereco_id
            WHERE f.id = ?
        ", [$id]);

        if (!$func) {
            return null;
        }

        $func['contatos'] = $this->findContatos($id);
        $func['perfis'] = $this->findPerfis($id);
        return $func;
    }

    // Busca contatos do funcionário
    public function findContatos(int $funcionarioId): array {
        return $this->fetchAll("
            SELECT * FROM funcionario_contato WHERE funcionario_id = ?
        ", [$funcionarioId]);
    }

    // Busca perfis do funcionário
    public function findPerfis(int $funcionarioId): array {
        return $this->fetchAll("
            SELECT p.* FROM perfil p
            INNER JOIN funcionario_perfil fp ON fp.perfil_id = p.id
            WHERE fp.funcionario_id = ?
        ", [$funcionarioId]);
    }
}
