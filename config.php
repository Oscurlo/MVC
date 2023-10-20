<?php
# Control del sistema
define("PRODUCTION", false);
define("CACHE_CONTROL", false);
define("SHOW_ERROR", !PRODUCTION);

# Todo lo regional idioma y demas
define("TIMEZONE", "America/Bogota");
define("CHARSET", "utf-8");
define("LANGUAGE", "es");
define("CURRENCY", "COP");
define("UPS_CODE", "CO");
define("LOCALE", LANGUAGE . "-" . UPS_CODE);

# Carpetas y rutas
// define("BASE_FOLDER", str_replace("\\", "/", __DIR__));
// define("BASE_SERVER", str_replace($_SERVER["DOCUMENT_ROOT"], ($_REQUEST["REQUEST_SCHEME"] ?? "http") . "://" . $_SERVER["HTTP_HOST"], BASE_FOLDER));
define("BASE_FOLDER", (PRODUCTION ? getenv("BASE_FOLDER") : "C:/xampp/htdocs/MVC"));
define("BASE_SERVER", (PRODUCTION ? getenv("BASE_SERVER") : "http://localhost/MVC"));
define("VIEW_MODE", isset($_SESSION["usuario"]) ? "onSession" : "offSession");
define("BASE_FOLDER_VIEW", BASE_FOLDER . "/view/page/" . VIEW_MODE);

# Conexion a la base de datos
define("DATABASE", [
    "HOSTNAME" => (PRODUCTION ? getenv("DB_HOST") : "localhost"),
    "USERNAME" => (PRODUCTION ? getenv("DB_USERNAME") : "root"),
    "PASSWORD" => (PRODUCTION ? getenv("DB_PASSWORD") : ""),
    "DATABASE" => (PRODUCTION ? getenv("DB_DATABASE") : "mvc"),
    "FILE" => BASE_FOLDER . "/db.sqlite",
    "PORT" => (PRODUCTION ? getenv("DB_PORT") : 3306),
    "GESTOR" => "MYSQL" // (MYSQL, SQLSRV, SQLITE)
]);

# Para envio de correo
define("MAIL", [
    "USERNAME" => (PRODUCTION ? getenv("MAIL_USERNAME") : ""),
    "PASSWORD" => (PRODUCTION ? getenv("MAIL_PASSWORD") : ""),
    "HOST" => (PRODUCTION ? getenv("MAIL_HOST") : ""),
    "PORT" => (PRODUCTION ? getenv("MAIL_PORT") : ""),
    "SMTP" => (PRODUCTION ? getenv("MAIL_SMTP") : "")
]);

# Desactivar la visualización de errores en producción
if (SHOW_ERROR) {
    error_reporting(0);
    ini_set("display_errors", 0);
}

# Configuración de zona horaria
date_default_timezone_set(TIMEZONE);

# Control de las sessiones
session_start([
    "cookie_lifetime" => 86400, // Tiempo de vida de la cookie de sesión en segundos
    "use_strict_mode" => true,  // Modo estricto para mitigar ataques de fijación de sesiones
    "cookie_secure" => PRODUCTION, // Solo enviar cookies en conexiones seguras en producción
    "cookie_httponly" => true, // Hacer que las cookies de sesión sean accesibles solo a través del protocolo HTTP
]);
