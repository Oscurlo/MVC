<?php

/**
 * This class represents a routing mechanism with view generation capabilities.
 */

namespace Model;

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

        $this->id = uniqid();
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
                fwrite($openString, self::templates($template));
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
        echo "<div data-router>";
        try {

            $ext = explode(".", $this->page);
            $this->view = $this->config::BASE_FOLDER_VIEW . $ext[0] . "." . ($ext[1] ?? "view") . "." . ($ext[2] ?? "php");

            if (!$this->config::PRODUCTION && $createView === true) self::createFilesAndFolders();

            if (file_exists($this->view)) {
                $folder = self::string_slice($this->view, "/", 0, -1);

                $nameScript = explode("/", $this->view);
                $arrayName = explode(".", end($nameScript));
                $uniqueScript = "{$folder}/script/{$arrayName[0]}/front.js";

                # css
                echo implode("\n", array_map(function ($css) {
                    return "<link rel=\"stylesheet\" href=\"{$css}\">";
                }, self::folder_to_server(glob($folder . "/*.css"))));

                # content
                include $this->view;

                # script.
                $scrits = glob($folder . "/*.js");
                if (file_exists($uniqueScript)) array_push($scrits, $uniqueScript);

                echo "<LOAD-SCRIPT style=\"display: none !important\">",
                json_encode(self::folder_to_server($scrits), JSON_UNESCAPED_UNICODE),
                "</LOAD-SCRIPT>";
            } else if ($this->array_folder_error["ERROR_404"] && file_exists($this->array_folder_error["ERROR_404"]))
                include $this->array_folder_error["ERROR_404"];
            else echo <<<HTML
                <div class="container">
                    <h3>Error 404</h3>
                </div>
            HTML;
        } catch (Exception | Error $th) {
            if ($this->array_folder_error["ERROR_500"] && file_exists($this->array_folder_error["ERROR_500"]))
                include $this->array_folder_error["ERROR_500"];
            else if ($this->config::SHOW_ERROR) {
                $html = self::showFileError($th);
                $message = $th instanceof Exception ? "Exception" : "Error";
                $divId = $th instanceof Exception ? "CodeMirrorException" : "CodeMirrorError";

                echo <<<HTML
                    <div class="container">
                        <textarea class="m-5" id="{$divId}" data-line="{$th->getLine()}" data-message="{$message}: {$th->getMessage()}" disabled>{$html}</textarea>
                    </div>
                HTML;
            } else echo <<<HTML
                <div class="container">
                    <h3>Error 500</h3>
                </div>
            HTML;
        }

        echo "</div>";
    }

    protected function showLineError(Exception|Error $e): String
    {
        return trim(explode("\n", file_get_contents($e->getFile()))[$e->getLine() + 1]);
    }

    protected function showFileError(Exception|Error $e): String
    {
        return trim(file_get_contents($e->getFile()));
    }

    /**
     * Generates HTML script for loading components.
     *
     * @return string - HTML script for loading components.
     */
    public function loadComponets(): String
    {
        return <<<HTML
            <script data-load="{$this->id}">
                $(document).ready(() => {
                    const loadJS = $(`LOAD-SCRIPT`)
                    const router = $(`[data-router]`)
                    const loadScript = $(`[data-load="{$this->id}"]`)
                    
                    if (loadJS.length) JSON.parse(loadJS.text()).forEach((e) => {
                        $.getScript(e)
                    })

                    loadJS.remove()
                    loadScript.remove()
                })
            </script>
        HTML;
    }

    /**
     * Destructor to close the database connection when the object is destroyed.
     */
    public function __destruct()
    {
        $this->conn->close();
    }
}
