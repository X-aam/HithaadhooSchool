# Starts the Laravel server + cloudflared quick tunnel and records the public URL.
# Intended to run at logon via a Scheduled Task (see scripts/register-public-task.ps1).
$ErrorActionPreference = 'SilentlyContinue'

$root = Split-Path -Parent $PSScriptRoot
$php = 'C:\php84\php.exe'
$cf = Join-Path $env:TEMP 'cloudflared.exe'
$urlFile = Join-Path $root 'public-url.txt'
$cfOut = Join-Path $env:TEMP 'cf-tunnel.out.log'
$cfErr = Join-Path $env:TEMP 'cf-tunnel.err.log'
$serveLog = Join-Path $env:TEMP 'artisan-serve.log'

# Ensure cloudflared binary exists.
if (-not (Test-Path $cf)) {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    Invoke-WebRequest -Uri 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe' -OutFile $cf -UseBasicParsing
}

# Serve built assets (not the Vite dev server).
Remove-Item (Join-Path $root 'public\hot') -Force -ErrorAction SilentlyContinue

# Stop previous instances.
Get-Process cloudflared -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Get-CimInstance Win32_Process -Filter "Name='php.exe'" -ErrorAction SilentlyContinue |
    Where-Object { $_.CommandLine -match 'artisan serve' } |
    ForEach-Object { Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue }
Remove-Item $cfOut, $cfErr -Force -ErrorAction SilentlyContinue

# Start the Laravel server on port 8000.
Start-Process -FilePath $php `
    -ArgumentList 'artisan', 'serve', '--port=8000' `
    -WorkingDirectory $root `
    -RedirectStandardOutput $serveLog `
    -WindowStyle Hidden

# Wait for port 8000 to accept connections (up to ~30s).
for ($i = 0; $i -lt 30; $i++) {
    if (Test-NetConnection -ComputerName 127.0.0.1 -Port 8000 -InformationLevel Quiet) { break }
    Start-Sleep -Seconds 1
}

# Start the cloudflared quick tunnel.
Start-Process -FilePath $cf `
    -ArgumentList 'tunnel', '--no-autoupdate', '--url', 'http://localhost:8000' `
    -RedirectStandardOutput $cfOut -RedirectStandardError $cfErr `
    -WindowStyle Hidden

# Wait for the public URL to appear in the log (up to ~30s) and record it.
$url = $null
for ($i = 0; $i -lt 30; $i++) {
    Start-Sleep -Seconds 1
    $match = Select-String -Path $cfErr -Pattern 'https://[a-z0-9-]+\.trycloudflare\.com' -ErrorAction SilentlyContinue |
        Select-Object -First 1
    if ($match) {
        $url = $match.Matches[0].Value
        break
    }
}

if ($url) {
    Set-Content -Path $urlFile -Value $url -Encoding ascii
    Write-Output $url
}
else {
    Write-Output 'FAILED: no tunnel URL found (see cf-tunnel.err.log)'
}
