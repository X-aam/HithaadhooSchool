$log = @()
$startup = "$env:APPDATA\Microsoft\Windows\Start Menu\Programs\Startup\HithaadhooPublicTunnel.cmd"
if (Test-Path $startup) { Remove-Item $startup -Force; $log += "Removed startup launcher" } else { $log += "Startup launcher not present" }

$t = Get-ScheduledTask -TaskName 'HithaadhooPublicTunnel' -ErrorAction SilentlyContinue
if ($t) { Unregister-ScheduledTask -TaskName 'HithaadhooPublicTunnel' -Confirm:$false; $log += "Unregistered scheduled task" } else { $log += "No scheduled task present" }

$cf = Get-Process cloudflared -ErrorAction SilentlyContinue
if ($cf) { $cf | Stop-Process -Force; $log += ("Stopped cloudflared quick tunnel PID " + ($cf.Id -join ',')) } else { $log += "No cloudflared running" }

$phpServe = Get-CimInstance Win32_Process -Filter "Name='php.exe'" -ErrorAction SilentlyContinue | Where-Object { $_.CommandLine -match 'artisan serve' }
if ($phpServe) { $phpServe | ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }; $log += ("Stopped php artisan serve PID " + ($phpServe.ProcessId -join ',')) } else { $log += "No php artisan serve running" }

$log | ForEach-Object { Write-Output $_ }
