# Módulos da API

Esta pasta reúne a documentação separada por módulo da API do projeto.

## Índice

- [Auth](./auth.md)
- [Usuario](./usuario.md)
- [Aluno](./aluno.md)
- [Funcionario](./funcionario.md)
- [Cargo](./cargo.md)
- [Modalidade](./modalidade.md)
- [Turma](./turma.md)
- [Treino](./treino.md)
- [Local](./local.md)
- [Anamnese](./anamnese.md)
- [Avaliacao](./avaliacao.md)
- [Relatorio](./relatorio.md)

## Padrão arquitetural

Cada módulo segue a mesma estrutura:

- Controller: recebe as requisições HTTP e responde em JSON.
- Service: concentra a regra de negócio.
- Repository: encapsula acesso ao banco de dados.
- DTO: representa as estruturas de entrada/saída.
- Validation rules: validam os dados antes de persistir.

Essa organização é aplicada em toda a API, facilitando manutenção, testes e evolução do sistema.
