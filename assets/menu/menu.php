<?php

use Controller\seeWithHTTPRequest;

include_once "C:/xampp/htdocs/MVC/vendor/autoload.php";


$view = $_POST["view"] ?? "";

seeWithHTTPRequest::httpView($view);
