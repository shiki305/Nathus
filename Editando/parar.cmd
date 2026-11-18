@echo off
setlocal EnableExtensions EnableDelayedExpansion
title Nathus - parar

set "APP=%~dp0"
if "%APP:~-1%"=="\" set "APP=%APP:~0,-1%"

echo.
echo  ==================================================
echo    Nathus - encerrando o servidor
echo  ==================================================
echo.

set "PAROU=0"
if exist "%APP%\servidor.porta" (
    set /p PORTA=<"%APP%\servidor.porta"
    if defined PORTA (
        for /f "tokens=5" %%A in ('netstat -ano ^| findstr /R /C:":!PORTA! .*LISTENING"') do (
            taskkill /PID %%A /F >nul 2>&1
            set "PAROU=1"
        )
    )
    del "%APP%\servidor.porta" >nul 2>&1
)

if "!PAROU!"=="1" (
    echo  Servidor encerrado.
) else (
    echo  Nenhum servidor do Nathus estava rodando.
)

if exist "%APP%\.mysql-iniciado" (
    echo.
    set "RESP="
    set /p RESP=  O MySQL foi iniciado pelo setup. Encerrar tambem? [s/N] 
    if /i not "!RESP!"=="s" goto :fim
    set "XAMPP="
    for %%D in ("C:\xampp" "D:\xampp" "E:\xampp" "%ProgramFiles%\xampp" "%ProgramFiles(x86)%\xampp") do (
        if not defined XAMPP if exist "%%~D\mysql\bin\mysqladmin.exe" set "XAMPP=%%~D"
    )
    if defined XAMPP if exist "%XAMPP%\mysql\bin\mysqladmin.exe" "%XAMPP%\mysql\bin\mysqladmin.exe" -u root shutdown >nul 2>&1
    del "%APP%\.mysql-iniciado" >nul 2>&1
    echo  MySQL encerrado.
)

:fim
echo.
exit /b 0