$projectRoot=Split-Path -Parent $PSScriptRoot
$pidPath=Join-Path $projectRoot 'storage/logs/local-server.pid'
if(Test-Path -LiteralPath $pidPath){$serverProcessId=[int](Get-Content -LiteralPath $pidPath);$server=Get-CimInstance Win32_Process -Filter "ProcessId = $serverProcessId" -ErrorAction SilentlyContinue;if($server -and $server.CommandLine -like '*127.0.0.1:8098*' -and $server.CommandLine -like '*tools/server.php*'){Stop-Process -Id $serverProcessId;Remove-Item -LiteralPath $pidPath;Write-Output 'Laravel local server stopped.'}}
