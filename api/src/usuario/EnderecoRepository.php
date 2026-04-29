<?php
namespace Usuario;

use Core\Repository;

class EnderecoRepository extends Repository {
    public function create(array $data): int {
        $this->execute("
            INSERT INTO endereco (logradouro, numero, cidade, bairro, cep, complemento)
            VALUES (?, ?, ?, ?, ?, ?)
        ", [
            $data['logradouro'],
            $data['numero'],
            $data['cidade'],
            $data['bairro'],
            $data['cep'],
            $data['complemento'] ?? null
        ]);

        return (int) $this->lastInsertId();
    }
}