<?php

namespace Controller;

use Model\DB;
use Model\ProcessData;
use Model\TableManager;
use Exception;

class Chat_test extends ProcessData
{
    const TABLE_CHAT = "chat";
    const TABLE_USER = "usuario";

    public function __construct()
    {
        $this->conn = new DB;
        $this->conn->connect();
        $this->tableManager = new TableManager($this->conn->getConn());
    }

    public function showChat($id)
    {
        if (!$this->tableManager->checkTableExists($this::TABLE_CHAT)) $this->createTableChat();

        return <<<HTML
        <div class="card direct-chat direct-chat-primary m-3" style="position: fixed; bottom: 0; right: 0" data-id="myFirstChat">
            <div class="card-header">
                <!-- <h3 class="card-title" data-card-widget="collapse">Direct Chat</h3> -->
                <div class="card-tools">
                    <!-- <span title="3 New Messages" class="badge badge-primary">3</span> -->
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" title="Contacts" data-widget="chat-pane-toggle">
                        <i class="fas fa-comments"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="direct-chat-messages"></div>
                <div class="direct-chat-contacts"></div>
            </div>
            <div class="card-footer">
                <form>
                    <div class="input-group">
                        <input type="hidden" name="data[sender_id]">
                        <input type="hidden" name="data[receiver_id]">
                        <input type="text" name="data[message]" placeholder="Mensaje" class="form-control" required disabled>
                        <input type="hidden" name="to">
                        <span class="input-group-append">
                            <button type="submit" class="btn btn-primary" disabled>Send</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
        HTML;
    }

    public function showMessages(Int $from = null, Int $to): String
    {
        if (is_null($from)) $from = $_SESSION["id"] ?? 0;

        $msg = self::getMessages($from, $to);
        $messages = [];

        foreach ($msg as $data) {
            $dataUser = self::getDataUser($data["sender_id"])[0];

            if (!empty($dataUser)) {
                $name = $dataUser["name"];
                $icon = $dataUser["files"];

                $date = date("d M h:s A", strtotime($data["fechaRegistro"]));
                $direction = $data["sender_id"] == $from ? "left" : "right";
                $directionReverse = $direction == "left" ? "right" : "left";

                $messages[] = <<<HTML
                <div class="direct-chat-msg {$direction}">
                    <div class="direct-chat-infos clearfix">
                        <span class="direct-chat-name float-{$direction}">{$name}</span>
                        <span class="direct-chat-timestamp float-{$directionReverse}">{$date}</span>
                    </div>
                    <img class="direct-chat-img" src="{$icon}" alt="message user image" style="height: 40px">
                    <div class="direct-chat-text">
                        {$data["message"]}
                    </div>
                </div>
                HTML;
            }
        }

        return implode("\n", $messages);
    }

    public function showContacts($users): String
    {
        $contacts = [];

        foreach ($users as $data) if (isset($data["systemID"]) && isset($data["socketID"])) {
            $dataUser = self::getDataUser($data["systemID"])[0];

            if (!empty($dataUser)) {
                $name = $dataUser["name"];
                $date = date("d/m/Y", strtotime($dataUser["fechaRegistro"]));
                $icon = $dataUser["files"];

                $contacts[] = <<<HTML
                <li data-system="{$data['systemID']}" data-socket="{$data['socketID']}">
                    <a href="#">
                        <img class="contacts-list-img" src="{$icon}" alt="User Avatar" style="height: 40px">
                        
                        <div class="contacts-list-info">
                            <span class="contacts-list-name">
                                {$name}
                                <small class="contacts-list-date float-right">{$date}</small>
                            </span>
                            <!-- <span class="contacts-list-msg">How have you been? I was...</span> -->
                        </div>
                    </a>
                </li>
                HTML;
            }
        }
        $contacts = implode("\n", $contacts);
        return <<<HTML
        <ul class="contacts-list">
            {$contacts}
        </ul>
        HTML;
    }

    public function addChat(array $data): array
    {
        return self::prepare(self::TABLE_CHAT, $data)->insert();
    }

    protected function createTableChat(): void
    {
        $this->tableManager->createTable(self::TABLE_CHAT);
        $this->tableManager->createColumn(self::TABLE_CHAT, "sender_id", "INT DEFAULT NULL");
        $this->tableManager->createColumn(self::TABLE_CHAT, "receiver_id", "INT DEFAULT NULL");
        $this->tableManager->createColumn(self::TABLE_CHAT, "seen", "BOOLEAN DEFAULT FALSE");
        $this->tableManager->createColumn(self::TABLE_CHAT, "message");
    }

    protected function getMessages(Int $from, Int $to): array
    {
        try {
            $TABLE = self::TABLE_CHAT;
            return $this->conn->executeQuery(<<<SQL
            SELECT * FROM {$TABLE}
            WHERE (sender_id = :FROM_1 and receiver_id = :TO_1) or (sender_id = :TO_2 and receiver_id = :FROM_2)
            SQL, [
                ":FROM_1" => $from,
                ":FROM_2" => $from,
                ":TO_1" => $to,
                ":TO_2" => $to
            ]);
        } catch (Exception $th) {
            throw $th;
        }
    }

    protected function getDataUser($id): array
    {
        try {
            $TABLE = self::TABLE_USER;
            return $this->conn->executeQuery("SELECT * FROM {$TABLE} WHERE id = :ID", [":ID" => $id]);
        } catch (Exception $th) {
            throw $th;
        }
    }
}
