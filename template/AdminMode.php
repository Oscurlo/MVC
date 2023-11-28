<?php

use Model\Route;
use System\Config\AppConfig;

$route = new Route;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="<?= AppConfig::CHARSET ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= AppConfig::COMPANY["NAME"] ?></title>
</head>

<body>
    <?php
    $route->view(false);
    $route->loadComponets();
    ?>
</body>

</html>