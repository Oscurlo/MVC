<?php

/**
 * Notes:
 * 1. Constants: I considered some existing systems to define these constants, choosing what seemed most relevant for each.
 * 2. Session: I found it more convenient to include it within the class to have the session available in the files where it's imported.
 * 3. Errors: Similarly, I declared errors here to apply them wherever this is imported.
 * 4. Timezone: Same old story that I already explained >:(
 */

namespace System\Config;

use Dotenv\Dotenv;

/** 
 * I'd like to declare my love, but I can only declare variables 
 */
class AppConfig
{
    # System control
    const PRODUCTION = false;
    const CACHE_CONTROL = false;
    const SHOW_ERROR = !self::PRODUCTION;

    # Regional settings, language, and more
    const TIMEZONE = "America/Bogota";
    const CHARSET = "utf-8";
    const LANGUAGE = "es";
    const CURRENCY = "COP";
    const UPS_CODE = "CO";
    const LOCALE = self::LANGUAGE . "-" . self::UPS_CODE;

    # Folders and paths
    const BASE_FOLDER = BASE_FOLDER;
    const BASE_SERVER = BASE_SERVER;
    const VIEW_MODE = VIEW_MODE;

    const BASE_ADMIN_LTE_3 = self::BASE_FOLDER . BASE_ADMIN_LTE_3;

    # Views
    const BASE_FOLDER_VIEW = self::BASE_FOLDER . "/app/Views/" . self::VIEW_MODE;

    # Files
    const BASE_FOLDER_FILE = self::BASE_FOLDER . "/file";

    # Database connection
    const DATABASE = DATABASE;

    # For email sending
    const MAIL = MAIL;

    # Company
    const COMPANY = [
        "NAME" => "Oscurlo :n",
        "LOGO" => self::BASE_SERVER . "/img/logo.webp",
        "HOME_PAGE" => "https://github.com/Oscurlo"
    ];

    # WebSocket
    const USE_WEBSOCKET = USE_WEBSOCKET;
    const WEBSOCKET = WEBSOCKET;
}

# Session control
session_start([
    "cookie_lifetime" => 86400, # Session cookie lifetime in seconds
    "use_strict_mode" => true, # Strict mode to mitigate session fixation attacks
    "cookie_secure" => AppConfig::PRODUCTION, # Only send cookies over secure connections in production
    "cookie_httponly" => true # Make session cookies accessible only through the HTTP protocol
]);

# Load of environment variables
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

# Since I can't define constants at compile time, I thought it'd be better to declare it outside and assign the value inside the class.
# Haha, I'm crazy, lalala
$host = $_SERVER["HTTP_HOST"] ?? false;

define("BASE_FOLDER", trim(trim($_ENV["BASE_FOLDER"], "\\"), "/"));
define("BASE_SERVER", trim($_SERVER["BASE_SERVER"], "/"));

define("BASE_ADMIN_LTE_3", "/vendor/almasaeed2010/adminlte");

define("VIEW_MODE", ($_SESSION["SESSION_MODE"] ?? "ClientMode") ?: "ClientMode");

define("DATABASE", [
    "HOSTNAME" => $_ENV["DB_HOST"],
    "USERNAME" => $_ENV["DB_USERNAME"],
    "PASSWORD" => $_ENV["DB_PASSWORD"],
    "DATABASE" => $_ENV["DB_DATABASE"],
    "PORT" => $_ENV["DB_PORT"],
    "FILE" => $_ENV["DB_SQLITE_FILE"],
    "GESTOR" => $_ENV["DB_GESTOR"]
]);

define("MAIL", [
    "USERNAME" => $_ENV["MAIL_USERNAME"],
    "PASSWORD" => $_ENV["MAIL_PASSWORD"],
    "HOST" => $_ENV["MAIL_HOST"],
    "PORT" => $_ENV["MAIL_PORT"],
    "SMTP" => $_ENV["MAIL_SMTP"]
]);

define("USE_WEBSOCKET", $_ENV["USE_WEBSOCKET"]);
define("WEBSOCKET", [
    "PORT" => $_ENV["WEBSOCKET_HOST"],
    "HOST" => $_ENV["WEBSOCKET_PORT"]
]);

# Disable error display in production
if (AppConfig::SHOW_ERROR) {
    error_reporting(0);
    ini_set("display_errors", 0);
}

# Timezone configuration
date_default_timezone_set(AppConfig::TIMEZONE);
