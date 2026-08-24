# Módulo Anamnese

## Objetivo

Gerencia formulários de anamnese, perguntas, opções de resposta e respostas dos alunos.

## Diretório

- `api/src/anamnese`

## Rotas principais

- `GET /anamnese/formularios`
- `GET /anamnese/formularios/{id}`
- `GET /anamnese/formularios/{id}/perguntas`
- `GET /anamnese/respostas/{aluno_id}`
- `POST /anamnese`

## Componentes

### AnamneseController

Arquivo: `api/src/anamnese/AnamneseController.php`

Responsável por:

- listar formulários e perguntas;
- recuperar respostas por aluno;
- gravar um envio de anamnese inteiro;
- responder em JSON estruturado.

### AnamneseService

Arquivo: `api/src/anamnese/AnamneseService.php`

Responsável por:

- validar o formulário enviado;
- consolidar regras do processo de anamnese;
- orquestrar persistência da resposta completa.

### AnamneseRepository

Arquivo: `api/src/anamnese/AnamneseRepository.php`

Responsável por:

- consultar formulários e perguntas;
- salvar respostas e opções marcadas;
- montar o retorno completo da anamnese do aluno.

### DTOs

- `FormularioDTO`
- `PerguntaDTO`
- `OpcaoDTO`
- `RespostaDTO`
- `EnvioAnamneseDTO`
- `RegraExibicaoDTO`
- `CondicaoDTO`

### Validation

- `RespostaValorRule`

Valida o valor enviado pela resposta, respeitando regras específicas do tipo de pergunta.

## Entidades principais

- `anamnese_formulario`
- `anamnese_pergunta`
- `anamnese_opcao`
- `anamnese_resposta`

## Regras de negócio

- As perguntas podem ter regras de exibição condicionais.
- O módulo suporta respostas com tipos específicos e validação de conteúdo.
- A resposta pode ser centralizada em um único envio da anamnese para o aluno.

## Importância funcional

Esse módulo é essencial para o acompanhamento inicial do aluno e para registrar o histórico de saúde e condições relevantes antes da prática esportiva.
