<?php

/**
 * Conexión básica a la base de datos,
 * de hecho utilicé los mismos parámetros que requiere PDO para establecer la conexión.
 * 
 * Si algo me disculpo por el spanglish,
 * pero en algún punto de mi vida me gustaría salir del país,
 * así que debes en cuando intento mantener todo en inglés
 */

namespace Model;

use Error;
use PDO;
use Exception;
use PDOException;
use System\Config\AppConfig;

class DB
{
    private $conn = null, $array_dsn, $params, $gestor;
    protected $dsn, $username, $password, $option;

    const isValid = ["MYSQL", "SQLSRV", "SQLITE"];

    # construct
    public function __construct()
    {
        $this->params = AppConfig::DATABASE;

        $this->gestor = strtoupper($this->params["GESTOR"]);

        if (!in_array($this->gestor, self::isValid))
            throw new Error("Gestor no válido para establecer la conexión: {$this->gestor}");

        $this->array_dsn = [
            "MYSQL" => "mysql:host={$this->params["HOSTNAME"]};dbname={$this->params["DATABASE"]}" . ($this->params["PORT"] ? ";port={$this->params["PORT"]}" : ""),
            "SQLSRV" => "sqlsrv:Server={$this->params["HOSTNAME"]};Database={$this->params["DATABASE"]}",
            "SQLITE" => "sqlite:{$this->params["FILE"]}"
        ];

        $this->dsn = $this->array_dsn[$this->gestor] ?? false;
        $this->username = $this->params["USERNAME"];
        $this->password = $this->params["PASSWORD"];
        $this->option = null;
    }

    # Getters and Setters
    // -------------------------------------------------------
    public function getGestor(): String
    {
        return $this->gestor;
    }
    public function setGestor(string $gestor): void
    {
        if (!in_array($this->gestor, self::isValid))
            throw new Error("Gestor no válido para establecer la conexión: {$this->gestor}");
        $this->gestor = $gestor;
    }
    public function getDSN(): String
    {
        return $this->dsn;
    }
    public function setDSN(string $dsn): void
    {
        $this->dsn = $dsn;
    }
    // -------------------------------------------------------
    public function getUser(): String
    {
        return $this->username;
    }
    public function setUser(string|null $user): void
    {
        $this->username = $user;
    }
    // -------------------------------------------------------
    public function getPass(): String
    {
        return $this->password;
    }
    public function setPass(string|null $pass): void
    {
        $this->password = $pass;
    }
    // -------------------------------------------------------
    public function getConn(): ?PDO
    {
        return $this->conn instanceof PDO ? $this->conn : null;
    }
    public function setConn(PDO $conn): void
    {
        $this->conn = $conn;
    }
    // -------------------------------------------------------
    /**
     * Realmente no he usado esto de "option" de hecho creo que no se para qué sirve :c, pero lo pongo por si acaso igual ;)
     */
    public function getOption(): array|null
    {
        return $this->option;
    }
    public function setOption(array|null $op): void
    {
        $this->option = $op;
    }
    // -------------------------------------------------------

    # Connect
    public function connect(): void
    {
        try {
            $this->conn = new PDO($this->dsn, $this->username, $this->password, $this->option);
        } catch (PDOException $th) {
            throw $th;
        }
    }


    # Execute Query
    public function executeQuery($query, $prepare = [], $options = [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]): mixed
    {
        if (!$this->conn) throw new Exception("No connection to database :(");

        try { # Valida si se ejecuto la consulta
            $query = trim($query);

            $exec = $this->conn->prepare($query, $options);
            $exec->execute($prepare);

            $queryType = strtoupper(explode(" ", $query)[0]);

            # De momento solo pensé como en lo más básico dejando por defecto el retorno de los datos 🙃
            if (in_array($queryType, ["INSERT", "CREATE"])) return true;
            else if (in_array($queryType, ["UPDATE", "DELETE"])) return $exec->rowCount();
            else return $exec->fetchAll(PDO::FETCH_ASSOC); # "SELECT" and the rest :n
        } catch (PDOException $th) {
            throw $th;
        }
    }

    # Por si acaso ¯\_(ツ)_/¯
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

    # Close Connect
    public function close(): void
    {
        $this->conn = null;
    }
}
