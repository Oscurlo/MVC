<?php

use Model\Route;

$route = new Route();

$SERVER = BASE_SERVER;
$FOLDER = BASE_FOLDER;
$LANG = LANGUAGE;

$styles = implode("", array_map(function ($s) use ($SERVER, $FOLDER) {
    $filePath = "{$FOLDER}/{$s}";
    return file_exists($filePath) ? "<link rel=\"stylesheet\" href=\"{$SERVER}/{$s}\">" : "";
}, [
    "/AdminLTE/plugins/fontawesome-free/css/all.min.css",
    "/AdminLTE/plugins/icheck-bootstrap/icheck-bootstrap.min.css",
    "/AdminLTE/dist/css/adminlte.min.css"
]));

$script = implode("", array_map(function ($s) use ($SERVER, $FOLDER) {
    $filePath = "{$FOLDER}/{$s}";
    return file_exists($filePath) ? "<script src=\"{$SERVER}/{$s}\"></script>" : "";
}, [
    "/AdminLTE/plugins/jquery/jquery.min.js",
    "/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js",
    "/AdminLTE/dist/js/adminlte.min.js"
]));


echo <<<HTML
<!DOCTYPE html>
<html lang="{$LANG}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    {$styles}
</head>

<body class="hold-transition login-page">
    {$route->view(true)}
</body>

{$script}
{$route->loadComponets()}
</html>
HTML;
