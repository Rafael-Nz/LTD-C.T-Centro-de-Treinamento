<?php
namespace Core;

use Core\Database;
use Throwable;

abstract class Service
{
    /**
     * Executa uma lógica dentro de uma transação segura,
     * suportando transações aninhadas via savepoints.
     *
     * @param callable $callback Função contendo a lógica de negócio
     * @return mixed O retorno da função callback
     * @throws Throwable Reassocia qualquer erro encontrado
     */
    protected function transaction(callable $callback)
    {
        $db = Database::getConnection();
        $inTransaction = $db->inTransaction();
        $savepoint = null;

        // Se já estiver em uma transação, cria um savepoint
        if ($inTransaction) {
            $rawId = uniqid('', true);
            $savepoint = 'sp_' . str_replace('.', '_', $rawId);
            $db->exec("SAVEPOINT {$savepoint}");
        } else {
            $db->beginTransaction();
        }

        try {
            $result = $callback();

            if ($inTransaction) {
                $db->exec("RELEASE SAVEPOINT {$savepoint}");
            } else {
                $db->commit();
            }

            return $result;
        } catch (Throwable $e) {
            if ($inTransaction) {
                $db->exec("ROLLBACK TO SAVEPOINT {$savepoint}");
            } else {
                $db->rollBack();
            }
            throw $e;
        }
    }
}