<?php

namespace Model;

use Exception;

class Route
{
    private $page, $conn;
    public $id;

    public function __construct(private $array_folder_error = [])
    {
        $this->id = date("YmdHis");
        $this->array_folder_error = array_merge([
            "ERROR_404" => false,
            "ERROR_500" => false
        ], $array_folder_error);

        $this->page = self::getURI();
        $this->conn = new DB;
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
        $ROUTE = $_GET["route"] ?? "default/";
        $isIndex = (substr($ROUTE, -1) === "/" ? "index" : "");
        return "/" . $ROUTE . $isIndex;
    }

    private function folder_to_server(array|String $string)
    {
        return [
            "ARRAY" => (is_array($string) ? array_map(function ($x) {
                return str_replace(BASE_FOLDER, BASE_SERVER, $x);
            }, $string) : ""),
            "STRING" => str_replace(BASE_FOLDER, BASE_SERVER, $string)
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

            if (!$folder || strpos($folder, BASE_FOLDER) !== 0)
                throw new Exception("Invalid folder path");

            $files = [
                "FRONTEND" => $page,
                "BACKEND" => $folder . "/backend.php",
                "CSS" => $folder . "/style.css",
                "JS" => $folder . "/frontend.js"
            ];

            # creo la carpeta
            if (!file_exists($folder)) mkdir($folder, 0777, true);

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
        $FOLDER = BASE_FOLDER;
        return [
            "ONSESSION" => [
                "FRONT" => <<<'HTML'
                <?php

                use Model\Route;

                $route = new Route;
                ?>
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1 class="m-0">Dashboard</h1>
                            </div>
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right">
                                    <li class="breadcrumb-item active"><?= substr($route::getURI(), 1) ?></li>
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
                <button class="btn btn-success"></button>
                HTML,
                "BACK" => <<<HTML
                <?php 
                /*-- {$date} --*/
                include_once "{$FOLDER}/vendor/autoload.php";
                include "{$FOLDER}/config.php";
                HTML,
                "STYLE" => "/*-- {$date} --*/",
                "SCRIPT" => <<<JS
                // {$date}

                $(document).ready(async () => {
                    console.log(`I am ready`)
                })
                JS
            ],
            "OFFSESSION" => [
                "FRONT" => "<!-- {$date} -->",
                "BACK" => <<<HTML
                <?php 
                /*-- {$date} --*/
                include_once "{$FOLDER}/vendor/autoload.php";
                include "{$FOLDER}/config.php";
                HTML,
                "STYLE" => "/*\ {$date} *\/",
                "SCRIPT" => <<<JS
                // {$date}

                $(document).ready(async () => {
                    console.log(`I am ready`)
                })
                JS
            ],
        ][strtoupper(VIEW_MODE)][strtoupper($type)] ?? $date;
    }

    public function view($createView = false)
    {
        try {

            $ext = explode(".", $this->page);
            $this->page = BASE_FOLDER_VIEW . $ext[0] . "." . ($ext[1] ?? "view") . "." . ($ext[2] ?? "php");

            if (!PRODUCTION && $createView === true) self::createFilesAndFolders();

            if (file_exists($this->page)) {
                $folder = self::string_slice($this->page, "/", 0, -1);
                # connect to database
                $this->conn->connect();

                # content
                echo "<div data-router>", include $this->page, "</div>";

                # css and scripts.
                echo "<LOAD-CSS style=\"display: none !important\">", json_encode(self::folder_to_server(glob($folder . "/*.css")), JSON_UNESCAPED_UNICODE), "</LOAD-CSS>";
                echo "<LOAD-SCRIPT style=\"display: none !important\">", json_encode(self::folder_to_server(glob($folder . "/*.js")), JSON_UNESCAPED_UNICODE), "</LOAD-SCRIPT>";
            } else if ($this->array_folder_error["ERROR_404"] && file_exists($this->array_folder_error["ERROR_404"]))
                include $this->array_folder_error["ERROR_404"];
            else echo $route, "<br>", "<h1>", 404, "</h1>";
        } catch (Exception $th) { // no se como puede llegar hasta aqui,pero mejor prevenir
            if ($this->array_folder_error["ERROR_500"] && file_exists($this->array_folder_error["ERROR_500"])) include $this->array_folder_error["ERROR_500"];
            else echo $route, "<br>", "<h1>", 500, "</h1>";
        } finally {
            # close connect to database
            $this->conn->close();
        }
    }

    public function loadComponets(): String
    {
        return <<<HTML
        <div data-load="{$this->id}">
            <script>        
                $(document).ready(() => {
                    console.log(`Hola wenas`)
                    const loadCSS = $(`LOAD-CSS`)
                    const loadJS = $(`LOAD-SCRIPT`)
                    const router = $(`[data-router]`)
                    const divLoad = $(`[data-load="{$this->id}"]`)

                    JSON.parse(loadCSS.text()).forEach((e) => {
                        $.get(e, (data) => {
                            $(`<style>`).html(data).appendTo(router)
                        })
                    })

                    JSON.parse(loadJS.text()).forEach((e) => {
                        $.getScript(e)
                    })

                    loadCSS.remove()
                    loadJS.remove()
                    divLoad.remove()
                })
            </script>
        </div>
        HTML;
    }
}
