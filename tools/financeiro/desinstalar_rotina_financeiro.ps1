$ErrorActionPreference = 'SilentlyContinue'
$financeiroTaskName = 'CTT - Gerar cobrancas'

$task = Get-ScheduledTask -TaskName $financeiroTaskName -ErrorAction SilentlyContinue
if ($task) {
    Unregister-ScheduledTask -TaskName $financeiroTaskName -Confirm:$false
    Write-Host "Tarefa agendada '$financeiroTaskName' removida com sucesso do Windows!" -ForegroundColor Green
} else {
    Write-Host "A tarefa '$financeiroTaskName' nao foi encontrada no Agendador do Windows." -ForegroundColor Yellow
}
