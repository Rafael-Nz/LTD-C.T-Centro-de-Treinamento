# Módulo Turma

## Objetivo

Gerencia as turmas do sistema, incluindo cadastros, associação de alunos, agenda de treinos e registro de presenças.

## Diretório

- `api/src/turma`

## Rotas principais

- `GET /turmas`
- `GET /turmas/{id}`
- `GET /turmas/{id}/gerenciar`
- `POST /turmas`
- `PUT /turmas/{id}`
- `PUT /turmas/{id}/desativar`
- `PUT /turmas/{id}/reativar`
- `POST /turmas/{id}/treinos`
- `PUT /turmas/{id}/treinos/{treino_id}/cancelar`
- `PUT /turmas/{id}/treinos/{treino_id}/presencas`

## Componentes

### TurmaController

Arquivo: `api/src/turma/TurmaController.php`

Responsável por:

- listar e detalhar turmas;
- criar e alterar turmas;
- administrar a gestão da turma;
- confirmar treinos, cancelar eventos e registrar presenças.

### TurmaService

Arquivo: `api/src/turma/TurmaService.php`

Responsável por:

- validar dados principais da turma;
- aplicar regras de horário e calendário;
- processar agendamento e presença de treinos;
- manter consistência com `treino` e `aluno`.

### TurmaRepository

Arquivo: `api/src/turma/TurmaRepository.php`

Responsável por:

- ler e escrever na tabela `turma`;
- consultar turmas por ID, filtros e paginação;
- persistir dados de agenda e presença.

### DTOs

- `TurmaDTO`
- `TurmaConfigHorarioDTO`

### Validation

- `ConfigHorariosRule`

Serve para validar configuração de horários da turma, garantindo consistência das regras de planejamento.

## Entidades principais

- `turma`
- `treino_agenda`
- `presenca_treino`

## Regras de negócio

- O módulo organiza o funcionamento das turmas e dos treinamentos vinculados.
- Há controle de presença em aulas ou treinos.
- O gerenciamento da turma inclui ações como confirmação e cancelamento de treinos.

## Relação com outros módulos

- `aluno`: alunos vinculados às turmas;
- `treino`: cronograma e agenda de treinamento;
- `local`: locais em que as atividades podem ocorrer.
