<?php

/**
 * Templates for creating views depending on whether the user is in a session or not.
 */

namespace Model;

use System\Config\AppConfig;

class RouteTemplateView
{
    /**
     * Generates view templates based on the provided type.
     *
     * @param string $type - The type of template (VIEW, BACK, GENERAL_STYLE, SCRIPT, GENERAL_SCRIPT).
     *
     * @return string - The generated view template.
     */
    static function templates($type): ?String
    {
        // Get the current date and base folder from the application configuration.
        $date = date("Y-m-d H:i:s");

        // Define view templates based on session status and template type.
        return [
            "ADMINMODE" => [
                "VIEW" => <<<'HTML'
                <?php
                # Includes your controller

                $breadcrumb = str_replace("index", "Dashboard", substr($this->page, 1))
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
                "BACK" => <<<'HTML'
                <?php
            
                # Includes your controller
                
                include_once explode("\\app\\", __DIR__)[0] . "/vendor/autoload.php";

                HTML,
                "GENERAL_STYLE" => <<<CSS
                /* {$date} */
                CSS,
                "SCRIPT" => <<<JS
                // {$date}

                $(document).ready(async () => {
                    console.log(`I am ready`)
                })
                JS,
                "GENERAL_SCRIPT" => <<<JS
                // {$date}

                $(document).ready(async () => {
                    console.log(`I am ready`)
                })
                JS
            ],
            "CLIENTMODE" => [
                "VIEW" => <<<HTML
                <?php
                # Includes your controller

                HTML,
                "BACK" => <<<'HTML'
                <?php
            
                # Includes your controller
                
                include_once explode("\\app\\", __DIR__)[0] . "/vendor/autoload.php";

                HTML,
                "GENERAL_STYLE" => <<<CSS
                /* {$date} */
                CSS,
                "SCRIPT" => <<<JS
                // {$date}

                $(document).ready(async () => {
                    console.log(`I am ready`)
                })
                JS,
                "GENERAL_SCRIPT" => <<<JS
                // {$date}

                $(document).ready(async () => {
                    console.log(`I am ready`)
                })
                JS
            ],
        ][strtoupper(AppConfig::VIEW_MODE)][strtoupper($type)] ?? null;
    }
}
