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
                        echo json_encode(array_map(fn($perm) => $perm->permission, $permissions));
                        break;
                    default:
                        $ID = $uri[2];
                        $role = $con->get_role($ID);
                        $permissions = $con->get_role_permissions($role->ID);

                        echo json_encode([
                            ...get_object_vars($role),
                            "permissions" => array_map(fn($perm) => $perm->permission, $permissions)
                        ]);
                        break;
                }
            }
            break;

        case 'POST':
            extract($_POST);

            if (!$name || !$description || !$permissions) {
                throw new Exception("Incomplete fields");
            }

            $result = $con->add_role($name, $description, json_decode($permissions, true));

            echo json_encode([
                "success" => $result,
                "message" => "New role created!",
                "data" => $result
            ]);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), false);

            if (!$data) {
                throw new Exception("Invalid request.");
            }

            if ($count === 3) {
                $role_id = $uri[2];

                if (isset($data->name)) {
                    $result = $con->update_role_details('name', $data->name, $role_id);
                    echo json_encode([
                        "success" => $result,
                        "message" => "Role name has been updated successfully."
                    ]);
                    break;
                }

                if (isset($data->description)) {
                    $result = $con->update_role_details('description', $data->description, $role_id);
                    echo json_encode([
                        "success" => $result,
                        "message" => "Role description has been updated successfully."
                    ]);
                    break;
                }
                if (isset($data->status)) {
                    $result = $con->update_role_details('status', $data->status, $role_id);
                    echo json_encode([
                        "success" => $result,
                        "message" => "Role status has been updated successfully."
                    ]);
                    break;
                }

                if (isset($data->permissions)) {
                    $permissions = $data->permissions;

                    $result = $con->update_role_permissions($permissions, $role_id);
                    echo json_encode([
                        "success" => $result,
                        "message" => "Role permissions has been updated successfully."
                    ]);
                    break;
                }


            }
            break;

        case 'DELETE':
            break;
    }
} catch (Exception $e) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => $e->getMessage()]);
}