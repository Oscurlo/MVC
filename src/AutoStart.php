<?php

namespace MyApp;

use System\Config\AppConfig;

use Exception;

class AutoStart
{
    static function initWebSocket()
    {
        try {
            $commands = [];
            $commands[] = "CD " . AppConfig::BASE_FOLDER;
            $commands[] = "composer dump-autoload";
            $commands[] = "composer update";
            $commands[] = "php " . AppConfig::BASE_FOLDER . "/bin/chat-server.php";

            $command = implode(" & ", $commands);

            $port = AppConfig::WEBSOCKET["PORT"];

            $BAT_COMMAND = <<<BAT
            @echo off

            setlocal

            REM Puerto
            set "port={$port}"

            REM Comando
            set "comando={$command}"

            REM Validar puerto
            netstat -ano | findstr ":%puerto%" >nul

            REM Validar si consiguió el puerto
            if %errorlevel% equ 0 (
                echo Puerto en uso %puerto%
            ) else (
                %comando%
            )

            endlocal
            BAT;

            $BAT_FILE = fopen(AppConfig::BASE_FOLDER . "/bin/AutoStart.bat", "w");
            fwrite($BAT_FILE, $BAT_COMMAND);
            fclose($BAT_FILE);

            self::executeCommand("start cmd.exe @cmd /k \"" . AppConfig::BASE_FOLDER . "/bin/AutoStart.bat\"");

            return true;
        } catch (Exception $th) {
            return false;
        }
    }

    protected static function executeCommand($command)
    {
        if (substr(php_uname(), 0, 7) === "windows") pclose(popen("start /B {$command}", "r"));
        else exec("{$command} > /dev/null &");
    }
}
