<?php

namespace Model;

use PDO;
use Exception;
use InvalidArgumentException;

use Intervention\Image\ImageManagerStatic as Image;

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

        if (!PRODUCTION && $this->autoCreation === true) self::autoCreate($this->table, $keys, DATABASE["GESTOR"]);

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

            $filePath = BASE_FOLDER_FILE . "/" . $this->table;

            # Creo una carpeta con el nombre la tabla
            if (!file_exists($filePath)) @mkdir($filePath, 0777, true);

            # Cargo los archivos
            if (is_array($value)) for ($i = 0; $i < count($value); $i++) if (!empty($this->file["tmp_name"][$name][$i])) {
                $value[$i] = $filePath . "/" . uniqid() . "_{$this->file["name"][$name][$i]}";
                @move_uploaded_file($this->file["tmp_name"][$name][$i], $value[$i]);
            } else if (!empty($this->file["tmp_name"][$name])) {
                $value = $filePath . "/" . uniqid() . "_{$value}";
                @move_uploaded_file($this->file["tmp_name"][$name], "{$value}");
            }

            $data["keys"][] = $name;
            $data["values"][] = ":{$name}";

            $value = is_array($value) ? implode(self::STRING_SEPARATOR, array_map(function ($v) {
                return str_replace(BASE_FOLDER, BASE_SERVER, $v);
            }, $value)) : str_replace(BASE_FOLDER, BASE_SERVER, $value);

            $this->prepareData[":{$name}"] = $value;

            # Optimización de imagenes
            if ($this->OPTIMIZE_IMAGES === true) {
                $path = str_replace(BASE_SERVER, BASE_FOLDER, explode(self::STRING_SEPARATOR, $value));
                self::optimizeImages($path, $this->DEFAULT_QUALITY);
            }
        }

        return $data;
    }

    private function cleam($str): String
    {
        return trim($str);
    }

    private function autoCreate(String $table, array $columns, String $gestor)
    {
        self::createTable($gestor, $table);
        foreach ($columns as $column) self::createColumn($gestor, $table, $column);
    }

    public function checkTableExists($g, $table): bool
    {
        $query = [
            "MYSQL" => self::cleam(<<<SQL
                SHOW TABLES LIKE '{$table}';
            SQL),
            "SQLSRV" => self::cleam(<<<SQL
                SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME LIKE '{$table}'
            SQL),
            "SQLITE" => self::cleam(<<<SQL
                SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}';
            SQL)
        ][$g];

        $data = $this->conn->executeQuery($query);

        return !empty($data);
    }

    public function createTable($g, $table): void
    {
        if (self::checkTableExists($g, $table)) return;

        $table = self::cleam($table);
        if (empty($table)) throw new Exception("Tabla es obligatoria");

        $query = [
            "MYSQL" => self::cleam(<<<SQL
                CREATE TABLE IF NOT EXISTS `{$table}` (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    fechaRegistro DATETIME DEFAULT CURRENT_TIMESTAMP()
                )
            SQL),
            "SQLSRV" => self::cleam(<<<SQL
                IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '{$table}' and xtype = 'U')
                CREATE TABLE {$table} (
                    id INT IDENTITY(1,1) PRIMARY KEY,
                    fechaRegistro DATETIME DEFAULT CURRENT_TIMESTAMP()
                )
            SQL),
            "SQLITE" => self::cleam(<<<SQL
                CREATE TABLE IF NOT EXISTS `{$table}` (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    fechaRegistro TEXT DEFAULT CURRENT_TIMESTAMP
                )
            SQL)
        ][strtoupper($g)] ?? "";

        $this->conn->executeQuery($query);
    }

    public function checkColumnExists($g, $table, $column): bool
    {
        $query = [
            "MYSQL" => self::cleam(<<<SQL
                SHOW COLUMNS FROM {$table} LIKE '{$column}';
            SQL),
            "SQLSRV" => self::cleam(<<<SQL
                SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table}' AND COLUMN_NAME LIKE '{$column}';
            SQL),
            "SQLITE" => self::cleam(<<<SQL
                PRAGMA table_info('{$table}');
            SQL),
        ][strtoupper($g)] ?? "";

        $data = $this->conn->executeQuery($query);

        foreach ($data as $row) if ($g === "MYSQL" || $g === "SQLSRV") {
            if (strtolower($row['Field']) === strtolower($column)) return true;
        } elseif ($g === "SQLITE") {
            if (strtolower($row['name']) === strtolower($column)) return true;
        }

        return false;
    }


    public function createColumn($g, $table, $column, $type = null): void
    {
        if (self::checkColumnExists($g, $table, $column)) return;

        if (is_null($type)) $type = [
            "MYSQL" => "TEXT DEFAULT NULL",
            "SQLSRV" => "VARCHAR(MAX) DEFAULT NULL",
            "SQLITE" => "VARCHAR(255) DEFAULT NULL"
        ][strtoupper($g)] ?? "";

        $query = [
            "MYSQL" => self::cleam(<<<SQL
                ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$type}
            SQL),
            "SQLSRV" => self::cleam(<<<SQL
                ALTER TABLE {$table} ADD {$column} {$type}
            SQL),
            "SQLITE" => self::cleam(<<<SQL
                ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$type}
            SQL)
        ][strtoupper($g)] ?? "";

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
            self::enableExtension("gd");
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
    private function enableExtension(String $ext)
    {
        $ext = strtolower($ext);

        $phpIniPaths = array_filter(explode(PATH_SEPARATOR, getenv("PATH")), function ($path) {
            return strpos($path, "php") !== false;
        });

        foreach ($phpIniPaths as $phpPath) {
            $phpIniPath = "{$phpPath}/php.ini";

            if (!file_exists($phpIniPath)) continue;

            $fileContent = file_get_contents($phpIniPath);

            if (strpos($fileContent, ";extension={$ext}") !== false) {
                $newContent = str_replace(";extension={$ext}", "extension={$ext}", $fileContent);
                copy($phpIniPath, "{$phpIniPath}.bak");
                file_put_contents($phpIniPath, $newContent);

                self::restartServer();

                return true;
            }
        }

        return false;
    }

    private function restartServer()
    {
        # Intento de reiniciar Apache
        exec("sudo service apache2 restart");
        exec("sudo systemctl restart apache2");
    }

    public function __destruct()
    {
        $this->conn->close();
    }
}
