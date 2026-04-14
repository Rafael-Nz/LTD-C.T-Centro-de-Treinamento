<?php
namespace Aluno;

use Core\Repository;

class AlunoRepository extends Repository {

    // -------------------------------------------------------------------------
    // Leitura
    // -------------------------------------------------------------------------

    public function findAll(): array {
        return $this->fetchAll("
            SELECT
                a.id,
                a.nome,
                a.sobrenome,
                a.cpf,
                a.genero,
                a.email,
                a.data_nascimento,
                a.ativo,
                a.data_matricula,
                a.data_criacao,
                e.logradouro,
                e.numero,
                e.cidade,
                e.bairro,
                e.cep,
                e.complemento,
                CONCAT(f.nome, ' ', f.sobrenome) AS cadastrado_por_nome
            FROM aluno a
            INNER JOIN endereco e ON e.id = a.endereco_id
            INNER JOIN funcionario f ON f.id = a.cadastrado_por
            ORDER BY a.nome, a.sobrenome
        ");
    }

    public function findById(int $id): ?array {
        $aluno = $this->fetch("
            SELECT
                a.*,
                e.logradouro, e.numero, e.cidade, e.bairro, e.cep, e.complemento,
                CONCAT(f.nome, ' ', f.sobrenome) AS cadastrado_por_nome
            FROM aluno a
            INNER JOIN endereco e ON e.id = a.endereco_id
            INNER JOIN funcionario f ON f.id = a.cadastrado_por
            WHERE a.id = ?
        ", [$id]);

        if (!$aluno) {
            return null;
        }

        $aluno['contatos']     = $this->findContatos($id);
        $aluno['questionario'] = $this->findQuestionario($id);
        $aluno['objetivos'] = $this->findObjetivos($id);

        return $aluno;
    }

    public function findByCpf(string $cpf): ?array {
        return $this->fetch(
            "SELECT id FROM aluno WHERE cpf = ?",
            [$cpf]
        );
    }

    public function findByEmail(string $email): ?array {
        return $this->fetch(
            "SELECT id FROM aluno WHERE email = ?",
            [$email]
        );
    }

    public function findContatos(int $alunoId): array {
        return $this->fetchAll(
            "SELECT tipo_contato, valor, observacao FROM aluno_contato WHERE aluno_id = ?",
            [$alunoId]
        );
    }

    public function findQuestionario(int $alunoId): ?array {
        return $this->fetch(
            "SELECT * FROM aluno_questionario WHERE aluno_id = ?",
            [$alunoId]
        );
    }

    public function findObjetivos(int $alunoId): array {
        return $this->fetchAll("
            SELECT o.id, o.nome
            FROM aluno_objetivo ao
            INNER JOIN objetivo o ON o.id = ao.objetivo_id
            WHERE ao.aluno_id = ?
        ", [$alunoId]);
    }

    // -------------------------------------------------------------------------
    // Escrita
    // -------------------------------------------------------------------------

    public function insertEndereco(array $data): int {
        $this->execute("
            INSERT INTO endereco (logradouro, numero, cidade, bairro, cep, complemento)
            VALUES (:logradouro, :numero, :cidade, :bairro, :cep, :complemento)
        ", [
            ':logradouro'  => $data['logradouro'],
            ':numero'      => $data['numero'],
            ':cidade'      => $data['cidade'],
            ':bairro'      => $data['bairro'],
            ':cep'         => $data['cep'],
            ':complemento' => $data['complemento'] ?? null,
        ]);

        return (int) $this->lastInsertId();
    }

    public function insertAluno(array $data): int {
        $this->execute("
            INSERT INTO aluno
                (nome, sobrenome, cpf, genero, email, data_nascimento,
                 ativo, endereco_id, data_matricula, cadastrado_por)
            VALUES
                (:nome, :sobrenome, :cpf, :genero, :email, :data_nascimento,
                 :ativo, :endereco_id, :data_matricula, :cadastrado_por)
        ", [
            ':nome'           => $data['nome'],
            ':sobrenome'      => $data['sobrenome'],
            ':cpf'            => $data['cpf'],
            ':genero'         => $data['genero']     ?? null,
            ':email'          => $data['email']      ?? null,
            ':data_nascimento' => $data['data_nascimento'],
            ':ativo'          => $data['ativo']      ?? 1,
            ':endereco_id'    => $data['endereco_id'],
            ':data_matricula' => $data['data_matricula'],
            ':cadastrado_por' => $data['cadastrado_por'],
        ]);

        return (int) $this->lastInsertId();
    }

    public function insertContato(int $alunoId, string $tipo, string $valor, ?string $obs = null): void {
        $this->execute("
            INSERT IGNORE INTO aluno_contato (aluno_id, tipo_contato, valor, observacao)
            VALUES (?, ?, ?, ?)
        ", [$alunoId, $tipo, $valor, $obs]);
    }

    public function insertObjetivos(int $alunoId, array $objetivos): void {
        foreach ($objetivos as $objetivoId) {
            $this->execute("
                INSERT INTO aluno_objetivo (aluno_id, objetivo_id)
                VALUES (?, ?)
            ", [$alunoId, $objetivoId]);
        }
    }

    public function insertQuestionario(int $alunoId, array $q): void {
        $this->execute("
            INSERT INTO aluno_questionario (
                aluno_id,
                problema_cardiaco, problema_cardiaco_descricao,
                dor_peito, desmaia_frequencia, pressao_alta,
                dor_costa, dor_musculo, doenca_pulmonar, doenca_pulmonar_descricao,
                nenhum_sintoma,
                osseo_articular, osseo_articular_descricao,
                limitacao_fisica, limitacao_descricao,
                medicamento_continuo, medicamento_descricao,
                cirurgia_anterior, cirurgia_descricao, cirurgia_data,
                gravida, gravida_tempo,
                pratica_exercicios, tipo_exercicios,
                fumante, consumo_alcool,
                problema_saude_familia, problema_saude_familia_descricao,
                outros_objetivos, observacoes_medicas
            ) VALUES (
                :aluno_id,
                :problema_cardiaco, :problema_cardiaco_descricao,
                :dor_peito, :desmaia_frequencia, :pressao_alta,
                :dor_costa, :dor_musculo, :doenca_pulmonar, :doenca_pulmonar_descricao,
                :nenhum_sintoma,
                :osseo_articular, :osseo_articular_descricao,
                :limitacao_fisica, :limitacao_descricao,
                :medicamento_continuo, :medicamento_descricao,
                :cirurgia_anterior, :cirurgia_descricao, :cirurgia_data,
                :gravida, :gravida_tempo,
                :pratica_exercicios, :tipo_exercicios,
                :fumante, :consumo_alcool,
                :problema_saude_familia, :problema_saude_familia_descricao,
                :outros_objetivos, :observacoes_medicas
            )
        ", [
            ':aluno_id' => $alunoId,
            ':problema_cardiaco' => $q['problema_cardiaco'] ?? 0,
            ':problema_cardiaco_descricao' => $q['problema_cardiaco_descricao'] ?? null,
            ':dor_peito' => $q['dor_peito'] ?? 0,
            ':desmaia_frequencia' => $q['desmaia_frequencia'] ?? 0,
            ':pressao_alta' => $q['pressao_alta'] ?? 0,
            ':dor_costa' => $q['dor_costa'] ?? 0,
            ':dor_musculo' => $q['dor_musculo'] ?? 0,
            ':doenca_pulmonar' => $q['doenca_pulmonar'] ?? 0,
            ':doenca_pulmonar_descricao' => $q['doenca_pulmonar_descricao'] ?? null,
            ':nenhum_sintoma' => $q['nenhum_sintoma'] ?? 0,
            ':osseo_articular' => $q['osseo_articular'] ?? 0,
            ':osseo_articular_descricao' => $q['osseo_articular_descricao'] ?? null,
            ':limitacao_fisica' => $q['limitacao_fisica'] ?? 0,
            ':limitacao_descricao' => $q['limitacao_descricao'] ?? null,
            ':medicamento_continuo' => $q['medicamento_continuo'] ?? 0,
            ':medicamento_descricao' => $q['medicamento_descricao'] ?? null,
            ':cirurgia_anterior' => $q['cirurgia_anterior'] ?? 0,
            ':cirurgia_descricao' => $q['cirurgia_descricao'] ?? null,
            ':cirurgia_data' => $q['cirurgia_data'] ?? null,
            ':gravida' => $q['gravida'] ?? 0,
            ':gravida_tempo' => $q['gravida_tempo'] ?? null,
            ':pratica_exercicios' => $q['pratica_exercicios'] ?? 0,
            ':tipo_exercicios' => $q['tipo_exercicios'] ?? null,
            ':fumante' => $q['fumante'] ?? 0,
            ':consumo_alcool' => $q['consumo_alcool'] ?? 0,
            ':problema_saude_familia' => $q['problema_saude_familia'] ?? 0,
            ':problema_saude_familia_descricao' => $q['problema_saude_familia_descricao'] ?? null,
            ':outros_objetivos' => $q['outros_objetivos'] ?? null,
            ':observacoes_medicas' => $q['observacoes_medicas'] ?? null,
        ]);
    }

    // -------------------------------------------------------------------------
    // Atualização
    // -------------------------------------------------------------------------

    public function updateAluno(int $id, array $data): bool {
        return $this->execute("
            UPDATE aluno SET
                nome            = :nome,
                sobrenome       = :sobrenome,
                cpf             = :cpf,
                genero          = :genero,
                email           = :email,
                data_nascimento = :data_nascimento,
                ativo           = :ativo,
                data_matricula  = :data_matricula
            WHERE id = :id
        ", [
            ':nome'            => $data['nome'],
            ':sobrenome'       => $data['sobrenome'],
            ':cpf'             => $data['cpf'],
            ':genero'          => $data['genero']     ?? null,
            ':email'           => $data['email']      ?? null,
            ':data_nascimento' => $data['data_nascimento'],
            ':ativo'           => $data['ativo']      ?? 1,
            ':data_matricula'  => $data['data_matricula'],
            ':id'              => $id,
        ]);
    }

    public function updateEndereco(int $enderecoId, array $data): void {
        $this->execute("
            UPDATE endereco SET
                logradouro  = :logradouro,
                numero      = :numero,
                cidade      = :cidade,
                bairro      = :bairro,
                cep         = :cep,
                complemento = :complemento
            WHERE id = :id
        ", [
            ':logradouro'  => $data['logradouro'],
            ':numero'      => $data['numero'],
            ':cidade'      => $data['cidade'],
            ':bairro'      => $data['bairro'],
            ':cep'         => $data['cep'],
            ':complemento' => $data['complemento'] ?? null,
            ':id'          => $enderecoId,
        ]);
    }

    public function deleteContatos(int $alunoId): void {
        $this->execute("DELETE FROM aluno_contato WHERE aluno_id = ?", [$alunoId]);
    }

    public function deleteObjetivos(int $alunoId): void {
        $this->execute("DELETE FROM aluno_objetivo WHERE aluno_id = ?", [$alunoId]);
    }

    public function syncObjetivos(int $alunoId, array $objetivos): void {
        $this->deleteObjetivos($alunoId);
        $this->insertObjetivos($alunoId, $objetivos);
    }

    public function upsertQuestionario(int $alunoId, array $q): void {
        // Remove o registro anterior (se existir) e insere novamente
        $this->execute("DELETE FROM aluno_questionario WHERE aluno_id = ?", [$alunoId]);
        $this->insertQuestionario($alunoId, $q);
    }

    // -------------------------------------------------------------------------
    // Exclusão lógica
    // -------------------------------------------------------------------------

    public function softDelete(int $id): bool {
        return $this->execute(
            "UPDATE aluno SET ativo = 0 WHERE id = ?",
            [$id]
        );
    }
}