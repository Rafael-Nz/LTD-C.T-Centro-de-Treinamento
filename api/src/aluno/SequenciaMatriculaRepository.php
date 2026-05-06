<?php
namespace Aluno;

use Core\Repository;

class SequenciaMatriculaRepository extends Repository {

    public function next(): int {
        $this->execute("INSERT INTO sequencia_matricula () VALUES ()");
        return (int) $this->lastInsertId();
    }
}