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
    "AdminLTE/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css",
    "AdminLTE/plugins/datatables-responsive/css/responsive.bootstrap4.min.css",
    "AdminLTE/plugins/datatables-buttons/css/buttons.bootstrap4.min.css",
    "AdminLTE/plugins/fullcalendar/main.css",
    "AdminLTE/plugins/daterangepicker/daterangepicker.css",
    "AdminLTE/plugins/icheck-bootstrap/icheck-bootstrap.min.css",
    "AdminLTE/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css",
    "AdminLTE/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css",
    "AdminLTE/plugins/select2/css/select2.min.css",
    "AdminLTE/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css",
    "AdminLTE/plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css",
    "AdminLTE/plugins/bs-stepper/css/bs-stepper.min.css",
    "AdminLTE/plugins/dropzone/min/dropzone.min.css",
    "AdminLTE/plugins/codemirror/codemirror.css",
    "AdminLTE/plugins/codemirror/theme/ayu-dark.css",
    "AdminLTE/dist/css/adminlte.min.css"
];

$script = [
    "AdminLTE/plugins/jquery/jquery.min.js",
    "AdminLTE/plugins/jquery-ui/jquery-ui.min.js",
    "AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js",
    "AdminLTE/plugins/sweetalert2/sweetalert2.all.min.js",
    "AdminLTE/plugins/bs-custom-file-input/bs-custom-file-input.min.js",
    "AdminLTE/plugins/select2/js/select2.full.min.js",
    "AdminLTE/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js",
    "AdminLTE/plugins/moment/moment.min.js",
    "AdminLTE/plugins/fullcalendar/main.js",
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
    "AdminLTE/plugins/codemirror/codemirror.js",
    "AdminLTE/plugins/codemirror/mode/css/css.js",
    "AdminLTE/plugins/codemirror/mode/xml/xml.js",
    "AdminLTE/plugins/codemirror/mode/htmlmixed/htmlmixed.js",
    "AdminLTE/plugins/codemirror/mode/php/php.js",
    "AdminLTE/dist/js/adminlte.min.js",
    "assets/menu/menu.js",
    "assets/main.js",
    AppConfig::USE_WEBSOCKET ? "assets/WebSocket/WebSocket.js" : "",
    "assets/forDatatable/forDatatable.js"
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
    "WEBSOCKET" => AppConfig::WEBSOCKET,
    "TIMEZONE" => AppConfig::TIMEZONE,
    "CHARSET" => AppConfig::CHARSET,
    "LANGUAGE" => AppConfig::LANGUAGE,
    "CURRENCY" => AppConfig::CURRENCY,
    "UPS_CODE" => AppConfig::UPS_CODE,
    "LOCALE" => AppConfig::LOCALE
], JSON_UNESCAPED_UNICODE);

$userInfo = json_encode([
    "id" => $_SESSION["id"] ?? null,
    "name" => $_SESSION["name"] ?? null,
    "email" => $_SESSION["email"] ?? null,
    "icon" => $_SESSION["files"] ?? null,
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
    <style>
        .CodeMirror-error-line {
            background: #ffecec;
            text-decoration: red wavy underline;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
    <div class="wrapper">
        <?php include(__DIR__ . "/shared/menu.php") ?>
        <div class="content-wrapper">
            <?php $route->view(!AppConfig::PRODUCTION) ?>
        </div>
        <aside class="control-sidebar control-sidebar-dark"></aside>
    </div>
    <script>
        const CONFIG = (find = null) => {
            const array = <?= $validConfig ?>;
            return find === null ? array : array[find] ?? null
        }

        sessionStorage.setItem("userInfo", `{$userInfo}`)
    </script>
    <?= $showComponet($script, "js") ?>
    <?= $route->loadComponets() ?>
</body>

</html>