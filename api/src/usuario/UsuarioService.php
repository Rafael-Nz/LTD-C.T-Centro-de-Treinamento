<?php
namespace Usuario;

use Core\Database;

class UsuarioService {

    private UsuarioRepository $repo;
    private EnderecoRepository $enderecoRepo;
    private ContatoRepository $contatoRepo;

    public function __construct() {
        $this->repo = new UsuarioRepository();
        $this->enderecoRepo = new EnderecoRepository();
        $this->contatoRepo = new ContatoRepository();
    }

    public function create(array $data): int {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            // 1. Endereço
            $enderecoId = null;
            if (!empty($data['endereco'])) {
                $enderecoId = $this->enderecoRepo->create($data['endereco']);
            }

            // 2. Usuário
            $data['endereco_id'] = $enderecoId;
            $usuarioId = $this->repo->create($data);

            // 3. Contatos
            if (!empty($data['contatos'])) {
                foreach ($data['contatos'] as $contato) {
                    $this->contatoRepo->create($usuarioId, $contato);
                }
            }

            $db->commit();
            return $usuarioId;
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $data): void {
        $db = Database::getConnection();
        $db->beginTransaction();

        try {
            $this->repo->update($id, $data);

            // Atualiza contatos (remove todos e insere de novo - estratégia comum)
            if (isset($data['contatos'])) {
                $this->contatoRepo->deleteByUsuario($id);
                foreach ($data['contatos'] as $contato) {
                    $this->contatoRepo->create($id, $contato);
                }
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function deactivate(int $id): void {
        $this->repo->deactivate($id);
    }

    public function reactivate(int $id): void {
        $this->repo->reactivate($id);
    }
}