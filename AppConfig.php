<?php

namespace System\Config;

/** 
 * Me gustaría declararle mi amor, pero solo puedo declarar variables 
 */

class AppConfig
{
    // Control del sistema
    const PRODUCTION = false;
    const CACHE_CONTROL = false;
    const SHOW_ERROR = !self::PRODUCTION;

    // Todo lo regional idioma y demás
    const TIMEZONE = "America/Bogota";
    const CHARSET = "utf-8";
    const LANGUAGE = "es";
    const CURRENCY = "COP";
    const UPS_CODE = "CO";
    const LOCALE = self::LANGUAGE . "-" . self::UPS_CODE;

    // Carpetas y rutas
    const BASE_FOLDER = __DIR__;
    const BASE_SERVER = (self::PRODUCTION ? getenv("BASE_SERVER") : "http://localhost/MVC");
    const VIEW_MODE = VIEW_MODE;

    // Vistas
    const BASE_FOLDER_VIEW = self::BASE_FOLDER . "/view/page/" . self::VIEW_MODE;

    // Archivos
    const BASE_FOLDER_FILE = self::BASE_FOLDER . "/file";

    // Conexion a la base de datos
    const DATABASE = [
        "HOSTNAME" => (self::PRODUCTION ? getenv("DB_HOST") : "localhost"),
        "USERNAME" => (self::PRODUCTION ? getenv("DB_USERNAME") : "root"),
        "PASSWORD" => (self::PRODUCTION ? getenv("DB_PASSWORD") : ""),
        "DATABASE" => (self::PRODUCTION ? getenv("DB_DATABASE") : "mvc"),
        "FILE" => self::BASE_FOLDER . "/db.sqlite",
        "PORT" => (self::PRODUCTION ? getenv("DB_PORT") : false),
        "GESTOR" => "MYSQL" // (MYSQL, SQLSRV, SQLITE)
    ];

    // Para envío de correo
    const MAIL = [
        "USERNAME" => (self::PRODUCTION ? getenv("MAIL_USERNAME") : ""),
        "PASSWORD" => (self::PRODUCTION ? getenv("MAIL_PASSWORD") : ""),
        "HOST" => (self::PRODUCTION ? getenv("MAIL_HOST") : ""),
        "PORT" => (self::PRODUCTION ? getenv("MAIL_PORT") : ""),
        "SMTP" => (self::PRODUCTION ? getenv("MAIL_SMTP") : "")
    ];

    // Empresa
    const COMPANY = [
        "NAME" => "Oscurlo :n",
        "LOGO" => self::BASE_SERVER . "/img/logo.webp",
        "HOME_PAGE" => "https://github.com/Oscurlo"
    ];
}

// Control de las sesiones
session_start([
    "cookie_lifetime" => 86400, // Tiempo de vida de la cookie de sesión en segundos
    "use_strict_mode" => true, // Modo estricto para mitigar ataques de fijación de sesiones
    "cookie_secure" => AppConfig::PRODUCTION, // Solo enviar cookies en conexiones seguras en producción
    "cookie_httponly" => true, // Hacer que las cookies de sesión sean accesibles solo a través del protocolo HTTP
]);

// Ya que no puedo definir constantes en tiempo de compilación se me ocurrió mejor declararla afuera y asignar el valor dentro de la clase
// jaja estoy loco lalala
define("VIEW_MODE", isset($_SESSION["usuario"]) ? "onSession" : "offSession");

// Desactivar la visualización de errores en producción
if (AppConfig::SHOW_ERROR) {
    error_reporting(0);
    ini_set("display_errors", 0);
}

// Configuración de zona horaria
date_default_timezone_set(AppConfig::TIMEZONE);
