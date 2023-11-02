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
    "AdminLTE/plugins/sweetalert2/sweetalert2.min.css",
    "AdminLTE/plugins/icheck-bootstrap/icheck-bootstrap.min.css",
    "AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css",
    "AdminLTE/plugins/datatables-responsive/css/responsive.bootstrap4.min.css",
    "AdminLTE/plugins/datatables-buttons/css/buttons.bootstrap4.min.css",
    "AdminLTE/plugins/daterangepicker/daterangepicker.css",
    "AdminLTE/plugins/icheck-bootstrap/icheck-bootstrap.min.css",
    "AdminLTE/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css",
    "AdminLTE/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css",
    "AdminLTE/plugins/select2/css/select2.min.css",
    "AdminLTE/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css",
    "AdminLTE/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css",
    "AdminLTE/plugins/bs-stepper/css/bs-stepper.min.css",
    "AdminLTE/plugins/dropzone/min/dropzone.min.css",
    "AdminLTE/dist/css/adminlte.min.css"
]));

$script = implode("", array_map(function ($s) use ($SERVER, $FOLDER) {
    $filePath = "{$FOLDER}/{$s}";
    return file_exists($filePath) ? "<script src=\"{$SERVER}/{$s}\"></script>" : "";
}, [
    "AdminLTE/plugins/jquery/jquery.min.js",
    "AdminLTE/plugins/jquery-ui/jquery-ui.min.js",
    "AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js",
    "AdminLTE/plugins/sweetalert2/sweetalert2.all.min.js",
    "AdminLTE/plugins/bs-custom-file-input/bs-custom-file-input.min.js",
    "AdminLTE/plugins/select2/js/select2.full.min.js",
    "AdminLTE/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js",
    "AdminLTE/plugins/moment/moment.min.js",
    "AdminLTE/plugins/inputmask/jquery.inputmask.min.js",
    "AdminLTE/plugins/daterangepicker/daterangepicker.js",
    "AdminLTE/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js",
    "AdminLTE/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js",
    "AdminLTE/plugins/bootstrap-switch/js/bootstrap-switch.min.js",
    "AdminLTE/plugins/dropzone/min/dropzone.min.js",
    "AdminLTE/plugins/datatables/jquery.dataTables.min.js",
    "AdminLTE/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js",
    "AdminLTE/plugins/datatables-responsive/js/dataTables.responsive.min.js",
    "AdminLTE/plugins/datatables-responsive/js/responsive.bootstrap4.min.js",
    "AdminLTE/plugins/datatables-buttons/js/dataTables.buttons.min.js",
    "AdminLTE/plugins/datatables-buttons/js/buttons.bootstrap4.min.js",
    "AdminLTE/plugins/jszip/jszip.min.js",
    "AdminLTE/plugins/pdfmake/pdfmake.min.js",
    "AdminLTE/plugins/pdfmake/vfs_fonts.js",
    "AdminLTE/plugins/datatables-buttons/js/buttons.html5.min.js",
    "AdminLTE/plugins/datatables-buttons/js/buttons.print.min.js",
    "AdminLTE/plugins/datatables-buttons/js/buttons.colVis.min.js",
    "AdminLTE/dist/js/adminlte.min.js",
    "assets/menu/menu.js",
    "assets/main.js"

]));

$forJS = json_encode([
    "BASE_SERVER" => AppConfig::BASE_SERVER
], JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="<?= $LANG ?>">

<head>
    <meta charset="<?= AppConfig::CHARSET ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $COMPANY["NAME"] ?></title>
    <link rel="icon" href="<?= $COMPANY['LOGO'] ?>" type="image/*" sizes="16x16">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&amp;display=fallback">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <?= $styles ?>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
    <div class="wrapper">
        <?php include(__DIR__ . "/shared/menu.php") ?>
        <div class="content-wrapper">
            <?php $route->view(!AppConfig::PRODUCTION) ?>
        </div>
        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>

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
</body>

</html>