$ErrorActionPreference='Stop'
$projectRoot=Split-Path -Parent $PSScriptRoot
$listener=Get-NetTCPConnection -State Listen -LocalPort 8098 -ErrorAction SilentlyContinue
if($listener){Write-Output 'Port 8098 is already in use. Laravel link: http://localhost:8098/';exit}
$server=Start-Process -FilePath 'C:\wamp64\bin\php\php8.3.6\php.exe' -ArgumentList @('-d','xdebug.mode=off','-d','opcache.enable_cli=1','-d','opcache.validate_timestamps=1','-d','opcache.revalidate_freq=0','-d','opcache.memory_consumption=192','-d','memory_limit=512M','-d','upload_max_filesize=20M','-d','post_max_size=24M','-S','127.0.0.1:8098','-t','public','tools/server.php') -WorkingDirectory $projectRoot -WindowStyle Hidden -PassThru -RedirectStandardOutput "$projectRoot\storage\logs\local-server.out.log" -RedirectStandardError "$projectRoot\storage\logs\local-server.err.log"
$server.Id | Set-Content "$projectRoot\storage\logs\local-server.pid"
# Warm the framework/opcode cache before handing the local link to the user.
$ready=$false
for($attempt=0;$attempt -lt 3;$attempt++){
    try{$response=Invoke-WebRequest -Uri 'http://127.0.0.1:8098/' -UseBasicParsing -TimeoutSec 20;if($response.StatusCode -eq 200){$ready=$true;break}}catch{Start-Sleep -Milliseconds 200}
}
if(-not $ready){Write-Warning 'The server started, but the homepage has not passed its readiness check yet.'}

Write-Output 'Laravel website: http://localhost:8098/'
Write-Output 'Laravel admin: http://localhost:8098/auth/v/login'
