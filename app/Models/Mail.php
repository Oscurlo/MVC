<?php

namespace Model;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use System\Config\AppConfig;

class Mail
{
    private $Mailer;

    public $EXCEPTION = true;
    public $SMTP_AUTH = true;
    public $IS_HTML = false;

    const VALID_COPY = [
        "ADDRESS" => "addAddress",
        "REPLYTO" => "addReplyTo",
        "CC" => "addCC",
        "BCC" => "addBCC"
    ];

    public function __construct()
    {
        # Create a new instance of PHPMailer with exception handling.
        $this->Mailer = new PHPMailer($this->EXCEPTION);
    }

    /**
     * Send an email.
     *
     * @param array|string $to
     * @param string $Subject
     * @param string $Body
     * @param array|string $files
     * @throws Exception
     */
    public function sendMail($to, $Subject, $Body, $files = "")
    {
        try {
            # Server settings
            $this->Mailer->SMTPDebug = SMTP::DEBUG_SERVER;
            $this->Mailer->isSMTP();
            $this->Mailer->Host = AppConfig::MAIL["HOST"];
            $this->Mailer->SMTPAuth = $this->SMTP_AUTH;
            $this->Mailer->Username = AppConfig::MAIL["USERNAME"];
            $this->Mailer->Password = AppConfig::MAIL["PASSWORD"];
            $this->Mailer->SMTPSecure = AppConfig::MAIL["SMTP"] ?? PHPMailer::ENCRYPTION_SMTPS;
            $this->Mailer->Port = AppConfig::MAIL["PORT"];

            # Recipients
            $this->Mailer->setFrom(AppConfig::MAIL["USERNAME"], AppConfig::COMPANY["NAME"]);
            foreach (self::addRessee($to) as $data) $this->Mailer->$data["method"]($data["address"], $data["name"]);

            # Attachments
            foreach (self::attachments($files) as $data) $this->Mailer->$data["method"]($data["path"], $data["name"]);

            # Content
            $this->Mailer->isHTML($this->IS_HTML);
            $this->Mailer->Subject = $Subject;
            $this->Mailer->Body = $Body;

            # Send the email.
            $this->Mailer->send();
        } catch (Exception $th) {
            # Handle exceptions and provide detailed error information.
            throw new Exception("{$this->Mailer->ErrorInfo}\n{$th->getMessage()}");
        }
    }

    /**
     * Get the list of recipients.
     *
     * @param string|array $toAddress
     * @return array
     * @throws Exception
     */
    private function addRessee($toAddress): array
    {
        $ressee = [];

        switch (strtoupper(gettype($toAddress))) {
            case 'STRING':
                # Ensure that the email address is provided.
                if (!$toAddress) throw new Exception("Email is required");

                # If a single email address is provided, add it to the list.
                $ressee[] = [
                    "method" => self::VALID_COPY["ADDRESS"],
                    "address" => $toAddress,
                    "name" => ""
                ];
                break;
            case 'ARRAY':
                foreach ($toAddress as $i => $data) {
                    # Extract recipient information from the array.
                    $copy = $data["copy"] ?? null;
                    $method = in_array(strtoupper($copy), array_keys(self::VALID_COPY)) ? self::VALID_COPY[strtoupper($copy)] : self::VALID_COPY["ADDRESS"];

                    $address = $data["address"] ?? null;
                    # Ensure that the email address is provided.
                    if (!$address) throw new Exception("Email is required");

                    $ressee[$i] = [
                        "method" => $method,
                        "address" => $address,
                        "name" => $data["name"] ?? ""
                    ];
                }
                break;
        }

        return $ressee;
    }

    /**
     * Get the list of attachments.
     *
     * @param string|array $files
     * @return array
     */
    private function attachments($files): array
    {
        $attachments = [];

        switch (strtoupper(gettype($files))) {
            case 'STRING':
                # If a single file path is provided, add it to the list of attachments.
                if (file_exists($files)) $attachments[] = [
                    "method" => "addAttachment",
                    "path" => $files,
                    "name" => ""
                ];
                break;
            case 'ARRAY':
                # If an array of files is provided, add valid files to the list of attachments.
                foreach ($files as $i => $data) if (file_exists($data["path"])) $attachments[$i] = [
                    "method" => "addAttachment",
                    "path" => $data["path"],
                    "name" => $data["name"] ?? ""
                ];
                break;
        }

        return $attachments;
    }
}
