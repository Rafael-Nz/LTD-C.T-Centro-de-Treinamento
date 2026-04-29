<?php
namespace Core;

use Core\Database;
use Throwable;

abstract class Service {
    /**
     * Executa uma lógica dentro de uma transação segura.
     * 
     * @param callable $callback Função contendo a lógica de negócio
     * @return mixed O retorno da função callback
     * @throws Throwable Reassocia qualquer erro encontrado
     */
    protected function transaction(callable $callback) {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $result = $callback();
            $db->commit();
            return $result;
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}