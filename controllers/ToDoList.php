<?php

namespace Controller;

use Exception;
use Model\ProcessData;

class ToDoList extends ProcessData
{
    private const TABLE_TO_DO_LIST = "ToDoList";
    private const TABLE_CATEGORY = "ToDoListCategory";
    private const TABLE_STATUS = "ToDoListStatus";

    public function getCategories(): array
    {
        if (!self::checkTableExists(self::TABLE_CATEGORY)) $this->createCategories();

        return $this->conn->executeQuery("SELECT * FROM " . self::TABLE_CATEGORY);
    }

    public function getToDoList(): array
    {
        if (!self::checkTableExists(self::TABLE_TO_DO_LIST)) $this->createToDoList();
        // return $this->conn->executeQuery("SELECT * FROM " . self::TABLE_TO_DO_LIST);
        return $this->conn->executeQuery("SELECT A.*, B.color colorCategory, B.nombre nameCategory FROM " . self::TABLE_TO_DO_LIST . " A left join " . self::TABLE_CATEGORY . " B on A.categoria = B.id");
    }

    private function createCategories(): void
    {
        self::createTable(self::TABLE_CATEGORY);

        $data = [
            "nombre" => "Importante",
            "color" => "#dc3545"
        ];

        self::prepare(self::TABLE_CATEGORY, ["data" => $data])->insert();
    }

    private function createToDoList(): void
    {
        self::createTable(self::TABLE_TO_DO_LIST);
        self::createColumn(self::TABLE_TO_DO_LIST, "estado", "INT DEFAULT 1");

        if (!self::checkTableExists(self::TABLE_STATUS)) {
            self::createTable(self::TABLE_STATUS);

            $dataStatus = [
                ["nombre" => "pendiente"],
                ["nombre" => "completado"],
                ["nombre" => "eliminado"]
            ];

            foreach ($dataStatus as $key => $data) self::prepare(self::TABLE_STATUS, ["data" => $data])->insert();
        }
    }

    public function addCategory(array $data): array
    {
        return self::prepare(self::TABLE_CATEGORY, $data)->insert();
    }

    public function addToDoList(array $data): array
    {
        return self::prepare(self::TABLE_TO_DO_LIST, $data)->insert();
    }

    static function timeElapsed(String $date, bool $returnComplete = false): string
    {
        $now = time();
        $date = strtotime($date);
        $diffSeconds = $now - $date;

        $intervals = [
            "year"   => 31536000,
            "month"  => 2592000,
            "week"   => 604800,
            "day"    => 86400,
            "hour"   => 3600,
            "minute" => 60,
            "second" => 1
        ];

        $result = [];

        foreach ($intervals as $label => $seconds) {
            $quantity = floor($diffSeconds / $seconds);

            if ($quantity > 0) {
                $result[] = "{$quantity} " . ($quantity === 1 ? $label : "{$label}s");
                $diffSeconds -= $quantity * $seconds;
                if ($returnComplete === false) return trim(implode(" - ", $result));
            }
        }

        if ($returnComplete === true) return trim(implode(" - ", $result));

        return "";
    }
}
