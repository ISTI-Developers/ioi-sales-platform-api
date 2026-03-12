<?php
require_once __DIR__ . "/controller.php";
class UserController extends Controller
{
    public function get_users()
    {
        $this->setStatement("SELECT ua.ID, ua.username, ua.email_address, ua.role_id, ui.first_name, ui.middle_name, ui.last_name, ui.image, ui.sales_unit_id, ua.status_id FROM user_accounts ua JOIN user_information ui ON ua.ID = ui.account_id");
        $this->statement->execute();
        return $this->statement->fetchAll();
    }
}