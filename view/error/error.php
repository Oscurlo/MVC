<?php

use System\Config\AppConfig;

$Error = "Error: {$error->getMessage()}";

$style = implode("", array_map(function ($herf) {
    $href = AppConfig::BASE_SERVER . "/{$herf}";
    return <<<HTML
        <link rel="stylesheet" href="{$href}">
    HTML;
}, [
    "AdminLTE/plugins/fontawesome-free/css/all.min.css",
    "AdminLTE/dist/css/adminlte.min.css",
]));

$home = AppConfig::BASE_SERVER;

echo <<<HTML
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    {$style}
</head>

<body class="hold-transition login-page">
    <section class="content">
        <div class="error-page">
            <h2 class="text-{$is}" style="float: left;">¯\_(ツ)_/¯</h2>

            <div class="error-content">
                <h3><i class="fas fa-exclamation-triangle text-{$is}"></i>¡Ups! Algo salió mal.</h3>
                <p>
                    <b>{$Error}</b><br>
                    Trabajaremos para solucionarlo de inmediato. Mientras tanto, puede <a href="{$home}">regresar al panel.</a>
                </p>
            </div>
        </div>
    </section>
</body>

</html>
HTML;
