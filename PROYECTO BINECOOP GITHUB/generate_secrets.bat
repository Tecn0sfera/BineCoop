@echo off
setlocal enabledelayedexpansion

REM Script para generar JWT_SECRET y ADMIN_API_KEY seguros
REM Ejecutar desde el directorio raíz del proyecto

echo ================================================
echo GENERADOR DE SECRETOS SEGUROS
echo ================================================
echo.
echo Este script generara:
echo   - JWT_SECRET (para htdocspanel)
echo   - ADMIN_API_KEY (para htdocscop y htdocspanel)
echo.

REM Cambiar al directorio del script
cd /d "%~dp0"

REM Generar JWT_SECRET usando PowerShell (compatible con versiones antiguas)
echo [1/2] Generando JWT_SECRET seguro...
for /f "delims=" %%i in ('powershell -Command "$rng = New-Object System.Security.Cryptography.RNGCryptoServiceProvider; $bytes = New-Object byte[] 32; $rng.GetBytes($bytes); $rng.Dispose(); [System.BitConverter]::ToString($bytes).Replace('-', '')"') do set "JWT_SECRET=%%i"

REM Verificar que JWT_SECRET se generó correctamente
if "!JWT_SECRET!"=="" (
    echo ❌ Error: No se pudo generar JWT_SECRET
    pause
    exit /b 1
)

REM Verificar que JWT_SECRET no sea solo ceros
echo !JWT_SECRET! | findstr /R /V "^0*$" >nul
if errorlevel 1 (
    echo ❌ Error: JWT_SECRET generado es invalido (solo ceros)
    echo Reintentando...
    for /f "delims=" %%i in ('powershell -Command "$rng = New-Object System.Security.Cryptography.RNGCryptoServiceProvider; $bytes = New-Object byte[] 32; $rng.GetBytes($bytes); $rng.Dispose(); [System.BitConverter]::ToString($bytes).Replace('-', '')"') do set "JWT_SECRET=%%i"
)

REM Generar ADMIN_API_KEY usando PowerShell (compatible con versiones antiguas)
echo [2/2] Generando ADMIN_API_KEY seguro...
for /f "delims=" %%i in ('powershell -Command "$rng = New-Object System.Security.Cryptography.RNGCryptoServiceProvider; $bytes = New-Object byte[] 32; $rng.GetBytes($bytes); $rng.Dispose(); [System.BitConverter]::ToString($bytes).Replace('-', '')"') do set "ADMIN_API_KEY=%%i"

REM Verificar que ADMIN_API_KEY se generó correctamente
if "!ADMIN_API_KEY!"=="" (
    echo ❌ Error: No se pudo generar ADMIN_API_KEY
    pause
    exit /b 1
)

REM Verificar que ADMIN_API_KEY no sea solo ceros
echo !ADMIN_API_KEY! | findstr /R /V "^0*$" >nul
if errorlevel 1 (
    echo ❌ Error: ADMIN_API_KEY generado es invalido (solo ceros)
    echo Reintentando...
    for /f "delims=" %%i in ('powershell -Command "$rng = New-Object System.Security.Cryptography.RNGCryptoServiceProvider; $bytes = New-Object byte[] 32; $rng.GetBytes($bytes); $rng.Dispose(); [System.BitConverter]::ToString($bytes).Replace('-', '')"') do set "ADMIN_API_KEY=%%i"
)

echo.
echo ✅ Secretos generados:
echo.
echo JWT_SECRET:
echo !JWT_SECRET!
echo.
echo ADMIN_API_KEY:
echo !ADMIN_API_KEY!
echo.

REM Verificar si existe config.json
set "CONFIG_FILE=config.json"
if not exist "%CONFIG_FILE%" (
    echo ⚠️  No se encontro config.json
    echo.
    echo Por favor, crea config.json basado en config.example.json primero.
    echo Luego ejecuta este script nuevamente.
    echo.
    pause
    exit /b 1
)

REM Validar JSON antes de continuar
echo Validando formato de config.json...
powershell -NoProfile -Command "try { $null = Get-Content 'config.json' -Raw -Encoding UTF8 | ConvertFrom-Json; Write-Output 'OK' } catch { Write-Output 'ERROR' }" > temp_validation.txt 2>&1
set /p VALIDATION=<temp_validation.txt
del temp_validation.txt

if not "!VALIDATION!"=="OK" (
    echo ❌ Error: config.json tiene formato JSON invalido
    echo.
    echo Por favor, corrige el formato de config.json antes de continuar.
    echo Puedes usar config.example.json como referencia.
    echo.
    echo Valores generados para copiar manualmente despues de corregir:
    echo   "htdocspanel": {
    echo     "JWT_SECRET": "!JWT_SECRET!",
    echo     "ADMIN_API_KEY": "!ADMIN_API_KEY!"
    echo   },
    echo   "htdocscop": {
    echo     "ADMIN_API_KEY": "!ADMIN_API_KEY!"
    echo   }
    echo.
    pause
    exit /b 1
)

echo ✅ config.json tiene formato valido
echo.

REM Actualizar config.json automáticamente
echo Actualizando config.json automáticamente...
echo.

REM Guardar las claves en archivos temporales para pasarlas a PowerShell de forma segura
echo !JWT_SECRET! > temp_jwt.txt
echo !ADMIN_API_KEY! > temp_admin.txt

REM Ejecutar PowerShell directamente con las variables
powershell -NoProfile -ExecutionPolicy Bypass -Command "$ErrorActionPreference = 'Stop'; $JWT_SECRET = (Get-Content 'temp_jwt.txt' -Raw).Trim(); $ADMIN_API_KEY = (Get-Content 'temp_admin.txt' -Raw).Trim(); try { $configFile = 'config.json'; $content = Get-Content $configFile -Raw -Encoding UTF8; $config = $content | ConvertFrom-Json; if (-not $config.htdocspanel) { $config | Add-Member -MemberType NoteProperty -Name 'htdocspanel' -Value @{} -Force }; if (-not $config.htdocscop) { $config | Add-Member -MemberType NoteProperty -Name 'htdocscop' -Value @{} -Force }; $config.htdocspanel.JWT_SECRET = $JWT_SECRET; $config.htdocspanel.ADMIN_API_KEY = $ADMIN_API_KEY; $config.htdocscop.ADMIN_API_KEY = $ADMIN_API_KEY; $json = $config | ConvertTo-Json -Depth 10; [System.IO.File]::WriteAllText((Resolve-Path $configFile), $json, [System.Text.Encoding]::UTF8); $updated = Get-Content $configFile -Raw -Encoding UTF8 | ConvertFrom-Json; if ($updated.htdocspanel.JWT_SECRET -eq $JWT_SECRET -and $updated.htdocspanel.ADMIN_API_KEY -eq $ADMIN_API_KEY) { Write-Output 'OK' } else { Write-Output 'ERROR' } } catch { Write-Output 'ERROR'; Write-Error $_.Exception.Message }" > temp_result.txt 2>&1

REM Limpiar archivos temporales
del temp_jwt.txt
del temp_admin.txt

REM Leer resultado
set /p RESULT=<temp_result.txt
type temp_result.txt
del temp_result.txt

REM Verificar resultado
if "!RESULT!"=="OK" (
    echo.
    echo ✅ config.json actualizado exitosamente
    echo   - htdocspanel.JWT_SECRET actualizado
    echo   - htdocspanel.ADMIN_API_KEY actualizado
    echo   - htdocscop.ADMIN_API_KEY actualizado
    echo.
) else (
    echo.
    echo ❌ Error al actualizar config.json
    echo.
    echo Por favor, actualiza manualmente en config.json:
    echo   "htdocspanel": {
    echo     "JWT_SECRET": "!JWT_SECRET!",
    echo     "ADMIN_API_KEY": "!ADMIN_API_KEY!"
    echo   },
    echo   "htdocscop": {
    echo     "ADMIN_API_KEY": "!ADMIN_API_KEY!"
    echo   }
    echo.
)

echo ================================================
echo ⚠️  IMPORTANTE: Manten estos secretos seguros
echo    No los compartas ni los subas al repositorio
echo    Ejecuta setup_env.bat para aplicar los cambios
echo ================================================
echo.
pause
