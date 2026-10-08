# rename-apbd.ps1
# Jalankan dulu TANPA -Apply (dry run), cek hasilnya, baru jalankan dengan -Apply
#   .\rename-apbd.ps1            -> hanya menampilkan apa yang akan berubah
#   .\rename-apbd.ps1 -Apply     -> benar-benar mengubah

param([switch]$Apply)

$ErrorActionPreference = 'Stop'
$root = 'C:\laragon\www\dinsos26'
Set-Location $root

# false = file migration lama TIDAK disentuh (aman untuk database yang sudah berisi data,
#         pakai migration rename di bagian 2).
# true  = migration lama ikut diganti (hanya kalau database boleh di-reset / migrate:fresh).
$IncludeMigrations = $false

$scanDirs  = 'app','bootstrap','config','database','resources','routes','tests','lang','public\js','public\css' |
             Where-Object { Test-Path $_ }
$extensions = '.php','.js','.css','.json','.md','.txt','.xml','.yml','.yaml'
$excludeRegex = '\\(vendor|node_modules|storage|\.git|build)\\'
$migrationsRegex = '\\database\\migrations\\'

$utf8NoBom = New-Object System.Text.UTF8Encoding($false)

function Convert-Apbn([string]$s) {
    # urutan aman: semua varian huruf (APBN / Apbn / apbn) -> APBD / Apbd / apbd
    return $s.Replace('APBN','APBD').Replace('Apbn','Apbd').Replace('apbn','apbd')
}

function Skip-Path([string]$path) {
    if ($path -match $excludeRegex) { return $true }
    if (-not $IncludeMigrations -and $path -match $migrationsRegex) { return $true }
    return $false
}

# ---------- TAHAP 1: ISI FILE ----------
Write-Host "`n=== TAHAP 1: ISI FILE ===" -ForegroundColor Cyan
$files = Get-ChildItem -Path $scanDirs -Recurse -File |
         Where-Object { $extensions -contains $_.Extension.ToLower() -and -not (Skip-Path $_.FullName) }

$changed = 0
foreach ($f in $files) {
    $content = [System.IO.File]::ReadAllText($f.FullName)
    if ($content -match '(?i)apbn') {
        $count = ([regex]::Matches($content, '(?i)apbn')).Count
        Write-Host ("{0}  ({1} kemunculan)" -f $f.FullName.Replace($root + '\',''), $count)
        if ($Apply) {
            [System.IO.File]::WriteAllText($f.FullName, (Convert-Apbn $content), $utf8NoBom)
        }
        $changed++
    }
}
Write-Host "Total file berisi 'apbn': $changed" -ForegroundColor Yellow

# ---------- TAHAP 2: NAMA FILE & FOLDER ----------
Write-Host "`n=== TAHAP 2: NAMA FILE & FOLDER ===" -ForegroundColor Cyan
$items = Get-ChildItem -Path $scanDirs -Recurse |
         Where-Object { $_.Name -match '(?i)apbn' -and -not (Skip-Path ($_.FullName + '\')) } |
         Sort-Object { $_.FullName.Length } -Descending   # yang paling dalam dulu

foreach ($i in $items) {
    $newName = Convert-Apbn $i.Name
    Write-Host ("{0}  ->  {1}" -f $i.FullName.Replace($root + '\',''), $newName)
    if ($Apply) {
        Rename-Item -LiteralPath $i.FullName -NewName $newName
    }
}

if ($Apply) {
    Write-Host "`nSelesai. Lanjutkan: composer dump-autoload ; php artisan optimize:clear" -ForegroundColor Green
} else {
    Write-Host "`nIni DRY RUN. Jalankan ulang dengan -Apply untuk mengeksekusi." -ForegroundColor Green
}
