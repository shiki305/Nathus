@echo off
setlocal EnableExtensions EnableDelayedExpansion
title Nathus - verificar

set "APP=%~dp0"
if "%APP:~-1%"=="\" set "APP=%APP:~0,-1%"
for %%I in ("%APP%\..") do set "RAIZ=%%~fI"
for %%I in ("%RAIZ%") do set "NOME_PASTA=%%~nxI"
for %%I in ("%APP%") do set "NOME_APP=%%~nxI"
set "TMPF=%APP%\.verifica-tmp"

echo.
echo  ==================================================
echo    Nathus - diagnostico
echo  ==================================================
echo.

set "XAMPP="
for %%D in ("C:\xampp" "D:\xampp" "E:\xampp" "%ProgramFiles%\xampp" "%ProgramFiles(x86)%\xampp") do (
    if not defined XAMPP if exist "%%~D\php\php.exe" set "XAMPP=%%~D"
)

set "PHP="
if defined XAMPP set "PHP=%XAMPP%\php\php.exe"
if not defined PHP for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP set "PHP=%%P"

set "MYSQL="
set "MYSQLADMIN="
if defined XAMPP (
    if exist "%XAMPP%\mysql\bin\mysql.exe" set "MYSQL=%XAMPP%\mysql\bin\mysql.exe"
    if exist "%XAMPP%\mysql\bin\mysqladmin.exe" set "MYSQLADMIN=%XAMPP%\mysql\bin\mysqladmin.exe"
)
if not defined MYSQL for /f "delims=" %%P in ('where mysql 2^>nul') do if not defined MYSQL set "MYSQL=%%P"
if not defined MYSQLADMIN for /f "delims=" %%P in ('where mysqladmin 2^>nul') do if not defined MYSQLADMIN set "MYSQLADMIN=%%P"

echo  Programa         Situacao
echo  ---------------- ------------------------------------------
if defined XAMPP (echo  XAMPP            OK em %XAMPP%) else (echo  XAMPP            NAO encontrado)
if defined PHP (echo  PHP              %PHP%) else (echo  PHP              NAO encontrado)
if defined MYSQL (echo  mysql.exe        OK) else (echo  mysql.exe        NAO encontrado)
echo.

if not defined PHP goto :sem_php

"%PHP%" -r "echo PHP_VERSION;" > "%TMPF%" 2>nul
set /p VERSAO_PHP=<"%TMPF%"
echo  Versao do PHP    !VERSAO_PHP!

"%PHP%" -r "echo '   Extensoes        mysqli=', (int)extension_loaded('mysqli'), ' | gd=', (int)extension_loaded('gd'), ' | mbstring=', (int)extension_loaded('mbstring'), ' | fileinfo=', (int)extension_loaded('fileinfo'), PHP_EOL;"

:sem_php
echo.
echo  Portas em uso
echo  ---------------- ------------------------------------------
netstat -ano | findstr /R /C:":80 .*LISTENING" >nul && (echo  Porta 80         ocupada pelo Apache ou outro) || echo  Porta 80         livre
netstat -ano | findstr /R /C:":3306 .*LISTENING" >nul && (echo  Porta 3306       ocupada - provavel MySQL) || echo  Porta 3306       livre
if exist "%APP%\servidor.porta" (
    set /p PORTA_ATUAL=<"%APP%\servidor.porta"
    netstat -ano | findstr /R /C:":!PORTA_ATUAL! .*LISTENING" >nul && (echo  Servidor Nathus  rodando em http://localhost:!PORTA_ATUAL!/!NOME_PASTA!/!NOME_APP!/) || echo  Servidor Nathus  parado
) else (
    echo  Servidor Nathus  parado
)

echo.
echo  Banco de dados
echo  ---------------- ------------------------------------------
if not defined MYSQL goto :sem_mysql

"%MYSQLADMIN%" -u root ping >nul 2>&1
if errorlevel 1 (
    echo  MySQL            NAO responde - abra o XAMPP Control Panel e clique em Start
    goto :sem_mysql
)
echo  MySQL            respondendo

"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='nathus';" > "%TMPF%" 2>nul
set /p TABELAS=<"%TMPF%"
if not defined TABELAS set "TABELAS=0"
if "%TABELAS%"=="0" (
    echo  Banco nathus     NAO existe - rode setup.cmd
    goto :sem_mysql
)
echo  Tabelas          %TABELAS%

"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM nathus.cliente;" > "%TMPF%" 2>nul
set /p CLIENTES=<"%TMPF%"
echo  Clientes         !CLIENTES!

"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM nathus.promotor;" > "%TMPF%" 2>nul
set /p PROMOTORES=<"%TMPF%"
echo  Promotores       !PROMOTORES!

"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM nathus.cliente WHERE email LIKE '%%@nathus.com';" > "%TMPF%" 2>nul
set /p DEMO=<"%TMPF%"
echo  Contas demo      !DEMO! - esperado 32

"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM nathus.promotor p JOIN nathus.cliente c ON c.id=p.id_cliente JOIN nathus.promotor_campo pc ON pc.id_promotor=p.id AND pc.campo='local' WHERE c.email LIKE '%%@nathus.com' AND p.espaco_area_m2 IS NULL;" > "%TMPF%" 2>nul
set /p SEMAREA=<"%TMPF%"
echo  Locais sem area  !SEMAREA! - precisa ser 0

:sem_mysql
echo.
echo  Arquivos do app
echo  ---------------- ------------------------------------------
if exist "%APP%\Banco\nathus.sql" (echo  nathus.sql       OK) else (echo  nathus.sql       FALTA)
if exist "%APP%\Banco\perfis-demo.sql" (echo  perfis-demo.sql  OK) else (echo  perfis-demo.sql  FALTA)

> "%APP%\uploads\.teste-escrita" echo ok 2>nul
if exist "%APP%\uploads\.teste-escrita" (
    echo  uploads/         gravavel
    del "%APP%\uploads\.teste-escrita" >nul 2>&1
) else (
    echo  uploads/         SEM permissao de escrita
)

for %%F in (
    "perfil-promotor\demo-buffet1-perfil.jpg"
    "banner\demo-buffet1-banner.jpg"
    "galeria\demo-buffet1-galeria-3.jpg"
    "galeria\demo-buffet1-galeria-4.jpg"
    "galeria\demo-local1-galeria-4.jpg"
) do (
    if exist "%APP%\uploads\%%~F" (echo  imagem demo      OK %%~F) else (echo  imagem demo      FALTA %%~F)
)

if exist "%TMPF%" del "%TMPF%" >nul 2>&1
echo.
echo  ==================================================
echo.
pause
exit /b 0