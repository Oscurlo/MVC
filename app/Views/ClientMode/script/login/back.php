<?php

use Controller\Session;

include_once explode("\\app\\", __DIR__)[0] . "/vendor/autoload.php";

$Session = new Session();

$user = $_POST["data"]["user"] ?? "";
$pass = $_POST["data"]["pass"] ?? "";

try {
    echo json_encode([
        "status" => $Session->startSession($user, $pass)
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $th) {
    echo json_encode([
        "status" => false,
        "error" => $th->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
