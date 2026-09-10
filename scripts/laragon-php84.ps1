# Switch Laragon to PHP 8.4 by adding the standalone C:\php84 build and disabling 8.3.
# Reversible: the 8.3 folder is renamed (not deleted) to *.disabled.
$ErrorActionPreference = 'Stop'
$src = 'C:\php84'
$phpDir = 'C:\laragon\bin\php'
$dst = Join-Path $phpDir 'php-8.4.22-Win32-vs17-x64'
$laragon = 'C:\laragon\laragon.exe'

# 1. Copy the 8.4 build into Laragon (includes its working php.ini + ext).
if (-not (Test-Path $dst)) {
    Copy-Item -Path $src -Destination $dst -Recurse -Force
    Write-Output "Copied 8.4 build to $dst"
} else {
    Write-Output "8.4 build already present at $dst"
}
# Ensure a php.ini exists in the copy.
if (-not (Test-Path (Join-Path $dst 'php.ini'))) {
    if (Test-Path (Join-Path $dst 'php.ini-production')) {
        Copy-Item (Join-Path $dst 'php.ini-production') (Join-Path $dst 'php.ini') -Force
        Write-Output "Created php.ini from php.ini-production"
    }
}

# 2. Stop Laragon services (nginx + php-cgi) so the 8.3 folder unlocks.
& $laragon stop | Out-Null
Start-Sleep -Seconds 5
# Force-release any lingering handlers.
Get-Process nginx, php-cgi -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 2

# 3. Disable the 8.3 folder so Laragon has only 8.4 to use.
Get-ChildItem $phpDir -Directory -Filter 'php-8.3.30-*' | Where-Object { $_.Name -notlike '*.disabled' } | ForEach-Object {
    $new = "$($_.FullName).disabled"
    if (-not (Test-Path $new)) { Rename-Item $_.FullName $new; Write-Output "Disabled: $($_.Name)" }
}

# 4. Restart Laragon services with 8.4 as the only PHP.
& $laragon start | Out-Null
Start-Sleep -Seconds 8

# 5. Report.
Write-Output "=== php-cgi now running from ==="
Get-CimInstance Win32_Process -Filter "Name='php-cgi.exe'" -ErrorAction SilentlyContinue |
    Select-Object -First 1 -ExpandProperty CommandLine
Write-Output "=== port 8000 ==="
Test-NetConnection 127.0.0.1 -Port 8000 -InformationLevel Quiet
