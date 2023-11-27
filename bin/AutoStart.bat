@echo off

setlocal

REM Puerto
set "port=8080"

REM Comando
set "comando=CD {$_SESSION["BASE_FOLDER"]} & composer dump-autoload & composer update & php {$_SESSION["BASE_FOLDER"]}/bin/chat-server.php"

REM Validar puerto
netstat -ano | findstr ":%port%" >nul

REM Validar si consiguió el puerto
if %errorlevel% equ 0 (
    echo Puerto en uso %puerto%
) else (
    %comando%
)

endlocal