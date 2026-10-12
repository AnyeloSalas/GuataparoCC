@echo off
title PUENTE DE HUELLA HIKVISION (PYTHON) - SISTEMA GUATAPARO
echo ====================================================
echo   SERVIDOR BRIDGE HIKVISION (PUERTO 8888)
echo ====================================================
echo.
echo Este servidor permite que el sistema web se comunique
echo con el lector de huellas Hikvision (DS-K1F820-F).
echo.
echo IMPORTANTE: No cierre esta ventana mientras use el sistema.
echo.

set PHP_PATH=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe

if not exist "%PHP_PATH%" (
    echo ERROR: No se encontro PHP en %PHP_PATH%
    echo Intentando usar comando "php" global...
    set PHP_PATH=php
)

cd huella
"%PHP_PATH%" -S localhost:8888 bridge_router.php

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERROR CRITICO: No se pudo iniciar el servidor. 
    echo Verifique la configuracion de Laragon.
    pause
)
