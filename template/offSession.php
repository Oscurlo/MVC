<?php

use Model\Route;
use System\Config\AppConfig;

$route = new Route();

$SERVER = AppConfig::BASE_SERVER;
$FOLDER = AppConfig::BASE_FOLDER;
$LANG = AppConfig::LANGUAGE;
$COMPANY = AppConfig::COMPANY;

$styles = implode("", array_map(function ($s) use ($SERVER, $FOLDER) {
    $filePath = "{$FOLDER}/{$s}";
    return file_exists($filePath) ? "<link rel=\"stylesheet\" href=\"{$SERVER}/{$s}\">" : "";
}, [
    "AdminLTE/plugins/fontawesome-free/css/all.min.css",
    "AdminLTE/plugins/icheck-bootstrap/icheck-bootstrap.min.css",
    "AdminLTE/dist/css/adminlte.min.css"
]));

$script = implode("", array_map(function ($s) use ($SERVER, $FOLDER) {
    $filePath = "{$FOLDER}/{$s}";
    return file_exists($filePath) ? "<script src=\"{$SERVER}/{$s}\"></script>" : "";
}, [
    "AdminLTE/plugins/jquery/jquery.min.js",
    "AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js",
    "AdminLTE/plugins/bs-custom-file-input/bs-custom-file-input.min.js",
    "AdminLTE/dist/js/adminlte.min.js"
]));

$forJS = json_encode([
    "BASE_SERVER" => AppConfig::BASE_SERVER
], JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="<?= $LANG ?>">

<head>
    <html lang="<?= $LANG ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $COMPANY["NAME"] ?></title>
    <link rel="icon" href="<?= $COMPANY['LOGO'] ?>" type="image/*" sizes="16x16">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&amp;display=fallback">
    <?= $styles ?>
</head>

<?php $route->view(true) ?>

<?= $script ?>
<?= $route->loadComponets() ?>
<script>
    <?= <<<JS
    const CONFIG = (find = null) => {
        const array = {$forJS}
        return find === null ? array : array[find] ?? null
    }
    JS ?>
</script>

</html>