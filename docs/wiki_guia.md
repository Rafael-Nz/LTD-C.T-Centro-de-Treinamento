# Guia de Publicação & Manutenção da Wiki

Esta documentação foi desenhada especificamente para funcionar de duas formas:
1. **Navegável diretamente no repositório**: Visualizada no GitHub / GitLab navegando pela pasta [`docs/`](README.md).
2. **Wiki Oficial do Repositório**: Publicada na aba **Wiki** nativa do GitHub ou GitLab.

---

## Como o GitHub Wiki Funciona

No GitHub, a Wiki de um repositório é, nos bastidores, um **repositório Git independente** cujo endereço segue o padrão:
```text
https://github.com/<usuario-ou-organizacao>/<nome-do-repositorio>.wiki.git
```

Quando você clona e faz `push` para esse repositório, o GitHub renderiza automaticamente as páginas Markdown.

### Arquivos Especiais da Wiki:
- **`Home.md`**: É a página inicial exibida ao abrir a aba Wiki.
- **`_Sidebar.md`**: Menu lateral customizado que aparece fixado em todas as páginas da Wiki.
- **`_Footer.md`**: Rodapé exibido no final de todas as páginas da Wiki.

Todos esses 3 arquivos já estão criados e configurados dentro de [`docs/`](Home.md).

---

## Como Publicar a Wiki (Passo a Passo)

### Passo 1: Habilitar a Wiki no GitHub
1. No seu repositório no GitHub, clique na aba **Settings** (Configurações).
2. Na seção **Features**, marque a caixa de seleção **Wikis**.
3. Clique na aba **Wiki** no topo do repositório.
4. Crie a primeira página inicial clicando em **Create the first page** e depois em **Save Page** (isso inicializa o repositório git da Wiki).

### Passo 2: Clonar o Repositório da Wiki
No seu terminal local (fora da pasta do projeto atual, por exemplo em `D:\xampp\htdocs\ctt-wiki`):

```powershell
git clone https://github.com/<seu-usuario>/<seu-repositorio>.wiki.git D:\xampp\htdocs\ctt-wiki
```

### Passo 3: Sincronizar os Arquivos
Você pode copiar os arquivos manualmente ou usar o script automatizado fornecido no projeto:

#### Opção A: Usando o Script Automatizado (Recomendado)
Execute no terminal a partir da raiz do projeto:
```powershell
& .\tools\wiki\sincronizar_wiki.ps1 -Destino "D:\xampp\htdocs\ctt-wiki"
```
O script copia `Home.md`, `_Sidebar.md`, `_Footer.md`, e todas as pastas (`core/`, `modules/`, `admin/`, `database/`, `tools/`, etc.) para o repositório da Wiki.

#### Opção B: Cópia Manual
Copie o conteúdo de `D:\xampp\htdocs\ctt\docs\` para a pasta `D:\xampp\htdocs\ctt-wiki\`.

### Passo 4: Enviar para o GitHub
No terminal da pasta clonada da wiki:

```powershell
cd D:\xampp\htdocs\ctt-wiki
git add .
git commit -m "docs: publicando wiki oficial do sistema"
git push origin master # ou main
```

Pronto! Ao atualizar a aba **Wiki** no GitHub, todas as páginas, índices, menus laterais e diagramas Mermaid estarão disponíveis.

---

## Bônus: Sincronização Automática via GitHub Actions (CI/CD)

Se desejar que toda alteração feita na pasta `docs/` do repositório principal seja enviada automaticamente para a Wiki a cada `git push`, crie o arquivo `.github/workflows/sync-wiki.yml`:

```yaml
name: Sincronizar Wiki

on:
  push:
    branches:
      - main
    paths:
      - 'docs/**'

jobs:
  sync-wiki:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout do código
        uses: actions/checkout@v4

      - name: Clonar Wiki do Repositório
        run: |
          git clone "https://x-access-token:${{ secrets.GITHUB_TOKEN }}@github.com/${{ github.repository }}.wiki.git" wiki-repo

      - name: Sincronizar arquivos docs para a wiki
        run: |
          # Copia os arquivos de docs para a wiki
          cp -R docs/* wiki-repo/
          cd wiki-repo
          git config user.name "github-actions[bot]"
          git config user.email "github-actions[bot]@users.noreply.github.com"
          git add .
          git diff --staged --quiet || (git commit -m "docs: atualizacao automatica da wiki via action" && git push)
```

---

## Boas Práticas ao Editar a Documentação

1. **Mantenha os links relativos**: Use `[Nome](subpasta/arquivo.md)` para garantir que funcionem tanto na visualização de arquivos do repositório quanto na Wiki.
2. **Atualize o `_Sidebar.md`**: Sempre que criar um documento novo, inclua o link na seção correspondente do menu lateral.
3. **Use alertas GitHub Flavored Markdown**: Como `> [!NOTE]`, `> [!IMPORTANT]` e `> [!WARNING]`, pois o GitHub Wiki os renderiza com estilos visuais destacados.
