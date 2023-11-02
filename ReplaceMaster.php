<?php

namespace System\Config;

/**
 * Clase para realizar reemplazos en archivos y directorios.
 *
 * @deprecated Esta clase es peligrosa y solo debe usarse para corregir rutas.
 */
class ReplaceMaster
{
    /**
     * Realiza reemplazos en archivos y directorios.
     *
     * @param string $search Texto a buscar.
     * @param string $replace Texto de reemplazo.
     * @param string $folder Ruta del directorio.
     * @param array $config Configuración adicional.
     *
     * @return array Conteo de cambios y detalles.
     */
    private static function replace(String $search, String $replace, String $folder = "./*", array $config = [])
    {
        $defaultConfig = [
            "returnCount" => true,
            "dirs" => true,
            "replace" => true,
        ];

        $config = array_merge($defaultConfig, $config);
        $change = [];
        $allFiles = glob($folder);

        foreach ($allFiles as $route) if (strpos(__FILE__, basename($route)) === false) {
            if (is_dir($route) && $config["dirs"] === true)
                $change = array_merge($change, self::replace($search, $replace, "{$route}/*", $config));
            elseif (is_file($route)) {
                $oldFile = file_get_contents($route);
                $newFile = $oldFile;

                if (strpos($newFile, $search) !== false && !empty($search)) {
                    $change[] = [
                        "folder" => dirname($route),
                        "file" => basename($route),
                        "replace" => [
                            $search => $replace
                        ]
                    ];

                    if ($config["replace"] === true) {
                        $newFile = str_replace($search, $replace, $newFile);
                        file_put_contents($route, $newFile);
                    }
                }
            }
        }

        return $config["returnCount"]
            ? array_merge($change, ["count" => count($change) - (isset($config["returnCount"]) ? 1 : 0)])
            : $change;
    }
}
