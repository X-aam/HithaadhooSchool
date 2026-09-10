# Restart Laragon cleanly so it re-reads the selected PHP (8.4) from default.ini.
$ErrorActionPreference = 'SilentlyContinue'
$laragon = 'C:\laragon\laragon.exe'

# Kill supervisor first so it cannot respawn services, then the services.
Get-Process laragon -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2
Get-Process nginx, php-cgi, httpd -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2

Start-Process -FilePath $laragon
Start-Sleep -Seconds 12

Write-Output "=== php-cgi running from ==="
Get-CimInstance Win32_Process -Filter "Name='php-cgi.exe'" -ErrorAction SilentlyContinue |
    Select-Object -First 1 -ExpandProperty CommandLine
Write-Output "=== HTTP localhost:8000 ==="
try { "HTTP " + (Invoke-WebRequest 'http://127.0.0.1:8000/' -UseBasicParsing -TimeoutSec 20).StatusCode } catch { "ERR " + $_.Exception.Message }
