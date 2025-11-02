<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private $secret_key;
    private $issuer;
    private $audience;
    private $expiration_time = 3600; // 1 hour

    public function __construct() {
        $this->secret_key = getenv('JWT_SECRET') ?: 'your-secret-key-change-this-in-production';
        $this->issuer = "notes-app";
        $this->audience = "notes-app-users";
    }

    public function generateToken($user_id, $username) {
        $issued_at = time();
        $expiration = $issued_at + $this->expiration_time;

        $payload = array(
            "iss" => $this->issuer,
            "aud" => $this->audience,
            "iat" => $issued_at,
            "exp" => $expiration,
            "data" => array(
                "user_id" => $user_id,
                "username" => $username
            )
        );

        return JWT::encode($payload, $this->secret_key, 'HS256');
    }

    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secret_key, 'HS256'));
            return $decoded->data;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getTokenFromHeaders() {
        $headers = getallheaders();
        
        if (isset($headers['Authorization'])) {
            $auth_header = $headers['Authorization'];
            $token = str_replace('Bearer ', '', $auth_header);
            return $token;
        }
        
        return null;
    }

    public function getCurrentUser() {
        $token = $this->getTokenFromHeaders();
        
        if (!$token) {
            return false;
        }

        return $this->validateToken($token);
    }
}
