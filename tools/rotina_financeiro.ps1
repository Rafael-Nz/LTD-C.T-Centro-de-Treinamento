$ErrorActionPreference = 'Stop'
$financeiroPhp = 'D:\xampp\php\php.exe'
$financeiroLog = Join-Path $PSScriptRoot 'financeiro_rotina.log'
& $financeiroPhp (Join-Path $PSScriptRoot 'gerar_cobrancas.php') >> $financeiroLog 2>&1
exit $LASTEXITCODE
