# Módulo Anamnese

## Objetivo

Gerencia formulários de anamnese, perguntas, opções de resposta e respostas dos alunos.

## Diretório

- [`api/src/anamnese`](../../api/src/anamnese)

## Rotas principais

- `GET /anamnese/formularios`
- `GET /anamnese/formularios/{id}`
- `GET /anamnese/formularios/{id}/perguntas`
- `GET /anamnese/respostas/{aluno_id}`
- `POST /anamnese`

## Componentes

### AnamneseController

Arquivo: [`AnamneseController.php`](../../api/src/anamnese/AnamneseController.php)

Responsável por:

- listar formulários e perguntas;
- recuperar respostas por aluno;
- gravar um envio de anamnese inteiro;
- responder em JSON estruturado.

### AnamneseService

Arquivo: [`AnamneseService.php`](../../api/src/anamnese/AnamneseService.php)

Responsável por:

- validar o formulário enviado;
- consolidar regras do processo de anamnese;
- orquestrar persistência da resposta completa.

### AnamneseRepository

Arquivo: [`AnamneseRepository.php`](../../api/src/anamnese/AnamneseRepository.php)

Responsável por:

- consultar formulários e perguntas;
- salvar respostas e opções marcadas;
- montar o retorno completo da anamnese do aluno.

### DTOs

- [`FormularioDTO.php`](../../api/src/anamnese/DTO/FormularioDTO.php)
- [`PerguntaDTO.php`](../../api/src/anamnese/DTO/PerguntaDTO.php)
- [`OpcaoDTO.php`](../../api/src/anamnese/DTO/OpcaoDTO.php)
- [`RespostaDTO.php`](../../api/src/anamnese/DTO/RespostaDTO.php)
- [`EnvioAnamneseDTO.php`](../../api/src/anamnese/DTO/EnvioAnamneseDTO.php)
- [`RegraExibicaoDTO.php`](../../api/src/anamnese/DTO/RegraExibicaoDTO.php)
- [`CondicaoDTO.php`](../../api/src/anamnese/DTO/CondicaoDTO.php)

### Validation

- [`RespostaValorRule`]()

Valida o valor enviado pela resposta, respeitando regras específicas do tipo de pergunta.

## Regras de negócio

- As perguntas podem ter regras de exibição condicionais.
- O módulo suporta respostas com tipos específicos e validação de conteúdo.
- A resposta pode ser centralizada em um único envio da anamnese para o aluno.

## Importância funcional

Esse módulo é essencial para o acompanhamento inicial do aluno e para registrar o histórico de saúde e condições relevantes antes da prática esportiva.
