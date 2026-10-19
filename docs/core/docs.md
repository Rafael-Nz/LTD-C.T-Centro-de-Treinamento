# Core: Documentação da API (Swagger / OpenAPI)

## Objetivo

Prover infraestrutura para renderização da documentação interativa da API via Swagger UI e servir a especificação OpenAPI 3.0 em formato JSON.

## Arquivos principais

- [`api/src/docs/DocsController.php`](../../api/src/docs/DocsController.php): Controller que serve a interface Swagger UI (HTML) e o endpoint da especificação JSON.
- [`api/src/docs/openapi.json`](../../api/src/docs/openapi.json): Especificação técnica OpenAPI 3.0.3 contendo schemas, rotas, parâmetros e respostas de todos os endpoints.
- [`api/routes/api.php`](../../api/routes/api.php): Registro das rotas públicas de documentação.
- [`api/.htaccess`](../../api/.htaccess): Liberação explícita para o arquivo `openapi.json`.
- [`.htaccess`](../../.htaccess): Regra de redirecionamento amigável (`/ctt/docs` -> `/ctt/api/docs`).

## Rotas disponíveis

- `GET /api/docs`: Renderiza a interface interativa do Swagger UI.
- `GET /api/openapi.json`: Retorna a especificação técnica em formato JSON.
- `GET /docs`: Redireciona com status HTTP 301 para `/ctt/api/docs`.

## Recursos do Swagger UI

1. **Testes Interativos ("Try it out"):**
   - Suporte completo para envio de requisições diretamente pela interface.
2. **Integração Automática com Sessão e CSRF:**
   - O Swagger UI utiliza a flag `credentials: 'same-origin'` para enviar os cookies de sessão (`PHPSESSID`) do navegador.
   - O interceptor de requisição lê o cookie `CTT_CSRF_TOKEN` e injeta automaticamente o cabeçalho `X-CSRF-Token` em todas as requisições de mutação (POST, PUT, DELETE), permitindo testar rotas protegidas sem configuração manual de tokens.
3. **Tema e Identidade Visual:**
   - Barra superior estilizada com a identidade do sistema Cross C.T.
