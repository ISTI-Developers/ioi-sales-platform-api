<?php

require_once __DIR__ . "/../config/env.php";
class Controller
{
    public $connection;
    public $statement;
    public $isConnectionSuccess;
    public $connectionError;

    public function __construct($server = DB_SERVER, $dbname = DB_NAME, $username = DB_USERNAME, $password = DB_PASSWORD)
    {
        try {
            $dsn = "mysql:host={$server};dbname={$dbname}";
            $this->connection = new PDO($dsn, $username, $password);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->isConnectionSuccess = true;
        } catch (PDOException $e) {
            $this->connectionError = $e->getMessage();
        }
    }

    public function setStatement($query)
    {
        if ($this->isConnectionSuccess) {
            $this->statement = $this->connection->prepare($query);
        } else {
            throw new Exception($this->connectionError);
        }
    }

    public function auditLog(array $data)
    {
        $this->setStatement("INSERT INTO audit_logs (account_id, account_name, action, module, entity_type, entity_id, old_values, new_values, description) VALUES (:account_id, :account_name, :action, :module, :entity_type, :entity_id, :old_values, :new_values, :description)");
        return $this->statement->execute([
            ':account_id' => $data['account_id'] ?? null,
            ':account_name' => $data['account_name'] ?? null,
            ':action' => $data['action'],
            ':module' => $data['module'],
            ':entity_type' => $data['entity_type'] ?? null,
            ':entity_id' => $data['entity_id'] ?? null,
            ':old_values' => isset($data['old_values']) ? json_encode($data['old_values']) : null,
            ':new_values' => isset($data['new_values']) ? json_encode($data['new_values']) : null,
            ':description' => $data['description'] ?? null,
        ]);
    }
}
