<?php

namespace Config;

use PDO;
use Exception;
use InvalidArgumentException;

use System\Config\AppConfig;

class ProcessData
{
    # Properties
    private $table, $query, $prepareData;
    private $data, $file;

    public $autoCreation = true;
    public $checkEmptyValues = false;

    protected $isPrepared = false;
    public $conn, $tableManager, $imageProcessor;

    # Image processing options
    public $OPTIMIZE_IMAGES = false;
    public $DEFAULT_QUALITY = 90;
    public $USE_RELATIVE_PATH = true;

    # Constructor
    public function __construct(PDO $conn = null)
    {
        $this->conn = new DB();

        if ($conn !== null) $this->conn->setConn($conn);
        else $this->conn->connect();

        $this->imageProcessor = new ImageProcessor();
        $this->tableManager = new TableManager($this->conn->getConn());
    }

    # Prepare data for insert/update
    public function prepare(String $table, array $data)
    {
        $this->isPrepared = true;
        $this->prepareData = [];

        $this->table = $table;
        $this->data = $data["data"] ?? [];
        $this->file = $data["file"] ?? [];

        return $this;
    }

    # Insert data into the database
    public function insert(): array
    {
        try {
            if ($this->isPrepared) $this->conn->executeQuery(self::formatQuery("INSERT")->query, $this->prepareData);
            else throw new Exception("The operation is not prepared.");
        } catch (Exception $th) {
            throw $th;
        }

        return [
            "lastInsertId" => $this->conn->getConn()->lastInsertId(),
            "query" => $this->query,
        ];
    }

    # Update data in the database
    public function update($condition): array
    {
        try {
            if (empty($condition)) throw new InvalidArgumentException("Condition is mandatory for updating.");

            if ($this->isPrepared) $count = $this->conn->executeQuery(self::formatQuery("UPDATE", $condition)->query, $this->prepareData);
            else throw new Exception("The operation is not prepared.");
        } catch (Exception $th) {
            throw $th;
        }

        return [
            "rowCount" => $count,
            "query" => $this->query,
        ];
    }

    # Format SQL query based on data and file information
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

    private function encodeData(array $array): String
    {
        return json_encode($array, JSON_UNESCAPED_UNICODE);
    }

    # Process data to be inserted into the database
    private function processData(): array
    {
        $data = [];
        $data["keys"] = [];
        $data["values"] = [];

        if (!empty(count($this->data))) foreach ($this->data as $name => $value) if ($this->checkEmptyValues === true ? !empty($value) : true) {
            $data["keys"][] = $name;
            $data["values"][] = ":{$name}";

            $value = is_array($value) ? self::encodeData($value) : $value;

            $this->prepareData[":{$name}"] = $value;
        }

        return $data;
    }

    # Process file data for file uploads
    private function processFile(): array
    {
        $data = [];
        $data["keys"] = [];
        $data["values"] = [];

        if (!empty(count($this->file))) foreach ($this->file["name"] as $name => $value) {

            $filePath = AppConfig::BASE_FOLDER_FILE . "/" . $this->table;

            # Create a folder with the table name
            if (!file_exists($filePath)) @mkdir($filePath, 0777, true);

            # Load the files
            if (is_array($value)) for ($i = 0; $i < count($value); $i++) {
                if (!empty($this->file["tmp_name"][$name][$i])) {
                    $value[$i] = $filePath . "/" . uniqid() . "_{$this->file["name"][$name][$i]}";
                    @move_uploaded_file($this->file["tmp_name"][$name][$i], $value[$i]);
                    # Image optimization
                    if ($this->OPTIMIZE_IMAGES === TRUE) $this->imageProcessor::optimizeImages($value[$i], $this->DEFAULT_QUALITY);
                }
            }
            else {
                $value = $filePath . "/" . uniqid() . "_{$value}";
                @move_uploaded_file($this->file["tmp_name"][$name], "{$value}");
                # Image optimization
                if ($this->OPTIMIZE_IMAGES === TRUE) $this->imageProcessor::optimizeImages($value, $this->DEFAULT_QUALITY);
            }

            $data["keys"][] = $name;
            $data["values"][] = ":{$name}";

            $value = is_array($value) ? self::encodeData(array_map(function ($v) {
                return str_replace(AppConfig::BASE_FOLDER, AppConfig::BASE_SERVER, $v);
            }, $value)) : str_replace(AppConfig::BASE_FOLDER, AppConfig::BASE_SERVER, $value);

            if ($this->USE_RELATIVE_PATH === true) $value = str_replace(AppConfig::BASE_SERVER, "", $value);

            $this->prepareData[":{$name}"] = $value;
        }

        return $data;
    }

    private function autoCreate(String $table, array $columns)
    {
        $this->tableManager->createTable($table);
        foreach ($columns as $column) $this->tableManager->createColumn($table, $column);
    }

    # Destructor to close the database connection
    public function __destruct()
    {
        $this->conn->close();
    }
}
