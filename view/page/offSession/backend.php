<?php
/*-- 2023-10-22 16:41:03 --*/

use Controller\Session;

include_once "C:/xampp/htdocs/MVC/vendor/autoload.php";


$action = $_GET["action"] ?? false;

$SESSION = new Session;

switch (strtoupper($action)) {
    case 'LOGIN':
        $user = $_POST["data"]["user"] ?? "";
        $pass = $_POST["data"]["pass"] ?? "";
        try {
            echo json_encode([
                "status" => $SESSION->startSession($user, $pass)
            ], JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            echo $th->getMessage();
        }
        break;
    case 'REGISTER':
        $data = array_merge($_POST, $_FILES);
        $result = $SESSION->registerUser($data);

        echo json_encode([
            "status" => !empty($result["lastInsertId"] ?? false)
        ], JSON_UNESCAPED_UNICODE);

        break;
    default:
        echo json_encode([
            "test" => true
        ], JSON_UNESCAPED_UNICODE);
        break;
}
