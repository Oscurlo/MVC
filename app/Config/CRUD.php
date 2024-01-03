<?php

namespace Config;

use Closure;
use PDO;

class CRUD extends ProcessData
{
    public Closure $create, $read, $update, $delete;
    public function __construct(?PDO $conn = null)
    {
        parent::__construct($conn);
        $this->create = fn (String $table, array $data): array => $this->prepare($table, $data)->insert();
        $this->read   = fn (String $table, String $condition = "1 = 1", array $prepare = []): mixed => $this->conn->executeQuery("SELECT * FROM {$table} WHERE {$condition}", $prepare);
        $this->update = fn (String $table, array $data, String $condition): array => $this->prepare($table, $data)->update($condition);
        $this->delete = fn (): String => "coming soon ¯\_(ツ)_/¯";
    }
}
