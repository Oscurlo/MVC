<?php


use System\Config\AppConfig;
use System\Config\ReplaceMaster;

# Manejo de errores
set_error_handler(function ($severity, $message, $file, $line) {
    # Este error no está incluido en error_reporting
    if (!(error_reporting() & $severity))
        return;
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function ($error) {
    # Manejo de excepciones
    echo "Error: {$error->getMessage()}";
});

try {

    include_once __DIR__ . "/vendor/autoload.php";

    header("Content-type: text/html; charset=" . AppConfig::CHARSET);

    if (AppConfig::CACHE_CONTROL) {
        $lastModified = filemtime(__FILE__);
        $etag = md5_file(__FILE__);

        header("Last-Modified: " . gmdate("D, d M Y H:i:s", $lastModified) . " GMT");
        header("Etag: {$etag}");
        header("Cache-Control: public, max-age=3600");

        $ifModifiedSince = $_SERVER["HTTP_IF_MODIFIED_SINCE"] ?? false;
        $ifNoneMatch = trim($_SERVER["HTTP_IF_NONE_MATCH"] ?? false);

        if (@strtotime($ifModifiedSince) == $lastModified || $ifNoneMatch == $etag) {
            header("HTTP/1.1 304 Not Modified");
            exit;
        }
    }
    # template for views
    $pathView = __DIR__ . "/template/" . AppConfig::VIEW_MODE . ".php";

    if (file_exists($pathView)) include $pathView;
    else throw new Exception("failed to include template");

    // echo "<pre>", json_encode(ReplaceMaster::replace("/", "/"), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), "</pre>";
} catch (Exception $e) {
    # Manejo de excepciones generales
    echo "Excepción: {$e->getMessage()}";
}
