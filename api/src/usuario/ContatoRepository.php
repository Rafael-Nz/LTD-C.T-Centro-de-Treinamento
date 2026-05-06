<?php
namespace Usuario;

use Core\Repository;
use Usuario\DTO\ContatoDTO;

class ContatoRepository extends Repository {

    private array $tiposPermitidos = ['telefone', 'whatsapp', 'email_secundario'];

    private function normalizarTipo(string $tipo): string {
        return strtolower(trim($tipo));
    }

    private function validarTipo(string $tipo): void {
        if (!in_array($tipo, $this->tiposPermitidos)) {
            throw new \Exception("Tipo de contato inválido.");
        }
    }

    /**
     * Cria ou atualiza um contato (UPSERT)
     */
    public function upsert(int $usuarioId, array|ContatoDTO $data): void {
        if ($data instanceof ContatoDTO) {
            $data = $data->toArray();
        }

        if (empty($data['tipo']) || empty($data['valor'])) {
            throw new \InvalidArgumentException("Contato inválido: tipo e valor são obrigatórios.");
        }

        $tipo = $this->normalizarTipo($data['tipo']);
        $this->validarTipo($tipo);

        $this->execute("
            INSERT INTO contato (usuario_id, tipo, valor)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                valor = VALUES(valor)
        ", [
            $usuarioId,
            $tipo,
            $data['valor']
        ]);
    }

    /**
     * Cria ou atualiza múltiplos contatos
     */
    public function upsertMany(int $usuarioId, array $contatos): void {
        foreach ($contatos as $contato) {
            $this->upsert($usuarioId, $contato);
        }
    }

    public function deleteByTipos(int $usuarioId, array $tipos): void {
        if (empty($tipos)) return;

        $tiposNormalizados = array_map(
            fn($t) => $this->normalizarTipo($t),
            $tipos
        );

        $placeholders = implode(',', array_fill(0, count($tiposNormalizados), '?'));

        $this->execute("
            DELETE FROM contato
            WHERE usuario_id = ?
            AND tipo IN ($placeholders)
        ", array_merge([$usuarioId], $tiposNormalizados));
    }

    /**
     * Remove todos os contatos de um usuário
     */
    public function deleteByUsuario(int $usuarioId): void {
        $this->execute("
            DELETE FROM contato 
            WHERE usuario_id = ?
        ", [$usuarioId]);
    }

    /**
     * Lista todos os contatos de um usuário
     */
    public function findByUsuario(int $usuarioId): array {
        return $this->fetchAll("
            SELECT tipo, valor
            FROM contato
            WHERE usuario_id = ?
        ", [$usuarioId]);
    }

    /**
     * Busca um contato específico por tipo
     */
    public function findByTipo(int $usuarioId, string $tipo): ?array {
        return $this->fetch("
            SELECT tipo, valor
            FROM contato
            WHERE usuario_id = ? AND tipo = ?
            LIMIT 1
        ", [$usuarioId, $tipo]);
    }
}