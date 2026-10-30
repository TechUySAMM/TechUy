@echo off
REM ============================================================
REM S.I.G.S.M. - Copia de seguridad (Windows + XAMPP)
REM Respalda la base de datos y la carpeta de PDF en
REM respaldos\AAAAMMDD_HHMM\ dentro del proyecto.
REM Uso: doble clic o "scripts\respaldo.bat" desde la carpeta sigsm
REM ============================================================

set XAMPP=C:\xampp
set BD=hc_gestion_documental
set RAIZ=%~dp0..
for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd_HHmm"') do set FECHA=%%i
set DESTINO=%RAIZ%\respaldos\%FECHA%

mkdir "%DESTINO%" 2>nul

echo [1/2] Exportando la base %BD% ...
"%XAMPP%\mysql\bin\mysqldump.exe" -u root --routines --single-transaction --default-character-set=utf8mb4 --databases %BD% > "%DESTINO%\base.sql"
if errorlevel 1 (
    echo ERROR: no se pudo exportar la base. Verifique que MySQL este iniciado.
    exit /b 1
)

echo [2/2] Copiando los PDF ...
powershell -NoProfile -Command "Compress-Archive -Path '%RAIZ%\documentos\*.pdf' -DestinationPath '%DESTINO%\documentos.zip' -Force"

echo Respaldo terminado en: %DESTINO%
