# Daily MySQL backup for DTIS v2 (Docker setup).
# Reads MYSQL_ROOT_PASSWORD from .env.docker, dumps the dtis database to
# $BackupDir, and deletes dumps older than $RetentionDays.
#
# Register with Task Scheduler (run once, elevated):
#   schtasks /Create /TN "DTIS DB Backup" /SC DAILY /ST 02:00 /RU SYSTEM /TR "powershell -NoProfile -ExecutionPolicy Bypass -File C:\docker\DTISV2\docker\backup-db.ps1"

param(
    [string]$ProjectDir    = "C:\docker\DTISV2",
    [string]$BackupDir     = "C:\backups\dtis",
    [int]   $RetentionDays = 14
)

$ErrorActionPreference = "Stop"
Set-Location $ProjectDir

if (-not (Test-Path $BackupDir)) {
    New-Item -ItemType Directory -Force $BackupDir | Out-Null
}

$match = Select-String -Path ".env.docker" -Pattern '^MYSQL_ROOT_PASSWORD=(.+)$'
if (-not $match) {
    Write-Error "MYSQL_ROOT_PASSWORD not found in .env.docker"
    exit 1
}
$rootPass = $match.Matches[0].Groups[1].Value.Trim()

$stamp = Get-Date -Format "yyyyMMdd_HHmmss"
$file  = Join-Path $BackupDir "dtis_$stamp.sql"

# cmd handles the > redirection so the dump is written as raw bytes
# (PowerShell's > would re-encode it as UTF-16 and corrupt the dump).
cmd /c "docker compose exec -T db mysqldump -u root -p$rootPass --single-transaction --routines --triggers dtis > `"$file`""

if ($LASTEXITCODE -ne 0 -or -not (Test-Path $file) -or (Get-Item $file).Length -eq 0) {
    if (Test-Path $file) { Remove-Item $file }
    Write-Error "Backup failed - mysqldump exited with code $LASTEXITCODE"
    exit 1
}

Get-ChildItem $BackupDir -Filter "dtis_*.sql" |
    Where-Object LastWriteTime -lt (Get-Date).AddDays(-$RetentionDays) |
    Remove-Item

Write-Output "Backup OK: $file ($([math]::Round((Get-Item $file).Length / 1MB, 2)) MB)"
