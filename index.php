<?php

header('Access-Control-Allow-Origin: http://localhost:1003');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Content-Length, Authorization, X-User-Id');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Send the appropriate headers for the preflight response
    header('HTTP/1.1 200 OK');
    exit();
}

$request = $_SERVER['REQUEST_URI'];

$uri = parse_url($request, PHP_URL_PATH);

$uri = explode('/', trim($uri, '/'));

switch ($uri[1]) {

    case 'users':
        require __DIR__ . '/routes/users.php';
        break;

    case 'auth':
        require __DIR__ . '/routes/auth.php';
        break;

    case 'roles':
        require __DIR__ . '/routes/roles.php';
        break;

    default:
        http_response_code(404);
        echo json_encode(["message" => "Route not found", "route" => $uri]);
}