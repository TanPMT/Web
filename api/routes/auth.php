<?php

$method = $_SERVER['REQUEST_METHOD'];
$action = $parts[2] ?? '';

switch ($action) {
    case 'login':
        if ($method === 'POST') {
            handleLogin();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
    
    case 'signup':
        if ($method === 'POST') {
            handleSignup();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
    
    case 'verify':
        if ($method === 'GET') {
            handleVerify();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
    
    default:
        Response::notFound('Auth endpoint not found');
}

function handleLogin() {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['username']) || !isset($data['password'])) {
        Response::error('Username and password are required');
    }

    $username = trim($data['username']);
    $password = $data['password'];

    try {
        $database = new Database();
        $db = $database->connect();

        $query = "SELECT id, username, password FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch();

        if (!$user) {
            Response::error('Invalid username or password', 401);
        }

        if (!password_verify($password, $user['password'])) {
            Response::error('Invalid username or password', 401);
        }

        $auth = new Auth();
        $token = $auth->generateToken($user['id'], $user['username']);

        Response::success([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username']
            ]
        ], 'Login successful');

    } catch (Exception $e) {
        Response::error('Login failed: ' . $e->getMessage(), 500);
    }
}

function handleSignup() {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!isset($data['username']) || !isset($data['password'])) {
        Response::error('Username and password are required');
    }

    $username = trim($data['username']);
    $password = $data['password'];
    $email = isset($data['email']) ? trim($data['email']) : null;

    // Validation
    if (strlen($username) < 3) {
        Response::error('Username must be at least 3 characters long');
    }

    if (strlen($password) < 6) {
        Response::error('Password must be at least 6 characters long');
    }

    try {
        $database = new Database();
        $db = $database->connect();

        // Check if username already exists
        $query = "SELECT id FROM users WHERE username = :username";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        if ($stmt->fetch()) {
            Response::error('Username already exists', 409);
        }

        // Create new user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (username, password, email) VALUES (:username, :password, :email)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':email', $email);
        
        if ($stmt->execute()) {
            $user_id = $db->lastInsertId();
            
            $auth = new Auth();
            $token = $auth->generateToken($user_id, $username);

            Response::success([
                'token' => $token,
                'user' => [
                    'id' => $user_id,
                    'username' => $username
                ]
            ], 'Account created successfully', 201);
        } else {
            Response::error('Failed to create account', 500);
        }

    } catch (Exception $e) {
        Response::error('Signup failed: ' . $e->getMessage(), 500);
    }
}

function handleVerify() {
    $auth = new Auth();
    $user = $auth->getCurrentUser();

    if (!$user) {
        Response::unauthorized('Invalid or expired token');
    }

    Response::success([
        'user' => [
            'id' => $user->user_id,
            'username' => $user->username
        ]
    ], 'Token is valid');
}
