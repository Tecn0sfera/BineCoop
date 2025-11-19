@echo off
setlocal enabledelayedexpansion

REM Script para generar un ADMIN_API_KEY seguro y actualizar config.json
REM Ejecutar desde el directorio raíz del proyecto

echo ================================================
echo GENERADOR DE ADMIN_API_KEY
echo ================================================
echo.

REM Cambiar al directorio del script
cd /d "%~dp0"

REM Generar ADMIN_API_KEY usando PowerShell (64 caracteres hexadecimales)
echo Generando ADMIN_API_KEY seguro...
for /f "delims=" %%i in ('powershell -Command "$bytes = New-Object byte[] 32; [System.Security.Cryptography.RandomNumberGenerator]::Fill($bytes); [System.BitConverter]::ToString($bytes).Replace('-', '')"') do set "ADMIN_API_KEY=%%i"

echo.
echo ✅ ADMIN_API_KEY generado:
echo !ADMIN_API_KEY!
echo.

REM Verificar si existe config.json
set "CONFIG_FILE=config.json"
if not exist "%CONFIG_FILE%" (
    echo ⚠️  No se encontro config.json
    echo.
    echo Por favor, crea config.json basado en config.example.json primero.
    echo Luego ejecuta este script nuevamente para actualizar el ADMIN_API_KEY.
    echo.
    pause
    exit /b 1
)

REM Actualizar config.json automáticamente usando PowerShell
echo.
echo Actualizando config.json automáticamente...
echo Esto actualizara tanto htdocscop como htdocspanel.
powershell -Command "$config = Get-Content 'config.json' -Raw | ConvertFrom-Json; $config.htdocscop.ADMIN_API_KEY = '!ADMIN_API_KEY!'; $config.htdocspanel.ADMIN_API_KEY = '!ADMIN_API_KEY!'; $config | ConvertTo-Json -Depth 10 | Set-Content 'config.json' -Encoding UTF8"

if errorlevel 1 (
    echo ❌ Error al actualizar config.json
    echo.
    echo Por favor, actualiza manualmente en config.json:
    echo   En htdocscop: "ADMIN_API_KEY": "!ADMIN_API_KEY!"
    echo   En htdocspanel: "ADMIN_API_KEY": "!ADMIN_API_KEY!"
    echo.
) else (
    echo ✅ config.json actualizado exitosamente
    echo   - htdocscop.ADMIN_API_KEY actualizado
    echo   - htdocspanel.ADMIN_API_KEY actualizado
    echo.
)

echo ================================================
echo ⚠️  IMPORTANTE: Manten este secreto seguro
echo    No lo compartas ni lo subas al repositorio
echo    Este mismo ADMIN_API_KEY se usara en ambas
echo    carpetas (htdocscop y htdocspanel)
echo ================================================
echo.
pause

