<?php

use Controller\seeWithHTTPRequest;

include_once "C:/xampp/htdocs/MVC/vendor/autoload.php";

$action = $_GET["action"] ?? false;
$view = $_POST["view"] ?? "";

switch ($action) {
    case 'checkSession':
        session_start();

        echo json_encode(["status" => isset($_SESSION["usuario"])]);
        break;
    case 'view':
        seeWithHTTPRequest::httpView($view);
        break;
    default:
        echo "action is undefined";
        break;
}
