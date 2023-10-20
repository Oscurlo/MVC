<?php

namespace Model;

use PDO;
use Exception;
use PDOException;

class DB
{
    private $conn = NULL, $dsn, $params;

    public function __construct()
    {
        $this->params = DATABASE;
        $this->dsn = [
            "MYSQL" => "mysql:{$this->params["HOSTNAME"]};dbname={$this->params["USERNAME"]}" . ($this->params["PORT"] ? ";port={$this->params["PORT"]}" : ""),
            "SQLSRV" => "sqlsrv:{$this->params["HOSTNAME"]};Database={$this->params["USERNAME"]}",
            "SQLITE" => "sqlite:{$this->params["FILE"]}"
        ];
    }

    public function connect(): void
    {
        try {
            $this->conn = new PDO($this->dsn[$this->params["GESTOR"]], $this->params["USERNAME"], $this->params["PASSWORD"]);
        } catch (Exception $th) {
            throw $th;
        }
    }

    public function executeQuery($query, $prepare = [], $options = [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY]): mixed
    {
        if (!$this->conn) throw new Exception("no connection to database");

        try { # valida si se ejecuto la consulta
            $exec = $this->conn->prepare($query, $options);
            $exec->execute($prepare);

            try { # valida si contiente datos
                return $exec->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $th) { # Si no contiene datos solo le retorno true de que todo bien
                return true;
            }
        } catch (PDOException $th) {
            throw $th;
        }
    }

    public function close(): void
    {
        $this->conn = null;
    }

    public function getConn(): ?PDO
    {
        return $this->conn;
    }
}
