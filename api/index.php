<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Response.php';

// Parse request URI
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = dirname($_SERVER['SCRIPT_NAME']);
$path = str_replace($script_name, '', $request_uri);
$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');

// Route the request
$parts = explode('/', $path);
$endpoint = $parts[1] ?? '';

switch ($endpoint) {
    case 'auth':
        require_once __DIR__ . '/routes/auth.php';
        break;
    case 'notes':
        require_once __DIR__ . '/routes/notes.php';
        break;
    default:
        Response::notFound('API endpoint not found');
}
