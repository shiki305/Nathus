@echo off
setlocal EnableExtensions EnableDelayedExpansion
title Nathus - menu
color 0F

rem ------------------------------------------------------------------
rem  Nathus - menu numerado de setup. Fica dentro da pasta Projeto.
rem  Uso:  setup.cmd             abre o menu
rem        setup.cmd reset       reinicia o banco direto (sem menu)
rem        setup.cmd --porta N   fixa a porta do site
rem
rem  O menu volta para a lista depois de cada acao.
rem ------------------------------------------------------------------

set "APP=%~dp0"
if "%APP:~-1%"=="\" set "APP=%APP:~0,-1%"
for %%I in ("%APP%\..") do set "RAIZ=%%~fI"
for %%I in ("%RAIZ%\..") do set "DOCROOT=%%~fI"
for %%I in ("%RAIZ%") do set "NOME_PASTA=%%~nxI"
for %%I in ("%APP%") do set "NOME_APP=%%~nxI"
set "LOG=%APP%\setup.log"
set "TMPF=%APP%\.setup-tmp"
set "PORTARQ=%APP%\servidor.porta"
set "PORTA_FIXA="
set "SEM_NAV="
set "ACAO_DIRETA="

:args
if "%~1"=="" goto :args_fim
if /i "%~1"=="reset" set "ACAO_DIRETA=reset"
if /i "%~1"=="--sem-navegador" set "SEM_NAV=1"
if /i "%~1"=="--porta" (
    set "PORTA_FIXA=%~2"
    shift
)
shift
goto :args
:args_fim

call :achar_programas

if /i "%ACAO_DIRETA%"=="reset" (
    call :f_reset
    goto :sair
)

rem ------------------------------------------------------------------
rem menu
rem ------------------------------------------------------------------

:menu
cls
echo.
echo  ==================================================
echo    Nathus - menu
echo  ==================================================
call :estado
echo.
echo    [1] Verificar programas (XAMPP, PHP, MySQL)
echo    [2] Subir o MySQL
echo    [3] Criar o banco (estrutura)
echo    [4] Importar perfis demo
echo    [5] Abrir o site
echo    [6] Parar o site
echo    [7] Diagnostico completo
echo    [8] Resetar o banco (apagar e recriar)
echo.
echo    [0] Sair
echo.
rem `choice` espera uma tecla da lista: Enter, espaco ou tecla perdida nao
rem redesenham a tela (era o que fazia o menu piscar). 1..8 e 0 (ultima).
choice /c 123456780 /n /m "  Escolha uma opcao: " 2>nul
set "SEL=!errorlevel!"
if "!SEL!"=="255" (
    echo.
    echo    Nao consegui ler a opcao neste terminal.
    echo    Feche esta janela e abra o setup.cmd de novo.
    ping -n 5 127.0.0.1 >nul 2>&1
    goto :sair
)
if "!SEL!"=="0" goto :sair
if "!SEL!"=="1" goto :acao1
if "!SEL!"=="2" goto :acao2
if "!SEL!"=="3" goto :acao3
if "!SEL!"=="4" goto :acao4
if "!SEL!"=="5" goto :acao5
if "!SEL!"=="6" goto :acao6
if "!SEL!"=="7" goto :acao7
if "!SEL!"=="8" goto :acao8
rem errorlevel 9 = tecla "0" (sair)
goto :sair

:acao1
call :f_programas
goto :voltar

:acao2
call :f_mysql
goto :voltar

:acao3
call :f_banco
goto :voltar

:acao4
call :f_demo
goto :voltar

:acao5
call :f_site
goto :voltar

:acao6
call :f_parar
goto :voltar

:acao7
cls
call "%APP%\verificar.cmd"
goto :menu

:acao8
call :f_reset
goto :voltar

:voltar
echo.
if not defined SEM_PAUSA pause
goto :menu

:sair
if exist "%TMPF%" del "%TMPF%" >nul 2>&1
exit /b 0

rem ------------------------------------------------------------------
rem estado (linha do topo do menu)
rem ------------------------------------------------------------------
:estado
set "L_XAMPP=nao encontrado"
if defined XAMPP set "L_XAMPP=%XAMPP%"
set "L_PHP=nao encontrado"
if defined PHP set "L_PHP=ok"
set "L_MYSQL=parado"
call :mysql_online
if "!MYSQL_ONLINE!"=="1" set "L_MYSQL=rodando"

set "PORTA_ATUAL="
if exist "%PORTARQ%" set /p PORTA_ATUAL=<"%PORTARQ%"
set "L_SITE=parado"
if defined PORTA_ATUAL (
    netstat -ano | findstr /R /C:":!PORTA_ATUAL! .*LISTENING" >nul
    if not errorlevel 1 set "L_SITE=rodando na porta !PORTA_ATUAL!"
)

echo.
echo    XAMPP: !L_XAMPP!   PHP: !L_PHP!
echo    MySQL: !L_MYSQL!   Site: !L_SITE!
exit /b

rem ------------------------------------------------------------------
rem 1. programas
rem ------------------------------------------------------------------
:f_programas
echo.
if defined XAMPP (echo    XAMPP        OK em %XAMPP%) else (echo    XAMPP        NAO encontrado)
if defined PHP (echo    PHP          OK %PHP%) else (echo    PHP          NAO encontrado)
if defined MYSQL (echo    mysql.exe    OK) else (echo    mysql.exe    NAO encontrado)
if defined MYSQLD (echo    mysqld.exe   OK) else (echo    mysqld.exe   NAO encontrado)
if defined MYSQLADMIN (echo    mysqladmin   OK) else (echo    mysqladmin   NAO encontrado)
if defined MYSQLDUMP (echo    mysqldump    OK) else (echo    mysqldump    NAO encontrado)

if defined PHP (
    "%PHP%" -r "echo PHP_VERSION;" > "%TMPF%" 2>nul
    set /p VERSAO_PHP=<"%TMPF%"
    echo    Versao do PHP !VERSAO_PHP!
    "%PHP%" -r "echo '   Extensoes    mysqli=', (int)extension_loaded('mysqli'), ' gd=', (int)extension_loaded('gd'), ' mbstring=', (int)extension_loaded('mbstring'), ' fileinfo=', (int)extension_loaded('fileinfo');" 
    echo.
)

if not exist "%APP%\Banco\nathus.sql" echo    FALTA Projeto\Banco\nathus.sql
if not exist "%APP%\Banco\perfis-demo.sql" echo    FALTA Projeto\Banco\perfis-demo.sql
if exist "%APP%\Banco\nathus.sql" if exist "%APP%\Banco\perfis-demo.sql" echo    Arquivos do banco OK
exit /b

rem ------------------------------------------------------------------
rem 2. mysql
rem ------------------------------------------------------------------
:f_mysql
echo.
call :mysql_online
if "!MYSQL_ONLINE!"=="1" (
    echo    O MySQL ja esta rodando na porta 3306.
    exit /b
)
if not defined MYSQLD (
    echo    Nao achei o mysqld.exe. Instale o XAMPP em C:\xampp.
    exit /b
)
if not defined XAMPP (
    echo    Nao achei a pasta do XAMPP.
    exit /b
)
if not exist "%XAMPP%\mysql\bin\my.ini" (
    echo    Nao achei o my.ini em %XAMPP%\mysql\bin.
    exit /b
)

echo    MySQL parado. Iniciando...
start "Nathus - MySQL" /min "%MYSQLD%" --defaults-file="%XAMPP%\mysql\bin\my.ini" --standalone
set /a TENT=0

:espera_mysql
ping -n 2 127.0.0.1 >nul
call :mysql_online
if "!MYSQL_ONLINE!"=="1" (
    > "%APP%\.mysql-iniciado" echo 1
    echo    MySQL no ar na porta 3306.
    exit /b
)
set /a TENT+=1
if !TENT! GEQ 30 (
    echo    Nao consegui iniciar o MySQL.
    echo    Abra o XAMPP Control Panel e clique em Start no MySQL.
    exit /b
)
goto :espera_mysql

rem ------------------------------------------------------------------
rem 3. banco
rem ------------------------------------------------------------------
:f_banco
echo.
if not exist "%APP%\Banco\nathus.sql" (
    echo    Falta o arquivo Projeto\Banco\nathus.sql
    exit /b
)
call :mysql_online
if not "!MYSQL_ONLINE!"=="1" (
    echo    O MySQL esta parado. Use a opcao 2 para subir.
    exit /b
)

set "TABELAS="
"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='nathus';" > "%TMPF%" 2>nul
if errorlevel 1 (
    echo    Nao consegui conectar no MySQL como root sem senha.
    echo    Confira se o MySQL esta Start no XAMPP Control Panel.
    exit /b
)
set /p TABELAS=<"%TMPF%"
if not defined TABELAS (
    echo    Nao consegui conectar no MySQL como root sem senha.
    exit /b
)
if not "!TABELAS!"=="0" (
    echo    O banco nathus ja existe, com !TABELAS! tabelas. Nada a fazer.
    echo    Para comecar do zero use a opcao 8.
    exit /b
)

echo    Criando a estrutura do banco nathus...
"%MYSQL%" -u root --default-character-set=utf8mb4 --force < "%APP%\Banco\nathus.sql" >> "%LOG%" 2>&1

set "TABELAS="
"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='nathus';" > "%TMPF%" 2>nul
set /p TABELAS=<"%TMPF%"
if not defined TABELAS set "TABELAS=0"
if "!TABELAS!"=="0" (
    echo    ERRO: o banco nao foi criado. Veja o final de setup.log.
    exit /b
)
echo    Banco nathus criado com !TABELAS! tabelas.
echo    Proximo passo: opcao 4 (importar os perfis demo).
exit /b

rem ------------------------------------------------------------------
rem 4. perfis demo
rem ------------------------------------------------------------------
:f_demo
echo.
if not exist "%APP%\Banco\perfis-demo.sql" (
    echo    Falta o arquivo Projeto\Banco\perfis-demo.sql
    exit /b
)
call :mysql_online
if not "!MYSQL_ONLINE!"=="1" (
    echo    O MySQL esta parado. Use a opcao 2 para subir.
    exit /b
)

set "TABELAS="
"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='nathus';" > "%TMPF%" 2>nul
set /p TABELAS=<"%TMPF%"
if not defined TABELAS (
    echo    Nao consegui conectar no MySQL como root sem senha.
    exit /b
)
if "!TABELAS!"=="0" (
    echo    O banco nathus ainda nao existe. Use a opcao 3 primeiro.
    exit /b
)

echo    Importando os perfis demo (pode levar alguns segundos)...
"%MYSQL%" -u root --default-character-set=utf8mb4 --force < "%APP%\Banco\perfis-demo.sql" >> "%LOG%" 2>&1

set "DEMO="
"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM nathus.cliente WHERE email LIKE '%%@nathus.com';" > "%TMPF%" 2>nul
set /p DEMO=<"%TMPF%"
set "PROMO="
"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM nathus.promotor;" > "%TMPF%" 2>nul
set /p PROMO=<"%TMPF%"
set "PACOTES="
"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM nathus.promotor_pacote;" > "%TMPF%" 2>nul
set /p PACOTES=<"%TMPF%"
if not defined DEMO set "DEMO=0"

if "!DEMO!"=="0" (
    echo    ERRO: nenhuma conta demo foi criada. Veja o final de setup.log.
    exit /b
)
echo    Pronto: !DEMO! contas demo, !PROMO! promotores e !PACOTES! pacotes.
echo.
echo    Login de exemplo (senha 123):
echo      buffet1@nathus.com        promotor buffet
echo      local1@nathus.com         local de festas
echo      organizador1@nathus.com   organizador
echo      cliente1@nathus.com       cliente
exit /b

rem ------------------------------------------------------------------
rem 5. abrir o site
rem ------------------------------------------------------------------
:f_site
echo.
if not defined PHP (
    echo    Nao achei o PHP. Instale o XAMPP em C:\xampp.
    exit /b
)
call :mysql_online
if not "!MYSQL_ONLINE!"=="1" echo    AVISO: o MySQL esta parado. Use a opcao 2 antes de abrir o site.

if exist "%PORTARQ%" (
    set "JA_PORTA="
    set /p JA_PORTA=<"%PORTARQ%"
    if defined JA_PORTA (
        netstat -ano | findstr /R /C:":!JA_PORTA! .*LISTENING" >nul
        if not errorlevel 1 (
            echo    O site ja esta rodando na porta !JA_PORTA!.
            if not defined SEM_NAV start "" "http://localhost:!JA_PORTA!/%NOME_PASTA%/%NOME_APP%/"
            exit /b
        )
    )
    del "%PORTARQ%" >nul 2>&1
)

set "PORTA="
if defined PORTA_FIXA set "PORTA=%PORTA_FIXA%"
if not defined PORTA (
    for %%P in (8000 8001 8002 8003 8004 8005 8006 8007 8008 8009 8010 8011 8012 8013 8014 8015) do (
        if not defined PORTA (
            netstat -ano | findstr /R /C:":%%P .*LISTENING" >nul || set "PORTA=%%P"
        )
    )
)
if not defined PORTA (
    echo    Nenhuma porta livre entre 8000 e 8015.
    echo    Feche outros programas ou rode: setup.cmd --porta 8090
    exit /b
)

> "%PORTARQ%" echo %PORTA%
cd /d "%APP%"
set "PHP_CLI_SERVER_WORKERS=4"
start "Nathus - servidor PHP porta %PORTA%" "%PHP%" -d upload_max_filesize=20M -d post_max_size=24M -d memory_limit=256M -d max_execution_time=120 -d date.timezone=America/Sao_Paulo -d display_errors=0 -d log_errors=1 -d error_log=php-erros.log -S localhost:%PORTA% -t "%DOCROOT%"

set /a TENT_SRV=0

:espera_servidor
ping -n 2 127.0.0.1 >nul
netstat -ano | findstr /R /C:":%PORTA% .*LISTENING" >nul
if not errorlevel 1 goto :servidor_ok
set /a TENT_SRV+=1
if !TENT_SRV! GEQ 20 goto :servidor_falhou
goto :espera_servidor

:servidor_falhou
echo    ERRO: o servidor nao respondeu na porta %PORTA%.
echo    Veja a janela "Nathus - servidor PHP" e o arquivo php-erros.log.
exit /b

:servidor_ok
set "URL=http://localhost:%PORTA%/%NOME_PASTA%/%NOME_APP%/"
echo    Servidor no ar.
echo.
echo      URL: %URL%
echo      Para encerrar: opcao 6 deste menu (ou parar.cmd).
echo.
if not defined SEM_NAV start "" "%URL%"
exit /b

rem ------------------------------------------------------------------
rem 6. parar o site
rem ------------------------------------------------------------------
:f_parar
echo.
set "PAROU=0"
if exist "%PORTARQ%" (
    set "P_PARAR="
    set /p P_PARAR=<"%PORTARQ%"
    if defined P_PARAR (
        for /f "tokens=5" %%A in ('netstat -ano ^| findstr /R /C:":!P_PARAR! .*LISTENING"') do (
            taskkill /PID %%A /F >nul 2>&1
            set "PAROU=1"
        )
    )
    del "%PORTARQ%" >nul 2>&1
)
if "!PAROU!"=="1" (
    echo    Site encerrado.
) else (
    echo    O site nao estava rodando.
)
exit /b

rem ------------------------------------------------------------------
rem 8. reset
rem ------------------------------------------------------------------
:f_reset
echo.
call :mysql_online
if not "!MYSQL_ONLINE!"=="1" (
    echo    O MySQL esta parado. Use a opcao 2 para subir.
    exit /b
)
if not exist "%APP%\Banco\nathus.sql" (
    echo    Falta o arquivo Projeto\Banco\nathus.sql
    exit /b
)
if not exist "%APP%\Banco\perfis-demo.sql" (
    echo    Falta o arquivo Projeto\Banco\perfis-demo.sql
    exit /b
)

echo    ATENCAO: isso APAGA o banco nathus inteiro e reimporta do zero.
echo    Tudo que foi cadastrado depois do setup sera perdido.
echo    Um backup e salvo antes, na pasta backups.
echo.
set "CONF="
set /p CONF=  Digite APAGAR para confirmar: 
if /i not "!CONF!"=="APAGAR" (
    echo    Cancelado. Nada foi apagado.
    exit /b
)

for /f "delims=" %%T in ('powershell -NoProfile -Command "Get-Date -Format yyyyMMdd-HHmm"') do set "STAMP=%%T"
if not defined STAMP set "STAMP=backup"
if defined MYSQLDUMP (
    if not exist "%APP%\backups" mkdir "%APP%\backups"
    echo    Salvando backup em backups\nathus-!STAMP!.sql
    "%MYSQLDUMP%" -u root --default-character-set=utf8mb4 --databases nathus > "%APP%\backups\nathus-!STAMP!.sql" 2>nul
)

echo    Apagando o banco nathus...
"%MYSQL%" -u root -e "DROP DATABASE IF EXISTS nathus;" >> "%LOG%" 2>&1

echo    Reimportando estrutura e perfis demo...
"%MYSQL%" -u root --default-character-set=utf8mb4 --force < "%APP%\Banco\nathus.sql" >> "%LOG%" 2>&1
"%MYSQL%" -u root --default-character-set=utf8mb4 --force < "%APP%\Banco\perfis-demo.sql" >> "%LOG%" 2>&1

rem Os ids mudam no reset: quem estava logado ficaria com o id antigo.
rem Apagar as sessoes do PHP forca todo mundo a entrar de novo.
if defined XAMPP if exist "%XAMPP%\tmp" del /q "%XAMPP%\tmp\sess_*" >nul 2>&1
echo    Sessoes abertas encerradas (todos precisam entrar de novo).

set "DEMO="
"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM nathus.cliente WHERE email LIKE '%%@nathus.com';" > "%TMPF%" 2>nul
set /p DEMO=<"%TMPF%"
set "PROMO="
"%MYSQL%" -u root -N -e "SELECT COUNT(*) FROM nathus.promotor;" > "%TMPF%" 2>nul
set /p PROMO=<"%TMPF%"
if not defined DEMO set "DEMO=0"
if "!DEMO!"=="0" (
    echo    ERRO: o banco nao foi reimportado. Veja o final de setup.log.
    exit /b
)
echo    Pronto: banco recriado com !DEMO! contas demo e !PROMO! promotores.
exit /b

rem ------------------------------------------------------------------
rem utilidades
rem ------------------------------------------------------------------
:achar_programas
set "XAMPP="
for %%D in ("C:\xampp" "D:\xampp" "E:\xampp" "%ProgramFiles%\xampp" "%ProgramFiles(x86)%\xampp") do (
    if not defined XAMPP if exist "%%~D\php\php.exe" set "XAMPP=%%~D"
)

set "PHP="
if defined XAMPP set "PHP=%XAMPP%\php\php.exe"
if not defined PHP for /f "delims=" %%P in ('where php 2^>nul') do if not defined PHP set "PHP=%%P"

set "MYSQL="
set "MYSQLD="
set "MYSQLADMIN="
set "MYSQLDUMP="
if defined XAMPP (
    if exist "%XAMPP%\mysql\bin\mysql.exe" set "MYSQL=%XAMPP%\mysql\bin\mysql.exe"
    if exist "%XAMPP%\mysql\bin\mysqld.exe" set "MYSQLD=%XAMPP%\mysql\bin\mysqld.exe"
    if exist "%XAMPP%\mysql\bin\mysqladmin.exe" set "MYSQLADMIN=%XAMPP%\mysql\bin\mysqladmin.exe"
    if exist "%XAMPP%\mysql\bin\mysqldump.exe" set "MYSQLDUMP=%XAMPP%\mysql\bin\mysqldump.exe"
)
if not defined MYSQL for /f "delims=" %%P in ('where mysql 2^>nul') do if not defined MYSQL set "MYSQL=%%P"
if not defined MYSQLADMIN for /f "delims=" %%P in ('where mysqladmin 2^>nul') do if not defined MYSQLADMIN set "MYSQLADMIN=%%P"
if not defined MYSQLDUMP for /f "delims=" %%P in ('where mysqldump 2^>nul') do if not defined MYSQLDUMP set "MYSQLDUMP=%%P"
exit /b

:mysql_online
set "MYSQL_ONLINE=0"
if not defined MYSQLADMIN exit /b
"%MYSQLADMIN%" -u root ping >nul 2>&1
if not errorlevel 1 set "MYSQL_ONLINE=1"
exit /b