<?php

/**
 * http://socketo.me/
 */

namespace MyApp;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

use Model\DB;
use Model\ProcessData;

use Exception;
use SplObjectStorage;

class Chat extends ProcessData implements MessageComponentInterface
{
    protected $DB;
    protected $clients;
    protected $users = [];
    protected $typeValid = ["userInfo", "userList", "userChat"];

    public function __construct()
    {
        echo "start webSocket \n";

        $this->DB = new DB;
        $this->DB->connect();

        $this->clients = new SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        $this->users[$conn->resourceId] = [
            "systemID" => null,
            "socketID" => $conn->resourceId,
            "conn" => $conn
        ];

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);


        if (isset($data["type"]) && in_array($data["type"], $this->typeValid)) {

            // $this->prepare("WebSocket", ["data" => $data])->insert();

            if (in_array($data["type"], ["userChat", "userList"])) {

                if (isset($data["to"])) if (isset($this->users[$data["to"]])) {
                    $client = $this->users[$data["to"]]["conn"];
                    $client->send($msg); // Enviar a un usuario
                } else foreach ($this->clients as $client) if ($from !== $client) $client->send($msg); // Enviar a todos los usuarios

            } else if ($data["type"] === "userInfo") {
                $this->users[$from->resourceId]["systemID"] = $data["id"] ?? null;
                $this->sendUserList();
            }
        }
    }


    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        unset($this->users[$conn->resourceId]);

        $this->sendUserList();

        echo "Disconnected client\n";
    }

    public function onError(ConnectionInterface $conn, Exception $e)
    {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }

    /**
     * Envía a todos los usuarios una lista actualizada de los que están activos
     */
    protected function sendUserList()
    {
        $userList = [];

        foreach ($this->users as $user) {
            unset($user["conn"]);
            $userList[] = $user;
        }

        $data = [
            "type" => "userList",
            "users" => $userList
        ];

        foreach ($this->users as $user) $user["conn"]->send(json_encode($data));
    }
}
