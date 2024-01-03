<?php

/**
 * Clase para administrar mejor el contenido html y las plantillas para las vistas
 * 
 * El contenido puede variar según el modo en el que se encuentre la sesión "Admin - Client"
 */

namespace Config;

use System\Config\AppConfig;
use Exception;
use Error;
use Throwable;

class RouteTemplateView
{
    /**
     * Generates view templates based on the provided type.
     * @param string $type - The type of template (VIEW, BACK, GENERAL_STYLE, SCRIPT, GENERAL_SCRIPT).
     * @return string - The generated view template.
     */
    static function templates($type): ?String
    {
        # Define view templates based on session status and template type.
        $method = "template_{$type}";
        return method_exists(__CLASS__, $method) ? self::$method(strtoupper(AppConfig::VIEW_MODE)) : null;
    }

    /**
     * Vistas
     */
    protected static function template_VIEW(): String
    {
        # vista por defecto
        return AppConfig::VIEW_MODE === "AdminMode"
            ? <<<'HTML'
            <?php
            
            # Includes your controller

            use Controller\Dashboard;

            ?>
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Dashboard</h1>
                        </div>
                        <div class="col-sm-6">
                            <?= Dashboard::breadcrumb() ?>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class=card-title>Titulo</h3>
                                </div>
                                <div class="card-body">Contenido</div>
                                <div class="card-footer">Pie de pagina</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            HTML
            : <<<'HTML'
            <?php

            # Includes your controller
            
            ?>

            <body class="hold-transition login-page">
                <div class="login-box">
                    <div class="container d-flex justify-content-center align-items-center">
                    <dotlottie-player src="https://lottie.host/7d1fcb4e-13a4-4917-882b-aa444f99c855/HxfMtbFSGu.json" background="##FFF" speed="1" style="width: 100%; height: 100%" loop autoplay></dotlottie-player>
                    </div>
                </div>
            </body>
            HTML;
    }

    static function breadcrumb(): String
    {
        $route = $_GET["route"] ?? "";
        $dir = explode("/", trim($route, "/"));
        $count = count($dir);

        $list = implode(PHP_EOL, array_map(function ($key, $value) use ($count) {
            $class = $key == ($count - 1) ? "breadcrumb-item active" : "breadcrumb-item";
            return "<li class=\"{$class}\">{$value}</li>";
        }, array_keys($dir), array_values($dir))) ?: "<li class=\"breadcrumb-item active\">Dashboard</li>";

        return <<<HTML
        <ol class="breadcrumb float-sm-right">
            {$list}
        </ol>
        HTML;
    }

    /**
     * Archivo de back para la vista
     */
    protected static function template_BACK(): String
    {
        return <<<'HTML'
        <?php
        
        # Includes your controller

        include_once explode("\\app\\", __DIR__)[0] . "/vendor/autoload.php";
        HTML;
    }

    /**
     * Archivo de front para la vista
     */
    protected static function template_SCRIPT(): String
    {
        return <<<JS
        $(document).ready(async () => {
            console.log(`I am ready`)
        })
        JS;
    }

    /**
     * Estilo general para todas las vistas dentro de la misma carpeta
     */
    protected static function template_GENERAL_STYLE(): String
    {
        $date = date("Y-m-d H:i:s");

        return <<<CSS
        /* {$date} */
        CSS;
    }

    /**
     * Script general para todas las vistas dentro de la misma carpeta
     */
    protected static function template_GENERAL_SCRIPT(): String
    {
        return self::template_SCRIPT();
    }

    /**
     * Contenido de las vistas junto con los estilos y scripts
     * @param String $view - Ruta dde la vista con la extension de .view
     * @return void - Muestra directamente el contenido para atrapar las Excepcion y errores que puedan a ver en la vista
     */
    static function show_content(String $view): void
    {
        echo "<div data-router>";
        try {
            if (file_exists($view) && strpos($view, ".view") !== false) {
                $folder = dirname($view);
                # style
                print self::viewStyle($folder);

                # View
                include $view;

                # script
                print self::viewScript($view, false);
            } else echo self::show_error_404();
        } catch (Exception | Error $th) {
            echo self::show_error_500($th);
        }
        echo "</div>";
    }

    /**
     * 
     */
    protected static function viewStyle($folder, $directReference = true): ?String
    {
        $styles = glob("{$folder}/*.css");

        $json_encode = fn ($json) => json_encode($json, JSON_UNESCAPED_UNICODE);
        $folder_to_server = fn ($str) => str_replace(AppConfig::BASE_FOLDER, AppConfig::BASE_SERVER, $str);

        if (empty($styles)) return null;

        return $directReference ? implode(PHP_EOL, array_map(function ($href) use ($folder_to_server) {
            return $folder_to_server(<<<HTML
            <link rel="stylesheet" href="{$href}">
            HTML);
        }, $styles)) : <<<HTML
        <LOAD-STYLE style="display: none">{$json_encode($folder_to_server($styles))}</LOAD-STYLE>
        HTML;
    }

    /**
     * 
     */
    protected static function viewScript($view, $directReference = true): ?String
    {

        $folder = dirname($view);
        $array = explode("/", explode(".", $view)[0]);
        $nameView = end($array);

        $uniqueScript = glob("{$folder}/script/{$nameView}/*.js");
        $generalScripts = glob("{$folder}/*.js");

        $scripts = array_merge($uniqueScript, $generalScripts);

        $json_encode = fn ($json) => json_encode($json, JSON_UNESCAPED_UNICODE);
        $folder_to_server = fn ($str) => str_replace(AppConfig::BASE_FOLDER, AppConfig::BASE_SERVER, $str);

        if (empty($scripts)) return null;

        return $directReference ? implode(PHP_EOL, array_map(function ($src) use ($folder_to_server) {
            return $folder_to_server(<<<HTML
            <script src="{$src}"></script>
            HTML);
        }, $scripts)) : <<<HTML
        <LOAD-SCRIPT style="display: none">{$json_encode($folder_to_server($scripts))}</LOAD-SCRIPT>
        HTML;
    }

    /**
     * Error 404
     */
    static function show_error_404(): String
    {

        $BASE_SERVER = AppConfig::BASE_SERVER;
        $route = $_GET["route"] ?? "";

        return AppConfig::VIEW_MODE === "AdminMode"
            ? <<<HTML
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Dashboard</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item active">404</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                <div class="error-page">
                    <h2 class="headline text-warning">404</h2>
                    <div class="error-content">
                        <h3><i class="fas fa-exclamation-triangle text-warning"></i>¡Ups! Página no encontrada.</h3>
                        <p>No pudimos encontrar la página que estabas buscando. Mientras tanto, puede <a href="{$BASE_SERVER}">regresar al Inicio</a>.</p>
                    </div>
                </div>
            </section>
            HTML
            : <<<HTML
            <body class="hold-transition login-page">
                <div class="login-box">
                    <div class="container d-flex justify-content-center align-items-center">
                        <div class="text-center">
                            <h1 class="display-1 fw-bold">404</h1>
                            <p class="fs-3"> <span class="text-warning">¡Ups! Página no encontrada.</span></p>
                            <p class="lead">No pudimos encontrar la página que estabas buscando. Mientras tanto, puede <a href="{$BASE_SERVER}">regresar al Inicio</a>.</p>
                            <p class="lead">{$route}</p>
                        </div>
                    </div>
                </div>
            </body>
            HTML;
    }

    /**
     * Error 500
     * @param Throwable $th
     * @return String
     */
    static function show_error_500(Throwable $th): String
    {
        $msg = AppConfig::SHOW_ERROR ? $th->getMessage() : "Ha ocurrido un error inesperado";

        # Pendiente
        return str_replace([
            "404",
            "warning",
            "Página no encontrada",
            'No pudimos encontrar la página que estabas buscando.'
        ], [
            "500",
            "danger",
            "Ha ocurrido un error inesperado",
            ($th instanceof Error ? "Error: " : "Exception: ") . $msg
        ], self::show_error_404());
    }

    /**
     * Generates HTML script for loading components.
     *
     * @return string - HTML script for loading components.
     */
    public function loadComponets(): String
    {
        $id = uniqid();

        return <<<HTML
            <script data-load="{$id}">
                $(document).ready(() => {
                    const loadJS = $(`LOAD-SCRIPT`)
                    const router = $(`[data-router]`)
                    const loadScript = $(`[data-load="{$id}"]`)
                    
                    if (loadJS && loadJS.length) JSON.parse(loadJS.text()).forEach((e) => $.getScript(e))

                    loadJS.remove()
                    loadScript.remove()

                    if (window.codeError) window.codeError()
                })
            </script>
        HTML;
    }
}
