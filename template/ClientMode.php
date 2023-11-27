<?php

use Model\Route;
use System\Config\AppConfig;

$route = new Route;

$SERVER = AppConfig::BASE_SERVER;
$FOLDER = AppConfig::BASE_FOLDER;
$LANG = AppConfig::LANGUAGE;
$COMPANY = AppConfig::COMPANY;

$styles = [
    "AdminLTE/plugins/fontawesome-free/css/all.min.css",
    "AdminLTE/plugins/sweetalert2/sweetalert2.min.css",
    "AdminLTE/plugins/icheck-bootstrap/icheck-bootstrap.min.css",
    "AdminLTE/dist/css/adminlte.min.css"
];

$script = [
    "AdminLTE/plugins/jquery/jquery.min.js",
    "AdminLTE/plugins/sweetalert2/sweetalert2.all.min.js",
    "AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js",
    "AdminLTE/plugins/bs-custom-file-input/bs-custom-file-input.min.js",
    "AdminLTE/dist/js/adminlte.min.js",
    "assets/main.js",
];

$showComponet = function ($array, $type) {
    return implode("\n", array_filter(
        array_map(function ($file) use ($type) {
            $SERVER = AppConfig::BASE_SERVER;
            $FOLDER = AppConfig::BASE_FOLDER;

            $pathServer = "{$SERVER}/{$file}";
            $pathFolder = "{$FOLDER}/{$file}";

            if (file_exists($pathFolder)) return $type == "js"
                ? "<script src=\"{$pathServer}\"></script>"
                : "<link rel=\"stylesheet\" href=\"{$pathServer}\">";
            else return null;
        }, array_filter(
            array_unique($array),
            function ($file) {
                return !empty($file);
            }
        )),
        function ($file) {
            return !empty($file);
        }
    ));
};

$validConfig = json_encode([
    "BASE_SERVER" => AppConfig::BASE_SERVER,
], JSON_UNESCAPED_UNICODE);

?>
<!DOCTYPE html>
<html lang="<?= $LANG ?>">

<head>
    <meta charset="<?= AppConfig::CHARSET ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $COMPANY["NAME"] ?></title>
    <base href="<?= $SERVER ?>">
    <link rel="icon" href="<?= $COMPANY['LOGO'] ?>" type="image/*" sizes="16x16">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&amp;display=fallback">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <?= $showComponet($styles, "css") ?>
</head>

<?php $route->view(false) ?>

<script>
    const CONFIG = (find = null) => {
        const array = <?= $validConfig ?>;
        return find === null ? array : array[find] ?? null
    }
</script>
<?= $showComponet($script, "js") ?>
<?= $route->loadComponets() ?>

</html>