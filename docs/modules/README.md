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
- [Auditoria](./auditoria.md)

## Padrão arquitetural

Os módulos de negócio seguem a mesma estrutura:

- Controller: recebe as requisições HTTP e responde em JSON.
- Service: concentra a regra de negócio.
- Repository: encapsula acesso ao banco de dados.
- DTO: representa as estruturas de entrada/saída.
- Validation rules: validam os dados antes de persistir.

Essa organização facilita a manutenção e a evolução dos módulos de negócio. A auditoria é transversal: utiliza classes do núcleo e triggers, sem Controller, Repository ou DTO próprios.

## Documentação complementar da auditoria

- [Eventos da auditoria](./auditoria_eventos.md): catálogo, condições de emissão e dados registrados.
- [Operação da auditoria](./auditoria_operacoes.md): instalação, verificação da cadeia e checkpoints.
