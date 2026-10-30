@echo off
REM ============================================================
REM S.I.G.S.M. - Restaurar una copia de seguridad (Windows + XAMPP)
REM Uso: scripts\restaurar.bat 20261028_2330
REM (el nombre de la carpeta dentro de respaldos\)
REM ATENCION: reemplaza la base actual por la del respaldo.
REM ============================================================

if "%~1"=="" (
    echo Uso: restaurar.bat CARPETA_DEL_RESPALDO
    exit /b 1
)
set XAMPP=C:\xampp
set RAIZ=%~dp0..
set ORIGEN=%RAIZ%\respaldos\%~1

if not exist "%ORIGEN%\base.sql" (
    echo ERROR: no existe %ORIGEN%\base.sql
    exit /b 1
)

echo [1/2] Restaurando la base ...
"%XAMPP%\mysql\bin\mysql.exe" -u root --default-character-set=utf8mb4 < "%ORIGEN%\base.sql"
if errorlevel 1 exit /b 1

echo [2/2] Restaurando los PDF ...
powershell -NoProfile -Command "Expand-Archive -Path '%ORIGEN%\documentos.zip' -DestinationPath '%RAIZ%\documentos' -Force"

echo Restauracion terminada desde: %ORIGEN%
