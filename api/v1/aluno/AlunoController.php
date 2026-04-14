<?php
namespace Aluno;

use Core\Auth;
use Core\Controller;

class AlunoController extends Controller {

    private AlunoRepository $repo;

    public function __construct() {
        $this->repo = new AlunoRepository();
    }

    // =========================================================================
    // GET /v1/aluno/         → lista todos
    // GET /v1/aluno/?id=N    → detalhe de um aluno
    // =========================================================================
    public function index(array $params = []): void {
        $id = isset($params[0]) ? (int)$params[0] : null;

        if ($id !== null) {
            $aluno = $this->repo->findById((int)$id);
            if (!$aluno) {
                $this->error("Aluno não encontrado.", 404);
            }
            $this->json($aluno);
        } else {
            $this->json($this->repo->findAll());
        }
    }

    // =========================================================================
    // POST /v1/aluno/   → cadastra novo aluno
    // =========================================================================
    public function store(): void {
        $this->auth();
        $this->only('POST');

        $body = $this->body();

        // --- Validação básica ---------------------------------------------------
        $erros = $this->validar($body);
        if (!empty($erros)) {
            $this->error(implode(' | ', $erros), 422);
        }

        // --- Unicidade CPF / e-mail --------------------------------------------
        $cpf = preg_replace('/\D/', '', $body['cpf']);

        if ($this->repo->findByCpf($cpf)) {
            $this->error("CPF já cadastrado.", 409);
        }

        if (!empty($body['email']) && $this->repo->findByEmail($body['email'])) {
            $this->error("E-mail já cadastrado.", 409);
        }

        // --- Valida objetivos (se enviados) ------------------------------------
        $objetivosIds = $body['objetivos'] ?? [];
        if (!is_array($objetivosIds)) {
            $objetivosIds = [];
        }
        $this->validarObjetivos($objetivosIds);

        // --- Persistência em transação -----------------------------------------
        $db = \Core\Database::getConnection();
        $db->beginTransaction();

        try {
            // 1. Endereço
            $enderecoId = $this->repo->insertEndereco([
                'logradouro'  => $body['endereco'],
                'numero'      => $body['numero'],
                'cidade'      => $body['cidade'],
                'bairro'      => $body['bairro'],
                'cep'         => preg_replace('/\D/', '', $body['cep']),
                'complemento' => $body['complemento'] ?? null,
            ]);

            // 2. Aluno
            $alunoId = $this->repo->insertAluno([
                'nome'             => trim($body['nome']),
                'sobrenome'        => trim($body['sobrenome']),
                'cpf'              => $cpf,
                'genero'           => $body['genero']        ?? null,
                'email'            => $body['email']         ?? null,
                'data_nascimento'  => $body['nascimento'],
                'ativo'            => 1,
                'endereco_id'      => $enderecoId,
                'data_matricula'   => $body['dataMatricula'],
                'cadastrado_por'   => Auth::id(),
            ]);

            // 3. Contatos
            if (!empty($body['telefone1'])) {
                $this->repo->insertContato($alunoId, 'telefone', $body['telefone1'], 'Celular');
            }
            if (!empty($body['telefone2'])) {
                $this->repo->insertContato($alunoId, 'telefone', $body['telefone2'], 'Fixo');
            }
            if (!empty($body['email'])) {
                $this->repo->insertContato($alunoId, 'email', $body['email']);
            }

            // 4. Objetivos (relacionamento muitos-para-muitos)
            $objetivosIds = $body['objetivos'] ?? [];
            if (!empty($objetivosIds) && is_array($objetivosIds)) {
                $this->repo->insertObjetivos($alunoId, $objetivosIds);
            }

            // 5. Questionário (anamnese) – sem o campo 'objetivos'
            $this->repo->insertQuestionario($alunoId, $this->extrairQuestionario($body));

            $db->commit();

            $this->json(['id' => $alunoId, 'message' => 'Aluno cadastrado com sucesso.'], 201);

        } catch (\Throwable $e) {
            $db->rollBack();
            error_log('[AlunoController::store] ' . $e->getMessage());
            $this->error("Erro interno ao cadastrar aluno.", 500);
        }
    }

    // =========================================================================
    // PUT /v1/aluno/?id=N   → atualiza aluno
    // =========================================================================
    public function update(array $params = []): void {
        $this->auth();
        $this->only('PUT');

        $id = isset($params[0]) ? (int)$params[0] : null;
        if ($id === null) {
        $this->error("ID obrigatório.", 400);
        }

        $aluno = $this->repo->findById($id);
        if (!$aluno) {
            $this->error("Aluno não encontrado.", 404);
        }

        $body  = $this->body();
        $erros = $this->validar($body);
        if (!empty($erros)) {
            $this->error(implode(' | ', $erros), 422);
        }

        $cpf = preg_replace('/\D/', '', $body['cpf']);

        // Verificar CPF duplicado em outro registro
        $existente = $this->repo->findByCpf($cpf);
        if ($existente && (int) $existente['id'] !== $id) {
            $this->error("CPF já pertence a outro aluno.", 409);
        }

        // --- Valida objetivos (se enviados) ------------------------------------
        $objetivosIds = $body['objetivos'] ?? [];
        if (!is_array($objetivosIds)) {
            $objetivosIds = [];
        }
        $this->validarObjetivos($objetivosIds);

        $db = \Core\Database::getConnection();
        $db->beginTransaction();

        try {
            $this->repo->updateEndereco($aluno['endereco_id'], [
                'logradouro'  => $body['endereco'],
                'numero'      => $body['numero'],
                'cidade'      => $body['cidade'],
                'bairro'      => $body['bairro'],
                'cep'         => preg_replace('/\D/', '', $body['cep']),
                'complemento' => $body['complemento'] ?? null,
            ]);

            $this->repo->updateAluno($id, [
                'nome'            => trim($body['nome']),
                'sobrenome'       => trim($body['sobrenome']),
                'cpf'             => $cpf,
                'genero'          => $body['genero']       ?? null,
                'email'           => $body['email']        ?? null,
                'data_nascimento' => $body['nascimento'],
                'ativo'           => isset($body['status']) && $body['status'] === 'ativo' ? 1 : 0,
                'data_matricula'  => $body['dataMatricula'],
            ]);

            // Recria contatos
            $this->repo->deleteContatos($id);
            if (!empty($body['telefone1'])) {
                $this->repo->insertContato($id, 'telefone', $body['telefone1'], 'Celular');
            }
            if (!empty($body['telefone2'])) {
                $this->repo->insertContato($id, 'telefone', $body['telefone2'], 'Fixo');
            }
            if (!empty($body['email'])) {
                $this->repo->insertContato($id, 'email', $body['email']);
            }

            // Sincroniza os objetivos (remove os antigos e insere os novos)
            $objetivosIds = $body['objetivos'] ?? [];
            if (!is_array($objetivosIds)) {
                $objetivosIds = [];
            }
            $this->repo->syncObjetivos($id, $objetivosIds);

            // Atualiza questionário
            $this->repo->upsertQuestionario($id, $this->extrairQuestionario($body));

            $db->commit();

            $this->json(['message' => 'Aluno atualizado com sucesso.']);

        } catch (\Throwable $e) {
            $db->rollBack();
            error_log('[AlunoController::update] ' . $e->getMessage());
            $this->error("Erro interno ao atualizar aluno.", 500);
        }
    }

    // =========================================================================
    // DELETE /v1/aluno/?id=N  → desativa aluno (soft delete)
    // =========================================================================
    public function destroy(array $params = []): void {
        $this->auth();
        $this->only('DELETE');

        $id = isset($params[0]) ? (int)$params[0] : null;
        if ($id === null) {
            $this->error("ID obrigatório.", 400);
        }

        if (!$this->repo->findById($id)) {
            $this->error("Aluno não encontrado.", 404);
        }

        $this->repo->softDelete($id);
        $this->json(['message' => 'Aluno desativado com sucesso.']);
    }

    // =========================================================================
    // Helpers privados
    // =========================================================================

    private function validar(array $data): array {
        $erros = [];

        if (empty($data['nome']))        $erros[] = "Nome é obrigatório.";
        if (empty($data['sobrenome']))   $erros[] = "Sobrenome é obrigatório.";
        if (empty($data['nascimento']))  $erros[] = "Data de nascimento é obrigatória.";
        if (empty($data['dataMatricula'])) $erros[] = "Data de matrícula é obrigatória.";
        if (empty($data['endereco']))    $erros[] = "Logradouro é obrigatório.";
        if (empty($data['numero']))      $erros[] = "Número do endereço é obrigatório.";
        if (empty($data['cidade']))      $erros[] = "Cidade é obrigatória.";
        if (empty($data['bairro']))      $erros[] = "Bairro é obrigatório.";
        if (empty($data['cep']))         $erros[] = "CEP é obrigatório.";

        if (empty($data['cpf'])) {
            $erros[] = "CPF é obrigatório.";
        } elseif (strlen(preg_replace('/\D/', '', $data['cpf'])) !== 11) {
            $erros[] = "CPF inválido.";
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $erros[] = "E-mail inválido.";
        }

        return $erros;
    }

    /**
     * Mapeia os campos do body do formulário para o array do questionário.
     * Os campos de radio vêm como "Sim"/"Não" do formulário.
     */
    private function extrairQuestionario(array $body): array {
        $bool = fn($v) => ($v === 'Sim' || $v === true || $v === 1) ? 1 : 0;

        $sintomas = $body['sintomas'] ?? [];

        return [
            'problema_cardiaco'               => $bool($body['problemaCardiaco'] ?? null),
            'problema_cardiaco_descricao'     => $body['problemaCardiacoObs']    ?? null,
            'dor_peito'                       => $bool($body['dorPeito']         ?? null),
            'desmaia_frequencia'              => $bool($body['desmaioTontura']   ?? null),
            'pressao_alta'                    => $bool($body['pressaoArterial']  ?? null),
            'dor_costa'                       => in_array('Dor nas costas', $sintomas) ? 1 : 0,
            'dor_musculo'                     => in_array('Dor nas articulações, tendões ou músculo', $sintomas) ? 1 : 0,
            'doenca_pulmonar'                 => in_array('Doença pulmonar', $sintomas) ? 1 : 0,
            'doenca_pulmonar_descricao'       => $body['doencaPulmonarObs']      ?? null,
            'nenhum_sintoma'                  => in_array('Nenhum', $sintomas) ? 1 : 0,
            'osseo_articular'                 => $bool($body['problemaOsseoArticular'] ?? null),
            'osseo_articular_descricao'       => $body['problemaOsseoArticularObs']    ?? null,
            'limitacao_fisica'                => $bool($body['limitacaoFisica']  ?? null),
            'limitacao_descricao'             => $body['limitacaoFisicaObs']     ?? null,
            'medicamento_continuo'            => $bool($body['medicamento']      ?? null),
            'medicamento_descricao'           => $body['medicamentoObs']         ?? null,
            'cirurgia_anterior'               => $bool($body['cirurgia']         ?? null),
            'cirurgia_descricao'              => $body['cirurgiaObs']            ?? null,
            'cirurgia_data'                   => $body['cirurgiaData']           ?? null,
            'gravida'                         => $bool($body['gravida']          ?? null),
            'gravida_tempo'                   => $body['gravidaTempo']           ?? null,
            'pratica_exercicios'              => $bool($body['atividadeFisica']  ?? null),
            'tipo_exercicios'                 => $body['tipoAtividade']          ?? null,
            'fumante'                         => $bool($body['fumante']          ?? null),
            'consumo_alcool'                  => $bool($body['alcool']           ?? null),
            'problema_saude_familia'          => $bool($body['problemaFamilia']  ?? null),
            'problema_saude_familia_descricao' => $body['parenteProblemaObs']   ?? null,
            'outros_objetivos'                => $body['objetivoOutros']        ?? null,
            'observacoes_medicas'             => $body['observacoesMedicas']    ?? null,
        ];
    }

    private function validarObjetivos(array $ids): void {
    if (empty($ids)) {
        return;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT id FROM objetivo WHERE id IN ($placeholders)";
    $stmt = \Core\Database::getConnection()->prepare($sql);
    $stmt->execute($ids);
    $existentes = $stmt->fetchAll(\PDO::FETCH_COLUMN);

    $invalidos = array_diff($ids, $existentes);
    if (!empty($invalidos)) {
        $this->error("Objetivos inválidos: " . implode(', ', $invalidos), 422);
    }
    }
}