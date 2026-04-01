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
        $this->setStatement("SELECT permission FROM permissions WHERE role_id = ?");
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
                    $placeholders = array_map(fn() => "(?, ?)", $permissions);
                    $sql .= implode(", ", $placeholders);
                    foreach ($permissions as $permission) {
                        $values[] = $role_id;
                        $values[] = $permission;
                    }
                    $this->setStatement($sql);
                    $this->statement->execute($values);
                }
                $this->connection->commit();
                return $role_id;
            } else {
                throw new Exception("Error in creating role.");
            }
        } catch (Exception $e) {
            $this->connection->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function update_role_details($field, $value, $id)
    {
        $this->setStatement("UPDATE user_roles SET {$field} = ? WHERE ID = ?");
        return $this->statement->execute([$value, $id]);
    }
    public function update_role_permissions(array $permissions, $role_id)
    {
        $this->connection->beginTransaction();

        try {
            $this->setStatement("DELETE FROM permissions WHERE role_id = ?");
            $this->statement->execute([$role_id]);

            $this->setStatement("INSERT INTO permissions (role_id, permission) VALUES (?, ?)");

            foreach ($permissions as $permission) {
                $this->statement->execute([$role_id, $permission]);
            }

            $this->connection->commit();
            return true;

        } catch (Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}