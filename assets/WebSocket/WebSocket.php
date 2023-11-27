<?php

use Controller\Chat_test;

include_once dirname(dirname(__DIR__)) . "/vendor/autoload.php";

$action = $_GET["action"] ?? false;

$chat = new Chat_test;

switch ($action) {
    case 'sendMessage':
        $data = $_POST ?? [];
        $response = $chat->addChat($data);

        unset($response["query"]);

        $response["status"] = !empty($response["lastInsertId"]);

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        break;
    case 'showChat':
        echo $chat->showChat($_SESSION["id"]);
        break;
    case 'showList':
        $users = $_POST["users"] ?? [];
        echo $chat->showContacts($users);
        break;
    case 'showMessage':
        $from = $_POST["from"] ?? 0;
        $to = $_POST["to"] ?? 0;
        echo $chat->showMessages($from, $to);
        break;
    default:
        echo json_encode(["error" => "action is undefined"]);
        break;
}
