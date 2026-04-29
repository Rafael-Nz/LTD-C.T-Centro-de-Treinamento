<?php
namespace Usuario;

use Core\Database;
use Core\Service;

class UsuarioService extends Service {

    private UsuarioRepository $repo;
    private EnderecoRepository $enderecoRepo;
    private ContatoRepository $contatoRepo;

    public function __construct() {
        $this->repo = new UsuarioRepository();
        $this->enderecoRepo = new EnderecoRepository();
        $this->contatoRepo = new ContatoRepository();
    }

    public function create(array $data): int {
        return $this->transaction(function() use ($data) {
            // 1. Persistir Endereço primeiro para obter o ID
            $enderecoId = null;
            if (!empty($data['endereco'])) {
                $enderecoId = $this->enderecoRepo->create($data['endereco']);
            }

            // 2. Criar o registro principal do Usuário
            $data['endereco_id'] = $enderecoId;
            $usuarioId = $this->repo->create($data);

            // 3. Vincular múltiplos contatos (celular, fixo, etc)
            if (!empty($data['contatos'])) {
                foreach ($data['contatos'] as $contato) {
                    $this->contatoRepo->create($usuarioId, $contato);
                }
            }

            return $usuarioId;
        });
    }

    public function update(int $id, array $data): void {
        $this->transaction(function() use ($id, $data) {
            // Atualiza dados básicos da tabela 'usuario'
            $this->repo->update($id, $data);

            // Sincronização de contatos: remove antigos e insere os novos
            if (isset($data['contatos'])) {
                $this->contatoRepo->deleteByUsuario($id);
                foreach ($data['contatos'] as $contato) {
                    $this->contatoRepo->create($id, $contato);
                }
            }
        });
    }

    public function deactivate(int $id): void {
        $this->repo->deactivate($id);
    }

    public function reactivate(int $id): void {
        $this->repo->reactivate($id);
    }
}