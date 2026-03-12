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

    public function get_client_information_value($table_name, $row_id)
    {
        $column = "name";
        $id = "ID";
        switch ($table_name) {
            case "user_information":
                $column = "CONCAT(first_name, ' ', last_name) as full_name";
                $id = "account_id";
                break;
            case "sales_units":
                $column = "unit_name";
                break;
        }
        $query = "SELECT {$column} FROM {$table_name} WHERE {$id} = ?";
        $this->setStatement($query);
        $this->statement->execute([$row_id]);
        return $this->statement->fetchColumn();
    }
}
