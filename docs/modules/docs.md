# Módulo Docs (Swagger / OpenAPI)

## Objetivo

Centralizar e fornecer a documentação navegável, viva e interativa de todas as rotas e contratos de dados da API da aplicação Cross C.T através do Swagger UI e da especificação OpenAPI 3.0.

## Diretório

- [`api/src/docs`](../../api/src/docs)

## Rotas principais

- `GET /api/docs`: Interface visual interativa (Swagger UI).
- `GET /api/openapi.json`: Especificação completa em JSON (OpenAPI 3.0.3).
- `GET /docs`: Atalho público com redirecionamento automático para a interface.

## Componentes

### DocsController

Arquivo: [`DocsController.php`](../../api/src/docs/DocsController.php)

Responsável por:

- Iniciar a sessão da aplicação para verificação de permissões e contexto.
- Renderizar a página HTML do Swagger UI configurada com os assets oficiais via CDN (jsdelivr).
- Injetar no cliente Swagger scripts para interceptação de requisições, capturando o cookie `CTT_CSRF_TOKEN` e transmitindo o cabeçalho `X-CSRF-Token` automaticamente.
- Servir o arquivo `openapi.json` com os headers adequados (`Content-Type: application/json; charset=utf-8`).

### Especificação OpenAPI 3.0

Arquivo: [`openapi.json`](../../api/src/docs/openapi.json)

Catálogo estruturado contendo:

- Informações gerais da API, versão e servidores disponíveis.
- Esquemas de segurança: Autenticação por Cookie (`cookieAuth`) e Proteção CSRF (`csrfHeader`).
- Mapeamento detalhado de rotas por grupo/domínio:
  - Autenticação e Sessão
  - Usuários, Cargos e Permissões
  - Alunos, Fichas de Anamnese e Avaliações Físicas
  - Funcionários e Modalidades
  - Turmas, Espaços de Treino, Treinos e Presenças
  - Módulo Financeiro (Serviços, Planos, Contratos, Cobranças e Pagamentos)
  - Relatórios e Agenda
- Definição dos Schemas DTO e formatos esperados de requisições e respostas.

## Como utilizar

1. Inicie o Apache no XAMPP.
2. Acesse no navegador: `http://localhost/ctt/api/docs` (ou `http://localhost/ctt/docs`).
3. Para testar endpoints protegidos diretamente pelo navegador, faça login previamente no painel administrativo ou utilize o endpoint de login documentado na interface.
