<#
.SYNOPSIS
    Script auxiliar para criar ou redefinir o Administrador inicial do LTD-C.T.

.DESCRIPTION
    Localiza o binario do PHP (PATH ou padrao XAMPP) e executa tools/sistema/criar_admin.php
    de forma interativa ou com parametros.

.PARAMETER Email
    E-mail de acesso do administrador.

.PARAMETER Senha
    Senha de acesso forte (minimo 12 caracteres, maiuscula, minuscula, numero e simbolo).

.PARAMETER Nome
    Nome do administrador.

.PARAMETER Reset
    Habilita o modo de redefinicao de senha para conta existente.

.EXAMPLE
    .\tools\sistema\criar_admin.ps1

.EXAMPLE
    .\tools\sistema\criar_admin.ps1 -Email "admin@meuclube.com" -Senha "Senha@Forte2026"

.EXAMPLE
    .\tools\sistema\criar_admin.ps1 -Reset
#>

[CmdletBinding()]
param (
    [Parameter(Mandatory = $false)]
    [string]$Email,

    [Parameter(Mandatory = $false)]
    [string]$Senha,

    [Parameter(Mandatory = $false)]
    [string]$Nome,

    [Parameter(Mandatory = $false)]
    [switch]$Reset
)

$ErrorActionPreference = 'Stop'

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$rootDir = Resolve-Path "$scriptDir\..\.."
$phpScript = Join-Path $scriptDir "criar_admin.php"

# Localiza o executavel do PHP
$phpBinary = "php"
if (-not (Get-Command "php" -ErrorAction SilentlyContinue)) {
    $xamppPhp = "D:\xampp\php\php.exe"
    $xamppPhpC = "C:\xampp\php\php.exe"
    if (Test-Path $xamppPhp) {
        $phpBinary = $xamppPhp
    } elseif (Test-Path $xamppPhpC) {
        $phpBinary = $xamppPhpC
    } else {
        Write-Error "O executavel do PHP nao foi encontrado no PATH nem nos caminhos padroes do XAMPP."
        exit 1
    }
}

# Monta os argumentos
$argsList = @($phpScript)

if ($Email) {
    $argsList += "--email=$Email"
}
if ($Senha) {
    $argsList += "--senha=$Senha"
}
if ($Nome) {
    $argsList += "--nome=$Nome"
}
if ($Reset) {
    $argsList += "--reset"
}

# Executa o script PHP
& $phpBinary $argsList
