@echo off
setlocal enabledelayedexpansion

REM Script para generar un JWT_SECRET seguro y actualizar config.json
REM Ejecutar desde el directorio raíz del proyecto

echo ================================================
echo GENERADOR DE JWT_SECRET
echo ================================================
echo.

REM Cambiar al directorio del script
cd /d "%~dp0"

REM Generar JWT_SECRET usando PowerShell
echo Generando JWT_SECRET seguro...
for /f "delims=" %%i in ('powershell -Command "$bytes = New-Object byte[] 32; [System.Security.Cryptography.RandomNumberGenerator]::Fill($bytes); [System.BitConverter]::ToString($bytes).Replace('-', '')"') do set "JWT_SECRET=%%i"

echo.
echo ✅ JWT_SECRET generado:
echo !JWT_SECRET!
echo.

REM Verificar si existe config.json
set "CONFIG_FILE=config.json"
if not exist "%CONFIG_FILE%" (
    echo ⚠️  No se encontro config.json
    echo.
    echo Por favor, crea config.json basado en config.example.json primero.
    echo Luego ejecuta este script nuevamente para actualizar el JWT_SECRET.
    echo.
    pause
    exit /b 1
)

REM Actualizar config.json automáticamente usando PowerShell
echo.
echo Actualizando config.json automáticamente...
powershell -Command "$config = Get-Content 'config.json' -Raw | ConvertFrom-Json; $config.htdocspanel.JWT_SECRET = '!JWT_SECRET!'; $config | ConvertTo-Json -Depth 10 | Set-Content 'config.json' -Encoding UTF8"

if errorlevel 1 (
    echo ❌ Error al actualizar config.json
    echo.
    echo Por favor, actualiza manualmente en config.json:
    echo   "JWT_SECRET": "!JWT_SECRET!"
    echo.
) else (
    echo ✅ config.json actualizado exitosamente
    echo.
)

echo ================================================
echo ⚠️  IMPORTANTE: Manten este secreto seguro
echo    No lo compartas ni lo subas al repositorio
echo ================================================
echo.
pause

