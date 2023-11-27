<?php

use Controller\seeWithHTTPRequest;
use Controller\Session;

include_once dirname(dirname(__DIR__)) . "/vendor/autoload.php";

$action = $_GET["action"] ?? false;
$view = $_POST["view"] ?? false;

switch ($action) {
    case 'checkSession':
        session_start();

        echo json_encode(["status" => isset($_SESSION["SESSION_MODE"])]);
        break;
    case 'view':
        seeWithHTTPRequest::httpView($view);
        break;
    case 'disconnect':
        session_start();

        Session::destroy();
        break;
    default:
        echo "action is undefined";
        break;
}
