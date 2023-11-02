<?php

namespace Controller;

use System\Config\AppConfig;
use Model\Route;

class seeWithHTTPRequest
{
    static function httpView(String $view)
    {
        $view = str_replace(AppConfig::BASE_SERVER, "", $view);
        $view = (substr($view, 0, 1) == "/" ? $view : (!empty($view) ? "/{$view}" : "/index"));

        $Route = new Route();

        $Route->setPage($view);
        $Route->view(!AppConfig::PRODUCTION);
    }
}
