#requires -Version 5.1
<#
.SYNOPSIS
    Prueba automatizada de protección CSRF en DLGC_RRHH.

.DESCRIPTION
    1. Obtiene un token CSRF del formulario de login.
    2. Prueba que login.php rechace POST sin token (403).
    3. Prueba que login.php rechace POST con token inválido (403).
    4. Inicia sesión con credenciales válidas + token.
    5. Obtiene un token CSRF de un formulario protegido (Áreas).
    6. Prueba que insertar_area.php rechace POST sin token (403).
    7. Prueba que insertar_area.php rechace POST con token inválido (403).
    8. Crea y elimina un área de prueba usando token válido.

.PARAMETER BaseUrl
    URL base del proyecto, e.g. http://localhost/dlgc_rrhh

.PARAMETER User
    Nombre de usuario o correo para login.

.PARAMETER Password
    Contraseña del usuario.

.EXAMPLE
    .\scripts\test_csrf.ps1 -BaseUrl "http://localhost/dlgc_rrhh" -User "admin" -Password "secret"
#>
[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$BaseUrl,

    [Parameter(Mandatory = $true)]
    [string]$User,

    [Parameter(Mandatory = $true)]
    [string]$Password
)

$ErrorActionPreference = 'Stop'

# Normalizar URL base (quitar slash final)
$BaseUrl = $BaseUrl.TrimEnd('/')

function Get-CsrfToken {
    param(
        [Parameter(Mandatory = $true)]
        [string]$Html
    )
    $match = [regex]::Match($Html, 'name="csrf_token" value="([^"]+)"')
    if (-not $match.Success) {
        throw "No se encontró el campo csrf_token en el HTML."
    }
    return $match.Groups[1].Value
}

function Invoke-HttpRequest {
    param(
        [string]$Uri,
        [string]$Method = 'GET',
        [hashtable]$Body = @{},
        [System.Net.CookieContainer]$Cookies
    )
    $request = [System.Net.HttpWebRequest]::Create($Uri)
    $request.Method = $Method
    $request.AllowAutoRedirect = $false
    $request.CookieContainer = $Cookies
    $request.UserAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)'

    if ($Body.Count -gt 0) {
        $request.ContentType = 'application/x-www-form-urlencoded'
        $parts = @()
        foreach ($k in $Body.Keys) { $parts += "$k=$([System.Uri]::EscapeDataString($Body[$k]))" }
        $payload = [System.Text.Encoding]::UTF8.GetBytes(($parts -join '&'))
        $request.ContentLength = $payload.Length
        $stream = $request.GetRequestStream()
        $stream.Write($payload, 0, $payload.Length)
        $stream.Close()
    }

    $response = $null
    try {
        $response = $request.GetResponse()
    } catch [System.Net.WebException] {
        $response = $_.Exception.Response
        if (-not $response) { throw }
    }

    $result = @{ StatusCode = [int]$response.StatusCode; Location = $null; Content = $null }
    if ($response.Headers['Location']) { $result.Location = $response.Headers['Location'] }

    $stream = $response.GetResponseStream()
    if ($stream) {
        $reader = New-Object System.IO.StreamReader($stream)
        $result.Content = $reader.ReadToEnd()
        $reader.Close()
    }
    $response.Close()
    return $result
}

function Write-Result {
    param(
        [string]$Test,
        [bool]$Passed,
        [string]$Detail = ''
    )
    $status = if ($Passed) { '✅ PASS' } else { '❌ FAIL' }
    Write-Host "$status $Test" -ForegroundColor $(if ($Passed) { 'Green' } else { 'Red' })
    if ($Detail) {
        Write-Host "   $Detail" -ForegroundColor Gray
    }
}

$cookies = New-Object System.Net.CookieContainer

# ---------------------------------------------------------------------------
# 1. Obtener token del login
# ---------------------------------------------------------------------------
Write-Host "`n[1/8] Obteniendo formulario de login..." -ForegroundColor Cyan
$loginPage = Invoke-HttpRequest -Uri "$BaseUrl/templates/login.php" -Method GET -Cookies $cookies
$loginToken = Get-CsrfToken -Html $loginPage.Content
Write-Host "   Token login: $loginToken" -ForegroundColor Gray

# ---------------------------------------------------------------------------
# 2. Login sin token -> debe fallar
# ---------------------------------------------------------------------------
Write-Host "`n[2/8] Login SIN token CSRF..." -ForegroundColor Cyan
$response = Invoke-HttpRequest -Uri "$BaseUrl/app/login.php" -Method POST `
    -Body @{user = $User; contrasena = $Password} -Cookies $cookies
Write-Result -Test 'Login sin token debe ser rechazado' -Passed ($response.StatusCode -eq 403) `
    -Detail "HTTP $($response.StatusCode)"

# ---------------------------------------------------------------------------
# 3. Login con token inválido -> debe fallar
# ---------------------------------------------------------------------------
Write-Host "`n[3/8] Login CON token inválido..." -ForegroundColor Cyan
$response = Invoke-HttpRequest -Uri "$BaseUrl/app/login.php" -Method POST `
    -Body @{user = $User; contrasena = $Password; csrf_token = 'token_falso_12345'} -Cookies $cookies
Write-Result -Test 'Login con token inválido debe ser rechazado' -Passed ($response.StatusCode -eq 403) `
    -Detail "HTTP $($response.StatusCode)"

# ---------------------------------------------------------------------------
# 4. Login correcto con token válido
# ---------------------------------------------------------------------------
Write-Host "`n[4/8] Login CON token válido..." -ForegroundColor Cyan
$response = Invoke-HttpRequest -Uri "$BaseUrl/app/login.php" -Method POST `
    -Body @{user = $User; contrasena = $Password; csrf_token = $loginToken} -Cookies $cookies
$passed = ($response.StatusCode -eq 302 -and $response.Location -notlike '*login.php*')
Write-Result -Test 'Login con token válido debe aceptarse' -Passed $passed `
    -Detail "HTTP $($response.StatusCode) -> $($response.Location)"
if (-not $passed) { return }

# ---------------------------------------------------------------------------
# 5. Obtener token de un formulario protegido
# ---------------------------------------------------------------------------
Write-Host "`n[5/8] Accediendo a formulario de Áreas..." -ForegroundColor Cyan
$areaForm = Invoke-HttpRequest -Uri "$BaseUrl/src/area/forma_area.php" -Method GET -Cookies $cookies
$areaToken = Get-CsrfToken -Html $areaForm.Content
Write-Result -Test 'Formulario de áreas accesible' -Passed $true `
    -Detail "Token áreas: $areaToken"

# ---------------------------------------------------------------------------
# 6. CRUD sin token -> debe fallar
# ---------------------------------------------------------------------------
Write-Host "`n[6/8] Crear área SIN token CSRF..." -ForegroundColor Cyan
$response = Invoke-HttpRequest -Uri "$BaseUrl/src/area/insertar_area.php" -Method POST `
    -Body @{nombre_area = 'Area_CSRF_Test'} -Cookies $cookies
Write-Result -Test 'CRUD sin token debe ser rechazado' -Passed ($response.StatusCode -eq 403) `
    -Detail "HTTP $($response.StatusCode)"

# ---------------------------------------------------------------------------
# 7. CRUD con token inválido -> debe fallar
# ---------------------------------------------------------------------------
Write-Host "`n[7/8] Crear área CON token inválido..." -ForegroundColor Cyan
$response = Invoke-HttpRequest -Uri "$BaseUrl/src/area/insertar_area.php" -Method POST `
    -Body @{nombre_area = 'Area_CSRF_Test'; csrf_token = 'token_falso_12345'} -Cookies $cookies
Write-Result -Test 'CRUD con token inválido debe ser rechazado' -Passed ($response.StatusCode -eq 403) `
    -Detail "HTTP $($response.StatusCode)"

# ---------------------------------------------------------------------------
# 8. CRUD con token válido -> debe funcionar
# ---------------------------------------------------------------------------
$testAreaName = "Area_CSRF_Test_$(Get-Date -Format 'yyyyMMddHHmmss')"
Write-Host "`n[8/8] Crear área CON token válido ($testAreaName)..." -ForegroundColor Cyan
$response = Invoke-HttpRequest -Uri "$BaseUrl/src/area/insertar_area.php" -Method POST `
    -Body @{nombre_area = $testAreaName; csrf_token = $areaToken} -Cookies $cookies
$created = ($response.StatusCode -eq 302 -and $response.Location -like '*listar_area.php*')
Write-Result -Test 'CRUD con token válido debe funcionar' -Passed $created `
    -Detail "HTTP $($response.StatusCode) -> $($response.Location)"

if ($created) {
    # Limpieza: obtener id del área creada y eliminarla
    $listPage = Invoke-HttpRequest -Uri "$BaseUrl/src/area/listar_area.php" -Method GET -Cookies $cookies
    $deleteToken = Get-CsrfToken -Html $listPage.Content

    $pattern = [regex]::Escape($testAreaName) + '.*?<form method="POST" action="eliminar_area\.php"[^>]*>.*?<input type="hidden" name="id_area" value="(\d+)">'
    $idMatch = [regex]::Match($listPage.Content, $pattern, [System.Text.RegularExpressions.RegexOptions]::Singleline)
    if ($idMatch.Success) {
        $idArea = $idMatch.Groups[1].Value
        $null = Invoke-HttpRequest -Uri "$BaseUrl/src/area/eliminar_area.php" -Method POST `
            -Body @{id_area = $idArea; csrf_token = $deleteToken} -Cookies $cookies
        Write-Host "   Área de prueba eliminada." -ForegroundColor Gray
    }
}

Write-Host "`nPruebas finalizadas." -ForegroundColor Cyan
