<?php

require_once __DIR__ . '/../controller/user.controller.php';
require_once __DIR__ . '/../lib/helper.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/email.php';

$method = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'];
$uri = parse_url($request, PHP_URL_PATH);
$params = explode('/', trim($uri, '/'));
$count = count($params);
try {
    $con = new UserController();

    switch ($method) {
        case 'GET':
            if ($count === 2) {
                $result = $con->get_users();
                echo json_encode($result);
                break;
            }
            break;
        case 'POST':
            // add user 
            $data = $_POST['user'] ?? null;
            $filename = null;

            if (!$data) {
                throw new Exception("User data is missing");
            }

            if (isset($_FILES['image'])) {
                $filename = process_file(file_key: "image");
            }
            $data = json_decode($data);
            $password = generate_password();
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $result = $con->add_user($data->username, $password_hash, $data->email_address, $data->role_id, $data->first_name, $data->middle_name, $data->last_name, $filename);

            if ($result) {
                $template = file_get_contents(__DIR__ . '/../templates/onboarding.html');
                $template = str_replace("{{name}}", $data->first_name, $template);
                $template = str_replace("{{username}}", $data->username, $template);
                $template = str_replace("{{password}}", $password, $template);
                $template = str_replace("{{link}}", DEV_SELF, $template);

                $mail = new EmailUtils();
                if ($response = $mail->sendMail("Your Innovation Sales Platform Account Has Been Created", $template, $data->email_address, ucfirst($data->first_name) . " " . ucfirst($data->last_name))) {
                    unset($data);
                    echo json_encode([
                        "success" => $response,
                        "message" => "Account created. Login credentials will be sent to their email address.",
                        "id" => $result,
                    ]);
                    break;
                }
            }
            break;

        case 'PUT':
            // users/profile - PUT VIA POST
            if ($count < 3) {
                throw new Exception("Invalid parameters sent.");
            }
            if ($params[2] === "profile") {

                $data = $_POST['user'] ?? null;
                $filename = null;

                if (!$data) {
                    throw new Exception("User data is missing");
                }

                if (isset($_FILES['image'])) {
                    $filename = process_file(file_key: "image");
                }
                $data = json_decode($data);
                $result = $con->update_user($data->username, $data->email_address, $data->first_name, $data->middle_name, $data->last_name, $filename, $data->ID);

                echo json_encode([
                    "success" => $result,
                    "message" => "User details has been updated successfully."
                ]);
                break;
            } else {
                $data = json_decode(file_get_contents('php://input'), false);
                if (!$params[2]) {
                    throw new Exception("User data is missing");
                }
                $result = $con->update_user_detail($data->detail, $data->value, $params[2], $data->table);

                echo json_encode([
                    "success" => $result,
                    "message" => "User details has been updated successfully."
                ]);
                break;
            }
            break;
        case 'DELETE':
            break;
    }
} catch (Exception $e) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => $e->getMessage()]);
}