# Force Laragon to use PHP 8.4: stop the supervisor, hide the 8.3 folder from
# Laragon's "php*" scan, then relaunch. Reversible (8.3 folder is only renamed).
$ErrorActionPreference = 'SilentlyContinue'
$phpDir = 'C:\laragon\bin\php'
$laragon = 'C:\laragon\laragon.exe'
$old83 = Join-Path $phpDir 'php-8.3.30-Win32-vs16-x64'
$hidden83 = Join-Path $phpDir '_disabled_php-8.3.30-Win32-vs16-x64'

# 1. Stop the Laragon supervisor and its services.
& $laragon quit 2>$null | Out-Null
Start-Sleep -Seconds 3
Get-Process laragon -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Get-Process nginx, php-cgi, httpd -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 3

# 2. Rename 8.3 so it no longer matches Laragon's php* folder scan.
if (Test-Path $old83) {
    if (-not (Test-Path $hidden83)) {
        Rename-Item $old83 $hidden83 -ErrorAction Stop
        Write-Output "Hid 8.3 folder -> $hidden83"
    }
} else {
    Write-Output "8.3 folder already hidden/absent"
}

# 3. Confirm only 8.4 matches php*.
Write-Output "=== php* folders Laragon will see ==="
Get-ChildItem $phpDir -Directory -Filter 'php*' | Select-Object -ExpandProperty Name

# 4. Relaunch Laragon (AutoStart will bring nginx + php-cgi back on 8.4).
Start-Process -FilePath $laragon
Start-Sleep -Seconds 12

# 5. Verify.
Write-Output "=== php-cgi now running from ==="
Get-CimInstance Win32_Process -Filter "Name='php-cgi.exe'" -ErrorAction SilentlyContinue |
    Select-Object -First 1 -ExpandProperty CommandLine
Write-Output "=== port 8000 ==="
Test-NetConnection 127.0.0.1 -Port 8000 -InformationLevel Quiet
