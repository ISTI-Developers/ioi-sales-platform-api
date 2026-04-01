<?php
require_once __DIR__ . "/controller.php";

class ActivitiesController extends Controller
{
    public function get_activities()
    {
        $this->setStatement("SELECT a.ID, a.activity, CONCAT(ui.first_name,' ',ui.last_name) as user, ui.image, a.tags, cm.name as stage, a.date_modified, a.date_submitted FROM activities a 
        LEFT JOIN user_information ui ON a.user_id = ui.account_id 
        JOIN client_meta cm ON a.stage = cm.ID AND cm.category = 'stage'
        WHERE a.client_id = ? ORDER BY a.date_submitted DESC;");
        $this->statement->execute();
        return $this->statement->fetchAll();
    }

    public function get_client_activities($client_id)
    {
        $this->setStatement("SELECT a.ID, a.activity, CONCAT(ui.first_name,' ',ui.last_name) as user, ui.image, a.tags, cm.name as stage, a.date_modified, a.date_submitted FROM activities a 
        LEFT JOIN user_information ui ON a.user_id = ui.account_id 
        JOIN client_meta cm ON a.stage = cm.ID AND cm.category = 'stage'
        WHERE a.client_id = ? ORDER BY a.date_submitted DESC;");
        $this->statement->execute([$client_id]);
        return $this->statement->fetchAll();
    }


    public function get_activity_log()
    {

    }

    public function add_activity($activity, $user_id, $team_id, $tags, $client_id, $stage)
    {

        $this->setStatement("INSERT INTO activities (activity, user_id, team_id, client_id, tags, stage) VALUES (?,?,?,?,?,?)");
        $this->statement->execute([$activity, $user_id, $team_id, $client_id, $tags, $stage]);
        return $this->connection->lastInsertId();
    }

    public function get_projects()
    {

    }

    public function get_client_projects($client_id)
    {
        $this->setStatement("SELECT p.ID, p.name, p.contract_reference, p.created_at, p.modified_at, p.status FROM projects p JOIN clients c ON p.client_id = c.ID WHERE p.status <> 5 AND c.ID = ?;");
        $this->statement->execute([$client_id]);
        return $this->statement->fetchAll();
    }
    public function get_project_updates($project_id)
    {
        $this->setStatement("SELECT * FROM `updates` WHERE project_id = ? ORDER BY created_at DESC LIMIT 1;");
        $this->statement->execute([$project_id]);
        return $this->statement->fetch();
    }

}