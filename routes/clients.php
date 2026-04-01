<?php

require_once __DIR__ . '/../controller/clients.controller.php';
// require_once __DIR__ . '/../controller/activities.controller.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($request, PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));
$count = count($uri);

try {
    require_once __DIR__ . '/../middleware/auth.middleware.php';
    $user = authMiddleware();

    $con = new ClientsController();
    switch ($method) {

        case 'GET':
            if ($count === 2) {
                $clients = $con->get_clients(($_GET['full'] ?? "1") === "0" ? false : true);
                foreach ($clients as $client) {
                    $accounts = $con->get_client_accounts($client->ID);
                    $services = $con->get_client_services($client->ID);

                    $client->accounts = $accounts;
                    $client->services = $services;
                    if (isset($_GET['full'])) {
                        $contact = $con->get_client_contact($client->ID);
                        $client->contact = $contact;
                    }
                    $client->created_at = date('c', strtotime($client->created_at));
                    $client->modified_at = date('c', strtotime($client->modified_at));
                }
                echo json_encode($clients);
                break;
            }
            if ($count === 3) {

                // /api/clients/services
                if ($uri[2] === "services") {
                    echo json_encode($con->get_services());
                    break;
                }

                $ID = $uri[2];
                $client = $con->get_client($ID);
                $accounts = $con->get_client_accounts($client->ID);
                $services = $con->get_client_services($client->ID);
                $contact = $con->get_client_contact($client->ID);

                $client->accounts = $accounts;
                $client->services = $services;
                $client->contact = $contact;
                $client->created_at = date('c', strtotime($client->created_at));
                $client->modified_at = date('c', strtotime($client->modified_at));
                echo json_encode($client);
                break;
            }

            // /api/clients/logs/1
            if ($uri[2] === "logs") {
                echo json_encode($con->get_client_logs($uri[3]));
                break;
            }

            // /api/clients/meta/:category
            if ($uri[2] === "meta") {
                echo json_encode($con->get_client_meta($uri[3]));
                break;
            }
            break;

        case 'POST':
            if (!isset($_POST['data'])) {
                throw new Exception("Data is missing");
            }

            $data = json_decode($_POST['data']);
            // api/clients
            if ($count === 2) {
                $result = $con->add_client($data);

                $log = [
                    "account_id" => $user['ID'],
                    "account_name" => $user['first_name'] . ' ' . $user['last_name'],
                    "action" => "CREATE",
                    "module" => "clients",
                    "entity_type" => "client",
                    "entity_id" => $result,
                    "new_values" => [
                        "id" => $result,
                        "value" => $data,
                    ],
                ];
                $con->auditLog($log);

                echo json_encode([
                    "success" => $result,
                    "message" => "Client has been added successfully.",
                    "data" => ["ID" => $result]
                ]);
                break;
            }
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), false);

            if (!$data) {
                throw new Exception("Invalid request.");
            }

            if ($count === 3) {
                $ID = $uri[2];
                if ($data->stage) {
                    $result = $con->update_client_stage($ID, $data->stage);

                    if (!$result) {
                        throw new Exception("Cannot update the client stage");
                    }
                    // $activity = "[{$data->previousStage}] to [{$data->newStage}]";
                    // $activities_con = new ActivitiesController();
                    // $tag = json_encode(["stage update"]);
                    // $result = $activities_con->add_activity($activity, $user['ID'], $user['team_id'] ?? null, $tag, $ID, $data->stage);

                    $log = [
                        "account_id" => $user['ID'],
                        "account_name" => $user['first_name'] . ' ' . $user['last_name'],
                        "action" => "UPDATE",
                        "module" => "clients",
                        "entity_type" => "stage",
                        "entity_id" => $ID,
                        "old_values" => [
                            "id" => $data->stage - 1,
                            "value" => $data->previousStage,
                        ],
                        "new_values" => [
                            "id" => $data->stage,
                            "value" => $data->newStage,
                        ],
                    ];
                    $con->auditLog($log);
                    echo json_encode([
                        "success" => $result,
                        "message" => "Client stage has been updated."
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