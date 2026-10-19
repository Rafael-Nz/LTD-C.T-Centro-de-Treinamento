<#
.SYNOPSIS
    Sincroniza os arquivos de documentacao de docs/ para o repositorio clonado da Wiki do GitHub/GitLab.

.DESCRIPTION
    Copia Home.md, _Sidebar.md, _Footer.md e todos os arquivos Markdown e subpastas de documentacao
    para o diretorio de destino da Wiki, ignorando arquivos de banco SQL pesados se desejado.

.PARAMETER Destino
    O caminho para o repositorio clonado da Wiki (ex: D:\xampp\htdocs\ctt-wiki).

.EXAMPLE
    .\tools\wiki\sincronizar_wiki.ps1 -Destino "..\ctt.wiki"
#>

[CmdletBinding()]
param (
    [Parameter(Mandatory = $true, HelpMessage = "Caminho da pasta do repositorio clonado da Wiki")]
    [string]$Destino
)

$ErrorActionPreference = 'Stop'

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$rootDir = Resolve-Path "$scriptDir\..\.."
$docsDir = Join-Path $rootDir "docs"

if (-not (Test-Path $docsDir)) {
    Write-Error "Pasta docs nao encontrada em: $docsDir"
    exit 1
}

if (-not (Test-Path $Destino)) {
    Write-Error "Pasta de destino da Wiki nao encontrada: $Destino. Clone o repositorio .wiki.git primeiro."
    exit 1
}

Write-Host "Iniciando sincronizacao da documentacao..." -ForegroundColor Cyan
Write-Host "Origem:  $docsDir"
Write-Host "Destino: $Destino"

# Lista de pastas e arquivos para copiar
$items = Get-ChildItem -Path $docsDir

foreach ($item in $items) {
    $targetPath = Join-Path $Destino $item.Name

    # Se for a pasta sql, apenas copiamos se o usuario quiser, ou mantemos para referencia
    if ($item.PSIsContainer) {
        Write-Host "-> Sincronizando diretorio: $($item.Name)..." -ForegroundColor Yellow
        Copy-Item -Path $item.FullName -Destination $Destino -Recurse -Force
    } else {
        Write-Host "-> Copiando arquivo: $($item.Name)..." -ForegroundColor Green
        Copy-Item -Path $item.FullName -Destination $targetPath -Force
    }
}

Write-Host "`nSincronizacao concluida com sucesso!" -ForegroundColor Green
Write-Host "Para enviar as alteracoes para a Wiki remota, acesse $Destino e execute:" -ForegroundColor White
Write-Host "  git add ." -ForegroundColor Gray
Write-Host "  git commit -m 'docs: sincronizacao da wiki'" -ForegroundColor Gray
Write-Host "  git push" -ForegroundColor Gray
