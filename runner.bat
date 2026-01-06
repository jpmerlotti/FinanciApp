@echo off
TITLE FinanciApp Launcher
CLS

ECHO =====================================================
ECHO      FinanciApp - Sistema Financeiro Pessoal
ECHO =====================================================
ECHO.

:: 1. Check if PHP is available
php -v >nul 2>&1
IF %ERRORLEVEL% NEQ 0 (
    ECHO [ERRO] PHP nao encontrado no sistema.
    ECHO Por favor, instale o PHP 8.2+ e adicione ao PATH.
    PAUSE
    EXIT /B
)

:: 2. Create Database if not exists
IF NOT EXIST "database\database.sqlite" (
    ECHO [SETUP] Criando banco de dados local...
    type nul > "database\database.sqlite"
    ECHO [SETUP] Rodando migracoes...
    call php artisan migrate --force
)

:: 3. Start Server
ECHO.
ECHO [INFO] Iniciando servidor em http://localhost:8000 ...
ECHO [INFO] Pressione CTRL+C para encerrar.
ECHO.

:: Open Browser (Start in background after 2 seconds)
START /B "" cmd /c "timeout /t 2 >nul & start http://localhost:8000/admin"

:: Start Artisan Serve
php artisan serve --port=8000

PAUSE
