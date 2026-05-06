<?php
namespace Anamnese;

use Core\Controller;
use Anamnese\AnamneseService;
use Anamnese\DTO\EnvioAnamneseDTO;

class AnamneseController extends Controller {
    private AnamneseService $service;

    public function __construct() {
        $this->service = new AnamneseService();
    }

    /**
     * GET /api/anamnese/formularios
     * Lista todos os formulários ativos (sem perguntas)
     */
    public function listar() {
        $this->auth();
        $formularios = $this->service->listarFormularios();
        $this->json(array_map(fn($f) => $f->toArray(), $formularios));
    }

    /**
     * GET /api/anamnese/formularios/{id}
     * Retorna FormularioDTO completo com perguntas — usado pelo módulo de config
     */
    public function obterFormulario(int $id) {
        $this->auth();
        $formulario = $this->service->obterFormularioDTO($id);
        
        if (!$formulario) {
            $this->error('Formulário não encontrado', 404);
            return;
        }

        $this->json($formulario->toArray());
    }
    
    /**
     * GET /api/anamnese/formularios/{id}/perguntas
     * Retorna array de perguntas — consumido pelo AnamneseEngine no frontend
     */
    public function index() {
        $this->auth();
        $formularioId = (int) ($_GET['formulario_id'] ?? 1);
        $data = $this->service->getFormulario($formularioId);
        $this->json($data);
    }

    /**
     * GET /api/anamnese/respostas/{alunoId}
     * Retorna as respostas de um aluno
     */
    public function show(int $alunoId) {
        $this->auth();

        try {
            $respostas = $this->service->getRespostas($alunoId);
            $this->json($respostas);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    /**
     * POST /api/anamnese
     * Body: { aluno_id, formulario_id, respostas[] }
     */
    public function store() {
        $this->auth();
        $this->only('POST');

        $body = $this->body();
        $dto = EnvioAnamneseDTO::fromArray($body);

        try {
            $this->service->salvar($dto);
            $this->json(['message' => 'Salvo com sucesso']);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            // Log do erro interno aqui se necessário
            $this->error('Erro interno ao salvar anamnese', 500);
        }
    }
}