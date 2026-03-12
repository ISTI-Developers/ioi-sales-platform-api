<?php

require_once __DIR__ . '/../controller/user.controller.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($request, PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

try {
    $con = new UserController();

    switch ($method) {

        case 'GET':
            if (count($uri) === 2) {
                $result = $con->get_users();
                echo json_encode($result);
            }
            break;

        case 'POST':

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