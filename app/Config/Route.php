<?php

/**
 * This class represents a routing mechanism with view generation capabilities.
 */

namespace Config;

use Error;
use Exception;
use System\Config\AppConfig;

class Route extends RouteTemplateView

{
    protected $view;
    private $page, $conn, $config;
    public $id;

    /**
     * Constructor for the Route class.
     *
     * @param array $array_folder_error - Array containing error information for different folders.
     */
    public function __construct(private $array_folder_error = [])
    {
        $this->config = new AppConfig;
        $this->array_folder_error = array_merge([
            "ERROR_404" => false,
            "ERROR_500" => false
        ], $array_folder_error);

        $this->page = self::getURI();
        $this->conn = new DB;

        if (!$this->config::PRODUCTION) $this->conn->CREATE_DATABASE = true;

        $this->conn->connect();
    }

    public function getView(): String
    {
        return $this->view;
    }

    public function getPage(): String
    {
        return $this->page;
    }

    public function setPage($newPage): void
    {
        $this->page = $newPage;
    }

    static function getURI(): String
    {
        $route = $_GET["route"] ?? "index";
        $route = trim($route, "/");

        return "/{$route}";
    }

    private function folder_to_server(array|String $string): String|array
    {
        return [
            "ARRAY" => (is_array($string) ? array_map(function ($x) {
                return str_replace($this->config::BASE_FOLDER, $this->config::BASE_SERVER, $x);
            }, $string) : ""),
            "STRING" => str_replace($this->config::BASE_FOLDER, $this->config::BASE_SERVER, $string)
        ][strtoupper(gettype($string))] ?? $string;
    }

    private function string_slice(String $string, array|String $separator, Int $offset, Int $length): String
    {
        return implode($separator, array_slice(explode($separator, $string), $offset, $length));
    }

    /**
     * Creates view files and folders based on the current page.
     *
     * @return bool - True if files and folders are created successfully, false otherwise.
     */
    private function createFilesAndFolders(): bool
    {
        try {
            $arrayName = explode(".", end(explode("/", $this->view)));
            $folder = self::string_slice($this->view, "/", 0, -1);
            $folderScripts = "{$folder}/script/{$arrayName[0]}";

            if (!$folder || strpos($folder, $this->config::BASE_FOLDER) !== 0)
                throw new Exception("Invalid folder path");

            $files = [
                # vista
                "VIEW" => $this->view,
                # back y front de la vista
                "BACK" => "{$folderScripts}/back.php",
                "SCRIPT" => "{$folderScripts}/front.js",
                # archivos general para todas las vistas dentro la carpeta
                "GENERAL_STYLE" => "{$folder}/style.css",
                "GENERAL_SCRIPT" => "{$folder}/frontend.js",
                "GENERAL_BACK" => "{$folder}/backend.php",
            ];

            # creo las carpetas
            if (!file_exists($folder)) mkdir($folder, 0777, true);
            if (!file_exists($folderScripts)) mkdir($folderScripts, 0777, true);

            # creo los archivos
            foreach ($files as $template => $filename) if (!file_exists($filename)) {
                echo <<<HTML
                <pre class="m-0 p-0">new file created: {$filename}</pre>
                HTML;

                $openString = fopen($filename, "w");

                $data = self::templates($template);

                fwrite($openString, $data ?: "");
                fclose($openString);
            }

            return true;
        } catch (Exception $th) {
            return false;
        }
    }

    /**
     * Displays the view based on the current page.
     *
     * @param bool $createView - Flag indicating whether to create view files and folders.
     */
    public function view($createView = false): void
    {
        $ext = explode(".", $this->page);
        $this->view = $this->config::BASE_FOLDER_VIEW . explode("?", $ext[0])[0] . "." . ($ext[1] ?? "view") . "." . ($ext[2] ?? "php");

        if (!$this->config::PRODUCTION && $createView === true) self::createFilesAndFolders();

        self::show_content($this->view);
    }

    static function href(String $url = "", array $get = []): String
    {
        $url = trim(str_replace([
            AppConfig::BASE_FOLDER,
            AppConfig::BASE_SERVER,
            ".view",
            ".php"
        ], "", $url), "/");

        $url = !empty($url) ? "/{$url}" : "";
        $get = !empty($get) ? "?" . http_build_query($get) : "";

        return AppConfig::BASE_SERVER . $url . $get;
    }

    protected function showLineError(Exception|Error $e): String
    {

        $file = explode(PHP_EOL, file_get_contents($e->getFile()));
        $response = [];

        for ($i = 0; $i <= $e->getLine(); $i++) $response[] = $file[$i];
        return implode(PHP_EOL, $response);
    }

    protected function showFileError(Exception|Error $e): String
    {
        return trim(file_get_contents($e->getFile()));
    }

    /**
     * Destructor to close the database connection when the object is destroyed.
     */
    public function __destruct()
    {
        $this->conn->close();
    }
}
