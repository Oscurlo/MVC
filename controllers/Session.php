<?php

namespace Controller;

use Model\ProcessData;
use PDOException;

class Session extends ProcessData
{
    const TABLE_USER = "usuario";

    public function startSession($user, $pass): Bool
    {
        $dataUser = self::authenticateUser($user, $pass);

        if (!empty($dataUser)) foreach ($dataUser as $data) {
            foreach ($data as $key => $value)
                $_SESSION[$key] = $value;
            $_SESSION["usuario"] = $_SESSION["name"] ?? "";
            return true;
        }

        return false;
    }

    public function registerUser($data)
    {
        return self::prepare(self::TABLE_USER, $data)->insert();
    }

    private function authenticateUser($user, $pass): array
    {
        try {
            $table = self::TABLE_USER;
            return $this->conn->executeQuery(<<<SQL
                SELECT * FROM {$table} WHERE email = :MAIL AND password = :PASS
            SQL, [
                ":MAIL" => $user,
                ":PASS" => $pass
            ]);
        } catch (PDOException $th) {
            throw $th;
        }
    }
}
