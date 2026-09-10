$cf = Join-Path $env:TEMP 'cloudflared.exe'
$out = Join-Path $env:TEMP 'cf-tunnel.out.log'
$err = Join-Path $env:TEMP 'cf-tunnel.err.log'
# Stop any previous instance
Get-Process cloudflared -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
Remove-Item $out, $err -Force -ErrorAction SilentlyContinue
$p = Start-Process -FilePath $cf `
    -ArgumentList 'tunnel', '--no-autoupdate', '--url', 'http://localhost:8000' `
    -RedirectStandardOutput $out -RedirectStandardError $err `
    -WindowStyle Hidden -PassThru
Write-Output ("STARTED PID: " + $p.Id)
