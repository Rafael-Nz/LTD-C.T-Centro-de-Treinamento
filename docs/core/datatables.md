# Core: DataTables

Objetivo

Fornecer helpers para construir respostas compatíveis com o plugin DataTables (front-end) e padronizar paginação/listagem.

Arquivo principal

- [DataTablesResponseTrait.php](/D:/xampp/htdocs/ctt.worktrees/layered-architecture-explanation/api/core/DataTables/DataTablesResponseTrait.php)
- [DataTablesRepositoryInterface.php](/D:/xampp/htdocs/ctt.worktrees/layered-architecture-explanation/api/core/DataTables/DataTablesRepositoryInterface.php)

Funcionalidade

- `dataTablesResponse(...)` prepara e envia payload com chaves `draw`, `recordsTotal`, `recordsFiltered` e `data`.
- `dataTablesResponseCustom(...)` permite transformar os dados antes de enviá-los.
- A trait espera um repositório que implemente `DataTablesRepositoryInterface` (métodos como `findPaginated`, `countAll`, `countFiltered`).

Como usar

- Controllers que servem endpoints de listagem podem usar a trait para retornar payload compatível com DataTables.
- Repositórios de domínio devem implementar `DataTablesRepositoryInterface` para integrar perfeitamente com a trait.

Observações

- O tratamento de `length === -1` (interpretação do plugin para "todos") é convertido localmente para um valor padrão.
- A trait calcula `totalFiltered` condicionalmente, chamando `countFiltered()` somente quando há filtros ativos.
