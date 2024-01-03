<?php

use Config\Route;
use Config\Template;
use System\Config\AppConfig;

$route = new Route;

$style = [
    "https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback",
    "https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css",
    "plugins/fontawesome-free/css/all.min.css",
    "plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css",
    "plugins/icheck-bootstrap/icheck-bootstrap.min.css",
    "plugins/jqvmap/jqvmap.min.css",
    "dist/css/adminlte.min.css",
    "plugins/overlayScrollbars/css/OverlayScrollbars.min.css",
    "plugins/daterangepicker/daterangepicker.css",
    "plugins/summernote/summernote-bs4.min.css",
    "test"
];

$script = [
    "plugins/jquery/jquery.min.js",
    "plugins/jquery-ui/jquery-ui.min.js",
    "plugins/bootstrap/js/bootstrap.bundle.min.js",
    "plugins/chart.js/Chart.min.js",
    "plugins/sparklines/sparkline.js",
    "plugins/jqvmap/jquery.vmap.min.js",
    "plugins/jqvmap/maps/jquery.vmap.usa.js",
    "plugins/jquery-knob/jquery.knob.min.js",
    "plugins/moment/moment.min.js",
    "plugins/daterangepicker/daterangepicker.js",
    "plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js",
    "plugins/summernote/summernote-bs4.min.js",
    "plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js",
    "dist/js/adminlte.js",
    "test"
]

?>

<!DOCTYPE html>
<html lang="<?= AppConfig::LANGUAGE ?>">

<head>
    <meta charset="<?= AppConfig::CHARSET ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Lorem ipsum dolor sit.">
    <meta name="keywords" content="Lorem, ipsum dolor, Veritatis, debitis quas, Ipsa, tenetur suscipit!">
    <meta name="author" content="Esteban serna palacios">
    <title><?= AppConfig::COMPANY["NAME"] ?></title>
    <?= Template::styles($style) ?>
    <base href="<?= trim(AppConfig::BASE_SERVER, "/") . "/" ?>">
</head>

<body>
    <script type="module" src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs"></script>
    <?php $route->view(false) ?>
    <?= Template::scripts($script) ?>
    <?= $route->loadComponets() ?>
</body>

</html>