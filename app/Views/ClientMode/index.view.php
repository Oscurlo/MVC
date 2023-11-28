<?php
# Includes your controller
$Info = [
    "php version" => ">= 8+",
    "Author" => "Esteban Serna Palacios",
    "examples" => [
        "https://github.com/Oscurlo/MVC_AdminLTE"
    ]
];


$InfoJSON = json_encode($Info, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

echo <<<HTML
<div id="wrapper">
    <div class="col">
        <pre>{$InfoJSON}</pre>
    </div>
</div>
HTML;
