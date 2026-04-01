<?php
require_once __DIR__ . "/controller.php";

class ClientsController extends Controller
{
    public function get_clients($full = false)
    {
        $query = $full ? "SELECT 
                    c.ID, c.name, c.brand, 
                    cm.name as industry, 
                    cmt.name as type, 
                    cms.name as source, 
                    act.team_name as team, 
                    cmst.name as status, 
                    cmsg.name as stage,
                    c.notes, c.created_at, c.modified_at 
                  FROM clients c 
                  JOIN client_meta cm ON c.industry = cm.ID 
                  JOIN client_meta cmst ON c.status = cmst.ID 
                  JOIN client_meta cmt ON c.type = cmt.ID
                  JOIN client_meta cms ON c.source = cms.ID
                  JOIN client_meta cmsg ON c.stage = cmsg.ID
                  LEFT JOIN account_teams act ON c.team_id = act.ID
                  WHERE cmst.name <> 'DELETED';" :
            "SELECT 
                    c.ID, c.name, c.brand, 
                    cm.name as industry, 
                    act.team_name as team, 
                    cmst.name as status, 
                    cmsg.name as stage,
                    c.notes, c.created_at, c.modified_at 
                  FROM clients c 
                  JOIN client_meta cm ON c.industry = cm.ID 
                  JOIN client_meta cmst ON c.status = cmst.ID 
                  JOIN client_meta cmsg ON c.stage = cmsg.ID
                  LEFT JOIN account_teams act ON c.team_id = act.ID
                  WHERE cmst.name <> 'DELETED'";

        $this->setStatement($query);
        $this->statement->execute();
        return $this->statement->fetchAll();
    }
    public function get_client($id)
    {
        $query = "SELECT 
                    c.ID, c.name, c.brand, 
                    cm.name as industry, 
                    cmt.name as type, 
                    cms.name as source, 
                    act.team_name as team, 
                    cmst.name as status, 
                    cmsg.name as stage,
                    c.notes, c.created_at, c.modified_at 
                  FROM clients c 
                  JOIN client_meta cm ON c.industry = cm.ID 
                  JOIN client_meta cmst ON c.status = cmst.ID 
                  JOIN client_meta cmt ON c.type = cmt.ID
                  JOIN client_meta cms ON c.source = cms.ID
                  JOIN client_meta cmsg ON c.stage = cmsg.ID
                  LEFT JOIN account_teams act ON c.team_id = act.ID
                  WHERE cmst.name <> 30 AND c.ID = ?;";

        $this->setStatement($query);
        $this->statement->execute([$id]);
        return $this->statement->fetch();
    }
    public function get_client_accounts($clientID)
    {
        $this->setStatement("SELECT CONCAT(ui.first_name,' ', ui.last_name) as 'user', ce.type, ui.image FROM client_executives ce JOIN user_information ui ON ce.account_id = ui.account_id WHERE ce.client_id = ?");
        $this->statement->execute([$clientID]);
        return $this->statement->fetchAll();
    }
    public function get_client_contact($clientID)
    {
        $this->setStatement("SELECT * FROM client_contact WHERE client_id = ?");
        $this->statement->execute([$clientID]);
        return $this->statement->fetchAll();
    }
    public function get_client_services($clientID)
    {
        $this->setStatement("SELECT s.name as service, s.category as category, ss.name as sub_service, ssc.name as sub_service_category FROM client_services cs JOIN services s ON cs.service_id = s.ID LEFT JOIN sub_services ss ON cs.sub_service_id = ss.ID LEFT JOIN sub_service_category ssc ON cs.sub_service_category_id = ssc.ID WHERE cs.client_id = ?");
        $this->statement->execute([$clientID]);
        return $this->statement->fetchAll();
    }

    public function get_client_logs($clientID)
    {
        $this->setStatement("SELECT al.*, ui.image FROM audit_logs al JOIN user_information ui ON al.account_id = ui.account_id WHERE al.module = 'clients' AND al.entity_id = ?");
        $this->statement->execute([$clientID]);
        return $this->statement->fetchAll();
    }

    public function get_client_meta($category)
    {
        $this->setStatement("SELECT * FROM client_meta WHERE category = ? AND status = 1");
        $this->statement->execute([$category]);
        return $this->statement->fetchAll();
    }
    public function get_services()
    {
        $this->setStatement("SELECT * FROM services");
        $this->statement->execute();
        return $this->statement->fetchAll();
    }

    public function add_client($data)
    {
        try {
            $this->connection->beginTransaction();
            $this->setStatement("INSERT INTO clients (name, industry, brand, type, source, team_id, status, stage, notes) VALUES (?,?,?,?,?,?,?,?,?)");
            if ($this->statement->execute([$data->name, $data->industry, $data->brand, $data->type, $data->source, $data->team_id ?? null, $data->status, $data->stage, $data->notes])) {
                $client_id = $this->connection->lastInsertId();
                foreach ($data->services as $service) {
                    $this->setStatement("INSERT INTO client_services (client_id, service_id) VALUES (?,?)");
                    $this->statement->execute([$client_id, $service]);
                }

                foreach ($data->accounts as $account) {
                    $this->setStatement("INSERT INTO client_executives (client_id, account_id, type) VALUES (?,?, 'sales')");
                    $this->statement->execute([$client_id, $account]);
                }
                foreach ($data->contact as $contact) {
                    $this->setStatement("INSERT INTO client_contact (name, designation, contact_number, email_address, address, client_id) VALUES (?,?,?,?,?,?)");
                    $this->statement->execute([$contact->name, $contact->designation, $contact->contact_number, $contact->email_address, $contact->address, $client_id]);
                }
                $this->connection->commit();
                
                return $client_id;
            } else {
                throw new Exception("No changes has been made.");
            }
        } catch (PDOException $e) {
            $this->connection->rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function update_client_stage($client_id, $stage)
    {
        $this->setStatement("UPDATE clients SET stage = ? WHERE ID = ?");
        $this->statement->execute([$stage, $client_id]);
        return $this->statement->rowCount() > 0;
    }
}