<?php

namespace Controller;

use System\Config\AppConfig;
use Model\Route;

class seeWithHTTPRequest
{
    static function httpView(?String $view)
    {
        $Route = new Route();

        $Route->setPage($view ?: "/");
        $Route->view(!AppConfig::PRODUCTION);
    }
}
