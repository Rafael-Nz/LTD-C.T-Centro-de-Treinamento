<?php
namespace Core;

use PDO;
use PDOStatement;

abstract class Repository {

    protected PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Executa SELECT e retorna todos os registros
     */
    protected function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->prepareAndExecute($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Executa SELECT e retorna um único registro
     */
    protected function fetch(string $sql, array $params = []): ?array {
        $stmt = $this->prepareAndExecute($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Executa INSERT, UPDATE ou DELETE
     */
    protected function execute(string $sql, array $params = []): bool {
        $stmt = $this->prepareAndExecute($sql, $params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Retorna o último ID inserido
     */
    protected function lastInsertId(): string {
        return $this->db->lastInsertId();
    }

    /**
     * Centraliza prepare + execute
     */
    private function prepareAndExecute(string $sql, array $params): PDOStatement {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }
}