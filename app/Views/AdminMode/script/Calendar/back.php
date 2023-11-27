<?php

use Controller\Calendar;
use System\Config\AppConfig;

include_once explode("\\app\\", __DIR__)[0] . "/vendor/autoload.php";

$appConfig = new AppConfig; # session, timezone
$calendar = new Calendar;

$action = $_GET["action"] ?? false;

switch ($action) {
    case 'getEvents':
        $start = $_GET["start"];
        $end = $_GET["end"];
        echo json_encode($calendar->getEvents($start, $end, true), JSON_UNESCAPED_UNICODE);
        break;
    case 'formAddEvent':
        echo $calendar->formEvent();
        break;
    case 'formModifyEvent':
        $id = $_POST["id"] ?? false;
        echo $calendar->formEvent($id);
        break;
    case 'showEvent':
        $id = $_POST["id"] ?? false;
        echo $calendar->showEvent($id);
        break;
    case 'addEvent':
    case 'modifyEvent':
        $id = $_GET["id"] ?? false;
        $response = $calendar->$action($_POST, $id);

        unset($response["query"]);

        $num = $response["lastInsertId"] ?? $response["rowCount"];

        echo json_encode([
            "status" => is_numeric($num)
        ], JSON_UNESCAPED_UNICODE);
        break;
    default:
        echo json_encode(["error" => "action is undefined"]);
        break;
}
