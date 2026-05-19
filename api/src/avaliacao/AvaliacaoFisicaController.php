<?php
namespace Avaliacao;

use Avaliacao\DTO\AvaliacaoFisicaDTO;
use Core\Auth\Auth;
use Core\Http\Controller;

class AvaliacaoFisicaController extends Controller {
    private AvaliacaoFisicaService $service;

    public function __construct() {
        $this->service = new AvaliacaoFisicaService();
    }

    public function indexByAluno(int $alunoId): void {
        try {
            $this->json($this->service->listByAlunoId($alunoId));
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[AvaliacaoFisicaController::indexByAluno] ' . $e->getMessage());
            $this->error('Erro ao carregar avaliacoes.', 500);
        }
    }

    public function show(int $id): void {
        $avaliacao = $this->service->findById($id);
        if (!$avaliacao) {
            $this->error('Avaliacao nao encontrada.', 404);
            return;
        }

        $this->json($avaliacao);
    }

    public function storeByAluno(int $alunoId): void {
        $dto = AvaliacaoFisicaDTO::fromArray($this->body());
        $avaliadorId = (int) (Auth::id() ?? 0);

        if ($avaliadorId < 1) {
            $this->error('Usuario autenticado invalido.', 401);
            return;
        }

        try {
            $id = $this->service->createForAluno($alunoId, $avaliadorId, $dto);
            $this->json(['id' => $id, 'message' => 'Avaliacao criada com sucesso.'], 201);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\Throwable $e) {
            error_log('[AvaliacaoFisicaController::storeByAluno] ' . $e->getMessage());
            $this->error('Erro ao salvar avaliacao.', 500);
        }
    }

    public function update(int $id): void {
        $dto = AvaliacaoFisicaDTO::fromArray($this->body());

        try {
            $this->service->update($id, $dto);
            $this->json(['message' => 'Avaliacao atualizada com sucesso.']);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            error_log('[AvaliacaoFisicaController::update] ' . $e->getMessage());
            $this->error('Erro ao atualizar avaliacao.', 500);
        }
    }
}
