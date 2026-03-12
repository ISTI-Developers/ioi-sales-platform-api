<?php

require_once __DIR__ . '/../config/jwt.php';

function authMiddleware()
{
    $headers = getallheaders();

    if (!isset($headers["Authorization"])) {
        http_response_code(401);
        echo json_encode(["error" => "Missing Authorization header"]);
        exit;
    }

    if (!preg_match('/Bearer\s(\S+)/', $headers["Authorization"], $matches)) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid Authorization format"]);
        exit;
    }

    $token = $matches[1];

    $jwt = new JWTHandler();
    $payload = $jwt->verify($token);

    if (!$payload) {
        http_response_code(401);
        echo json_encode(["error" => "Invalid or expired token"]);
        exit;
    }

    return $payload;
}