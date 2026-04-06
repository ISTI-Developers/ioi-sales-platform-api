<?php

require_once __DIR__ . '/../controller/activities.controller.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($request, PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));
$count = count($uri);

try {
    require_once __DIR__ . '/../middleware/auth.middleware.php';
    // $user = authMiddleware();

    $con = new ActivitiesController();

    switch ($method) {
        case 'GET':
            if ($count === 2) {
                if (!isset($_GET['week'])) {
                    throw new Exception("Week not set");
                }
                $activities = $con->get_activities($_GET['week']);

                foreach ($activities as $activity) {
                    if ($activity->tags !== null) {
                        $activity->tags = json_decode($activity->tags);
                    }
                    if ($activity->date_submitted) {
                        $activity->date_submitted = date('c', strtotime($activity->date_submitted));
                    }
                }
                echo json_encode($activities);
                break;
            }
            if ($count === 3) {
                if ($uri[2] === "projects") {
                    //TODO: get all projects
                    break;
                }

                $ID = $uri[2];
                $activities = $con->get_client_activities($ID);

                foreach ($activities as $activity) {
                    if ($activity->tags !== null) {
                        $activity->tags = json_decode($activity->tags);
                    }
                    $activity->date_submitted = date('c', strtotime($activity->date_submitted));
                    $activity->date_modified = date('c', strtotime($activity->date_modified));
                }
                echo json_encode($activities);
                break;
            }
            if ($count === 4) {
                $ID = $uri[3];

                $projects = $con->get_client_projects($ID);

                foreach ($projects as $project) {
                    $updates = $con->get_project_updates($project->ID);
                    if ($updates) {
                        $project->update = $updates;
                    }
                    $project->created_at = date('c', strtotime($project->created_at));
                    $project->modified_at = date('c', strtotime($project->modified_at));
                }
                echo json_encode($projects);
                break;
            }
            break;

        case 'POST':
            $data = $_POST['data'];
            if (!isset($data)) {
                throw new Exception("Data is missing.");
            }

            $data = json_decode($data);

            if ($count === 3) {
                if ($uri[2] === "projects") {
                    echo 'POST';
                    break;
                }
            }
            $result = $con->add_activity($data->activity, $data->userID, $data->teamID, $data->tags, $data->clientID, $data->stage);
            $log = [
                "account_id" => $user['ID'],
                "account_name" => $user['first_name'] . ' ' . $user['last_name'],
                "action" => "CREATE",
                "module" => "clients",
                "entity_type" => "activity",
                "entity_id" => $data->clientID,
                "new_values" => [
                    "id" => $result,
                    "value" => $data->activity,
                ],
            ];
            $con->auditLog($log);
            echo json_encode([
                "success" => $result,
                "message" => "Activity recorded."
            ]);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), false);

            if (!$data) {
                throw new Exception("Invalid request.");
            }



            break;

        case 'DELETE':
            break;
    }
} catch (Exception $e) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => $e->getMessage()]);
}
