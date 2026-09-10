# Rename ANY php-8.3* Laragon folder to an "_off_" prefix so it no longer matches
# Laragon's php* scan, leaving only PHP 8.4. Reversible.
$ErrorActionPreference = 'SilentlyContinue'
$phpDir = 'C:\laragon\bin\php'
$laragon = 'C:\laragon\laragon.exe'

# 1. Stop supervisor + services.
& $laragon quit 2>$null | Out-Null
Start-Sleep -Seconds 3
Get-Process laragon -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Get-Process nginx, php-cgi, httpd -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Start-Sleep -Seconds 3

# 2. Rename every php-8.3* folder to _off_ prefix.
Get-ChildItem $phpDir -Directory -Filter 'php-8.3*' | ForEach-Object {
    $new = Join-Path $phpDir ("_off_" + $_.Name)
    if (-not (Test-Path $new)) { Rename-Item $_.FullName $new -ErrorAction Stop; Write-Output "Renamed $($_.Name) -> _off_$($_.Name)" }
}

Write-Output "=== folders matching php* (Laragon will see) ==="
Get-ChildItem $phpDir -Directory -Filter 'php*' | Select-Object -ExpandProperty Name

# 3. Relaunch Laragon.
Start-Process -FilePath $laragon
Start-Sleep -Seconds 12

# 4. Verify.
Write-Output "=== php-cgi now running from ==="
Get-CimInstance Win32_Process -Filter "Name='php-cgi.exe'" -ErrorAction SilentlyContinue |
    Select-Object -First 1 -ExpandProperty CommandLine
