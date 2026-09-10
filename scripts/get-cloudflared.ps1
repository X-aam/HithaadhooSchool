$ErrorActionPreference = 'Stop'
$dest = Join-Path $env:TEMP 'cloudflared.exe'
if (-not (Test-Path $dest)) {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    $url = 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe'
    Invoke-WebRequest -Uri $url -OutFile $dest -UseBasicParsing
}
$sizeMb = [math]::Round((Get-Item $dest).Length / 1MB, 1)
Write-Output ("PATH: " + $dest)
Write-Output ("SIZE_MB: " + $sizeMb)
& $dest --version
