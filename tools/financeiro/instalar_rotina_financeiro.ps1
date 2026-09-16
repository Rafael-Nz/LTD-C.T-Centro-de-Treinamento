$ErrorActionPreference = 'Stop'
# Verifica o banco antes de ativar uma rotina que altera cobrancas.
& 'D:\xampp\php\php.exe' (Join-Path $PSScriptRoot 'financeiro_status.php')
if ($LASTEXITCODE -ne 0) { throw 'Aplique a migracao financeira antes de agendar.' }
$financeiroTaskName = 'CTT - Gerar cobrancas'
$financeiroScript = Join-Path $PSScriptRoot 'rotina_financeiro.ps1'
$financeiroAction = New-ScheduledTaskAction -Execute 'powershell.exe' -Argument ('-NoProfile -NonInteractive -WindowStyle Hidden -File "' + $financeiroScript + '"')
$financeiroTrigger = New-ScheduledTaskTrigger -Daily -At '06:00'
$financeiroSettings = New-ScheduledTaskSettingsSet -StartWhenAvailable -MultipleInstances IgnoreNew
$financeiroPrincipal = New-ScheduledTaskPrincipal -UserId ([Security.Principal.WindowsIdentity]::GetCurrent().Name) -LogonType Interactive -RunLevel Limited
Register-ScheduledTask -TaskName $financeiroTaskName -Action $financeiroAction -Trigger $financeiroTrigger -Settings $financeiroSettings -Principal $financeiroPrincipal -Description 'Gera competencias faltantes de contratos ativos e atualiza vencidas.' -Force | Select-Object TaskName, State
