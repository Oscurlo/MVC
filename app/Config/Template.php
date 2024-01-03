<?php

namespace Config;

use System\Config\AppConfig;

class Template
{
    const ROUTE_STYLE_BASE = AppConfig::BASE_ADMIN_LTE_3;

    /**
     * Plantilla para contenido de la pagina
     * 
     * @deprecated
     * @param String $template
     * @param array $styles
     * @param array $scripts
     * 
     * @return String Plantilla con los estilo
     */
    static function content(String $template, array $styles = [], array $scripts = []): String
    {

        if (strpos($template, "@style") !== false) str_replace("@style", self::styles($styles), $template);
        if (strpos($template, "@script") !== false) str_replace("@script", self::scripts($scripts), $template);

        return $template;
    }

    static function styles(array $arr): String
    {
        $arr = array_map(function ($file) {
            if (strpos($file, "http") === false) {
                $file = self::ROUTE_STYLE_BASE . "/$file";
                $file = trim(str_replace([AppConfig::BASE_FOLDER, AppConfig::BASE_SERVER], "", $file), "/");
            }

            return <<<HTML
            <link rel="stylesheet" href="{$file}">
            HTML;
        }, array_filter($arr, fn ($file) => self::filterFileExists($file)));

        return implode("\n", $arr);
    }

    static function scripts(array $arr): String
    {
        return str_replace(
            ["<link", "href=", "rel=\"stylesheet\"", ">"],
            ["<script", "src=", "", "></script>"],
            self::styles($arr)
        );
    }

    protected static function filterFileExists(String $file): bool
    {
        if (strpos($file, "http") !== false) return true;

        $file = str_replace(self::ROUTE_STYLE_BASE, "", trim(trim($file, "\\"), "/"));

        return file_exists(self::ROUTE_STYLE_BASE . "/{$file}");
    }
}
