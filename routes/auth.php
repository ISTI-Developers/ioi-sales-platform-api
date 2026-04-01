<?php

require_once __DIR__ . '/../controller/auth.controller.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($request, PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

try {
    $con = new AuthController();

    switch ($method) {
        case 'GET':
            echo password_hash("un1t3dn30n", PASSWORD_BCRYPT);
            break;

        case 'POST':
            //login here
            switch ($uri[2]) {
                case "login":
                    $username = $_POST['username'] ?? null;
                    $password = $_POST['password'] ?? null;

                    if (!$username || !$password) {
                        throw new Exception("Username and password is missing");
                    }

                    $user = $con->login($username, $password, $_POST['remember_me'] ?? false);

                    echo json_encode($user);
                    break;
                case "register":
                    break;
                case "me":
                    if (!$_POST['token']) {
                        throw new Exception("Token not found.");
                    }
                    $user = $con->validate($_POST['token']);

                    echo json_encode($user);
                    break;
                case "logout":
                    
                    break;

            }
            break;

        case 'PUT':
            break;

        case 'DELETE':
            break;
    }
} catch (Exception $e) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => $e->getMessage()]);
}