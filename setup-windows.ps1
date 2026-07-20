#Requires -Version 5.1
<#
.SYNOPSIS
    Setup otomatis untuk project We Rent Car (Laravel + Inertia + Vue) di Windows 10.

.DESCRIPTION
    Script ini mengecek dan menyiapkan seluruh tool yang dibutuhkan:
      - Node.js  (minimum v20, di-upgrade lewat winget kalau lebih lawas)
      - PHP      (minimum 8.2, di-upgrade lewat winget kalau lebih lawas)
      - Composer (di-install via installer resmi getcomposer.org kalau belum ada,
                  di-self-update kalau sudah ada tapi lawas)
      - PostgreSQL (minimum versi 15, di-install via winget kalau belum ada;
                    kalau versi mayor sudah lebih tua dari target, script TIDAK
                    melakukan upgrade otomatis karena upgrade mayor Postgres
                    berpotensi merusak data - cukup diberi peringatan)
      - Ekstensi PHP yang dibutuhkan Laravel (pdo_pgsql, pgsql, mbstring, dst.)
      - Database & role PostgreSQL sesuai `.env` project ini
      - `composer install`, `npm install`, `.env`, `APP_KEY`, migrasi database

    Logika tiap tool: BELUM ADA -> install. ADA tapi LEBIH LAWAS dari minimum
    -> upgrade. ADA dan SUDAH SETARA/LEBIH BARU -> skip.

.NOTES
    - Jalankan sebagai Administrator (script akan minta elevasi otomatis kalau belum).
    - Package manager: winget dicoba dulu (bawaan Windows 10 versi 2004+ / Windows 11).
      Kalau winget tidak ada, ATAU gagal jalan (mis. minta update "App Installer"
      dari Microsoft Store), script otomatis beralih ke Chocolatey sebagai
      alternatif - Chocolatey di-bootstrap sendiri lewat installer resmi
      chocolatey.org (cukup HTTPS, tidak butuh Microsoft Store sama sekali).
    - Composer tidak punya package winget/choco yang dipakai di sini, jadi
      selalu dipasang lewat installer resmi (Composer-Setup.exe) mengikuti
      cara yang direkomendasikan getcomposer.org - tidak tergantung package
      manager mana pun.

.USAGE
    Klik kanan file ini -> "Run with PowerShell", ATAU dari terminal:
        powershell -ExecutionPolicy Bypass -File .\setup-windows.ps1
#>

[CmdletBinding()]
param(
    [switch]$SkipProjectSetup
)

$ErrorActionPreference = 'Stop'
$ProjectRoot = $PSScriptRoot

# ============================================================================
# Konfigurasi minimum versi & target install
# ============================================================================
$Config = @{
    NodeMinVersion   = [version]'20.0.0'
    NodeWingetId     = 'OpenJS.NodeJS.LTS'
    NodeChocoId      = 'nodejs-lts'

    PhpMinVersion    = [version]'8.2.0'
    PhpWingetId      = 'PHP.PHP.8.3'
    PhpChocoId       = 'php'

    ComposerMinVersion = [version]'2.2.0'

    PgMinMajor       = 15
    PgTargetMajor    = 16
    PgWingetId       = 'PostgreSQL.PostgreSQL.16'
    PgChocoId        = 'postgresql16'

    PhpExtensions    = @('pdo_pgsql', 'pgsql', 'mbstring', 'openssl', 'fileinfo', 'curl', 'zip', 'gd', 'intl', 'bcmath')
}

# Diisi oleh Initialize-PackageManager: 'winget' atau 'choco'
$script:PackageManager = $null

# ============================================================================
# Helper umum
# ============================================================================

function Write-Section($Title) {
    Write-Host ''
    Write-Host "=== $Title ===" -ForegroundColor Cyan
}

function Write-Ok($Message) { Write-Host "  [OK] $Message" -ForegroundColor Green }
function Write-Info($Message) { Write-Host "  [INFO] $Message" -ForegroundColor Yellow }
function Write-Skip($Message) { Write-Host "  [SKIP] $Message" -ForegroundColor DarkGray }
function Write-Err($Message) { Write-Host "  [ERROR] $Message" -ForegroundColor Red }

function Assert-Admin {
    $identity = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($identity)
    $isAdmin = $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)

    if (-not $isAdmin) {
        Write-Host 'Script ini butuh hak Administrator, akan diminta ulang lewat UAC...' -ForegroundColor Yellow
        $argList = @('-NoProfile', '-ExecutionPolicy', 'Bypass', '-File', "`"$PSCommandPath`"")
        if ($SkipProjectSetup) { $argList += '-SkipProjectSetup' }
        Start-Process powershell -Verb RunAs -ArgumentList $argList
        exit
    }
}

function ConvertTo-VersionSafe {
    # Sebagian tool (terutama Composer, dan PHP setelah ekstensi baru diaktifkan)
    # kadang mencetak baris peringatan SEBELUM baris versi (mis. "Do not run
    # Composer as root/superuser" - relevan karena script ini jalan sebagai
    # Administrator). Kalau itu terjadi, PowerShell mengembalikan output
    # sebagai System.Object[] (array baris), bukan string tunggal, sehingga
    # `-replace` dan cast [version] langsung berikutnya akan gagal dengan
    # error "Cannot convert System.Object[]...". Fungsi ini menggabungkan
    # dulu semua baris jadi satu string lalu mengambil hanya angka versinya
    # lewat regex, supaya aman dari banyak/sedikitnya baris output.
    param(
        [Parameter(Mandatory)][AllowNull()][object]$Output,
        [Parameter(Mandatory)][string]$Pattern
    )

    $text = ($Output | Out-String)
    if ($text -match $Pattern) {
        try { return [version]$Matches[1] } catch { return $null }
    }
    return $null
}

function Update-SessionPath {
    # Menyusun ulang $env:Path dari registry Machine + User supaya tool yang
    # baru diinstal langsung terdeteksi tanpa perlu buka terminal baru.
    $machinePath = [Environment]::GetEnvironmentVariable('Path', 'Machine')
    $userPath = [Environment]::GetEnvironmentVariable('Path', 'User')
    $env:Path = @($machinePath, $userPath) -join ';'
}

function Install-Chocolatey {
    if (Get-Command choco -ErrorAction SilentlyContinue) { return }
    Write-Info 'Memasang Chocolatey (package manager alternatif, cukup HTTPS - tidak butuh Microsoft Store)...'
    Set-ExecutionPolicy Bypass -Scope Process -Force
    [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072
    Invoke-Expression ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
    Update-SessionPath

    if (-not (Get-Command choco -ErrorAction SilentlyContinue)) {
        Write-Err 'Gagal memasang Chocolatey. Cek koneksi internet lalu jalankan ulang script ini.'
        exit 1
    }
    Write-Ok "Chocolatey terpasang: $(choco --version)"
}

function Switch-ToChocolatey {
    if ($script:PackageManager -eq 'choco') { return }
    $script:PackageManager = 'choco'
    Install-Chocolatey
}

function Initialize-PackageManager {
    Write-Section 'Package Manager'

    if (Get-Command winget -ErrorAction SilentlyContinue) {
        Write-Ok 'winget ditemukan, akan dicoba lebih dulu untuk tiap tool.'
        Write-Info 'Kalau winget gagal jalan (mis. minta update App Installer), script otomatis beralih ke Chocolatey.'
        $script:PackageManager = 'winget'
    } else {
        Write-Info 'winget tidak ditemukan di mesin ini, langsung memakai Chocolatey sebagai package manager.'
        Switch-ToChocolatey
    }
}

# Install satu package. $WingetOverrideArgs diteruskan ke installer asli lewat
# `winget --override "..."`; $ChocoParams diteruskan lewat `choco --params "..."`
# (format tiap package parameter beda-beda, sesuaikan saat manggil fungsi ini).
function Install-Package([string]$WingetId, [string]$ChocoId, [string[]]$WingetOverrideArgs, [string[]]$ChocoParams) {
    if ($script:PackageManager -eq 'winget') {
        try {
            $wingetArgs = @('install', '--id', $WingetId, '--silent', '--accept-package-agreements', '--accept-source-agreements', '--source', 'winget')
            if ($WingetOverrideArgs) { $wingetArgs += @('--override', ($WingetOverrideArgs -join ' ')) }

            & winget @wingetArgs
            if ($LASTEXITCODE -ne 0) { throw "winget keluar dengan kode $LASTEXITCODE" }

            Update-SessionPath
            return
        } catch {
            Write-Info "winget gagal ($($_.Exception.Message)) - beralih ke Chocolatey untuk sisa proses setup..."
            Switch-ToChocolatey
        }
    }

    $chocoArgs = @('install', $ChocoId, '-y', '--no-progress')
    if ($ChocoParams) { $chocoArgs += @('--params', ($ChocoParams -join ' ')) }
    & choco @chocoArgs
    Update-SessionPath
}

function Update-Package([string]$WingetId, [string]$ChocoId) {
    if ($script:PackageManager -eq 'winget') {
        try {
            & winget upgrade --id $WingetId --silent --accept-package-agreements --accept-source-agreements --source winget
            if ($LASTEXITCODE -ne 0) { throw "winget keluar dengan kode $LASTEXITCODE" }

            Update-SessionPath
            return
        } catch {
            Write-Info "winget gagal ($($_.Exception.Message)) - beralih ke Chocolatey untuk sisa proses setup..."
            Switch-ToChocolatey
        }
    }

    & choco upgrade $ChocoId -y --no-progress
    Update-SessionPath
}

# ============================================================================
# Node.js
# ============================================================================

function Test-NodeSetup {
    Write-Section 'Node.js'

    $cmd = Get-Command node -ErrorAction SilentlyContinue
    if (-not $cmd) {
        Write-Info "Node.js belum terpasang, menginstal..."
        Install-Package -WingetId $Config.NodeWingetId -ChocoId $Config.NodeChocoId
        Update-SessionPath
        Write-Ok "Node.js terpasang: $(node -v)"
        return
    }

    $current = ConvertTo-VersionSafe -Output (node -v) -Pattern 'v?(\d+\.\d+\.\d+)'
    if (-not $current) {
        Write-Err 'Tidak bisa membaca versi Node.js, lewati pengecekan upgrade.'
        return
    }

    if ($current -lt $Config.NodeMinVersion) {
        Write-Info "Node.js v$current lebih lawas dari minimum v$($Config.NodeMinVersion), meng-upgrade..."
        Update-Package -WingetId $Config.NodeWingetId -ChocoId $Config.NodeChocoId
        Write-Ok "Node.js sekarang: $(node -v)"
    } else {
        Write-Skip "Node.js v$current sudah memenuhi minimum v$($Config.NodeMinVersion)"
    }
}

# ============================================================================
# PHP
# ============================================================================

function Test-PhpSetup {
    Write-Section 'PHP'

    $cmd = Get-Command php -ErrorAction SilentlyContinue
    if (-not $cmd) {
        Write-Info "PHP belum terpasang, menginstal..."
        Install-Package -WingetId $Config.PhpWingetId -ChocoId $Config.PhpChocoId
        Update-SessionPath
        Write-Ok "PHP terpasang: $(php -r 'echo PHP_VERSION;')"
        return
    }

    $current = ConvertTo-VersionSafe -Output (php -r 'echo PHP_VERSION;') -Pattern '(\d+\.\d+\.\d+)'
    if (-not $current) {
        Write-Err 'Tidak bisa membaca versi PHP, lewati pengecekan upgrade.'
        return
    }

    if ($current -lt $Config.PhpMinVersion) {
        Write-Info "PHP $current lebih lawas dari minimum $($Config.PhpMinVersion), meng-upgrade..."
        Update-Package -WingetId $Config.PhpWingetId -ChocoId $Config.PhpChocoId
        Write-Ok "PHP sekarang: $(php -r 'echo PHP_VERSION;')"
    } else {
        Write-Skip "PHP $current sudah memenuhi minimum $($Config.PhpMinVersion)"
    }
}

function Get-PhpIniPath {
    $iniInfo = php --ini 2>$null
    $line = $iniInfo | Where-Object { $_ -match 'Loaded Configuration File:\s*(.+)$' }
    if ($line -and $Matches[1].Trim() -ne '(none)') {
        return $Matches[1].Trim()
    }
    return $null
}

function Get-PhpExtensionDir([string]$IniPath) {
    $content = Get-Content -LiteralPath $IniPath
    $match = $content | Select-String -Pattern '^\s*extension_dir\s*=\s*"?([^"]+)"?\s*$' | Select-Object -Last 1
    if (-not $match) { return $null }

    $dir = $match.Matches[0].Groups[1].Value.Trim()
    if (-not [System.IO.Path]::IsPathRooted($dir)) {
        $dir = Join-Path (Split-Path $IniPath -Parent) $dir
    }
    return $dir
}

function Set-PhpExtension([string]$IniPath, [string]$Extension) {
    $content = Get-Content -LiteralPath $IniPath

    # Sudah aktif -> tidak perlu apa-apa
    if ($content -match "^\s*extension\s*=\s*(php_)?$Extension(\.dll)?\s*$") {
        return $false
    }

    # Ada tapi ter-comment -> aktifkan
    $pattern = "^\s*;\s*extension\s*=\s*(php_)?$Extension(\.dll)?\s*$"
    if ($content -match $pattern) {
        $content = $content -replace $pattern, "extension=$Extension"
        Set-Content -LiteralPath $IniPath -Value $content
        return $true
    }

    # Tidak ada sama sekali -> tambahkan baris baru
    Add-Content -LiteralPath $IniPath -Value "extension=$Extension"
    return $true
}

function Set-PhpIniValue([string]$IniPath, [string]$Key, [string]$Value) {
    $content = Get-Content -LiteralPath $IniPath
    $pattern = "^\s*;?\s*$Key\s*=.*$"

    if ($content -match $pattern) {
        $content = $content -replace $pattern, "$Key = $Value"
        Set-Content -LiteralPath $IniPath -Value $content
    } else {
        Add-Content -LiteralPath $IniPath -Value "$Key = $Value"
    }
}

function Set-PhpConfiguration {
    Write-Section 'Konfigurasi php.ini'

    $iniPath = Get-PhpIniPath
    if (-not $iniPath) {
        Write-Err 'Tidak menemukan php.ini aktif (php --ini menunjukkan "(none)"). Lewati konfigurasi ekstensi.'
        return
    }
    Write-Info "php.ini: $iniPath"

    $extDir = Get-PhpExtensionDir -IniPath $iniPath

    $changed = $false
    foreach ($ext in $Config.PhpExtensions) {
        # Kalau file DLL ekstensinya tidak ada di instalasi PHP ini, jangan
        # dipaksa aktifkan - kalau tetap diaktifkan, SETIAP pemanggilan `php`
        # berikutnya (termasuk lewat Composer, yang juga skrip PHP) akan
        # mencetak "PHP Startup: Unable to load dynamic library ..." ke
        # output, yang berpotensi merusak parsing versi tool lain.
        if ($extDir -and -not (Test-Path (Join-Path $extDir "php_$ext.dll"))) {
            Write-Err "Ekstensi '$ext' dilewati - file php_$ext.dll tidak ditemukan di $extDir (build PHP ini mungkin tidak menyertakannya)."
            continue
        }

        if (Set-PhpExtension -IniPath $iniPath -Extension $ext) {
            Write-Ok "Ekstensi diaktifkan: $ext"
            $changed = $true
        } else {
            Write-Skip "Ekstensi sudah aktif: $ext"
        }
    }

    # Batas upload dinaikkan supaya fitur upload foto/dokumen di aplikasi tidak
    # mentok default PHP (upload_max_filesize 2M / post_max_size 8M terlalu kecil).
    Set-PhpIniValue -IniPath $iniPath -Key 'upload_max_filesize' -Value '20M'
    Set-PhpIniValue -IniPath $iniPath -Key 'post_max_size' -Value '25M'
    Set-PhpIniValue -IniPath $iniPath -Key 'memory_limit' -Value '256M'
    Set-PhpIniValue -IniPath $iniPath -Key 'max_execution_time' -Value '120'
    Set-PhpIniValue -IniPath $iniPath -Key 'date.timezone' -Value 'Asia/Jakarta'
    Write-Ok 'upload_max_filesize, post_max_size, memory_limit, max_execution_time, date.timezone disetel.'

    if ($changed) {
        Write-Info 'Ada ekstensi baru diaktifkan - pastikan tidak ada proses "php artisan serve" lama yang masih jalan (perlu direstart untuk baca ulang php.ini).'
    }
}

# ============================================================================
# Composer
# ============================================================================

function Test-ComposerSetup {
    Write-Section 'Composer'

    $cmd = Get-Command composer -ErrorAction SilentlyContinue
    if (-not $cmd) {
        Write-Info 'Composer belum terpasang, mengunduh & menjalankan Composer-Setup.exe...'
        $installerPath = Join-Path $env:TEMP 'Composer-Setup.exe'
        Invoke-WebRequest -Uri 'https://getcomposer.org/Composer-Setup.exe' -OutFile $installerPath -UseBasicParsing
        Start-Process -FilePath $installerPath -ArgumentList '/VERYSILENT', '/SUPPRESSMSGBOXES', '/NORESTART' -Wait
        Remove-Item $installerPath -ErrorAction SilentlyContinue
        Update-SessionPath
        Write-Ok "Composer terpasang: $(composer --version)"
        return
    }

    $current = ConvertTo-VersionSafe -Output (composer --version) -Pattern 'Composer version (\d+\.\d+\.\d+)'
    if (-not $current) {
        Write-Err 'Tidak bisa membaca versi Composer, lewati pengecekan upgrade.'
        return
    }

    if ($current -lt $Config.ComposerMinVersion) {
        Write-Info "Composer $current lebih lawas dari minimum $($Config.ComposerMinVersion), menjalankan self-update..."
        & composer self-update
        Write-Ok "Composer sekarang: $(composer --version)"
    } else {
        Write-Skip "Composer $current sudah memenuhi minimum $($Config.ComposerMinVersion)"
    }
}

# ============================================================================
# PostgreSQL
# ============================================================================

function Get-EnvValue([string]$Key, [string]$Default) {
    $envFile = Join-Path $ProjectRoot '.env'
    if (-not (Test-Path $envFile)) { $envFile = Join-Path $ProjectRoot '.env.example' }
    if (-not (Test-Path $envFile)) { return $Default }

    $line = Get-Content $envFile | Where-Object { $_ -match "^\s*$Key\s*=" } | Select-Object -First 1
    if (-not $line) { return $Default }

    $value = ($line -split '=', 2)[1].Trim().Trim('"')
    if ([string]::IsNullOrWhiteSpace($value)) { return $Default }
    return $value
}

function Test-PostgresSetup {
    Write-Section 'PostgreSQL'

    $dbPort = Get-EnvValue -Key 'DB_PORT' -Default '5432'
    $dbName = Get-EnvValue -Key 'DB_DATABASE' -Default 'maarentcar'
    $dbUser = Get-EnvValue -Key 'DB_USERNAME' -Default 'postgres'
    $dbPass = Get-EnvValue -Key 'DB_PASSWORD' -Default 'postgres'

    $cmd = Get-Command psql -ErrorAction SilentlyContinue
    if (-not $cmd) {
        Write-Info "PostgreSQL belum terpasang, menginstal (port $dbPort, superuser password '$dbPass')..."
        $wingetOverrides = @(
            '--mode unattended',
            '--unattendedmodeui minimal',
            "--superpassword $dbPass",
            "--serverport $dbPort"
        )
        # Format parameter package choco postgresql16 (community): /Password dan /Port.
        $chocoParams = @("/Password:$dbPass", "/Port:$dbPort")

        Install-Package -WingetId $Config.PgWingetId -ChocoId $Config.PgChocoId -WingetOverrideArgs $wingetOverrides -ChocoParams $chocoParams

        # Tambahkan bin dir Postgres secara eksplisit untuk sesi ini kalau belum ke-refresh dari registry
        $pgBin = Get-ChildItem 'C:\Program Files\PostgreSQL' -Directory -ErrorAction SilentlyContinue |
            Sort-Object Name -Descending | Select-Object -First 1 |
            ForEach-Object { Join-Path $_.FullName 'bin' }
        if ($pgBin -and (Test-Path $pgBin) -and ($env:Path -notlike "*$pgBin*")) {
            $env:Path += ";$pgBin"
        }
        Write-Ok "PostgreSQL terpasang: $(psql --version)"
    } else {
        $current = ConvertTo-VersionSafe -Output (psql --version) -Pattern '\(PostgreSQL\)\s+(\d+\.\d+)'
        if (-not $current) {
            Write-Err 'Tidak bisa membaca versi PostgreSQL, lewati pengecekan versi mayor.'
        } elseif ($current.Major -lt $Config.PgMinMajor) {
            Write-Err "PostgreSQL versi $current lebih lawas dari minimum mayor $($Config.PgMinMajor)."
            Write-Info 'Upgrade versi MAYOR PostgreSQL tidak dilakukan otomatis oleh script ini karena berisiko terhadap data yang sudah ada.'
            Write-Info "Silakan upgrade manual (pg_upgrade / dump-restore), atau lanjutkan pakai versi $current kalau cukup untuk kebutuhan project."
        } else {
            Write-Skip "PostgreSQL $current sudah memenuhi minimum mayor $($Config.PgMinMajor)"
        }
    }

    Set-PostgresDatabase -Port $dbPort -DbName $dbName -DbUser $dbUser -DbPassword $dbPass
    Set-PostgresDatabase -Port $dbPort -DbName "$($dbName)_test" -DbUser $dbUser -DbPassword $dbPass
}

function Set-PostgresDatabase([string]$Port, [string]$DbName, [string]$DbUser, [string]$DbPassword) {
    if (-not (Get-Command psql -ErrorAction SilentlyContinue)) {
        Write-Err "psql tidak ditemukan di PATH, lewati pembuatan database '$DbName'. Buka terminal baru lalu jalankan ulang script ini."
        return
    }

    $env:PGPASSWORD = $DbPassword
    try {
        $exists = & psql -h 127.0.0.1 -p $Port -U $DbUser -tAc "SELECT 1 FROM pg_database WHERE datname = '$DbName'" 2>$null

        if ($exists.Trim() -eq '1') {
            Write-Skip "Database '$DbName' sudah ada (port $Port)"
        } else {
            & psql -h 127.0.0.1 -p $Port -U $DbUser -c "CREATE DATABASE `"$DbName`"" 2>$null | Out-Null
            Write-Ok "Database '$DbName' dibuat (port $Port)"
        }
    } catch {
        Write-Err "Gagal menghubungi PostgreSQL di port $Port sebagai user '$DbUser': $($_.Exception.Message)"
        Write-Info 'Cek kembali service PostgreSQL sudah jalan dan port/kredensial di .env sudah benar.'
    } finally {
        Remove-Item Env:\PGPASSWORD -ErrorAction SilentlyContinue
    }
}

# ============================================================================
# Setup project (composer install, npm install, .env, migrate)
# ============================================================================

function Initialize-Project {
    Write-Section 'Setup Project'
    Push-Location $ProjectRoot
    try {
        $envPath = Join-Path $ProjectRoot '.env'
        if (-not (Test-Path $envPath)) {
            Copy-Item (Join-Path $ProjectRoot '.env.example') $envPath
            Write-Ok '.env dibuat dari .env.example'
        } else {
            Write-Skip '.env sudah ada'
        }

        Write-Info 'composer install...'
        & composer install --no-interaction

        $appKeySet = (Get-Content $envPath | Where-Object { $_ -match '^APP_KEY=base64:' })
        if (-not $appKeySet) {
            & php artisan key:generate --ansi
            Write-Ok 'APP_KEY digenerate'
        } else {
            Write-Skip 'APP_KEY sudah ada'
        }

        Write-Info 'npm install...'
        & npm install

        Write-Info 'php artisan migrate --seed...'
        & php artisan migrate --seed --force

        $storageLink = Join-Path $ProjectRoot 'public\storage'
        if (-not (Test-Path $storageLink)) {
            & php artisan storage:link
            Write-Ok 'storage:link dibuat'
        } else {
            Write-Skip 'storage:link sudah ada'
        }

        Write-Ok 'Setup project selesai.'
    } finally {
        Pop-Location
    }
}

# ============================================================================
# Main
# ============================================================================

Assert-Admin
Update-SessionPath
Initialize-PackageManager

Write-Host ''
Write-Host 'Setup We Rent Car - Windows 10' -ForegroundColor Magenta
Write-Host '================================' -ForegroundColor Magenta

Test-NodeSetup
Test-PhpSetup
Set-PhpConfiguration
Test-ComposerSetup
Test-PostgresSetup

if (-not $SkipProjectSetup) {
    Initialize-Project
} else {
    Write-Section 'Setup Project'
    Write-Skip 'Dilewati (-SkipProjectSetup)'
}

Write-Host ''
Write-Host '=== Selesai ===' -ForegroundColor Cyan
Write-Host '  Jalankan "composer run dev" untuk menyalakan server + queue + Vite sekaligus.' -ForegroundColor Green
Write-Host ''
