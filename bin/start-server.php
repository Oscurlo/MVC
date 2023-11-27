<?php

// use MyApp\AutoStart;

require dirname(__DIR__) . "/vendor/autoload.php";

// echo json_encode(
//     [
//         "status" => AutoStart::initWebSocket()
//     ],
//     JSON_UNESCAPED_UNICODE
// );


use System\Config\AppConfig;

// comandos
// 1. Entra a la carpeta del proyecto
// 2. Actualiza composer
// 3. Ejecuta el servidor
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
netstat -ano | findstr ":%port%" >nul

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

executeCommand('start cmd.exe @cmd /k "' . AppConfig::BASE_FOLDER . '/bin/AutoStart.bat"');

function executeCommand($cmd)
{
    if (substr(php_uname(), 0, 7) == "Windows") pclose(popen("start /B {$cmd}", "r"));
    else exec("{$cmd} > /dev/null &");
}
