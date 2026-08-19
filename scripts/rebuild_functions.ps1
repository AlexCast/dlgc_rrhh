#Requires -Version 5.1
<#
.SYNOPSIS
    Reconstruye todas las funciones almacenadas del proyecto en PostgreSQL.

.DESCRIPTION
    1. Genera un archivo SQL temporal con DROP FUNCTION IF EXISTS para todas
       las funciones detectadas en scripts/functions/**/fun_*.sql.
    2. Ejecuta los drops en la base de datos.
    3. Ejecuta todos los archivos .sql de funciones en orden alfabético.

.PARAMETER PgHost
    Host del servidor PostgreSQL (por defecto: localhost).

.PARAMETER PgPort
    Puerto del servidor PostgreSQL (por defecto: 5432).

.PARAMETER PgDatabase
    Nombre de la base de datos (por defecto: db_dlgc_rrhh).

.PARAMETER PgUser
    Usuario de PostgreSQL (por defecto: postgres).

.PARAMETER PgPassword
    Contraseña del usuario. Si no se especifica, psql pedirá la contraseña.

.EXAMPLE
    .\rebuild_functions.ps1
    .\rebuild_functions.ps1 -PgPassword "mi_clave"
#>
[CmdletBinding(SupportsShouldProcess = $true)]
param(
    [string]$PgHost = 'localhost',
    [int]$PgPort = 5432,
    [string]$PgDatabase = 'db_dlgc_rrhh',
    [string]$PgUser = 'postgres',
    [string]$PgPassword = ''
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$ProjectRoot = Split-Path -Parent $PSScriptRoot
$FunctionsDir = Join-Path -Path $ProjectRoot -ChildPath 'scripts' | Join-Path -ChildPath 'functions'
$TempSqlFile = [System.IO.Path]::GetTempFileName() + '.sql'

function Test-PsqlAvailable {
    $psql = Get-Command 'psql' -ErrorAction SilentlyContinue
    if (-not $psql) {
        throw "No se encontró 'psql' en el PATH. Instala PostgreSQL client tools o agrega psql al PATH."
    }
    return $psql.Source
}

function Get-FunctionSignatures {
    param([string]$FilePath)

    $content = Get-Content -Raw -Encoding UTF8 -Path $FilePath
    $signatures = @()

    # Captura funciones con RETURNS TABLE (...)
    $tableMatches = [regex]::Matches(
        $content,
        'CREATE\s+OR\s+REPLACE\s+FUNCTION\s+(?<name>\w+)\s*\((?<args>[^)]*)\)\s+RETURNS\s+TABLE\s*\((?<cols>[^)]+)\)',
        [System.Text.RegularExpressions.RegexOptions]::IgnoreCase -bor [System.Text.RegularExpressions.RegexOptions]::Singleline
    )

    foreach ($match in $tableMatches) {
        $funcName = $match.Groups['name'].Value
        $args = $match.Groups['args'].Value.Trim()
        $signatures += @{ Name = $funcName; Args = $args }
    }

    # Captura funciones normales con RETURNS tipo
    $normalMatches = [regex]::Matches(
        $content,
        'CREATE\s+OR\s+REPLACE\s+FUNCTION\s+(?<name>\w+)\s*\((?<args>[^)]*)\)\s+RETURNS\s+(?!TABLE)(?<ret>[\w\s%]+)',
        [System.Text.RegularExpressions.RegexOptions]::IgnoreCase -bor [System.Text.RegularExpressions.RegexOptions]::Singleline
    )

    foreach ($match in $normalMatches) {
        $funcName = $match.Groups['name'].Value
        $args = $match.Groups['args'].Value.Trim()
        $signatures += @{ Name = $funcName; Args = $args }
    }

    return $signatures
}

function Build-DropStatement {
    param([hashtable]$Signature)

    $name = $Signature.Name
    $args = $Signature.Args

    if ([string]::IsNullOrWhiteSpace($args)) {
        return "DROP FUNCTION IF EXISTS $name();"
    }

    # PostgreSQL requiere los tipos de argumento para DROP FUNCTION.
    # Eliminamos nombres de parámetros y DEFAULTs, dejando solo los tipos.
    $argTypes = @()
    $rawArgs = $args -split ','
    foreach ($rawArg in $rawArgs) {
        $trimmed = $rawArg.Trim()
        if ([string]::IsNullOrWhiteSpace($trimmed)) { continue }

        # Quitar nombre del parámetro al inicio (palabra seguida de tipo %TYPE o tipo directo)
        # Soporta: wid_usuario t_usuarios.id_usuario%TYPE
        #          wnombre_pension t_pension.nombre_pension%TYPE DEFAULT NULL
        #          p_id INT
        if ($trimmed -match '^\w+\s+(.+)$') {
            $typePart = $Matches[1].Trim()
        } else {
            $typePart = $trimmed
        }

        # Quitar DEFAULT
        if ($typePart -match '^(.*?)\s+DEFAULT\s+') {
            $typePart = $Matches[1].Trim()
        }

        $argTypes += $typePart
    }

    $argList = $argTypes -join ', '
    return "DROP FUNCTION IF EXISTS $name($argList);"
}

try {
    $psqlPath = Test-PsqlAvailable
    Write-Host "psql encontrado en: $psqlPath" -ForegroundColor Cyan

    # Si no se pasó -PgPassword, pedirla UNA sola vez (oculta) y reutilizarla en todos los
    # archivos; si queda vacía, psql la pediría en cada uno de los ~26+ archivos por separado.
    if ([string]::IsNullOrEmpty($PgPassword)) {
        $securePassword = Read-Host -Prompt "Password para el usuario $PgUser" -AsSecureString
        $bstr = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePassword)
        try {
            $PgPassword = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($bstr)
        } finally {
            [System.Runtime.InteropServices.Marshal]::ZeroFreeBSTR($bstr)
        }
    }

    if (-not (Test-Path -Path $FunctionsDir)) {
        throw "No se encontró el directorio de funciones: $FunctionsDir"
    }

    $sqlFiles = Get-ChildItem -Path $FunctionsDir -Filter 'fun_*.sql' -Recurse | Sort-Object FullName
    if ($sqlFiles.Count -eq 0) {
        throw "No se encontraron archivos fun_*.sql en $FunctionsDir"
    }

    Write-Host "Se encontraron $($sqlFiles.Count) archivos de funciones." -ForegroundColor Cyan

    # Generar archivo SQL de drops
    $dropLines = @()
    $dropLines += '-- Auto-generado por rebuild_functions.ps1'
    $dropLines += ('-- Fecha: ' + (Get-Date -Format 'yyyy-MM-dd HH:mm:ss'))
    $dropLines += ('-- Base de datos: ' + $PgDatabase)
    $dropLines += ''
    $dropLines += 'SET client_min_messages TO WARNING;'
    $dropLines += ''

    $uniqueSignatures = [ordered]@{}
    foreach ($file in $sqlFiles) {
        $sigs = Get-FunctionSignatures -FilePath $file.FullName
        foreach ($sig in $sigs) {
            $key = $sig.Name + '|' + $sig.Args
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

    # Construir credenciales para psql
    $env:PGPASSWORD = $PgPassword
    $psqlBaseArgs = @(
        '-h', $PgHost,
        '-p', $PgPort,
        '-U', $PgUser,
        '-d', $PgDatabase,
        '-v', 'ON_ERROR_STOP=1',
        '-f'
    )

    # Paso 1: ejecutar drops
    if ($PSCmdlet.ShouldProcess($TempSqlFile, 'Ejecutar DROP FUNCTION')) {
        Write-Host "`nPaso 1/2: Eliminando funciones existentes..." -ForegroundColor Yellow
        & $psqlPath @psqlBaseArgs $TempSqlFile
        if ($LASTEXITCODE -ne 0) { throw "Error al ejecutar drops. Revisa el archivo: $TempSqlFile" }
        Write-Host "Funciones eliminadas correctamente." -ForegroundColor Green
    }

    # Paso 2: recrear funciones
    if ($PSCmdlet.ShouldProcess($FunctionsDir, 'Recrear funciones')) {
        Write-Host "`nPaso 2/2: Recreando funciones..." -ForegroundColor Yellow
        foreach ($file in $sqlFiles) {
            Write-Host "  - $($file.FullName.Substring($ProjectRoot.Length + 1))" -ForegroundColor DarkGray
            & $psqlPath @psqlBaseArgs $file.FullName
            if ($LASTEXITCODE -ne 0) { throw "Error al ejecutar: $($file.FullName)" }
        }
        Write-Host "`nTodas las funciones se recrearon correctamente." -ForegroundColor Green
    }

} catch {
    Write-Host "`nERROR: $_" -ForegroundColor Red
    exit 1
} finally {
    $env:PGPASSWORD = ''
    if (Test-Path -Path $TempSqlFile) {
        # Descomenta la siguiente línea si quieres conservar el SQL de drops para revisión manual.
        # Remove-Item -Path $TempSqlFile -Force -ErrorAction SilentlyContinue
    }
}
