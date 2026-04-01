<?php
require_once __DIR__ . "/controller.php";
class UserController extends Controller
{
    public function get_users()
    {
        $this->setStatement("SELECT ua.ID, ua.username, ua.email_address, ua.role_id, ui.first_name, ui.middle_name, ui.last_name, ui.image, ui.team_id, ua.status_id FROM user_accounts ua JOIN user_information ui ON ua.ID = ui.account_id");
        $this->statement->execute();
        return $this->statement->fetchAll();
    }
    public function get_user($ID)
    {
        $this->setStatement("SELECT ua.ID, ua.username, ua.email_address, ua.role_id, ui.first_name, ui.middle_name, ui.last_name, ui.image, ui.team_id, ua.status_id FROM user_accounts ua JOIN user_information ui ON ua.ID = ui.account_id WHERE ua.ID = ?");
        $this->statement->execute([$ID]);
        return $this->statement->fetch();
    }
    public function add_user($username, $password, $email_address, $role_id, $first_name, $middle_name, $last_name, $filename = null)
    {
        try {
            $this->connection->beginTransaction();
            $this->setStatement("INSERT INTO user_accounts (username, password, email_address, role_id, status_id) VALUES (?,?,?,?,?)");
            $this->statement->execute([$username, $password, $email_address, $role_id, 3]);
            if ($account_id = $this->connection->lastInsertId()) {
                $this->setStatement("INSERT INTO user_information (first_name, middle_name, last_name, account_id, image) VALUES (?,?,?,?, ?)");
                if ($this->statement->execute([$first_name, $middle_name, $last_name, $account_id, $filename])) {
                    $this->connection->commit();
                    return $account_id;
                }
            }
        } catch (PDOException $e) {
            $this->connection->rollBack();
            throw new Exception($e->getMessage());
        }
    }
    public function update_user($username, $email_address, $first_name, $middle_name, $last_name, $image = null, $ID)
    {
        try {
            $this->connection->beginTransaction();
            $this->setStatement("UPDATE user_information SET first_name = ?, middle_name = ?, last_name = ?, image = ? WHERE account_id = ?");
            if ($this->statement->execute([$first_name, $middle_name, $last_name, $image, $ID])) {
                $this->setStatement("UPDATE user_accounts SET username = ?, email_address = ?, modified_at = NOW() WHERE ID = ?");
                if ($this->statement->execute([$username, $email_address, $ID])) {
                    $this->connection->commit();
                    return true;
                }
            } else {
                throw new Exception("No changes has been made.");
            }
        } catch (PDOException $e) {
            $this->connection->rollBack();
            throw new Exception($e->getMessage());
        }

    }
    public function update_user_detail($column, $value, $ID, $table = 'user_accounts')
    {
        $this->setStatement("UPDATE {$table} SET {$column} =  ? WHERE ID = ?");
        $this->statement->execute([$value, $ID]);
        return $this->statement->rowCount() > 0;
    }
}