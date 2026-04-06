<?php
require_once __DIR__ . "/controller.php";
require_once __DIR__ . "/../config/jwt.php";
class AuthController extends Controller
{
    public function login($username, $password, $remember_me = false): array
    {

        // VERIFY USERNAME EXISTENCE
        $this->setStatement("SELECT * FROM user_accounts WHERE username = :username  OR email_address = :username LIMIT 1; ");
        $this->statement->execute([":username" => $username]);

        if ($this->statement->rowCount() === 0) {
            throw new Exception("Account not found.");
        }

        $account = $this->statement->fetch();

        if ($account->status_id === 2 || $account->status_id === 5) {
            throw new Exception("Your account has been terminated. Please contact the IT Administrator.");
        }

        if (!password_verify($password, $account->password)) {
            throw new Exception("Username or Password is incorrect.");
        }

        $jwt = new JWTHandler();

        $this->setStatement("SELECT ua.ID, ua.username, ua.email_address, ua.role_id, ui.first_name, ui.middle_name, ui.last_name, ui.image, ui.team_id, ua.status_id FROM user_accounts ua JOIN user_information ui ON ua.ID = ui.account_id WHERE ua.ID = ?");
        $this->statement->execute([$account->ID]);
        $user = $this->statement->fetch(PDO::FETCH_ASSOC);

        $token = $jwt->generate($user, $remember_me);
        $user['token'] = $token;
        return $user;
        // return [...$user, "token" => $token];
    }

    public function validate($token)
    {
        $jwt = new JWTHandler();

        $data = $jwt->verify($token);

        if (!$data) {
            throw new Exception("Session expired.");
        }

        $this->setStatement("UPDATE user_accounts SET last_login = NOW() WHERE ID = ?");
        $this->statement->execute([$data['ID']]);

        return $data;
    }
}