@echo off
title Puente Capta Huella - Guataparo
color 0A
echo ===================================================
echo     INICIANDO PUENTE BIOMETRICO (CAPTA HUELLA)
echo ===================================================
echo.
echo No cierre esta ventana mientras necesite usar el 
echo lector de huellas en el sistema.
echo.
echo Servidor escuchando en http://localhost:8888
echo.

cd /d "%~dp0"
"C:\laragon\bin\php\php-8.1.20-Win32-vs16-x64\php.exe" -S localhost:8888 bridge_router.php

pause
