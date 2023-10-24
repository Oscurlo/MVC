<?php

namespace Model;

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

    public function __construct()
    {
        $this->conn = new DB();
        $this->conn->connect();
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
            else throw new Exception("La operación no está preparada", 1);
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
            else throw new Exception("La operación no está preparada", 1);
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
                $value[$i] = $filePath . date("YmdHis") . "_{$this->file["name"][$name][$i]}";
                @move_uploaded_file($this->file["tmp_name"][$name][$i], $value[$i]);
            } else if (!empty($this->file["tmp_name"][$name])) {
                $value = $filePath . date("YmdHis") . "_{$value}";
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

    public function optimizeImages(String|array $format, $quality): void
    {
        $imagePaths = strtoupper(gettype($format)) == "STRING" ? glob($format) : $format;

        $validExt = ["jpg", "png", "gif", "tif", "bmp", "ico", "psd", "webp"];

        foreach ($imagePaths as $imagePath) {
            if (file_exists($imagePath)) {

                $info = pathinfo($imagePath);

                if (isset($info['extension']) && in_array(strtolower($info['extension']), $validExt)) self::encodeImage($imagePath, $quality, $info["extension"]);
            }
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


    public function __destruct()
    {
        $this->conn->close();
    }
}
