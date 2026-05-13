<?php
namespace Core\Database;

use PDO;
use PDOStatement;

abstract class Repository {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    protected function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->prepareAndExecute($sql, $params);
        return $stmt->fetchAll();
    }

    protected function fetch(string $sql, array $params = []): ?array {
        $stmt = $this->prepareAndExecute($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    protected function execute(string $sql, array $params = []): bool {
        $stmt = $this->prepareAndExecute($sql, $params);
        return $stmt->rowCount() > 0;
    }

    protected function lastInsertId(): string {
        return $this->db->lastInsertId();
    }

    private function prepareAndExecute(string $sql, array $params): PDOStatement {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt;
    }
}
