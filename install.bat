@echo off
echo ================================================
echo   GUATAPARO - Instalador del Sistema PHP
echo ================================================
echo.

REM Verificar si existe la base de datos
echo [1/4] Verificando MySQL...
mysql --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: MySQL no esta instalado o no esta en el PATH
    pause
    exit /b 1
)
echo OK - MySQL encontrado

REM Crear base de datos si no existe
echo.
echo [2/4] Configurando base de datos...
echo Ingrese la contraseña de root de MySQL:
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS guataparo CHARACTER SET utf8 COLLATE utf8_general_ci;"
if errorlevel 1 (
    echo ERROR: No se pudo crear la base de datos
    pause
    exit /b 1
)

REM Importar estructura
echo Importando estructura...
mysql -u root -p guataparo < ..\aplicacion\guataparo.sql
if errorlevel 1 (
    echo ERROR: No se pudo importar la estructura
    pause
    exit /b 1
)

REM Crear tabla de huellas
echo Creando tabla de huellas...
mysql -u root -p guataparo < config\create_huellas_table.sql
if errorlevel 1 (
    echo ERROR: No se pudo crear la tabla de huellas
    pause
    exit /b 1
)
echo OK - Base de datos configurada

REM Verificar PHP
echo.
echo [3/4] Verificando PHP...
php --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: PHP no esta instalado o no esta en el PATH
    pause
    exit /b 1
)
echo OK - PHP encontrado

REM Verificar Java (opcional)
echo.
echo [4/4] Verificando Java (opcional para huellas)...
java -version >nul 2>&1
if errorlevel 1 (
    echo ADVERTENCIA: Java no encontrado. El modulo de huellas no funcionara.
    echo Puede continuar sin Java si no necesita captura de huellas.
) else (
    echo OK - Java encontrado
)

echo.
echo ================================================
echo   Instalacion completada exitosamente!
echo ================================================
echo.
echo Para iniciar el sistema:
echo   1. Ejecute: start_server.bat
echo   2. Abra el navegador en: http://localhost:8000
echo.
echo Para usar captura de huellas:
echo   1. Ejecute: bridge\run_bridge.bat
echo.
pause
