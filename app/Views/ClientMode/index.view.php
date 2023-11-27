<?php

use System\Config\AppConfig;

$apikey = strtoupper(implode("-", str_split(bin2hex(random_bytes(12)), 4)));

$showJSON = function ($array, $title = "Debug") {
    if (is_array($array) && !empty($array)) {
        $JSON = json_encode($array, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return <<<HTML
        <hr class="my-4">
        <h2>{$title}</h2>
        <pre>{$JSON}</pre>
        HTML;
    }
};

?>

<body class="">
    <div class="container mt-5">
        <div class="jumbotron">
            <h1 class="display-4">MVC by <b>Oscurlo</b></h1>
            <p class="lead">Una implementación simple del patrón Modelo-Vista-Controlador (MVC) utilizando AdminLTE.</p>
            <hr class="my-4">
            <p>Este proyecto muestra cómo estructurar tu aplicación web usando MVC y aprovechar las utilidades de diseño de Bootstrap.</p>
            <a href="<?= AppConfig::BASE_SERVER ?>/login" class="btn btn-primary btn-lg" role="button">Ver Proyecto</a>
            <a href="https://github.com/Oscurlo/MVC" class="btn btn-primary btn-lg" target="_blank" role="button">Ver Proyecto en GitHub</a>
            <?= $showJSON($_SESSION, "Variables de session") ?>
            <?= $showJSON($_ENV, "Variables de entorno") ?>
            <?= $showJSON($_SERVER, "Variables del servidor") ?>
        </div>
    </div>
</body>