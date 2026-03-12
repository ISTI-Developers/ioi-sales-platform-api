<?php

require_once __DIR__ . '/../controller/roles.controller.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($request, PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));
$count = count($uri);

try {
    $con = new RolesController();

    switch ($method) {

        case 'GET':
            if ($count === 2) {
                $result = $con->get_roles();
                echo json_encode($result);
            } else if ($count >= 3) {
                switch ($uri[2]) {
                    case "permissions";
                        $ID = $uri[3];

                        $permissions = $con->get_role_permissions($ID);
                        echo json_encode($permissions);
                        break;
                    default:
                        $ID = $uri[2];
                        $role = $con->get_role($ID);
                        $permissions = $con->get_role_permissions($role->ID);

                        echo json_encode([
                            ...get_object_vars($role),
                            "permissions" => $permissions
                        ]);
                }
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