# Ferramentas do projeto

Os comandos auxiliares ficam separados pela finalidade para evitar que testes sejam confundidos com rotinas usadas pelo sistema.

## Operacao financeira

A pasta `tools/financeiro/` faz parte da instalacao e da operacao normal:

- `financeiro_bootstrap.php`: inicializa os comandos PHP executados pelo terminal;
- `financeiro_status.php`: verifica se o banco possui a estrutura financeira atual;
- `gerar_cobrancas.php`: gera competencias faltantes e atualiza cobrancas vencidas;
- `rotina_financeiro.ps1`: executa a geracao e grava o log;
- `instalar_rotina_financeiro.ps1`: instala a tarefa diaria no Agendador do Windows;
- `ativar_cobranca_obrigatoria.php`: ajusta uma vez o indicador legado dos contratos;
- `financeiro_rotina.log`: arquivo local criado pela rotina e ignorado pelo Git.

## Verificacao do sistema

`tools/sistema/verify_audit.php` valida a cadeia de auditoria usando a conta configurada no `.env`.

## Testes

`tools/testes/financeiro/` contem somente testes. Execute a suite rapida pela raiz:

```powershell
composer test
```

Os testes completos exigem o MariaDB isolado descrito em `docs/modules/financeiro.md` e nunca devem usar o banco normal.

## Desenvolvimento

`tools/desenvolvimento/` nao faz parte da operacao diaria. O gerador de migracao financeira altera arquivos SQL do repositorio e deve ser usado apenas por desenvolvedores, com revisao posterior do `git diff`.

`tools/email/` contem o template e as dependencias para compilar os e-mails MJML durante o desenvolvimento ou a preparacao do pacote.
