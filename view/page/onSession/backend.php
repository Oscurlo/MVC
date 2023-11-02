<?php
# Includes your controller

/*-- 2023-10-27 14:31:22 --*/

use Controller\ToDoList;

include_once "C:/xampp/htdocs/MVC/vendor/autoload.php";

$action = $_GET["action"] ?? false;

$ToDoList = new ToDoList();

switch ($action) {
    case 'newCategory':
    case 'newToDoList':
        $method = [
            "newCategory" => "addCategory",
            "newToDoList" => "addToDoList"
        ][$action];

        try {
            $data = $_POST ?? [];
            $result = $ToDoList->$method($data);

            unset($result["query"]);

            $result["status"] = !empty($result["lastInsertId"]);

            echo json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            echo json_encode(["status" => false], JSON_UNESCAPED_UNICODE);
        }
        break;
    default:
        echo json_encode(["error" => "action is undefined"]);
        break;
}
