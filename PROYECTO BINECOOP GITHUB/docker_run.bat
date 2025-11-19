@echo off
set IMAGE=nombredeejemplo
set CONTAINER=miapache

:menu
echo.
echo ===== MENU DOCKER =====
echo 1 - Construir y correr
echo 2 - Solo construir
echo 3 - Solo correr
echo CTRL + C para salir y detener el contenedor (si está corriendo)
echo ========================
set /p option=Elegí una opción: 

if "%option%"=="1" goto build_and_run
if "%option%"=="2" goto build_only
if "%option%"=="3" goto run_only
echo Opción inválida.
goto menu

:build_only
echo Construyendo imagen Docker...
docker build -t %IMAGE% .
goto menu

:run_only
echo Corriendo contenedor en puerto 8080...
docker run -it --rm -p 8080:80 --name %CONTAINER% %IMAGE%
goto menu

:build_and_run
echo Construyendo imagen Docker...
docker build -t %IMAGE% .

echo Corriendo contenedor en puerto 8080...
docker run -it --rm -p 8080:80 --name %CONTAINER% %IMAGE%
goto menu
