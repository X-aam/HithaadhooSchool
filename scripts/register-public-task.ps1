# Registers (or updates) a Scheduled Task that runs scripts/start-public.ps1 at logon.
# Run once from an elevated PowerShell: powershell -ExecutionPolicy Bypass -File .\scripts\register-public-task.ps1
$ErrorActionPreference = 'Stop'

$taskName = 'HithaadhooPublicTunnel'
$script = Join-Path $PSScriptRoot 'start-public.ps1'

$action = New-ScheduledTaskAction -Execute 'powershell.exe' `
    -Argument ('-NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File "{0}"' -f $script)

# Run shortly after logon so the network stack is ready.
$trigger = New-ScheduledTaskTrigger -AtLogOn
$trigger.Delay = 'PT20S'

$settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries `
    -StartWhenAvailable -ExecutionTimeLimit ([TimeSpan]::Zero)

$principal = New-ScheduledTaskPrincipal -UserId $env:USERNAME -LogonType Interactive -RunLevel Limited

Register-ScheduledTask -TaskName $taskName -Action $action -Trigger $trigger `
    -Settings $settings -Principal $principal -Force | Out-Null

Write-Output ("Registered scheduled task '{0}' (runs at logon)." -f $taskName)
Write-Output "To remove: Unregister-ScheduledTask -TaskName '$taskName' -Confirm:`$false"
