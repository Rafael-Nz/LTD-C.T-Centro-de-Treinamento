<?php

namespace Usuario;

use Core\Auth\PasswordPolicy;
use Core\Services\Service;
use Funcionario\FuncionarioRepository;
use Usuario\DTO\UsuarioDTO;

class UsuarioService extends Service
{

    private UsuarioRepository $repo;
    private EnderecoRepository $enderecoRepo;
    private ContatoRepository $contatoRepo;
    private FuncionarioRepository $funcionarioRepo;

    public function __construct()
    {
        $this->repo = new UsuarioRepository();
        $this->enderecoRepo = new EnderecoRepository();
        $this->contatoRepo = new ContatoRepository();
        $this->funcionarioRepo = new FuncionarioRepository();
    }

    public function create(UsuarioDTO $dto, bool $requirePassword = true, ?bool $active = null): int
    {
        if ($requirePassword && ($dto->senha === null || $dto->senha === '')) {
            throw new \InvalidArgumentException('Uma senha com pelo menos 12 caracteres deve ser informada.');
        }

        if ($requirePassword) {
            PasswordPolicy::validate($dto->senha);
        }

        if (!$requirePassword) {
            $dto->senha = bin2hex(random_bytes(32));
        }

        if ($active !== null) {
            $dto->ativo = $active;
        }

        return $this->transaction(function () use ($dto) {
            $enderecoId = null;


            if ($dto->endereco !== null) {
                $enderecoId = $this->enderecoRepo->create($dto->endereco);
            }

            $usuarioId = $this->repo->create($dto, $enderecoId);

            if (!empty($dto->contatos)) {
                foreach ($dto->contatos as $contato) {
                    $this->contatoRepo->upsert($usuarioId, $contato);
                }
            }

            return $usuarioId;
        });
    }

    public function update(int $id, UsuarioDTO $dto): void
    {
        if (property_exists($dto, 'tipo_usuario') && isset($dto->tipo_usuario)) {
            throw new \Exception("Não é permitido alterar o tipo de usuário.");
        }

        $this->transaction(function () use ($id, $dto) {
            $updateData = [];

            $allowedFields = ['nome', 'sobrenome', 'email', 'cpf', 'genero', 'ativo'];

            foreach ($allowedFields as $field) {
                if (property_exists($dto, $field) && isset($dto->$field)) {
                    $updateData[$field] = $dto->$field;
                }
            }

            if (!empty($updateData)) {
                $this->repo->update($id, $updateData);
            }

            if ($dto->endereco !== null) {
                $enderecoId = $this->repo->getEnderecoId($id);

                $enderecoArray = $dto->endereco->toArray();

                $enderecoArray = array_filter($enderecoArray, function ($value) {
                    return $value !== null && $value !== '';
                });

                if (!empty($enderecoArray)) {
                    if ($enderecoId) {
                        $this->enderecoRepo->update($enderecoId, $enderecoArray);
                    } else {
                        $novoId = $this->enderecoRepo->create($enderecoArray);
                        $this->repo->update($id, ['endereco_id' => $novoId]);
                    }
                }
            }

            if (!empty($dto->contatos)) {
                foreach ($dto->contatos as $contato) {
                    // Converte para array apenas na hora de persistir
                    $this->contatoRepo->upsert($id, $contato->toArray());
                }
            }
        });
    }

    public function findById(int $id): ?array
    {
        $usuario = $this->repo->findById($id);
        if (!$usuario) return null;

        $funcionario = $this->funcionarioRepo->findById($id);
        if ($funcionario) {
            $usuario['cargo_nome'] = $funcionario['cargo_nome'] ?? null;
            $usuario['cargo_id'] = $funcionario['cargo_id'] ?? null;
            $usuario['registro_profissional'] = $funcionario['registro_profissional'] ?? null;
        }

        return $usuario;
    }

    public function deactivate(int $id): void
    {
        $this->repo->deactivate($id);
    }

    public function reactivate(int $id): void
    {
        $this->repo->reactivate($id);
    }
}
