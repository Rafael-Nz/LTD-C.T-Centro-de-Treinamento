# Core: Validation

Objetivo

Validar dados de entrada e centralizar regras e mensagens de erro.

Arquivos principais

- [Validator.php](../../api/core/Validation/Validator.php)
- [ValidatorInterface.php](../../api/core/Validation/ValidatorInterface.php)
- [ValidationRuleInterface.php](../../api/core/Validation/ValidationRuleInterface.php)
- [ValidationException.php](../../api/core/Validation/ValidationException.php)
- [ValidationErrorBag.php](../../api/core/Validation/ValidationErrorBag.php)

Funcionalidades do Validator

- Normaliza dados (inclui suporte a DTOs) e aplica regras por campo.
- Regras embutidas: `required`, `nullable`, `string`, `numeric`, `integer`, `array`, `min`, `max`, `max_length`, `in`, `date`, `before_field`, `after_field`, `less_than_field`, `greater_than_field`.
- Aceita regras implementadas via instâncias de `ValidationRuleInterface`, callables e strings com parâmetros (ex.: `min:3`).
- Em caso de falha, lança `ValidationException` com mensagens e um `ValidationErrorBag` contendo todos os erros.

Como integrar

- Chamar `Service::validateData()` dentro dos serviços para prevenir persistência de dados inválidos.
- Implementar regras customizadas criando classes que implementam `ValidationRuleInterface` ou closures callables.

Boas práticas

- Definir mensagens e nomes de atributos quando necessário para mensagens amigáveis.
- Reutilizar regras customizadas do módulo (ex.: `ConfigHorariosRule` em `turma`) para manter lógica consolidada.
