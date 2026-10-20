#Requires -Version 5.1
<#
.SYNOPSIS
    Reconstruye todas las funciones y procedimientos almacenados del proyecto en MariaDB.

.DESCRIPTION
    1. Genera un archivo SQL temporal con DROP FUNCTION / DROP PROCEDURE IF EXISTS
       para todos los objetos detectados en scripts/functions/**/fun_*.sql y proc_*.sql.
    2. Ejecuta los drops en la base de datos.
    3. Ejecuta todos los archivos .sql (funciones y procedimientos) en orden alfabético.

.PARAMETER MyHost
    Host del servidor MariaDB (por defecto: localhost).

.PARAMETER MyPort
    Puerto del servidor MariaDB (por defecto: 3306).

.PARAMETER MyDatabase
    Nombre de la base de datos (por defecto: db_dlgc_rrhh).

.PARAMETER MyUser
    Usuario de MariaDB (por defecto: root).

.PARAMETER MyPassword
    Contraseña del usuario. Si no se especifica, se solicita una sola vez de forma oculta.

.EXAMPLE
    .\rebuild_functions.ps1
    .\rebuild_functions.ps1 -MyPassword "mi_clave"
#>
[CmdletBinding(SupportsShouldProcess = $true)]
param(
    [string]$MyHost = 'localhost',
    [int]$MyPort = 3306,
    [string]$MyDatabase = 'db_dlgc_rrhh',
    [string]$MyUser = 'root',
    [string]$MyPassword = ''
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$ProjectRoot = Split-Path -Parent $PSScriptRoot
$FunctionsDir = Join-Path -Path $ProjectRoot -ChildPath 'scripts' | Join-Path -ChildPath 'functions'
$TempSqlFile = [System.IO.Path]::GetTempFileName() + '.sql'

function Test-MySqlAvailable {
    $mysql = Get-Command 'mysql' -ErrorAction SilentlyContinue
    if (-not $mysql) {
        $mysql = Get-Command 'mariadb' -ErrorAction SilentlyContinue
    }
    if (-not $mysql) {
        throw "No se encontró 'mysql' ni 'mariadb' en el PATH. Instala el cliente de MariaDB o agrégalo al PATH."
    }
    return $mysql.Source
}

function Get-ObjectSignatures {
    param([string]$FilePath)

    $content = Get-Content -Raw -Encoding UTF8 -Path $FilePath
    $signatures = @()

    $objectMatches = [regex]::Matches(
        $content,
        'CREATE\s+OR\s+REPLACE\s+(FUNCTION|PROCEDURE)\s+(?<name>\w+)',
        [System.Text.RegularExpressions.RegexOptions]::IgnoreCase
    )

    foreach ($match in $objectMatches) {
        $signatures += @{ Type = $match.Groups[1].Value.ToUpper(); Name = $match.Groups['name'].Value }
    }

    return $signatures
}

function Build-DropStatement {
    param([hashtable]$Signature)

    return "DROP $($Signature.Type) IF EXISTS $($Signature.Name);"
}

function Invoke-MySqlFile {
    param(
        [string]$MysqlPath,
        [string]$FilePath
    )

    $psi = New-Object System.Diagnostics.ProcessStartInfo
    $psi.FileName = $MysqlPath
    $psi.Arguments = "--host=$MyHost --port=$MyPort --user=$MyUser --database=$MyDatabase --default-character-set=utf8mb4"
    $psi.UseShellExecute = $false
    $psi.RedirectStandardInput = $true
    $psi.RedirectStandardOutput = $true
    $psi.RedirectStandardError = $true

    $process = New-Object System.Diagnostics.Process
    $process.StartInfo = $psi

    if (-not $process.Start()) {
        throw "No se pudo iniciar el cliente mysql: $MysqlPath"
    }

    $bytes = [System.IO.File]::ReadAllBytes($FilePath)
    $process.StandardInput.BaseStream.Write($bytes, 0, $bytes.Length)
    $process.StandardInput.BaseStream.Flush()
    $process.StandardInput.Close()

    $stdout = $process.StandardOutput.ReadToEnd()
    $stderr = $process.StandardError.ReadToEnd()
    $process.WaitForExit()

    if ($stdout) { Write-Host $stdout }
    if ($stderr) { Write-Host $stderr -ForegroundColor Yellow }

    if ($process.ExitCode -ne 0) {
        throw "Error al ejecutar '$FilePath' (código de salida $($process.ExitCode))."
    }
}

try {
    $MysqlPath = Test-MySqlAvailable
    Write-Host "mysql encontrado en: $MysqlPath" -ForegroundColor Cyan

    # Si no se pasó -MyPassword, pedirla UNA sola vez (oculta) y reutilizarla en todos los
    # archivos; si queda vacía, el cliente mysql la pediría en cada archivo por separado.
    if ([string]::IsNullOrEmpty($MyPassword)) {
        $securePassword = Read-Host -Prompt "Password para el usuario $MyUser" -AsSecureString
        $bstr = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePassword)
        try {
            $MyPassword = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($bstr)
        } finally {
            [System.Runtime.InteropServices.Marshal]::ZeroFreeBSTR($bstr)
        }
    }

    if (-not (Test-Path -Path $FunctionsDir)) {
        throw "No se encontró el directorio de funciones: $FunctionsDir"
    }

    $sqlFiles = Get-ChildItem -Path $FunctionsDir -File -Recurse |
        Where-Object { $_.Name -match '^(fun|proc)_.*\.sql$' } |
        Sort-Object FullName

    if ($sqlFiles.Count -eq 0) {
        throw "No se encontraron archivos fun_*.sql ni proc_*.sql en $FunctionsDir"
    }

    Write-Host "Se encontraron $($sqlFiles.Count) archivos de funciones/procedimientos." -ForegroundColor Cyan

    # Generar archivo SQL de drops
    $dropLines = @()
    $dropLines += '-- Auto-generado por rebuild_functions.ps1'
    $dropLines += ('-- Fecha: ' + (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'))
    $dropLines += ('-- Base de datos: ' + $MyDatabase)
    $dropLines += ''

    $uniqueSignatures = [ordered]@{}
    foreach ($file in $sqlFiles) {
        $sigs = Get-ObjectSignatures -FilePath $file.FullName
        foreach ($sig in $sigs) {
            $key = $sig.Type + '|' + $sig.Name
            if (-not $uniqueSignatures.Contains($key)) {
                $uniqueSignatures[$key] = $sig
            }
        }
    }

    foreach ($sig in $uniqueSignatures.Values) {
        $dropLines += Build-DropStatement -Signature $sig
    }

    $dropLines += ''
    $dropText = $dropLines -join "`n"
    [System.IO.File]::WriteAllText($TempSqlFile, $dropText, [System.Text.UTF8Encoding]::new($false))

    Write-Host "Archivo temporal de drops generado: $TempSqlFile" -ForegroundColor DarkGray

    # La contraseña se pasa por variable de entorno (el cliente mysql la hereda).
    $env:MYSQL_PWD = $MyPassword

    # Paso 1: ejecutar drops
    if ($PSCmdlet.ShouldProcess($TempSqlFile, 'Ejecutar DROP de funciones/procedimientos')) {
        Write-Host "`nPaso 1/2: Eliminando funciones y procedimientos existentes..." -ForegroundColor Yellow
        Invoke-MySqlFile -MysqlPath $MysqlPath -FilePath $TempSqlFile
        Write-Host "Funciones y procedimientos eliminados correctamente." -ForegroundColor Green
    }

    # Paso 2: recrear funciones y procedimientos
    if ($PSCmdlet.ShouldProcess($FunctionsDir, 'Recrear funciones y procedimientos')) {
        Write-Host "`nPaso 2/2: Recreando funciones y procedimientos..." -ForegroundColor Yellow
        foreach ($file in $sqlFiles) {
            Write-Host "  - $($file.FullName.Substring($ProjectRoot.Length + 1))" -ForegroundColor DarkGray
            Invoke-MySqlFile -MysqlPath $MysqlPath -FilePath $file.FullName
        }
        Write-Host "`nTodas las funciones y procedimientos se recrearon correctamente." -ForegroundColor Green
    }

} catch {
    Write-Host "`nERROR: $_" -ForegroundColor Red
    exit 1
} finally {
    $env:MYSQL_PWD = ''
    if (Test-Path -Path $TempSqlFile) {
        # Descomenta la siguiente línea si quieres conservar el SQL de drops para revisión manual.
        # Remove-Item -Path $TempSqlFile -Force -ErrorAction SilentlyContinue
    }
}