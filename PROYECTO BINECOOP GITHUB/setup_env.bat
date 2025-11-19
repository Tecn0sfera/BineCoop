@echo off
setlocal enabledelayedexpansion

REM Script de configuración automática de variables de entorno
REM Para htdocscop y htdocspanel
REM Lee la configuración desde config.json
REM Ejecutar desde el directorio raíz del proyecto

echo ================================================
echo CONFIGURACION AUTOMATICA DE VARIABLES DE ENTORNO
echo ================================================
echo.

REM Cambiar al directorio del script
cd /d "%~dp0"

REM Verificar si existe config.json
set "CONFIG_FILE=config.json"
if not exist "%CONFIG_FILE%" (
    echo ❌ Error: No se encontro el archivo %CONFIG_FILE%
    echo.
    echo Por favor, crea un archivo %CONFIG_FILE% basado en config.example.json
    echo o ejecuta el script con valores por defecto.
    echo.
    set /p use_defaults="¿Deseas usar valores por defecto? (s/N): "
    if /i not "!use_defaults!"=="s" (
        echo Saliendo...
        exit /b 1
    )
    set "CONFIG_FILE="
)

REM Ir al menú principal
goto :menu

REM Función para leer valor del JSON usando PowerShell
:read_json_value
set "section=%~2"
set "key=%~3"
set "default_value=%~4"

if defined CONFIG_FILE (
    REM Usar PowerShell para leer JSON (escapar comillas correctamente)
    set "value="
    for /f "delims=" %%i in ('powershell -NoProfile -Command "$ErrorActionPreference='Stop'; try { $json = Get-Content '!CONFIG_FILE!' -Raw -Encoding UTF8 | ConvertFrom-Json; if ($json.!section!.!key!) { Write-Output $json.!section!.!key! } } catch { Write-Output '' }"') do set "value=%%i"
    if defined value (
        REM Verificar que no sea null o vacío
        echo !value! | findstr /R /C:"^null$" >nul
        if errorlevel 1 (
            set "%~5=!value!"
        ) else (
            set "%~5=!default_value!"
        )
    ) else (
        set "%~5=!default_value!"
    )
) else (
    set "%~5=!default_value!"
)
goto :eof

REM Función para crear .env en htdocscop
:setup_htdocscop
set "ENV_FILE=htdocscop\.env"

if exist "%ENV_FILE%" (
    echo ⚠️  El archivo %ENV_FILE% ya existe.
    set /p overwrite="¿Deseas sobrescribirlo? (s/N): "
    if /i not "!overwrite!"=="s" (
        echo ✅ Saltando configuracion de htdocscop...
        goto :eof
    )
)

echo.
echo 📝 Configurando htdocscop\.env
echo -------------------------------------------

REM Leer valores del JSON o usar defaults
call :read_json_value "" "htdocscop" "DB_HOST" "sql211.infinityfree.com" db_host
call :read_json_value "" "htdocscop" "DB_PORT" "3306" db_port
call :read_json_value "" "htdocscop" "DB_NAME" "if0_39215471_admin_panel" db_name
call :read_json_value "" "htdocscop" "DB_USER" "if0_39215471" db_user
call :read_json_value "" "htdocscop" "DB_PASS" "RockGuidoNetaNa" db_pass
call :read_json_value "" "htdocscop" "ADMIN_API_KEY" "isec5a931d2396eac98577583c22d783c2d50c054e1fe785855331208a70871893" admin_api_key

echo ✅ Valores leidos desde config.json
echo.

REM Crear el archivo .env
(
echo # Configuracion de Base de Datos
echo DB_HOST=!db_host!
echo DB_PORT=!db_port!
echo DB_NAME=!db_name!
echo DB_USER=!db_user!
echo DB_PASS=!db_pass!
echo.
echo # Clave API para autenticacion
echo ADMIN_API_KEY=!admin_api_key!
) > "%ENV_FILE%"

echo ✅ Archivo %ENV_FILE% creado exitosamente
goto :eof

REM Función para crear .env en htdocspanel
:setup_htdocspanel
set "ENV_FILE=htdocspanel\.env"

if exist "%ENV_FILE%" (
    echo ⚠️  El archivo %ENV_FILE% ya existe.
    set /p overwrite="¿Deseas sobrescribirlo? (s/N): "
    if /i not "!overwrite!"=="s" (
        echo ✅ Saltando configuracion de htdocspanel...
        goto :eof
    )
)

echo.
echo 📝 Configurando htdocspanel\.env
echo -------------------------------------------

REM Leer valores del JSON o usar defaults
call :read_json_value "" "htdocspanel" "DB_HOST" "localhost" db_host
call :read_json_value "" "htdocspanel" "DB_PORT" "3306" db_port
call :read_json_value "" "htdocspanel" "DB_NAME" "nombre_base_datos_ejemplo" db_name
call :read_json_value "" "htdocspanel" "DB_USER" "usuario_ejemplo" db_user
call :read_json_value "" "htdocspanel" "DB_PASS" "contraseña_ejemplo" db_pass
call :read_json_value "" "htdocspanel" "DB_CHARSET" "utf8mb4" db_charset
call :read_json_value "" "htdocspanel" "ADMIN_API_KEY" "clave_api_secreta_ejemplo_cambiar_en_produccion" admin_api_key
call :read_json_value "" "htdocspanel" "JWT_SECRET" "clave_secreta_jwt_ejemplo_cambiar_en_produccion" jwt_secret
call :read_json_value "" "htdocspanel" "APP_ENV" "development" app_env
call :read_json_value "" "htdocspanel" "DISPLAY_ERRORS" "false" display_errors

echo ✅ Valores leidos desde config.json
echo.

REM Crear el archivo .env
(
echo # Configuracion de Base de Datos
echo DB_HOST=!db_host!
echo DB_PORT=!db_port!
echo DB_NAME=!db_name!
echo DB_USER=!db_user!
echo DB_PASS=!db_pass!
echo DB_CHARSET=!db_charset!
echo.
echo # Clave API para autenticacion
echo ADMIN_API_KEY=!admin_api_key!
echo.
echo # Configuracion JWT
echo JWT_SECRET=!jwt_secret!
echo.
echo # Configuracion de entorno
echo APP_ENV=!app_env!
echo DISPLAY_ERRORS=!display_errors!
) > "%ENV_FILE%"

echo ✅ Archivo %ENV_FILE% creado exitosamente
goto :eof

REM Menú principal
:menu
echo Selecciona que configurar:
echo 1^) Solo htdocscop
echo 2^) Solo htdocspanel
echo 3^) Ambos (htdocscop y htdocspanel^)
echo 4^) Salir
echo.
set /p option="Opcion [3]: "
if "!option!"=="" set "option=3"

if "!option!"=="1" (
    call :setup_htdocscop
    goto :end
)
if "!option!"=="2" (
    call :setup_htdocspanel
    goto :end
)
if "!option!"=="3" (
    call :setup_htdocscop
    call :setup_htdocspanel
    goto :end
)
if "!option!"=="4" (
    echo Saliendo...
    exit /b 0
)

echo ❌ Opcion invalida
exit /b 1

:end
echo.
echo ================================================
echo ✅ Configuracion completada
echo ================================================
echo.
if defined CONFIG_FILE (
    echo ✅ Configuracion leida desde: %CONFIG_FILE%
) else (
    echo ⚠️  Se usaron valores por defecto (no se encontro config.json^)
)
echo.
echo NOTAS IMPORTANTES:
echo - Los archivos .env NO deben ser subidos al repositorio Git
echo - Asegurate de que .env este en .gitignore
echo - Los valores tienen prioridad sobre los valores por defecto
echo - Crea config.json basado en config.example.json para personalizar
echo.
pause
