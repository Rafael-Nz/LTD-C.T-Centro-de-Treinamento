<?php
namespace Usuario;

use Core\Database;

class UsuarioService {

    private UsuarioRepository $usuarioRepo;

    public function __construct() {
        $this->usuarioRepo = new UsuarioRepository();
    }

    public function create(array $data): int {
        return $this->usuarioRepo->create($data);
    }

    public function update(int $id, array $data): void {
        $this->usuarioRepo->update($id, $data);
    }

    public function deactivate(int $id): void {
        $this->usuarioRepo->update($id, ['ativo' => 0]);
    }

    public function reactivate(int $id): void {
        $this->usuarioRepo->update($id, ['ativo' => 1]);
    }
}