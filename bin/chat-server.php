<?php

use System\Config\AppConfig;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;

require dirname(__DIR__) . "/vendor/autoload.php";

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    AppConfig::WEBSOCKET["PORT"]
);

$server->run();
