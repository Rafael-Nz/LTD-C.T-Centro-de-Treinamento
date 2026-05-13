<?php
namespace Core\Services;

use Core\Database\Database;
use Core\Validation\Validator;
use Core\Validation\ValidatorInterface;
use Throwable;

abstract class Service {
    private ?ValidatorInterface $validator = null;

    protected function transaction(callable $callback) {
        $db = Database::getConnection();
        $inTransaction = $db->inTransaction();
        $savepoint = null;

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

    protected function validateData(array|object $data, array $rules, array $messages = [], array $attributes = []): array {
        return $this->validator()->validate($data, $rules, $messages, $attributes);
    }

    protected function validator(): ValidatorInterface {
        if ($this->validator === null) {
            $this->validator = new Validator();
        }

        return $this->validator;
    }
}
