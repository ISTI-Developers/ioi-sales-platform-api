<?php
require_once __DIR__ . "/controller.php";
class RolesController extends Controller
{
    public function get_roles()
    {
        $this->setStatement("SELECT * FROM user_roles WHERE status <> 5");
        $this->statement->execute();
        return $this->statement->fetchAll();
    }

    public function get_role($role_id)
    {
        $this->setStatement("SELECT * FROM user_roles WHERE ID = ? AND status <> 5");
        $this->statement->execute([$role_id]);
        return $this->statement->fetch();
    }

    public function get_role_permissions($role_id)
    {
        $this->setStatement("SELECT * FROM permissions WHERE ID = ?");
        $this->statement->execute([$role_id]);
        return $this->statement->fetchAll();
    }

    public function add_role($role, $description, array $permissions)
    {
        $this->connection->beginTransaction();
        try {
            $this->setStatement("INSERT INTO user_roles (name, description) VALUES (?,?)");
            $this->statement->execute([$role, $description]);
            if ($role_id = $this->connection->lastInsertId()) {
                if (count($permissions) > 0) {
                    $sql = "INSERT INTO permissions (role_id, permission) VALUES ";
                    $placeholders = array_map(fn() => `(?, ?)`, $permissions);
                    $sql .= implode(",", $placeholders);
                    $params = array_map(fn($item) => "({$role_id}, {$item})", $permissions);
                    $this->setStatement($sql);
                    $this->statement->execute($params);
                }
                $this->connection->commit();
            } else {
                throw new Exception("Error in creating role.");
            }
        } catch (Exception $e) {
            $this->connection->rollBack();
        }
    }

    public function update_role()
    {
        //TODO: do the update sql function
    }

    public function delete_role()
    {
        //TODO:
    }
}