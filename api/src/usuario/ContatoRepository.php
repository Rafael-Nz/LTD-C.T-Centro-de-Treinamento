<?php
namespace Usuario;

use Core\Repository;

class ContatoRepository extends Repository {
    public function create(int $usuarioId, array $data): void {
        $this->execute("
            INSERT INTO contato (usuario_id, tipo, valor)
            VALUES (?, ?, ?)
        ", [
            $usuarioId,
            $data['tipo'],
            $data['valor']
        ]);
    }

    public function deleteByUsuario(int $usuarioId): void {
        $this->execute("DELETE FROM contato WHERE usuario_id = ?", [$usuarioId]);
    }
}