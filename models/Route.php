<?php

/**
 * jaja creo que hice este archivo a las patadas,
 * creo que pude estructurarlo un poco mejor,
 * pero bueno de momento no me afecta mucho ya luego lo corrijo o capaz y lo dejo asi :n
 */

namespace Model;

use Exception;
use System\Config\AppConfig;

class Route
{
    private $page, $conn, $config;
    public $id;

    public function __construct(private $array_folder_error = [])
    {
        $this->id = uniqid();
        $this->array_folder_error = array_merge([
            "ERROR_404" => false,
            "ERROR_500" => false
        ], $array_folder_error);

        $this->page = self::getURI();
        $this->conn = new DB;
        $this->conn->connect();

        $this->config = new AppConfig;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function setPage($newPage)
    {
        $this->page = $newPage;
    }

    static function getURI(): String
    {
        $ROUTE = $_GET["route"] ?? "index";
        $isIndex = (substr($ROUTE, -1) === "/" ? "index" : "");
        return "/" . $ROUTE . $isIndex;
    }

    private function folder_to_server(array|String $string)
    {
        return [
            "ARRAY" => (is_array($string) ? array_map(function ($x) {
                return str_replace($this->config::BASE_FOLDER, $this->config::BASE_SERVER, $x);
            }, $string) : ""),
            "STRING" => str_replace($this->config::BASE_FOLDER, $this->config::BASE_SERVER, $string)
        ][strtoupper(gettype($string))] ?? $string;
    }

    private function string_slice(String $string, array|String $separator, Int $offset, Int $length)
    {
        return implode($separator, array_slice(explode($separator, $string), $offset, $length));
    }

    private function createFilesAndFolders(): bool
    {
        try {
            $page = self::getPage();
            $folder = self::string_slice($page, "/", 0, -1);
            $folderScripts = "{$folder}/script";

            if (!$folder || strpos($folder, $this->config::BASE_FOLDER) !== 0)
                throw new Exception("Invalid folder path");

            $files = [
                "FRONT" => $page,
                "BACK" => "{$folder}/backend.php",
                "STYLE" => "{$folder}/style.css",
                "SCRIPT" => "{$folder}/frontend.js"
            ];

            # Pensando como los que piensan creo que es mejor tener un único script por cada vista
            $nameScript = str_replace(".php", "", end(explode("/", $files["FRONT"])));
            $files["UNIQUE_SCRIPT"] = "{$folderScripts}/{$nameScript}.js";

            # creo las carpetas
            if (!file_exists($folder)) mkdir($folder, 0777, true);
            if (!file_exists($folderScripts)) mkdir($folderScripts, 0777, true);

            # creo los archivos
            foreach ($files as $key => $filename) if (!file_exists($filename)) {
                echo <<<HTML
                    <pre class="m-0 p-0">
                        new file created: {$filename}
                    </pre>
                HTML;
                $openString = fopen($filename, "w");
                fwrite($openString, self::templates($key));
                fclose($openString);
            }

            return true;
        } catch (Exception $th) {
            return false;
        }
    }

    private function templates($type): String
    {
        $date = date("Y-m-d H:i:s");
        $FOLDER = $this->config::BASE_FOLDER;
        return [
            "ONSESSION" => [
                "FRONT" => <<<'HTML'
                <?php
                # Includes your controller

                $breadcrumb = str_replace("index", "Dashboard", substr($this::getURI(), 1))
                ?>
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0">Dashboard</h1>
                            </div>
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                    <li class="breadcrumb-item active"><?= $breadcrumb ?></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <section class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-header"></div>
                                    <div class="card-body"></div>
                                    <div class="card-footer"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                HTML,
                "BACK" => <<<HTML
                <?php
            
                use System\Config\AppConfig;
                # Includes your controller

                /*-- {$date} --*/
                include_once "{$FOLDER}/vendor/autoload.php";
                HTML,
                "STYLE" => <<<CSS
                /* {$date} */
                CSS,
                "SCRIPT" => <<<JS
                // {$date}

                $(document).ready(async () => {
                    console.log(`I am ready`)
                })
                JS,
                "UNIQUE_SCRIPT" => <<<JS
                // {$date}

                $(document).ready(async () => {
                    console.log(`I am ready`)
                })
                JS
            ],
            "OFFSESSION" => [
                "FRONT" => <<<HTML
                <?php
                # Includes your controller
                HTML,
                "BACK" => <<<HTML
                <?php
            
                use System\Config\AppConfig;
                # Includes your controller

                /*-- {$date} --*/
                include_once "{$FOLDER}/vendor/autoload.php";
                HTML,
                "STYLE" => <<<CSS
                /* {$date} */
                CSS,
                "SCRIPT" => <<<JS
                // {$date}

                $(document).ready(async () => {
                    console.log(`I am ready`)
                })
                JS,
                "UNIQUE_SCRIPT" => <<<JS
                // {$date}

                $(document).ready(async () => {
                    console.log(`I am ready`)
                })
                JS
            ],
        ][strtoupper($this->config::VIEW_MODE)][strtoupper($type)] ?? $date;
    }

    public function view($createView = false)
    {
        echo "<div data-router>";
        try {

            $ext = explode(".", $this->page);
            $this->page = $this->config::BASE_FOLDER_VIEW . $ext[0] . "." . ($ext[1] ?? "view") . "." . ($ext[2] ?? "php");

            if (!$this->config::PRODUCTION && $createView === true) self::createFilesAndFolders();

            if (file_exists($this->page)) {
                $folder = self::string_slice($this->page, "/", 0, -1);

                $nameScript = str_replace(".php", "", end(explode("/", $this->page)));
                $uniqueScript = "{$folder}/script/{$nameScript}.js";

                # css
                echo implode("\n", array_map(function ($css) {
                    return "<link rel=\"stylesheet\" href=\"{$css}\">";
                }, self::folder_to_server(glob($folder . "/*.css"))));

                # content
                include $this->page;

                # script.
                $scrits = glob($folder . "/*.js");
                if (file_exists($uniqueScript)) array_push($scrits, $uniqueScript);

                echo "<LOAD-SCRIPT style=\"display: none !important\">",
                json_encode(self::folder_to_server($scrits), JSON_UNESCAPED_UNICODE),
                "</LOAD-SCRIPT>";
            } else if ($this->array_folder_error["ERROR_404"] && file_exists($this->array_folder_error["ERROR_404"]))
                include $this->array_folder_error["ERROR_404"];
            else echo "<br>", "<h1>", 404, "</h1>";
        } catch (Exception $th) { // no se como puede llegar hasta aqui,pero mejor prevenir
            if ($this->array_folder_error["ERROR_500"] && file_exists($this->array_folder_error["ERROR_500"])) include $this->array_folder_error["ERROR_500"];
            else echo "<br>", "<h1>", 500, "</h1>";
        }
        echo "</div>";
    }

    public function loadComponets(): String
    {
        return <<<HTML
            <script data-load="{$this->id}">
                $(document).ready(() => {
                    const loadJS = $(`LOAD-SCRIPT`)
                    const router = $(`[data-router]`)
                    const loadScript = $(`[data-load="{$this->id}"]`)

                    JSON.parse(loadJS.text()).forEach((e) => {
                        $.getScript(e)
                    })

                    loadJS.remove()
                    loadScript.remove()
                })
            </script>
        HTML;
    }
    public function __destruct()
    {
        $this->conn->close();
    }
}
