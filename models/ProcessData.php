<?php

namespace Model;

use PDO;
use Exception;
use InvalidArgumentException;

use Intervention\Image\ImageManagerStatic as Image;
use System\Config\AppConfig;

class ProcessData
{
    private $table, $query, $prepareData;
    private $data, $file, $allData;

    public $autoCreation = true;
    public $checkEmptyValues = false;

    protected $isPrepared = false;
    protected $conn;

    const STRING_SEPARATOR = "|/|";

    public $OPTIMIZE_IMAGES = false;
    public $DEFAULT_QUALITY = 90;

    public function __construct(PDO $conn = null)
    {
        $this->conn = new DB();

        if ($conn !== null) $this->conn->setConn($conn);
        else $this->conn->connect();
    }

    public function prepare(String $table, array $data)
    {
        $this->isPrepared = true;
        $this->prepareData = [];

        $this->table = $table;

        $this->data = $data["data"] ?? [];
        $this->file = $data["file"] ?? [];

        $this->allData = array_merge($this->data, $this->file);

        return $this;
    }

    public function insert(): array
    {
        try {
            if ($this->isPrepared) $this->conn->executeQuery(self::formatQuery("INSERT")->query, $this->prepareData);
            else throw new Exception("La operación no está preparada.");
        } catch (Exception $th) {
            throw $th;
        }

        return [
            "lastInsertId" => $this->conn->getConn()->lastInsertId(),
            "query" => $this->query
        ];
    }


    public function update($condition): array
    {
        try {
            if (empty($condition)) throw new InvalidArgumentException("La condición es obligatoria para actualizar");

            if ($this->isPrepared) $count = $this->conn->executeQuery(self::formatQuery("UPDATE", $condition)->query, $this->prepareData);
            else throw new Exception("La operación no está preparada.");
        } catch (Exception $th) {
            throw $th;
        }

        return [
            "rowCount" => $count,
            "query" => $this->query
        ];
    }

    private function formatQuery($type, $condition = ""): self
    {
        $pData = self::processData();
        $pFile = self::processFile();

        $keys = array_merge($pData["keys"], $pFile["keys"]);
        $values = array_merge($pData["values"], $pFile["values"]);

        if (!AppConfig::PRODUCTION && $this->autoCreation === true) self::autoCreate($this->table, $keys);

        $this->query = [
            "INSERT" => "INSERT INTO {$this->table} (" . implode(", ", $keys) . ") VALUES (" . implode(", ", $values) . ")",
            "UPDATE" => "UPDATE {$this->table} SET " . implode(", ", array_map(function ($k, $v) {
                return "{$k} = {$v}";
            }, $keys, $values)) . " WHERE {$condition}",
        ][$type] ?? "";

        return $this;
    }

    private function processData(): array
    {
        $data = [];
        $data["keys"] = [];
        $data["values"] = [];

        if (!empty(count($this->data))) foreach ($this->data as $name => $value) if ($this->checkEmptyValues === true ? !empty($value) : true) {
            $data["keys"][] = $name;
            $data["values"][] = ":{$name}";

            $value = is_array($value) ? implode(self::STRING_SEPARATOR, $value) : $value;

            $this->prepareData[":{$name}"] = $value;
        }

        return $data;
    }

    private function processFile(): array
    {
        $data = [];
        $data["keys"] = [];
        $data["values"] = [];

        if (!empty(count($this->file))) foreach ($this->file["name"] as $name => $value) {

            $filePath = AppConfig::BASE_FOLDER_FILE . "/" . $this->table;

            # Creo una carpeta con el nombre la tabla
            if (!file_exists($filePath)) @mkdir($filePath, 0777, true);

            # Cargo los archivos
            if (is_array($value)) for ($i = 0; $i < count($value); $i++) {
                if (!empty($this->file["tmp_name"][$name][$i])) {
                    $value[$i] = $filePath . "/" . uniqid() . "_{$this->file["name"][$name][$i]}";
                    @move_uploaded_file($this->file["tmp_name"][$name][$i], $value[$i]);
                }
            }
            else {
                $value = $filePath . "/" . uniqid() . "_{$value}";
                @move_uploaded_file($this->file["tmp_name"][$name], "{$value}");
            }

            $data["keys"][] = $name;
            $data["values"][] = ":{$name}";

            $value = is_array($value) ? implode(self::STRING_SEPARATOR, array_map(function ($v) {
                return str_replace(AppConfig::BASE_FOLDER, AppConfig::BASE_SERVER, $v);
            }, $value)) : str_replace(AppConfig::BASE_FOLDER, AppConfig::BASE_SERVER, $value);

            $this->prepareData[":{$name}"] = $value;

            # Optimización de imagenes
            if ($this->OPTIMIZE_IMAGES === true) {
                $path = str_replace(AppConfig::BASE_SERVER, AppConfig::BASE_FOLDER, explode(self::STRING_SEPARATOR, $value));
                self::optimizeImages($path, $this->DEFAULT_QUALITY);
            }
        }

        return $data;
    }

    private function clean($str): String
    {
        return trim($str);
    }

    private function autoCreate(String $table, array $columns)
    {
        self::createTable($table);
        foreach ($columns as $column) self::createColumn($table, $column);
    }

    public function checkTableExists($table): bool
    {
        $query = [
            "MYSQL" => self::clean(<<<SQL
                SHOW TABLES LIKE '{$table}';
            SQL),
            "SQLSRV" => self::clean(<<<SQL
                SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME LIKE '{$table}'
            SQL),
            "SQLITE" => self::clean(<<<SQL
                SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}';
            SQL)
        ][$this->conn->getGestor()];

        $data = $this->conn->executeQuery($query);

        return !empty($data);
    }

    public function createTable($table): void
    {
        if (self::checkTableExists($table)) return;

        $table = self::clean($table);
        if (empty($table)) throw new Exception("Tabla es obligatoria");

        $query = [
            "MYSQL" => self::clean(<<<SQL
                CREATE TABLE IF NOT EXISTS `{$table}` (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    fechaRegistro DATETIME DEFAULT CURRENT_TIMESTAMP()
                )
            SQL),
            "SQLSRV" => self::clean(<<<SQL
                IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '{$table}' and xtype = 'U')
                CREATE TABLE {$table} (
                    id INT IDENTITY(1,1) PRIMARY KEY,
                    fechaRegistro DATETIME DEFAULT CURRENT_TIMESTAMP()
                )
            SQL),
            "SQLITE" => self::clean(<<<SQL
                CREATE TABLE IF NOT EXISTS `{$table}` (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    fechaRegistro TEXT DEFAULT CURRENT_TIMESTAMP
                )
            SQL)
        ][strtoupper($this->conn->getGestor())] ?? "";

        $this->conn->executeQuery($query);
    }

    public function checkColumnExists($table, $column): bool
    {
        $gestor = $this->conn->getGestor();
        $query = [
            "MYSQL" => self::clean(<<<SQL
                SHOW COLUMNS FROM {$table} LIKE '{$column}';
            SQL),
            "SQLSRV" => self::clean(<<<SQL
                SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table}' AND COLUMN_NAME LIKE '{$column}';
            SQL),
            "SQLITE" => self::clean(<<<SQL
                PRAGMA table_info('{$table}');
            SQL),
        ][strtoupper($gestor)] ?? "";

        $data = $this->conn->executeQuery($query);

        foreach ($data as $row) if ($gestor === "MYSQL" || $gestor === "SQLSRV") {
            if (strtolower($row['Field']) === strtolower($column)) return true;
        } elseif ($gestor === "SQLITE") {
            if (strtolower($row['name']) === strtolower($column)) return true;
        }

        return false;
    }


    public function createColumn($table, $column, $type = null): void
    {
        if (self::checkColumnExists($table, $column)) return;

        if (is_null($type)) $type = [
            "MYSQL" => "TEXT DEFAULT NULL",
            "SQLSRV" => "VARCHAR(MAX) DEFAULT NULL",
            "SQLITE" => "VARCHAR(255) DEFAULT NULL"
        ][strtoupper($this->conn->getGestor())] ?? "";

        $query = [
            "MYSQL" => self::clean(<<<SQL
                ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$type}
            SQL),
            "SQLSRV" => self::clean(<<<SQL
                ALTER TABLE {$table} ADD {$column} {$type}
            SQL),
            "SQLITE" => self::clean(<<<SQL
                ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$type}
            SQL)
        ][strtoupper($this->conn->getGestor())] ?? "";

        $this->conn->executeQuery($query);
    }

    public function optimizeImages(String|array $format, $quality): void
    {
        if (extension_loaded("GD")) {
            $imagePaths = strtoupper(gettype($format)) == "STRING" ? glob($format) : $format;

            $validExt = ["jpg", "png", "gif", "tif", "bmp", "ico", "psd", "webp"];

            foreach ($imagePaths as $imagePath) {
                if (file_exists($imagePath)) {

                    $info = pathinfo($imagePath);

                    if (isset($info["extension"]) && in_array(strtolower($info["extension"]), $validExt)) self::encodeImage($imagePath, $quality, $info["extension"]);
                }
            }
        } else {
            // self::enableExtension("gd");
            throw new Exception("Habilita la extensión de \"GB\"");
        }
    }

    static function encodeImage($img, $quality, $outputFormat = "data-url"): void
    {
        Image::make($img)->encode($outputFormat, $quality)->save($img);
    }

    static function resizeImage($img, $newWidth, $newHeight): void
    {
        Image::make($img)->resize($newWidth, $newHeight)->save($img);
    }

    static function rotateImage($img, $angle): void
    {
        Image::make($img)->rotate($angle)->save($img);
    }

    /**
     * Método experimental realmente no creo que funcione :c
     */
    # Si funciona pero no creo que sea seguro
    // private function enableExtension(String $ext)
    // {
    //     $ext = strtolower($ext);

    //     $phpIniPaths = array_filter(explode(PATH_SEPARATOR, getenv("PATH")), function ($path) {
    //         return strpos($path, "php") !== false;
    //     });

    //     foreach ($phpIniPaths as $phpPath) {
    //         $phpIniPath = "{$phpPath}/php.ini";

    //         if (!file_exists($phpIniPath)) continue;

    //         $fileContent = file_get_contents($phpIniPath);

    //         if (strpos($fileContent, ";extension={$ext}") !== false) {
    //             $newContent = str_replace(";extension={$ext}", "extension={$ext}", $fileContent);
    //             copy($phpIniPath, "{$phpIniPath}.bak");
    //             file_put_contents($phpIniPath, $newContent);

    //             self::restartServer();

    //             return true;
    //         }
    //     }

    //     return false;
    // }

    // private function restartServer()
    // {
    //     # Intento de reiniciar Apache
    //     exec("sudo service apache2 restart");
    //     exec("sudo systemctl restart apache2");
    // }

    public function __destruct()
    {
        $this->conn->close();
    }
}
