<?php

namespace Controller;

use Model\ProcessData;
use System\Config\AppConfig;
use PDOException;

class Session extends ProcessData
{
    const TABLE_USER = "usuario";
    const TABLE_USER_ROLE = "rol_usuario";

    # As I don't have many roles, I'm handling it this way. In the future, if there are many roles, it's better to add them to a table.
    const ROLE_ADMIN = ["Admin"];

    private $userInfo = null;

    /**
     * Starts a user session based on the provided username and password.
     *
     * @param string $user Username
     * @param string $pass Password
     *
     * @return bool True if authentication is successful, false otherwise.
     */
    public function startSession($user, $pass)
    {
        // return $this->conn->getGestor();
        # Check if the user table exists, if not, create it
        if (!$this->tableManager->checkTableExists(self::TABLE_USER)) $this->createTableUser();

        # Authenticate the user
        $dataUser = self::authenticateUser($user, $pass);

        # If user authentication is successful, set session variables
        if (!empty($dataUser)) foreach ($dataUser as $data) {
            foreach ($data as $key => $value) $_SESSION[$key] = $value;
            $_SESSION["SESSION_MODE"] = self::getSessionMode();
            return true;
        }

        return false;
    }

    /**
     * Registers a new user.
     *
     * @param array $data User data to be registered.
     *
     * @return array
     */
    public function registerUser($data): array
    {
        return self::prepare(self::TABLE_USER, $data)->insert();
    }

    /**
     * Authenticates a user based on the provided username and password.
     *
     * @param string $user Username
     * @param string $pass Password
     *
     * @return array User information if authentication is successful.
     * @throws PDOException If an error occurs during authentication.
     */
    private function authenticateUser($user, $pass): array
    {
        try {
            $tableU = self::TABLE_USER;
            $tableUR = self::TABLE_USER_ROLE;
            $data = $this->conn->executeQuery(<<<SQL
                SELECT {$tableU}.*, {$tableUR}.role
                FROM {$tableU}
                INNER JOIN {$tableUR} ON {$tableU}.id_role = {$tableUR}.id
                WHERE {$tableU}.email = :MAIL AND {$tableU}.password = :PASS
            SQL, [
                ":MAIL" => $user,
                ":PASS" => $pass
            ]);

            $this->userInfo = $data[0];

            return $data;
        } catch (PDOException $th) {
            # Throw the exception if an error occurs during authentication
            throw $th;
        }
    }

    /**
     * Gets the session mode based on the user's role.
     *
     * @return string Session mode ("AdminMode" or "ClientMode").
     */
    private function getSessionMode(): string
    {
        $role = $this->userInfo["role"] ?? false;

        return in_array($role, self::ROLE_ADMIN) ? "AdminMode" : "ClientMode";
    }

    /**
     * Creates the user table and sets up initial data if it doesn't exist.
     */
    private function createTableUser(): void
    {
        # Check if the user role table exists, if not, create it
        if (!$this->tableManager->checkTableExists(self::TABLE_USER_ROLE)) {
            $this->tableManager->createTable(self::TABLE_USER_ROLE);

            # Initial roles data
            $roles = [
                [
                    "id"    => 1,
                    "role"  => "Admin"
                ], [
                    "id"    => 2,
                    "role"  => "Client"
                ]
            ];

            # Insert initial roles data
            foreach ($roles as $role) self::prepare(self::TABLE_USER_ROLE, ["data" => $role])->insert();
            # Add a foreign key relationship between user and user role tables
            $this->tableManager->addForeignKey([self::TABLE_USER => self::TABLE_USER_ROLE], ["id_role" => "id"]);
        }

        # Create the user table if it doesn't exist
        $this->tableManager->createTable(self::TABLE_USER);

        # Add a column for the user role with a default value
        $this->tableManager->createColumn(self::TABLE_USER, "id_role", "Int DEFAULT 2");

        # Initial data for the admin user
        $data = [
            "name"      => "Admin",
            "email"     => "Admin@admin.com",
            "password"  => "Admin",
            "files"     => AppConfig::COMPANY["LOGO"],
            "id_role"   => 1
        ];

        # Insert the initial admin user data
        self::prepare(self::TABLE_USER, ["data" => $data])->insert();
    }

    /**
     * Destroys the current session.
     */
    public static function destroy(): void
    {
        session_destroy();
    }
}
