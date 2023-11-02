<?php

use System\Config\AppConfig;
use System\Config\ReplaceMaster;

// Manejo de errores
set_error_handler(function ($severity, $message, $file, $line) {
    // Este error no está incluido en error_reporting
    if (!(error_reporting() & $severity))
        return;
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function ($error) {
    // Manejo de excepciones
    $is = "danger";
    include __DIR__ . "/view/error/error.php";
});

try {
    include_once __DIR__ . "/vendor/autoload.php";

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

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

    include __DIR__ . "/template/" . AppConfig::VIEW_MODE . ".php";
} catch (Exception $e) {
    // Manejo de excepciones generales
    echo "Excepción: {$e->getMessage()}";
}
