<?php

function process_file($upload_dir = '/uploads/profile_pictures/', $file_key = 'file')
{
    $upload_dir = __DIR__ . '/..' . $upload_dir;
    $max_file_size = 2 * 1024 * 1024; // 5MB
    try {

        // Ensure upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Validate request
        if (!isset($_FILES[$file_key])) {
            throw new Exception("File not found");
        }
        $file = $_FILES[$file_key];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Upload error code: ' . $file['error']);
        }

        // Validate file size
        if ($file['size'] > $max_file_size) {
            throw new Exception("File too large.");
        }

        // Generate safe filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        // Destination path
        $destination = $upload_dir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new Exception("Failed to save file.");
        }

        return $filename;
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['error' => $e->getMessage()]);
        exit;
    }
}

function generate_password($length = 12) {
    $bytes = random_bytes($length);
    return substr(bin2hex($bytes), 0, $length);
}