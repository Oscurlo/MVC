<?php

namespace Model;

use PDO;
use Exception;

class TableManager
{
    protected $dbManager;
    public function __construct(PDO $conn = null)
    {
        $this->dbManager = new DB();

        if ($conn !== null) $this->dbManager->setConn($conn);
        else $this->dbManager->connect();
    }

    # Check if a table exists in the database
    public function checkTableExists($table): bool
    {
        # SQL query to check table existence based on the database manager's gestor
        $query = [
            "MYSQL" => trim(<<<SQL
                SHOW TABLES LIKE '{$table}';
            SQL),
            "SQLSRV" => trim(<<<SQL
                SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME LIKE '{$table}'
            SQL),
            "SQLITE" => trim(<<<SQL
                SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}';
            SQL)
        ][$this->dbManager->getGestor()];

        # Execute the query and return true if data is not empty
        $data = $this->dbManager->executeQuery($query);
        return !empty($data);
    }

    # Create a table if it doesn't exist
    public function createTable($table): void
    {
        # Check if the table exists; if yes, return
        if (self::checkTableExists($table)) return;

        $table = trim($table);
        # Throw an exception if the table name is empty
        if (empty($table)) throw new Exception("Table is mandatory");

        # SQL query to create a table based on the database manager's gestor
        $query = [
            "MYSQL" => trim(<<<SQL
                CREATE TABLE IF NOT EXISTS `{$table}` (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    fechaRegistro DATETIME DEFAULT CURRENT_TIMESTAMP()
                )
            SQL),
            "SQLSRV" => trim(<<<SQL
                IF NOT EXISTS (SELECT * FROM sysobjects WHERE name = '{$table}' and xtype = 'U')
                CREATE TABLE {$table} (
                    id INT IDENTITY(1,1) PRIMARY KEY,
                    fechaRegistro DATETIME DEFAULT CURRENT_TIMESTAMP()
                )
            SQL),
            "SQLITE" => trim(<<<SQL
                CREATE TABLE IF NOT EXISTS `{$table}` (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    fechaRegistro TEXT DEFAULT CURRENT_TIMESTAMP
                )
            SQL)
        ][strtoupper($this->dbManager->getGestor())] ?? "";

        # Execute the query to create the table
        $this->dbManager->executeQuery($query);
    }

    # Check if a column exists in a table
    public function checkColumnExists($table, $column): bool
    {
        $gestor = $this->dbManager->getGestor();
        # SQL query to check column existence based on the database manager's gestor
        $query = [
            "MYSQL" => trim(<<<SQL
                SHOW COLUMNS FROM {$table} LIKE '{$column}';
            SQL),
            "SQLSRV" => trim(<<<SQL
                SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table}' AND COLUMN_NAME LIKE '{$column}';
            SQL),
            "SQLITE" => trim(<<<SQL
                PRAGMA table_info('{$table}');
            SQL),
        ][strtoupper($gestor)] ?? "";

        # Execute the query and check if the column exists
        $data = $this->dbManager->executeQuery($query);

        foreach ($data as $row) if ($gestor === "MYSQL" || $gestor === "SQLSRV") {
            if (strtolower($row['Field']) === strtolower($column)) return true;
        } elseif ($gestor === "SQLITE") {
            if (strtolower($row['name']) === strtolower($column)) return true;
        }

        return false;
    }

    # Create a column in a table if it doesn't exist
    public function createColumn($table, $column, $type = null): void
    {
        # Check if the column exists; if yes, return
        if (self::checkColumnExists($table, $column)) return;

        # Set a default type if not provided
        if (is_null($type)) $type = [
            "MYSQL" => "TEXT DEFAULT NULL",
            "SQLSRV" => "VARCHAR(MAX) DEFAULT NULL",
            "SQLITE" => "VARCHAR(255) DEFAULT NULL"
        ][strtoupper($this->dbManager->getGestor())] ?? "";

        # SQL query to add a column to the table based on the database manager's gestor
        $query = [
            "MYSQL" => trim(<<<SQL
                ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$type}
            SQL),
            "SQLSRV" => trim(<<<SQL
                ALTER TABLE {$table} ADD {$column} {$type}
            SQL),
            "SQLITE" => trim(<<<SQL
                ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$type}
            SQL)
        ][strtoupper($this->dbManager->getGestor())] ?? "";

        # Execute the query to add the column to the table
        $this->dbManager->executeQuery($query);
    }

    /**
     * This method adds foreign keys to a database.
     *
     * Keys point to the initial table where the foreign key will be created,
     * and values point to the relational table.
     *
     * Example:
     * self::addForeignKey(["table1" => "table2"], ["column1" => "column2"]);
     *
     * @param array $tables Associative array where keys are destination tables and values are source tables.
     * @param array $columns Associative array where keys are destination columns and values are source columns.
     *
     * @return array An array with the status and error information for each foreign key addition.
     */
    public function addForeignKey($tables, $columns): array
    {
        $response = [];

        # Check if the number of tables and columns match
        $countTables = count($tables);
        $countColumns = count($columns);

        if ($countTables === $countColumns) {
            # Create an array with the necessary foreign key data
            $foreignData = array_map(function ($kt, $vt, $kc, $vc) {
                if (!empty($kt) && !empty($vt) && !empty($kc) && !empty($vc)) return [
                    "destination_table" => $kt,
                    "destination_column" => $kc,
                    "source_table" => $vt,
                    "source_column" => $vc,
                ];
                else return null;
            }, array_keys($tables), array_values($tables), array_keys($columns), array_values($columns));

            # Remove null entries from the array
            $foreignData = array_filter($foreignData, function ($fD) {
                return $fD !== null;
            });

            # Iterate through the foreign key data and execute SQL queries
            foreach ($foreignData as $i => $data) try {
                $this->dbManager->executeQuery(trim(<<<SQL
                    ALTER TABLE {$data["destination_table"]}
                    ADD CONSTRAINT fk_{$data["destination_column"]}
                    FOREIGN KEY ({$data["destination_column"]})
                    REFERENCES {$data["source_table"]} ({$data["source_column"]});
                SQL));

                # If successful, add status true to the response array
                $response[] = [
                    "status" => true,
                    "error" => false
                ];
            } catch (Exception $th) {
                # If an exception occurs, add status false and the error message to the response array
                $response[] = [
                    "status" => false,
                    "error" => $th->getMessage()
                ];
            }
        } else {
            # If the number of tables and columns doesn't match, throw an exception
            throw new Exception("Mismatched number of tables and columns");
        }

        return $response;
    }
}
