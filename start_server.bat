@echo off
echo ================================================
echo   Iniciando Servidor PHP - Sistema Guataparo
echo ================================================
echo.
echo Servidor corriendo en: http://localhost:8000
echo Presione Ctrl+C para detener el servidor
echo.
cd public
php -S localhost:8000
pause
