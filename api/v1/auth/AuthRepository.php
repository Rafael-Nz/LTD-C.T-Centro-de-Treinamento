<?php
use Core\Repository;

class AuthRepository extends Repository {

    public function findFuncionarioByEmail(string $email): ?array {
        $sql = "
            SELECT id, nome, email, senha
            FROM funcionario
            WHERE email = :email
              AND ativo = 1
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);

        return $stmt->fetch() ?: null;
    }

    public function getPermissoes(int $funcionarioId): array {
        $sql = "
            SELECT p.nome
            FROM permissao p
            INNER JOIN perfil_permissao pp ON pp.permissao_id = p.id
            INNER JOIN funcionario_perfil fp ON fp.perfil_id = pp.perfil_id
            WHERE fp.funcionario_id = :id
              AND p.ativo = 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $funcionarioId]);

        return array_column($stmt->fetchAll(), 'nome');
    }
}