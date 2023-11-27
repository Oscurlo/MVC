<?php

use Controller\Session;

include_once explode("\\app\\", __DIR__)[0] . "/vendor/autoload.php";

$Session = new Session();

$data = array_merge($_POST, $_FILES);
$result = $Session->registerUser($data);

echo json_encode([
    "status" => !empty($result["lastInsertId"] ?? false)
], JSON_UNESCAPED_UNICODE);
