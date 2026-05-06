<?php
namespace Usuario;

use Core\Repository;
use Usuario\DTO\EnderecoDTO;

class EnderecoRepository extends Repository {
    public function create(array|EnderecoDTO $data): int {
        if ($data instanceof EnderecoDTO) {
            $data = $data->toArray();
        }
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

    public function update(int $id, array $data): void {
        $this->execute("
            UPDATE endereco SET
                logradouro = ?,
                numero = ?,
                cidade = ?,
                bairro = ?,
                cep = ?,
                complemento = ?
            WHERE id = ?
        ", [
            $data['logradouro'],
            $data['numero'],
            $data['cidade'],
            $data['bairro'],
            $data['cep'],
            $data['complemento'] ?? null,
            $id
        ]);
    }
}