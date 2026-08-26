# Core: DTO

Objetivo

Fornecer uma base para Data Transfer Objects (DTOs) que convertem arrays em objetos tipados e vice-versa.

Arquivo principal

- [BaseDTO.php](../../api/core/DTO/BaseDTO.php)

Funcionalidades

- Construtor que aceita um array e popula somente propriedades existentes na classe.
- Suporte a propriedades que são outras DTOs: converte arrays aninhados para instâncias DTO automaticamente.
- Suporte a arrays de DTOs via PHPDoc `@var Type[]`.
- Método `fromArray(array $data)` para criação e `toArray()` para serialização.

Como usar

- DTOs de domínio (ex.: `UsuarioDTO`, `AlunoDTO`) estendem `Core\DTO\BaseDTO`.
- Em controllers, receber payloads e transformá-los: `UsuarioDTO::fromArray($this->body())`.
- Em services, manipular DTOs e convertê-los para arrays antes de persistir, se necessário.

Boas práticas

- Definir propriedades públicas com tipos para que a reflexão do `BaseDTO` consiga inferir tipos.
- Usar DTOs para separar a camada de transporte (HTTP) da camada de domínio/entidade.
