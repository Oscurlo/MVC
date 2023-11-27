<?php

/**
 * Basic database connection class using PDO.
 * The class supports MySQL, SQL Server, and SQLite databases.
 * 
 * I'll try to maintain the comments in English for you ;)
 */

namespace Model;

use Error;
use PDO;
use Exception;
use PDOException;
use System\Config\AppConfig;

class DB
{
    # Properties for database connection
    private $conn = null, $array_dsn, $params, $gestor;
    protected $dsn, $username, $password, $option;

    # Flag to create a database if it doesn't exist
    public $CREATE_DATABASE = false;

    # Valid database types
    const VALID_DB_TYPES = ["MYSQL", "SQLSRV", "SQLITE"];

    # Constructor
    public function __construct()
    {
        # Get database parameters from AppConfig class
        $this->params = AppConfig::DATABASE;

        # Set the database type and validate it
        $this->gestor = strtoupper($this->params["GESTOR"]);
        if (!in_array($this->gestor, self::VALID_DB_TYPES))
            throw new Error("Invalid database type for connection: {$this->gestor}");

        # Define DSN for different database types
        $this->array_dsn = [
            "MYSQL" => "mysql:host={$this->params["HOSTNAME"]};dbname={$this->params["DATABASE"]}" . ($this->params["PORT"] ? ";port={$this->params["PORT"]}" : ""),
            "SQLSRV" => "sqlsrv:Server={$this->params["HOSTNAME"]};Database={$this->params["DATABASE"]}",
            "SQLITE" => "sqlite:{$this->params["FILE"]}"
        ];

        # Set DSN, username, password, and option
        $this->dsn = $this->array_dsn[$this->gestor] ?? false;
        $this->username = $this->params["USERNAME"];
        $this->password = $this->params["PASSWORD"];
        $this->option = null;
    }

    # Getters and Setters
    # -------------------------------------------------------
    # Getter and setter for database type
    public function getGestor(): String
    {
        return $this->gestor;
    }
    public function setGestor(string $gestor): void
    {
        if (!in_array($gestor, self::VALID_DB_TYPES))
            throw new Error("Invalid database type for connection: {$gestor}");
        $this->gestor = $gestor;
    }
    # -------------------------------------------------------
    # Getter and setter for DSN
    public function getDSN(): String
    {
        return $this->dsn;
    }
    public function setDSN(string $dsn): void
    {
        $this->dsn = $dsn;
    }
    # -------------------------------------------------------
    # Getter and setter for username
    public function getUser(): String
    {
        return $this->username;
    }
    public function setUser(string|null $user): void
    {
        $this->username = $user;
    }
    # -------------------------------------------------------
    # Getter and setter for password
    public function getPass(): String
    {
        return $this->password;
    }
    public function setPass(string|null $pass): void
    {
        $this->password = $pass;
    }
    # -------------------------------------------------------
    # Getter and setter for connection
    public function getConn(): ?PDO
    {
        return $this->conn instanceof PDO ? $this->conn : null;
    }
    public function setConn(PDO $conn): void
    {
        $this->conn = $conn;
    }
    # -------------------------------------------------------
    # Getter and setter for option
    public function getOption(): array|null
    {
        return $this->option;
    }
    public function setOption(array|null $op): void
    {
        $this->option = $op;
    }
    # -------------------------------------------------------

    # Connect
    public function connect(): void
    {
        try {
            # Establish a new PDO connection
            $this->conn = new PDO($this->dsn, $this->username, $this->password, $this->option);
        } catch (PDOException $th) {
            # Handle database connection error
            if ($th->getCode() === 1049 && $this->CREATE_DATABASE === true) {
                $this->CREATE_DATABASE = false;
                # If CREATE_DATABASE flag is set, try creating the database
                $status = self::createDatabase();
                if ($status === true) self::connect();
                else throw $th;
            } else throw $th;
        }
    }

    # Execute Query
    public function executeQuery($query, $prepare = [], $options = [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]): mixed
    {
        if (!$this->conn) throw new Exception("No connection to database :(");

        try { # Validate if the query executed successfully
            $query = trim($query);

            $exec = $this->conn->prepare($query, $options);
            $exec->execute($prepare);

            $queryType = strtoupper(explode(" ", $query)[0]);

            # Return appropriate result based on query type
            if (in_array($queryType, ["INSERT", "CREATE", "ALTER"])) return true;
            else if (in_array($queryType, ["UPDATE", "DELETE"])) return $exec->rowCount();
            else return $exec->fetchAll(PDO::FETCH_ASSOC); # "SELECT" and the rest :n
        } catch (PDOException $th) {
            # Handle PDO exceptions
            throw $th;
        }
    }

    # Transaction handling
    public function beginTransaction(): void
    {
        $this->conn->beginTransaction();
    }

    public function commit(): void
    {
        $this->conn->commit();
    }

    public function rollBack(): void
    {
        $this->conn->rollBack();
    }

    # Method to create a database
    protected function createDatabase(): bool
    {
        try {
            # Create database query for different database types
            $query = [
                "MYSQL" => "CREATE DATABASE IF NOT EXISTS {$this->params["DATABASE"]};",
                "SQLSRV" => "CREATE DATABASE {$this->params["DATABASE"]};"
            ][$this->gestor] ?? false;

            if ($query) {
                # Modify DSN to connect to the server without selecting a specific database
                $dsn = str_replace(["dbname={$this->params["DATABASE"]}", "Database={$this->params["DATABASE"]}"], "", $this->dsn);
                $connTemp = new PDO($dsn, $this->username, $this->password, $this->option);

                # Set the temporary connection and execute the create database query
                self::setConn($connTemp);
                $status = self::executeQuery($query);
                return $status === true;
            }
            return false;
        } catch (Exception $th) {
            # Handle unexpected errors during database creation
            throw new Exception("Unexpected error creating the database: {$th->getMessage()}");
        }
    }

    # Close Connect
    public function close(): void
    {
        # Set the connection to null
        $this->conn = null;
    }
}
